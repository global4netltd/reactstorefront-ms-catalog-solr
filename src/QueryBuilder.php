<?php

namespace G4NReact\MsCatalogSolr;

/**
 * Class QueryBuilder
 * @package G4NReact\MsCatalogSolr
 */
class QueryBuilder implements \G4NReact\MsCatalog\QueryBuilderInterface
{
    protected $query;

    protected $options;

    protected $searchBoost = '';
    protected $searchFields = [];
    protected $filterQueries = [];
    protected $searchAdditionalInfo = '';

    /**
     * QueryBuilder constructor
     */
    public function __construct()
    {
        $this->query = new Query();
    }

    /**
     * @return \G4NReact\MsCatalog\QueryInterface
     */
    public function buildQuery()
    {
        $queryStr = '*';
        $queryFilter = [];

        if (isset($this->options['search'])) {
            $queryText = $this->getSearchBoost();
            $searchText = mb_strtolower($this->options['search']);
            $searchText = $this->convertPolishLetters($searchText);

            $queryText .= $this->getQueryStringByValue($searchText, $this->getSearchFields());
            $queryText .= $this->getSearchAdditionalInfo();

            $this->query->setQueryText($queryText);
        }

        if (isset($this->options['filter'])) {
            foreach ($this->options['filter'] as $code => $filter) {

                if ($code == 'category_id') {
                    $code = 'category';
                }

                if ($code == 'custom') {
                    $code = $filter['code'];
                    $filter = $filter['input'];
                }

                foreach ($filter as $op => $value) {
                    if ($op == 'eq') {
                        $queryFilter[$code][] = $code . ':' . $value;
                    }
                }

                if (isset($queryFilter[$code])) {
                    $queryFilter[$code] = implode(' OR ', $queryFilter[$code]);
                }
            }

            $queryStr = implode(' AND ', $queryFilter);

            $this->query->setFilterQueryText($queryStr);
        }
        
        $this->query->setFilterQueries($this->filterQueries);

        if (isset($this->options['facet'])) {
            $this->query->setFacet(true);
            $this->query->setFacetFilds($this->options['facet']);
        }

        if (isset($this->options['pageSize'])) {
            $this->query->setPageSize($this->options['pageSize']);
        }

        if (isset($this->options['currentPage'])) {
            $this->query->setCurrentPage($this->options['currentPage']);
        }

        return $this->query;
    }

    /**
     * @return \G4NReact\MsCatalog\QueryInterface
     */
    public function getQuery()
    {
        // TODO: Implement getQuery() method.
    }

    /**
     * @param $boost
     */
    public function setSearchBoost($boost)
    {
        $this->searchBoost = $boost;
    }

    /**
     * @return mixed
     */
    public function getSearchBoost()
    {
        return $this->searchBoost;
    }

    /**
     * @param $fields
     */
    public function setSearchFields($fields)
    {
        $this->searchFields = $fields;
    }

    /**
     * @return mixed
     */
    public function getSearchFields()
    {
        return $this->searchFields;
    }

    /**
     * @param $additionalInfo
     */
    public function setSearchAdditionalInfo($additionalInfo)
    {
        $this->searchAdditionalInfo = $additionalInfo;
    }

    /**
     * @return mixed
     */
    public function getSearchAdditionalInfo()
    {
        return $this->searchAdditionalInfo;
    }

    /**
     * @param $value
     * @param array $fields
     * @return string
     */
    public function getQueryStringByValue($value, $fields = array())
    {
        $clearValue = (strpos($value, ' ') !== false) ? str_replace(' ', '', $value) : false;

        $regexText = false;
        if (preg_match_all('/(.* . )(.*)/', $value, $matches) && (count($matches) == 3)) {
            $regexText = str_replace(' ', '', $matches[1][0]) . ' ' . $matches[2][0];
        }

        $queryText = '';
        $isFirst = true;

        foreach ($fields as $field => $priority) {
            $queryText .= ($isFirst) ? '' : ' OR ';
            $queryText .= $field . ':"' . $value . '"' . $priority;
            if ($clearValue) {
                $queryText .= ' OR ' . $field . ':"' . $clearValue . '"' . $priority;
            }
            if ($regexText) {
                $queryText .= ' OR ' . $field . ':"' . $regexText . '"' . $priority;
            }
            $isFirst = false;
        }

        return $queryText;
    }

    /**
     * Converts polish letters to non diacritic version
     * @param $string
     * @return string
     */
    static function convertPolishLetters($string)
    {
        $table = Array(
            //WIN
            "\xb9"     => "a", "\xa5" => "A", "\xe6" => "c", "\xc6" => "C",
            "\xea"     => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
            "\xf3"     => "o", "\xd3" => "O", "\x9c" => "s", "\x8c" => "S",
            "\x9f"     => "z", "\xaf" => "Z", "\xbf" => "z", "\xac" => "Z",
            "\xf1"     => "n", "\xd1" => "N",
            //UTF
            "\xc4\x85" => "a", "\xc4\x84" => "A", "\xc4\x87" => "c", "\xc4\x86" => "C",
            "\xc4\x99" => "e", "\xc4\x98" => "E", "\xc5\x82" => "l", "\xc5\x81" => "L",
            "\xc3\xb3" => "o", "\xc3\x93" => "O", "\xc5\x9b" => "s", "\xc5\x9a" => "S",
            "\xc5\xbc" => "z", "\xc5\xbb" => "Z", "\xc5\xba" => "z", "\xc5\xb9" => "Z",
            "\xc5\x84" => "n", "\xc5\x83" => "N",
            //ISO
            "\xb1"     => "a", "\xa1" => "A", "\xe6" => "c", "\xc6" => "C",
            "\xea"     => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
            "\xf3"     => "o", "\xd3" => "O", "\xb6" => "s", "\xa6" => "S",
            "\xbc"     => "z", "\xac" => "Z", "\xbf" => "z", "\xaf" => "Z",
            "\xf1"     => "n", "\xd1" => "N");

        return strtr($string, $table);
    }

    public function addFilterQuery(array $filterQuery)
    {
        $this->filterQueries[] = $filterQuery;

        return $this;
    }
}
