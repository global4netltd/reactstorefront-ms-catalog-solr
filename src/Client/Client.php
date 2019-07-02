<?php

namespace G4NReact\MsCatalogSolr\Client;

use G4NReact\MsCatalog\Client\ClientInterface;
use Solarium\Core\Query\QueryInterface;
use Solarium\Exception\UnexpectedValueException;

/**
 * Class Client
 * @package G4NReact\MsCatalogSolr\Client
 */
class Client implements ClientInterface
{
    /**
     * @var \Solarium\Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->client = new \Solarium\Client($config);
    }

    /**
     * @param array $fields
     *
     * @return \Solarium\Core\Query\Result\ResultInterface|\Solarium\QueryType\Update\Result
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
     * @param int $id
     *
     * @return \Solarium\Core\Query\Result\ResultInterface|\Solarium\QueryType\Update\Result
     */
    public function delete($id)
    {
        $update = $this->client->createUpdate();
        $update
            ->addDeleteById($id)
            ->addCommit();

        return $this->client->update($update);
    }

    /**
     * @param $field
     * @param $value
     *
     * @return \Solarium\Core\Query\Result\ResultInterface|\Solarium\QueryType\Update\Result
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
     * @return \Solarium\Core\Query\Result\ResultInterface
     */
    public function get($options)
    {
        $query = $this->client->createSelect($options);

        return $this->client->execute($query);
    }

    /**
     * @param $query
     *
     * @return \Solarium\Core\Query\Result\ResultInterface
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
     * @return \Solarium\Core\Query\AbstractQuery|\Solarium\Core\Query\QueryInterface
     */
    public function prepareQuery(string $type)
    {
        return $this->client->createQuery($type);
    }
}
