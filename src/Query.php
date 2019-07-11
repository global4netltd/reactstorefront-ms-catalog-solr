<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\AbstractQuery;
use G4NReact\MsCatalog\Document\Field;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\QueryType\Select\Query\Query as SolariumSelectQuery;

/**
 * Class Query
 * @package G4NReact\MsCatalogSolr
 */
class Query extends AbstractQuery
{
    /**
     * @var SolariumSelectQuery
     */
    protected $query;

    /**
     * @return ResultInterface
     */
    public function buildQuery() : ResultInterface
    {
        /** @var \G4NReact\MsCatalogSolr\Client\Client $client */
        $client = $this->getClient();

        $query = $client
            ->getSelect()
            ->setQuery($this->getQueryText())
            ->setFilterQueries($this->prepareFilterQueries())
            ->setFields($this->prepareFields())
            ->setStart($this->getPageStart())
            ->setRows($this->getPageSize())
            ->addSorts($this->sort);

        $this->query = $query;
        $this->addFacetsToQuery();
        $this->addStatsToQuery();

        return $client->query($query);
    }

    public function getResponse()
    {
        // TODO: Implement getResponse() method.
    }

    /**
     * @return array
     */
    protected function prepareFilterQueries() : array
    {
        $filtersQuery = [];

        foreach ($this->filters as $key => $filter) {
            if (!isset($filter[self::FIELD]) || !isset($filter[self::NEGATIVE])) {
                continue;
            }
            /** @var Field $field */
            $field = $filter[self::FIELD];
            $filtersQuery[$key] = $filter[self::NEGATIVE] ? -$field->getValue() : $field->getValue();
        }

        return $filtersQuery;
    }

    /**
     * Add Facets to Query
     */
    protected function addFacetsToQuery()
    {
        $this->query->getFacetSet()->addFacets($this->facets);
    }

    /**
     * Add Stats to Query
     */
    protected function addStatsToQuery()
    {
        $this->query->getStats()->addFacets($this->facets);
    }

    /**
     * @return array
     */
    protected function prepareFields() : array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->fields as $field) {
            $fields [] = $field->getValue();
        }

        return $fields;
    }
}
