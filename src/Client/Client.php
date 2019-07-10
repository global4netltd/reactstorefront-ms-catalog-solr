<?php

namespace G4NReact\MsCatalogSolr\Client;

use G4NReact\MsCatalog\Client\ClientInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use G4NReact\MsCatalogSolr\Config;
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
     * @return Result
     */
    public function add($fields)
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
     * @return Result
     */
    public function deleteById($id)
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
     * @return Result
     */
    public function deleteByIds(array $ids)
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
     * @return ResultInterface
     */
    public function get($options)
    {
        $query = $this->client->createSelect($options);

        return $this->client->execute($query);
    }

    /**
     * @param $query
     *
     * @return ResultInterface
     */
    public function query($query)
    {
        if (!($query instanceof QueryInterface)) {
            throw new UnexpectedValueException(
                'query must implement Query Interface'
            );
        }
        return $this->client->execute($query);
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
}
