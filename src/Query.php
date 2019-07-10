<?php

namespace G4NReact\MsCatalogSolr;

/**
 * Class Query
 * @package G4NReact\MsCatalogSolr\Query
 */
class Query implements \G4NReact\MsCatalog\QueryInterface
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $filterText;

    /**
     * @var bool
     */
    private $facet = false;

    /**
     * @var array
     */
    private $facetFields = [];

    /**
     * @var array
     */
    private $statFields = [];

    /**
     * @var array
     */
    private $filterQueries = [];

    /**
     * @var array
     */
    private $fieldsToFetch = [];
    
    /**
     * @var int
     */
    private $currentPage = 0;

    /**
     * @var int
     */
    private $pageSize = 20;

    /**
     * @var array
     */
    private $sort = [];

    /**
     * @param string $text
     * @return string
     */
    public function setQueryText($text)
    {
        $this->text = $text;
    }
    /**
     * @return string
     */
    public function getQueryText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setFilterQueryText($filterText)
    {
        $this->filterText = $filterText;
    }
    /**
     * @return string
     */
    public function getFilterQueryText()
    {
        return $this->filterText;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setFacet($facet)
    {
        $this->facet = $facet;
    }
    /**
     * @return string
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setFacetFields($facets)
    {
        $this->facetFields = $facets;
    }
    /**
     * @return string
     */
    public function addFacetField($field)
    {
        $this->facetFields[] = $field;
    }
    /**
     * @return string
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setStatFilds($stats)
    {
        $this->statFields = $stats;
    }
    /**
     * @return string
     */
    public function addStatFild($stat)
    {
        $this->statFields[] = $stat;
    }
    /**
     * @return string
     */
    public function getStatFilds()
    {
        return $this->statFields;
    }

    /**
     * @return array
     */
    public function getFieldsToFetch(): array
    {
        return $this->fieldsToFetch;
    }

    /**
     * @param array $fieldsToFetch
     * @return Query
     */
    public function setFieldsToFetch(array $fieldsToFetch): Query
    {
        $this->fieldsToFetch = $fieldsToFetch;

        return $this;
    }

    /**
     * @param $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }
    /**
     * @return string
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }
    /**
     * @return string
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }
    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getFilterQueries(): array
    {
        return $this->filterQueries;
    }

    /**
     * @param array $filterQueries
     * @return Query
     */
    public function setFilterQueries(array $filterQueries): Query
    {
        $this->filterQueries = $filterQueries;

        return $this;
    }

    /**
     * @param array $filterQuery
     * @return Query
     */
    public function addFilterQuery(array $filterQuery): Query
    {
        $this->filterQueries[] = $filterQuery;

        return $this;
    }
}
