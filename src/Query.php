<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\AbstractQuery;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Client\Client as MsCatalogSolrClient;
use Solarium\QueryType\Select\Query\Query as SolariumSelectQuery;

/**
 * Class Query
 * @package G4NReact\MsCatalogSolr
 */
class Query extends AbstractQuery
{
    /** text, sort, filter, page size, query text */
    /**
     * @return SolariumSelectQuery
     * @throws Exception
     */
    public function buildQuery()
    {
        /** @var MsCatalogSolrClient $client */
        $client = $this->getClient();
        $query = $client
            ->getSelect()
            ->setFields($this->fields)
            ->addSorts($this->sort);

        return $query;
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function getResponse()
    {
        /** @var MsCatalogSolrClient $client */
        $client = $this->getClient();

        return $client->query($this->buildQuery());
    }
}
