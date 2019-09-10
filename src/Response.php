<?php

namespace G4NReact\MsCatalogSolr;

use Exception;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface as MsCatalogQueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use Solarium\Component\Result\Stats\Result;
use Solarium\Component\Result\Stats\Stats;

/**
 * deprecated moved to ms-catalog
 *
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
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * @var string
     */
    private $statusMessage = 'empty';

    /**
     * @var MsCatalogQueryInterface|string
     */
    protected $query;

    /**
     * @var array
     */
    protected $debugInfo;

    /**
     * @return int
     */
    public function getNumFound(): int
    {
        return $this->numFound ?: 0;
    }

    /**
     * @param int $numFound
     * @return ResponseInterface
     */
    public function setNumFound(int $numFound): ResponseInterface
    {
        $this->numFound = $numFound;

        return $this;
    }

    /**
     * @return array
     */
    public function getDocumentsCollection(): array
    {
        return $this->documentsCollection ?: [];
    }

    /**
     * @param array $documentsCollection
     * @return ResponseInterface
     */
    public function setDocumentsCollection(array $documentsCollection): ResponseInterface
    {
        $this->documentsCollection = $documentsCollection;

        return $this;
    }

    /**
     * @return Document
     */
    public function getFirstItem(): Document
    {
        $arrayKeys = array_keys($this->documentsCollection ?: []);
        if (isset($arrayKeys[0])) {
            return $this->documentsCollection[$arrayKeys[0]];
        } else {
            return new Document();
        }
    }

    /**
     * @return array|null
     */
    public function getFacets(): ?array
    {

        return $this->getNumFound() ? $this->facets : [];
    }

    /**
     * @param array|null $facets
     * @return ResponseInterface
     */
    public function setFacets(?array $facets): ResponseInterface
    {
        $this->facets = $facets;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getStats(): ?array
    {
        $stats = [];
        if ($this->getNumFound()) { // @todo think about better place for getNumFound check
            /** @var Result $stat */
            foreach ($this->stats as $stat) {
                if (!($stat instanceof Result) || !$stat->getCount()) {
                    continue;
                }
                $stats[$stat->getName()] = [
                    'max'     => $stat->getMax(),
                    'min'     => $stat->getMin(),
                    'missing' => $stat->getMissing(),
                    'count'   => $stat->getCount(),
                    'mean'    => $stat->getMean(),
                    'sum'     => $stat->getSum(),
                ];
                /** @todo ! all stats */
            }
        }

        return $stats;
    }

    /**
     * @param Stats $stats
     * @return ResponseInterface
     */
    public function setStats(Stats $stats): ResponseInterface
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage ?: 0;
    }

    /**
     * @param int $currentPage
     * @return ResponseInterface
     */
    public function setCurrentPage(int $currentPage): ResponseInterface
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @param int $statusCode
     * @return ResponseInterface
     */
    public function setStatusCode(int $statusCode): ResponseInterface
    {
        $this->statusCode = (int)$statusCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int)$this->statusCode;
    }

    /**
     * @param string $statusMessage
     * @return ResponseInterface
     */
    public function setStatusMessage(string $statusMessage): ResponseInterface
    {
        $this->statusMessage = (string)$statusMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusMessage(): string
    {
        return (string)$this->statusMessage;
    }

    /**
     * @param $query
     * @return Response
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return MsCatalogQueryInterface|string
     * @throws Exception
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $debugInfo
     * @return ResponseInterface
     */
    public function setDebugInfo(array $debugInfo): ResponseInterface
    {
        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * @return array
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
}
