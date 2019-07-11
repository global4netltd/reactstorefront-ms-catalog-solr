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
     * @return \G4NReact\MsCatalog\ResponseInterface
     */
    public function buildQuery(): \G4NReact\MsCatalog\ResponseInterface
    {
        /** @var \G4NReact\MsCatalogSolr\Client\Client $client */
        $client = $this->getClient();

        $query = $client
            ->getSelect()
            ->setQuery($this->getQueryText() ?? '*:*')
            ->setStart($this->getPageStart())
            ->setRows($this->getPageSize())
            ->setFields($this->prepareFields());

        $this->query = $query;
        $this->addFiltersToQuery();
        $this->addFacetsToQuery();
        $this->addStatsToQuery();

        return $client->query($this->query);
    }

    public function getResponse()
    {
        // TODO: Implement getResponse() method.
    }

    /**
     * Add filters to query
     */
    protected function addFiltersToQuery()
    {
        foreach ($this->filters as $key => $filter) {
            if (!isset($filter[self::FIELD]) || !isset($filter[self::NEGATIVE])) {
                continue;
            }

            $this->query->createFilterQuery($key)->setQuery($this->prepareFilterQuery($filter[self::FIELD], $filter[self::NEGATIVE]));
        }
    }

    /**
     * @param Field $field
     *
     * @param bool $isNegative
     *
     * @return string
     */
    protected function prepareFilterQuery(Field $field, bool $isNegative)
    {
        return (string)$isNegative ? '-' : '' . $field->getName() . ':' . $field->getValue();
    }

    /**
     * Add Facets to Query
     */
    protected function addFacetsToQuery()
    {
        foreach ($this->facets as $key => $facet) {
            $this->query->getFacetSet()->createFacetQuery($key)->setQuery($this->prepareQueryFacet($facet));
        }
    }

    /**
     * @param Field $field
     *
     * @return string
     */
    protected function prepareQueryFacet($field)
    {
        return (string)$field->getName() . ': ' . $field->getValue();
    }

    /**
     * Add Stats to Query
     */
    protected function addStatsToQuery()
    {
        /**
         * @var  $key
         * @var Field $stat
         */
        foreach ($this->stats as $key => $stat) {
            $this->query->getStats()->addFacet($key)->createField($stat->getName());
        }
    }

    /**
     * @return array
     */
    protected function prepareFields(): array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->fields as $field) {
            $fields [] = $field->getValue();
        }

        return $fields;
    }

    /**
     * set Sorts
     */
    protected function setSorts()
    {
        if ($this->sort) {
            $this->query->setSorts($this->sort);
        }
    }
}
