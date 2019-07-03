<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\ConfigInterface;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\PusherInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use Iterator;
use Solarium\Client;

/**
 * Class Pusher
 * @package G4NReact\MsCatalogSolr
 */
class Pusher implements PusherInterface
{
    /**
     * @var SolrConfig
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * Pusher constructor
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = new SolrConfig($config);
        $this->client = new Client($this->config->getConfigArray());
    }

    /**
     * @param Iterator|PullerInterface $documents
     * @return ResponseInterface
     */
    public function push($documents): ResponseInterface
    {
        $pageSize = $this->config->getPageSize();
        $response = new Response();
        if ($documents) {
            try {
                $update = $this->client->createUpdate();

                // @ToDo: delete index before reindexing if setting == true -> set delete query eg '*:*' or 'product_type:"category"'
                $this->clearIndex();

                $i = 0;
                /** @var Document $document */
                foreach ($documents as $document) {
                    if ($i < $pageSize) {
                        $doc = $update->createDocument();

                        $doc->id = (string)$document->getUniqueId();
                        $doc->object_id = (int)$document->getObjectId();
                        $doc->object_type = (string)$document->getObjectType();

                        /** @var Document\Field $field */
                        foreach ($document->getData() as $field) {
                            if (!$field->getValue()) {
                                continue;
                            }
                            $solrFieldName = $field->getName()
                                . (Helper::$mapFieldType[$field->getType()] ?? Helper::SOLR_FIELD_TYPE_DEFAULT)
                                . ($field->getIndexable() ? '' : Helper::SOLR_NOT_INDEXABLE_MARK)
                                . ($field->getMultiValued() ? Helper::SOLR_MULTI_VALUE_MARK : '');

                            $solrFieldValue = $field->getValue();
                            if (isset(Helper::$mapFieldType[$field->getType()]) && Helper::$mapFieldType[$field->getType()] === Helper::SOLR_FIELD_TYPE_DATETIME) {
                                $solrFieldValue = date(Helper::SOLR_DATETIME_FORMAT, strtotime($field->getValue()));
                            }

                            $doc->{$solrFieldName} = $solrFieldValue;
                        }

                        $i++;
                        $update->addDocument($doc);
                    } else {
                        $update->addCommit();
                        $result = $this->client->update($update);
                        $i = 0;
                        $update = $this->client->createUpdate();
                    }
                }

                if ($i > 0) {
                    $update->addCommit();
                    $result = $this->client->update($update);
                }

                $response->setStatusCode($result->getResponse()->getStatusCode())
                    ->setStatusMessage($result->getResponse()->getStatusMessage());
            } catch (Exception $e) {
                echo $e->getMessage();
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
}
