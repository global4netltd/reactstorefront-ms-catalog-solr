<?php

namespace G4NReact\MsCatalogSolr\Client;

use G4NReact\MsCatalog\Client\ClientInterface;
use G4NReact\MsCatalog\Config;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\PusherInterface;
use G4NReact\MsCatalog\QueryInterface as MsCatalogQueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use G4NReact\MsCatalogSolr\Puller;
use G4NReact\MsCatalogSolr\Pusher;
use G4NReact\MsCatalogSolr\Query as MsCatalogSolrQuery;
use G4NReact\MsCatalogSolr\Response;
use Solarium\Client as SolariumClient;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Solarium\Exception\UnexpectedValueException;
use Solarium\QueryType\Select\Query\Query;

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
     * @var SolrConfig
     */
    protected $solrConfig;

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
     * @param array $fields
     * @return ResponseInterface
     */
    public function add($fields): ResponseInterface
    {
        $update = $this->client->createUpdate();
        $document = $update->createDocument($fields);
        $update
            ->addDocument($document)
            ->addCommit();

        return $this->client->update($update);
    }

    /**
     * @param int|string $id
     *
     * @return ResponseInterface
     */
    public function deleteById($id): ResponseInterface
    {
        $update = $this->client->createUpdate();
        $update
            ->addDeleteById($id)
            ->addCommit();

        return $this->client->update($update);
    }

    /**
     * @param array $ids
     *
     * @return ResponseInterface
     */
    public function deleteByIds(array $ids): ResponseInterface
    {
        $update = $this->client->createUpdate();

        $update
            ->addDeleteByIds($ids)
            ->addCommit();

        return $this->client->update($update);
    }

    /**
     * @param $field
     * @param $value
     *
     * @return ResponseInterface
     */
    public function deleteByField($field, $value): ResponseInterface
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
     * @return ResponseInterface
     */
    public function get($options): ResponseInterface
    {
        $query = $this->client->createSelect($options);

        return $this->client->execute($query);
    }

    /**
     * @param $query
     *
     * @return ResponseInterface
     */
    public function query($query): ResponseInterface
    {
        if (!($query instanceof SolariumQueryInterface)) {
            throw new UnexpectedValueException(
                'query must implement Query Interface'
            );
        }
        return $this->client->execute($query);
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
     */
    public function getQuery(): MsCatalogQueryInterface
    {
        return new MsCatalogSolrQuery($this->config, $this);
    }
}
