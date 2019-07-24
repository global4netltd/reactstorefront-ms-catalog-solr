<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\ConfigInterface;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogSolr\Config as SolrConfig;
use Solarium\Client as SolariumClient;
use Solarium\QueryType\Select\Result\Result;

/**
 * Class Puller
 * @package G4NReact\MsCatalogSolr
 */
class Puller implements PullerInterface
{
    /**
     * @var SolrConfig Config Configuration object
     */
    private $config;

    /**
     * @var SolariumClient Solarium client
     */
    private $client;

    /**
     * Puller constructor
     *
     * @param ConfigInterface $config
     * @param SolariumClient $client
     */
    public function __construct(ConfigInterface $config, SolariumClient $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * @param QueryInterface $query
     * @return ResponseInterface
     */
    public function pull(QueryInterface $query = null): ResponseInterface
    {
        $solarium = $this->client;
        $response = new Response();

        if (!$query) {
            return $response;
        }

        // get a select query instance
        $solariumQuery = $solarium->createSelect();
        if ($query->getQueryText()) {
            $solariumQuery->setQuery($query->getQueryText());
        }

        if ($query->getFilterQueryText()) {
            // $solariumQuery->addFilterQuery(array('key' => 'category', 'query' => 'category:100029', 'tag' => 'inner'));
            // create a filterquery
            $solariumQuery->createFilterQuery('filter_query')->setQuery($query->getFilterQueryText());
        }

        if ($filterQueries = $query->getFilterQueries()) {
            foreach ($filterQueries as $filterQuery) {
                $solariumQuery->addFilterQuery($filterQuery);
            }
        }

        $facetSet = false;
        $statsSet = false;
        if ($query->getFacet()) {
            // get the facetset component
            $facetSet = $solariumQuery->getFacetSet();
            $facetSet->setMincount('1');
            // $facetSet->setLimit('10');

            $statsSet = $solariumQuery->getStats();
        }

        if ($facetSet && ($facets = $query->getFacetFields()) && is_array($facets)) {
            // create a facet field instance and set options
            foreach ($facets as $facet) {
                $facetSet->createFacetField($facet)->setField($facet);
            }

            // create a facet query instance and set options
            // $facetSet->createFacetQuery('query_category')->setQuery('category:100029');

            // create a facet field instance and set options
            // $facet = $facetSet->createFacetRange('priceranges');
            // $facet->setField('price_f');
            // $facet->setStart(1);
            // $facet->setGap(100);
            // $facet->setEnd(1000);
        }

        if ($statsSet && ($stats = $query->getStatFilds())) {
            // add stats settings
            // $stats->createField('price_f')->addFacet('price_f');
            foreach ($stats as $stat) {
                $statsSet->createField($stat);
            }
        }

        // set fields to fetch (this overrides the default setting 'all fields')
        // @ToDo: Check, why we get only product_id in this array
        if (($fieldsToFetch = $query->getFieldsToFetch()) && !empty($query->getFieldsToFetch())) {
            $solariumQuery->setFields($query->getFieldsToFetch());
        }

        // sort the results by price ascending
        foreach ($query->getSort() as $sort) {
            $solariumQuery->addSort(FieldHelper::getFieldName($sort), $sort->getValue() ?: 'DESC');
        }

        $solariumQuery->setStart($query->getCurrentPage());
        $solariumQuery->setRows($query->getPageSize());

        // add debug settings
        // $debug = $solariumQuery->getDebug();

        // this executes the query and returns the result
        $resultset = $solarium->select($solariumQuery);
//        $debugResult = $resultset->getDebug();
//        var_dump($debugResult);die;

        $resultResponse = $resultset->getData();
        $response->setCurrentPage($resultResponse['response']['start']);

        $response->setNumFound($resultset->getNumFound());

        if ($query->getFacet()) {
            $solariumFacet = $resultset->getFacetSet();
            $response->setFacets($this->getFacets($solariumFacet->getFacets()));

            if ($solariumStats = $resultset->getStats()) {
                $response->setStats($this->getStats($solariumStats));
            }
        }

        $response->setDocumentsCollection($this->getDocuments($resultset));

        return $response;
    }

    /**
     * @param $solariumFacets
     * @return array
     */
    protected function getFacets($solariumFacets)
    {
        $facetsCollection = [];

        foreach ($solariumFacets as $code => $solariumFacet) {
            $facet = [];

            $facet['code'] = $code;
            foreach ($solariumFacet->getValues() as $value => $count) {
                $facet['values'][] = [
                    'value_id' => $value,
                    'count' => $count
                ];
            }

            if (isset($facet['values'])) {
                $facetsCollection[] = $facet;
            }
        }

        return $facetsCollection;
    }

    /**
     * @param $solariumStats
     * @return array
     */
    protected function getStats($solariumStats)
    {
        $statsCollection = [];

        foreach ($solariumStats as $code => $solariumStat) {
            $stat['code'] = $solariumStat->getName();
            $stat['values'] = [
                'min' => $solariumStat->getMin(),
                'max' => $solariumStat->getMax(),
                'sum' => $solariumStat->getSum(),
                'count' => $solariumStat->getCount()
            ];

            if ($solariumStat->getCount() > 0) {
                $statsCollection[] = $stat;
            }
        }

        return $statsCollection;
    }

    /**
     * @param Result $solariumResultSet
     * @return array
     */
    protected function getDocuments(Result $solariumResultSet)
    {
        $documentsCollection = [];

        foreach ($solariumResultSet as $solariumDocument) {
            $document = new Document();
            foreach ($solariumDocument as $solrFieldName => $value) {
                /** @var Document\Field $field */
                $field = $this->parseSolrFieldToField($solrFieldName, $value);
                $document->setData($field->getName(), $field);
            }
            $documentsCollection[] = $document;
        }

        return $documentsCollection;
    }

    /**
     * @param string $solrFieldName
     * @param mixed $value
     * @return Document\Field
     */
    public function parseSolrFieldToField(string $solrFieldName, $value): Document\Field
    {
        $nameParts = explode('_', $solrFieldName);

        $type = FieldHelper::FIELD_TYPE_DEFAULT;
        $indexable = true;
        $multiValue = false;

        if ($nameParts[count($nameParts) - 1] === 'mv') {
            $multiValue = true;
            unset($nameParts[count($nameParts) - 1]);
        }

        if (($nameParts[count($nameParts) - 1] === 'ni') || ($nameParts[count($nameParts) - 1] === 'nonindex')) {
            $indexable = false;
            unset($nameParts[count($nameParts) - 1]);
        }

        if (isset(FieldHelper::$mapSolrFieldTypeToFieldType[$nameParts[count($nameParts) - 1]])) {
            $type = FieldHelper::$mapSolrFieldTypeToFieldType[$nameParts[count($nameParts) - 1]];
            unset($nameParts[count($nameParts) - 1]);
        }

        $name = implode('_', $nameParts);

        return new Document\Field($name, $value, $type, $indexable, $multiValue);
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
     * @param array $ids
     * @return PullerInterface
     */
    public function setIds(array $ids): PullerInterface
    {
        // TODO: Implement setIds() method.
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        // TODO: Implement getIds() method.
    }
}
