<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalogSolr\Config;
use Solarium\Client;

/**
 * Class Pusher
 * @package G4NReact\MsCatalogSolr
 */
class Pusher
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * Pusher constructor
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->client = new Client($this->getConfigArray());
    }

    /**
     * @param \Iterator $documents
     */
    public function push(\Iterator $documents)
    {

        $pageSize = $this->config->getPageSize();

        if ($documents) {
            $update = $this->client->createUpdate();

            // delete index before reindexing if setting == true -> set delete query eg '*:*' or 'product_type:"category"'
            $this->clearIndex();

            // @ToDo: pagination - page size from config

            //test//$pageSize = 2;
            $i = 0;
            foreach ($documents as $document) {
                if($i < $pageSize) {
                    $doc = $update->createDocument();

                    $doc->id = (string)$document->getUniqueId();
                    $doc->object_id = (int)$document->getObjectId();
                    $doc->object_type = (string)$document->getObjectType();

                    /** @var Document\Field $field */
                    foreach ($document->getData() as $field) {

                        $solrFieldName = $field->getName()
                            . (Helper::$mapFieldType[$field->getType()] ?? Helper::SOLR_FIELD_TYPE_DEFAULT)
                            . ($field->getIndexable() ? '' : Helper::SOLR_NOT_INDEXABLE_MARK)
                            . Helper::SOLR_MULTI_VALUE_MARK;

                        $solrFieldValue = $field->getValue();
                        if (isset(Helper::$mapFieldType[$field->getType()]) && Helper::$mapFieldType[$field->getType()] === Helper::SOLR_FIELD_TYPE_DATETIME) {
                            $solrFieldValue = date(Helper::SOLR_DATETIME_FORMAT, strtotime($field->getValue()));
                        }

                        $doc->{$solrFieldName} = $solrFieldValue;
                    }

                    $update->addDocument($doc);

                    $i++;
                    //print_r($i . ' krok ');
                }else{
                    //print_r(' update ');
                    $update->addCommit();
                    $result = $this->client->update($update);
                    $i = 0;
                    $update = $this->client->createUpdate();
                }
            }
            //print_r(' last update ');
            $update->addCommit();
            $result = $this->client->update($update);
        }
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
