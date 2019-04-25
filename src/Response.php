<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\ResponseInterface;

/**
 * Class Response
 * @package G4NReact\MsCatalogSolr
 */
class Response implements ResponseInterface
{
    /**
     * @var int
     */
    private $numFound;

    /**
     * @var array
     */
    private $documentsCollection;

    /**
     * @var array
     */
    private $facets = [];

    /**
     * @var array
     */
    private $stats = [];

    /**
     * @var array
     */
    private $currentPage;

    /**
     * @return int
     */
    public function getNumFound()
    {
        return $this->numFound;
    }

    /**
     * @param int $numFound
     * @return Response
     */
    public function setNumFound($numFound)
    {
        $this->numFound = $numFound;

        return $this;
    }

    /**
     * @return array
     */
    public function getDocumentsCollection()
    {
        return $this->documentsCollection;
    }

    /**
     * @param array $documentsCollection
     * @return Response
     */
    public function setDocumentsCollection($documentsCollection)
    {
        $this->documentsCollection = $documentsCollection;

        return $this;
    }

    /**
     * @return Document|null
     */
    public function getFirstItem()
    {
        $arrayKeys = array_keys($this->documentsCollection);
        if (isset($arrayKeys[0])) {
            return $this->documentsCollection[$arrayKeys[0]];
        } else {
            return new Document;
        }
    }

    /**
     * @return array
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @param array $facets
     * @return Response
     */
    public function setFacets($facets)
    {
        $this->facets = $facets;

        return $this;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     * @return Response
     */
    public function setStats($stats)
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param array $facets
     * @return Response
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;

        return $this;
    }
}
