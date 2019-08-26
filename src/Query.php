<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\AbstractQuery;
use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\Document\FieldValue;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use G4NReact\MsCatalogSolr\Client\Client as MsCatalogSolrClient;
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
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var []
     */
    protected $facetExcludedFields = [];

    /**
     * @param bool $rawFieldName
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function getResponse(bool $rawFieldName = false)
    {
        /** @var MsCatalogSolrClient $client */
        $client = $this->getClient();

        return $client->query($this->buildQuery(), $rawFieldName);
    }

    /**
     * @return mixed|SolariumSelectQuery
     * @throws Exception
     */
    public function buildQuery()
    {
        /** @var MsCatalogSolrClient $client */
        $client = $this->getClient();
        $this->fieldHelper = new FieldHelper();

        $query = $client
            ->getSelect()
            ->setQuery($this->buildQueryText())
            ->setStart($this->getPageStart())
            ->setRows($this->getPageSize())
            ->setFields($this->prepareFields())
            ->setSorts($this->prepareSorts());

        $this->query = $query;
        $this->addFiltersToQuery();
        $this->addFacetsToQuery();
        $this->addStatsToQuery();

        return $this->query;
    }

    /**
     * @return string
     */
    protected function buildQueryText()
    {
        $queryString = $this->getQueryText();

        if ($queryString) {
            $queryArray = [];
            $limit = 10;
            for ($i = 1; $i <= $limit; $i++) {
                $queryArray[] = SearchTerms::SEARCH_TERMS_FIELD_NAME . '_' . $i . '_' . FieldHelper::$mapFieldType[Field::FIELD_TYPE_TEXT_SEARCH] . ':"' . $queryString . '"~100^' . (int)($limit - $i);
            }

            return implode(' OR ', $queryArray);
        }

        return '*:*';
    }

    /**
     * @return array
     */
    protected function prepareFields(): array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->fields as $field) {
            $fields[] = FieldHelper::getFieldName($field);
        }

        return $fields;
    }

    /**
     * @return array
     */
    protected function prepareSorts(): array
    {
        $sorts = [];
        /** @var Field $sort */
        foreach ($this->getSorts() as $sort) {
            if ($sort->getIndexable()) {
                $sorts[FieldHelper::getFieldName($sort)] = $sort->getRawValue();
            }
        }

        return $sorts;
    }

    /**
     * Add filters to query
     */
    protected function addFiltersToQuery()
    {
        $filterQueries = $this->prepareFilterQuries();

        foreach ($filterQueries as $key => $filterQueryData) {
            $filterQuery = $this->query->createFilterQuery($key);
            if (in_array($key, $this->facetExcludedFields)) {
                $filterQuery->setTags(['exclude']);
            }
            $filterQuery->setQuery($filterQueryData);
        }
    }

    /**
     * @return array
     */
    protected function prepareFilterQuries(): array
    {
        $filterQueries = [];
        $filters = $this->getFilters();
        foreach ($filters as $key => $filterArray) {
            foreach ($filterArray as $idx => $filter) {
                if (!isset($filter[self::FIELD]) || !isset($filter[self::NEGATIVE]) || !isset($filter[self::OPERATOR])) {
                    continue;
                }
                if ($filter[self::FIELD]->getExcluded()) {
                    $this->facetExcludedFields[] = $key;
                }
                if ($filter[self::OPERATOR] == self::OR_OPERATOR && isset($filterQuery) && isset($filterQueryKey)) {
                    $filterQuery = $filterQuery . ' ' . self::OR_OPERATOR . ' ' . $this->prepareFilterQuery($filter[self::FIELD], $filter[self::NEGATIVE]);
                    if (isset($filterQueries[$filterQueryKey])) {
                        unset($filterQueries[$filterQueryKey]);
                    }
                    $filterQueries[$filterQueryKey . ' ' . self::OR_OPERATOR . ' ' . $key . $idx] = $filterQuery;
                } else {
                    $filterQuery = $this->prepareFilterQuery($filter[self::FIELD], $filter[self::NEGATIVE]);
                    $filterQueryKey = $key . $idx;
                    $filterQueries[$key . $idx] = $filterQuery;
                }
            }
        }

        return $filterQueries;
    }

    /**
     * @ToDo: Handle range fields
     *
     * @param Field $field
     * @param bool $isNegative
     *
     * @return string
     */
    protected function prepareFilterQuery(Field $field, bool $isNegative)
    {
        $queryFilter = '';
        $value = $field->getValue();

        if ($field->getRawValue() instanceof FieldValue && $field->getRawValue()->getFromValue() && $field->getRawValue()->getToValue()) {
            /** @var FieldValue $fieldValue */
            $fieldValue = $field->getRawValue();

            $queryFilter = '[' . $fieldValue->getFromValue() . ' TO ' . $fieldValue->getToValue() . ']';
        } elseif (is_array($value) && $value) {
            $queryFilter = '("' . implode('" OR "', $value) . '")';
        } elseif (stripos($value, ',') !== false) {
            $multi = explode(',', $value);
            $queryFilter = '(' . implode(' OR ', $multi) . ')';
        } elseif (stripos($value, '\-') !== false) {
            $queryFilter = $value;
        } elseif ((($field->getType() == Field::FIELD_TYPE_FLOAT
                || $field->getType() == Field::FIELD_TYPE_INT))
            && stripos($value, '-') !== false) {
            /**
             * @todo handle other numeric types !!!
             */
            $ranges = explode('-', $value);
            if (isset($ranges[0]) && isset($ranges[1])) {
                $queryFilter = '[' . $ranges[0] . ' TO ' . $ranges[1] . ']';
            }
        } elseif ($field->getType() == Field::FIELD_TYPE_STRING || $field->getType() == Field::FIELD_TYPE_TEXT) {
            $queryFilter = "\"$value\""; // match exact value
        } else {
            $queryFilter = $value;
        }

        return (string)($isNegative ? '-' : '') . $this->fieldHelper::getFieldName($field) . ':' . $queryFilter;
    }

    /**
     * Add Facets to Query
     */
    protected function addFacetsToQuery()
    {
        foreach ($this->facets as $key => $facet) {
            /** @var Field $facet */
            if ($facet->getIndexable()) {
                $facetField = $this->query->getFacetSet()
                    ->createFacetField($key);
                if (in_array($key, $this->facetExcludedFields)) {
                    $facetField->setExcludes(['exclude']);
                }
                $facetField
                    ->setMinCount($facet->getMinCount() ?: 1)
                    ->setLimit($facet->getLimit() ?: 100)
                    ->setField(FieldHelper::getFieldName($facet));
            }
        }
    }

    /**
     * Add Stats to Query
     */
    protected function addStatsToQuery()
    {
        $stats = $this->query->getStats();
        /**
         * @var  $key
         * @var Field $stat
         */
        foreach ($this->stats as $key => $stat) {
            if ($stat->getIndexable()) {
                $field = $this->fieldHelper::getFieldName($stat);
                $stats->createField($field);
            }
        }
    }

    /**
     * @param Field $field
     *
     * @return string
     */
    protected function prepareQueryFacet($field)
    {
        return (string)$this->fieldHelper::getFieldName($field) . ': ' . $field->getValue();
    }
}
