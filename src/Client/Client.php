<?php

namespace G4NReact\MsCatalogSolr\Client;

use G4NReact\MsCatalog\Client\ClientInterface;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\PusherInterface;
use G4NReact\MsCatalog\QueryBuilderInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use G4NReact\MsCatalogSolr\Config;
use G4NReact\MsCatalogSolr\Response;
use Solarium\Client as SolariumClient;
use Solarium\Core\Query\QueryInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\Exception\UnexpectedValueException;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Result;

/**
 * Class Client
 * @package G4NReact\MsCatalogSolr\Client
 */
class Client implements ClientInterface
{
    /**
     * @var SolariumClient
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Client constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = new SolrConfig($config);
        $this->client = new SolariumClient($this->config->getConfigArray());
    }

    /**
     * @param array $fields
     *
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function add($fields) : ResponseInterface
    {
        $update = $this->client->createUpdate();
        $document = $update->createDocument($fields);
        $update
            ->addDocument($document)
            ->addCommit();

        $result =  $this->client->update($update);
        $response = new Response();

        return $response
            ->setStatusMessage($result->getResponse()->getStatusMessage())
            ->setStatusCode($result->getResponse()->getStatusCode());
    }

    /**
     * @param int|string $id
     *
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function deleteById($id) : ResponseInterface
    {
        $update = $this->client->createUpdate();
        $update
            ->addDeleteById($id)
            ->addCommit();

        $result = $this->client->update($update);
        $response = new Response();
        return $response
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param array $ids
     *
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function deleteByIds(array $ids) : ResponseInterface
    {
        $update = $this->client->createUpdate();

        $update
            ->addDeleteByIds($ids)
            ->addCommit();

        $result = $this->client->update($update);
        $response = new Response();
        return $response
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param $field
     * @param $value
     *
     * @return Result
     */
    public function deleteByField($field, $value)
    {
        $update = $this->client->createUpdate();
        $update
            ->addDeleteQuery($field . ':' . $value)
            ->addCommit();

        return $this->client->update($update);
    }


    /**
     * @param array $options
     *
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function get($options) : ResponseInterface
    {
        $query = $this->client->createSelect($options);

        $result = $this->client->execute($query);

        $response = new Response();
        return $response
            ->setDocumentsCollection($result->getData())
            ->setNumFound(count($result->getData()))
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setStatusMessage($result->getResponse()->getStatusMessage());
    }

    /**
     * @param $query
     *
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function query($query) : ResponseInterface
    {
        if (!($query instanceof QueryInterface)) {
            throw new UnexpectedValueException(
                'query must implement Query Interface'
            );
        }
        $result = $this->client->execute($query);
        $response = new Response();

        return $response
            ->setDocumentsCollection($result->getData()['response']['docs'])
            ->setNumFound($result->getData()['response']['numFound'])
            ->setFacets($result->getData()['facet_counts']['facet_queries'])
            ->setStats($result->getData()['stats']['stats_fields'])
            ->setCurrentPage($result->getQuery()->getOption('start'))
            ->setStatusMessage($result->getResponse()->getStatusMessage())
            ->setStatusCode($result->getResponse()->getStatusCode());
    }

    /**
     * @param string $type
     *
     * @return QueryInterface
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

    public function getPusher(): PusherInterface
    {
        // TODO: Implement getPusher() method.
    }

    public function getPuller(): PullerInterface
    {
        // TODO: Implement getPuller() method.
    }

    public function getQueryBuilder(): QueryBuilderInterface
    {
        // TODO: Implement getQueryBuilder() method.
    }
}
