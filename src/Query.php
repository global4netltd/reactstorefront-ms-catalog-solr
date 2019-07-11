<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\AbstractQuery;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Client\Client as MsCatalogSolrClient;
use Solarium\QueryType\Select\Query\Query as SolariumSelectQuery;
use G4NReact\MsCatalog\Document\AbstractField;

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
     * @return ResponseInterface
     */
    public function buildQuery(): ResponseInterface
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

        $result = $client->query($this->query);
        return $client->query($this->query);
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
     * @param AbstractField $field
     *
     * @param bool $isNegative
     *
     * @return string
     */
    protected function prepareFilterQuery(AbstractField $field, bool $isNegative)
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
     * @param AbstractField $field
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
         * @var AbstractField $stat
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
        /** @var AbstractField $field */
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
