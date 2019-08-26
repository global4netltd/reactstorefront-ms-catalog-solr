<?php

namespace G4NReact\MsCatalogSolr\Client;

use Exception;
use G4NReact\MsCatalog\Client\ClientInterface;
use G4NReact\MsCatalog\Config;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\PusherInterface;
use G4NReact\MsCatalog\QueryInterface as MsCatalogQueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use G4NReact\MsCatalogSolr\FieldHelper;
use G4NReact\MsCatalogSolr\Puller;
use G4NReact\MsCatalogSolr\Pusher;
use G4NReact\MsCatalogSolr\Query as MsCatalogSolrQuery;
use G4NReact\MsCatalogSolr\Response;
use Solarium\Client as SolariumClient;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Solarium\Core\Query\Result\ResultInterface as SolariumResultInterface;
use Solarium\Exception\UnexpectedValueException;
use Solarium\QueryType\Select\Query\Query;

/**
 * Class Client
 * @package G4NReact\MsCatalogSolr\Client
 */
class Client implements ClientInterface
{
    /**
     * @var int
     */
    const DEFAULT_PUSHER_TIMEOUT = 10;

    /**
     * @var int
     */
    const DEFAULT_PULLER_TIMEOUT = 5;

    /**
     * @var SolariumClient
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SolrConfig
     */
    protected $solrConfig;

    /**
     * @var
     */
    protected $solariumHelper;

    /**
     * Client constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->solrConfig = new SolrConfig($config);
        $this->client = new SolariumClient($this->solrConfig->getConfigArray());
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->client->getEndpoint()->setTimeout($timeout);
    }

    /**
     * Set push operation timeout
     */
    public function setPusherTimeout()
    {
        $timeout = $this->config->getPusherTimeout() ?: self::DEFAULT_PUSHER_TIMEOUT;
        $this->setTimeout($timeout);
    }

    /**
     * Set pull operation timeout
     */
    public function setPullerTimeout()
    {
        $timeout = $this->config->getPullerTimeout() ?: self::DEFAULT_PULLER_TIMEOUT;
        $this->setTimeout($timeout);
    }

    /**
     * @param array $fields
     *
     * @return ResponseInterface
     */
    public function add(array $fields): ResponseInterface
    {
        $this->setPusherTimeout();

        $update = $this->client->createUpdate();
        $document = $update->createDocument($fields);
        $update
            ->addDocument($document)
            ->addCommit();

        $result = $this->client->update($update);
        $response = new Response();

        return $response
            ->setDebugInfo($this->getDebugInfo($update))
            ->setStatusMessage($result->getResponse()->getStatusMessage())
            ->setStatusCode($result->getResponse()->getStatusCode());
    }

    /**
     * @param int|string $id
     *
     * @return ResponseInterface
     */
    public function deleteById($id): ResponseInterface
    {
        $this->setPusherTimeout();

        $update = $this->client->createUpdate();
        $update
            ->addDeleteById($id)
            ->addCommit();

        $result = $this->client->update($update);
        $response = new Response();

        return $response
            ->setDebugInfo($this->getDebugInfo($update))
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param array $ids
     *
     * @return ResponseInterface
     */
    public function deleteByIds(array $ids): ResponseInterface
    {
        $this->setPusherTimeout();

        $update = $this->client->createUpdate();

        $update
            ->addDeleteByIds($ids)
            ->addCommit();

        $result = $this->client->update($update);
        $response = new Response();

        return $response
            ->setDebugInfo($this->getDebugInfo($update))
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param Field[] $fields
     *
     * @return ResponseInterface
     */
    public function deleteByFields(array $fields): ResponseInterface
    {
        $this->setPusherTimeout();

        $update = $this->client->createUpdate();

        if ($deleteQuery = $this->prepareDeleteQueryByFields($fields)) {
            $update->addDeleteQuery($deleteQuery);
            $update->addCommit();
        }

        $result = $this->client->update($update);
        $response = new Response();

        return $response
            ->setDebugInfo($this->getDebugInfo($update))
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    protected function prepareDeleteQueryByFields(array $fields): string
    {
        $query = '';
        /** @var Field $field */
        foreach ($fields as $field) {
            !empty($query) ? $query .= ' AND ' : $query .= '';
            $query .= FieldHelper::getFieldName($field) . ':' . $this->getSolariumHelper()->escapePhrase($field->getValue());
        }

        return (string)$query;
    }

    /**
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function get($options): ResponseInterface
    {
        $this->setPullerTimeout();

        $query = $this->client->createSelect($options);

        $result = $this->client->execute($query);

        $response = new Response();

        return $response
            ->setDebugInfo($this->getDebugInfo($query))
            ->setDocumentsCollection($result->getData())
            ->setNumFound(count($result->getData()))
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param $query
     * @param bool $rawFieldName
     *
     * @return ResponseInterface
     */
    public function query($query, bool $rawFieldName): ResponseInterface
    {
        if (!($query instanceof SolariumQueryInterface)) {
            throw new UnexpectedValueException(
                'query must implement Query Interface'
            );
        }

        $this->setPullerTimeout();

        $debugInfo = $this->getDebugInfo($query);
        $response = new Response();
        $response
            ->setQuery($query)
            ->setDebugInfo($debugInfo);

        try {
            $result = $this->client->select($query);
        } catch (Exception $e) {
            /** @todo logger for error */
            $debugInfo['message'] = $e->getMessage();
            $debugInfo['code'] = $e->getCode();
            $response
                ->setDebugInfo($debugInfo)
                ->setStatusCode($e->getCode())
                ->setStatusMessage($e->getMessage());
            return $response;
        }

        $response
            ->setDocumentsCollection($result->getData()['response']['docs'])
            ->setNumFound($result->getData()['response']['numFound'] ?? 0)
            ->setStats($result->getStats())
            ->setCurrentPage($result->getQuery()->getOption('start'))
            ->setStatusMessage($result->getResponse()->getStatusMessage())
            ->setStatusCode($result->getResponse()->getStatusCode());

        if ($result->getFacetSet()) {
            $response->setFacets($result->getFacetSet()->getFacets());
        }


        /** TODO move it to another function */
        $newDocumentColl = [];
        /**
         * @todo too much response data vs requested (fields like created_at, display mode etc)
         */
        foreach ($response->getDocumentsCollection() as $docKey => $document) {
            $newDocument = new Document();
            foreach ($document as $fieldName => $fieldValue) {
                $field = FieldHelper::createFieldByResponseField($fieldName, $fieldValue, $rawFieldName);
                $newDocument->setField($field);
            }
            $document = $newDocument;
            array_push($newDocumentColl, $document);
        }

        $response->setDocumentsCollection($newDocumentColl);

        return $response;
    }

    /**
     * @param string $type
     *
     * @return SolariumQueryInterface
     */
    public function prepareQuery(string $type)
    {
        return $this->client->createQuery($type);
    }

    /**
     * @return Query
     */
    public function getSelect()
    {
        return $this->client->createSelect();
    }

    /**
     * @return PullerInterface
     */
    public function getPuller(): PullerInterface
    {
        return new Puller($this->config, $this->client);
    }

    /**
     * @return PusherInterface
     */
    public function getPusher(): PusherInterface
    {
        return new Pusher($this->config, $this->client);
    }

    /**
     * @return MsCatalogQueryInterface
     * @throws Exception
     */
    public function getQuery(): MsCatalogQueryInterface
    {
        return new MsCatalogSolrQuery($this->config);
    }

    /**
     * @param string $name
     * @param null $value
     * @param string $type
     * @param bool $indexable
     * @param bool $multiValued
     * @param array $args
     *
     * @return Field|mixed
     */
    public function getField(string $name, $value = null, string $type = '', bool $indexable = false, bool $multiValued = false, array $args = [])
    {
        return new Field($name, $value, $type, $indexable, $multiValued, $args);
    }

    /**
     * @param SolariumQueryInterface $query
     * @return array
     */
    public function getDebugInfo(SolariumQueryInterface $query): array
    {
        if (!$this->config->isDebugEnabled()) {
            return [];
        }

        $builder = $query->getRequestBuilder();
        $debugRequest = $builder->build($query);

        if (!$debugRequest) {
            return [];
        }

        $debugInfo = [
            'options' => $debugRequest->getOptions(),
            'params' => $debugRequest->getParams(),
            'uri' => $debugRequest->getUri(),
            'raw_data' => $debugRequest->getRawData(),
        ];

        return $debugInfo;
    }

    /**
     * @return \Solarium\Core\Query\Helper
     */
    public function getSolariumHelper()
    {
        if (!$this->solariumHelper) {
            $this->solariumHelper = $this->client->createSelect()->getHelper();
        }

        return $this->solariumHelper;
    }
}
