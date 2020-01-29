<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\ConfigInterface;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\PusherInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Client\Client as MsCatalogSolrClient;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use Iterator;
use Solarium\Client as SolariumClient;

/**
 * Class Pusher
 * @package G4NReact\MsCatalogSolr
 */
class Pusher implements PusherInterface
{
    const MAX_TO_DELETE = 1000;

    /**
     * @var SolrConfig
     */
    private $config;

    /**
     * @var SolariumClient
     */
    private $client;

    /**
     * Pusher constructor
     *
     * @param ConfigInterface $config
     * @param SolariumClient $client
     */
    public function __construct(ConfigInterface $config, SolariumClient $client)
    {
        $this->config = $config;
        $this->client = $client;
        $endpoint = $client->getEndpoint();
        $timeout = $config->getPusherTimeout() ?: MsCatalogSolrClient::DEFAULT_PUSHER_TIMEOUT;
        $endpoint->setTimeout($timeout);
    }

    /**
     * @param Iterator|PullerInterface $documents
     *
     * @return ResponseInterface
     */
    public function push(PullerInterface $documents): ResponseInterface
    {
        $activeIds = [];
        $deleteFromSolr = false;
        $pageSize = $this->config->getPusherPageSize();
        $response = new Response();
        if ($documents) {
            try {
                $update = $this->client->createUpdate();

                if ($documents->getIds()) {
                    if (count($documents->getIds()) < self::MAX_TO_DELETE) {
                        $deleteFromSolr = true;
                    }
                    $this->addLog('Dokumenty odśeieżane w solrze', ['object_type' => $documents->getType(), 'count' => count($documents->getIds()), 'ids' => $documents->getIds()]);
                }

                $i = 0;
                $counter = 0;
                /** @var Document $document */
                foreach ($documents as $document) {
                    $profilerStart = microtime(true);
                    if (($counter === 0) || ($counter % 100 === 0)) {
                        $start = microtime(true);
                    }

                    if ($documents->getIds()) {
                        $deleteFromSolr = true;
                    }

//                    echo $i . ' - ' . $counter . PHP_EOL;

                    if (!$document->getUniqueId()) {
                        continue;
                    }

                    $doc = $update->createDocument();

                    $doc->solr_id = (string)$document->getUniqueId();
                    $doc->id = (int)$document->getObjectId();
                    $doc->object_type = (string)$document->getObjectType();
                    $doc->solr_updated_at_i = time();

                    /** @var Document\Field $field */
                    foreach ($document->getData() as $field) {
                        $solrFieldName = FieldHelper::getFieldName($field);
                        $solrFieldValue = $field->getValue();
                        if (
                            isset(FieldHelper::$mapFieldType[$field->getType()]) &&
                            FieldHelper::$mapFieldType[$field->getType()] === FieldHelper::SOLR_FIELD_TYPE_DATETIME &&
                            $field->getValue()
                        ) {
                            $solrFieldValue = date(FieldHelper::SOLR_DATETIME_FORMAT, strtotime($field->getValue()));
                        }

                        $doc->{$solrFieldName} = $solrFieldValue;
                    }

                    if ($doc->id) {
                        $i++;
                        $update->addDocument($doc);

                        if ($documents->getIds()) {
                            $activeIds[] = $doc->id;
                        }
                    }

                    if (++$counter % 100 === 0) {
//                        echo (round(microtime(true) - $start, 4)) . 's | ' . $counter . PHP_EOL;
                    }
                    \G4NReact\MsCatalog\Profiler::increaseTimer('create solarium documents', (microtime(true) - $profilerStart));

                    if ($i >= $pageSize) {
                        $update->addCommit();
                        $clientUpdateStart = microtime(true);
                        $result = $this->client->update($update);
                        \G4NReact\MsCatalog\Profiler::increaseTimer('send update to solarium', (microtime(true) - $clientUpdateStart));
                        $i = 0;
                        $update = $this->client->createUpdate();
                    }
                }
                if ($i > 0) {
                    $update->addCommit();
                }

                $start = microtime(true);
                $result = $this->client->update($update);
                \G4NReact\MsCatalog\Profiler::increaseTimer('send update to solarium', (microtime(true) - $start));

                $response->setStatusCode($result->getResponse()->getStatusCode())
                    ->setStatusMessage($result->getResponse()->getStatusMessage());
            } catch (Exception $e) {
                $deleteFromSolr = false;
                $this->addLogException('Problem odświeżenia dokumentu w solrze', ['exception' => $e]);
                echo $e->getMessage();
            }

            if ($this->config->getPusherRemoveMissingObjects()) {
                try {
                    if (!$update) {
                        $update = $this->client->createUpdate();
                    }

                    if ($documents->getIds() && $deleteFromSolr) {
                        $toDeleteIds = array_diff($documents->getIds(), $activeIds);
                        $documents->setToDeleteIds($toDeleteIds);
                        $solrIds = [];
                        foreach ($toDeleteIds as $objId) {
                            $solrIds[] = $documents->createUniqueId($objId);
                        }

                        if (!empty($solrIds)) {
                            $this->addLog('Dokumenty usuwane z solra', ['object_type' => $documents->getType(), 'count' => count($solrIds), 'solr_ids' => $solrIds]);

                            $update
                                ->addDeleteByIds($solrIds)
                                ->addCommit();

                            $this->client->update($update);
                        }
                    }
                } catch (Exception $e) {
                    $this->addLogException('Problem z usuwaniem dokumentu z solra', ['exception' => $e]);
                    echo $e->getMessage();
                }
            }
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return [
            'endpoint' => [
                'localhost' => $this->config->getConnectionConfigArray()
            ]
        ];
    }

    /**
     * @param string|null $deleteQuery Eg. '*:*' or 'object_type:"product"'
     */
    public function clearIndex($deleteQuery = null)
    {
        $update = $this->client->createUpdate();

        $query = $deleteQuery ?: '*:*';

        $update->addDeleteQuery($query);
        $update->addCommit();

        $result = $this->client->update($update);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function addLog($message, $data = [])
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/solr_pusher.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Log details: ' . $message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function addLogException($message, $data = [])
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/solr_pusher_error.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->crit('Log details: ' . $message, $data);
    }
}
