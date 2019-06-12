<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\ConfigInterface;

/**
 * Class Config
 * @package G4NReact\MsCatalogSolr
 */
class Config implements ConfigInterface
{
    /**
     * Engine types
     */
    const ENGINE_SOLR = 1;

    /**
     * @var int
     */
    private $engine;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $collection;

    /**
     * @var string
     */
    private $core;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var bool
     */
    private $clearIndexBeforeReindex;

    /**
     * Config constructor
     *
     * @param string $host
     * @param int $port
     * @param string $path
     * @param string $collection
     * @param string $core
     */
    public function __construct($host, $port, $path, $collection, $core)
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->collection = $collection;
        $this->core = $core;
    }

    /**
     * @return array
     */
    public function getConfigArray(): array
    {
        return [
            'endpoint' => [
                'localhost' => $this->getConnectionConfigArray()
            ]
        ];
    }

    /**
     * @return array
     */
    public function getConnectionConfigArray(): array
    {
        return [
            'host'       => $this->getHost(),
            'port'       => $this->getPort(),
            'path'       => $this->getPath(),
            'collection' => $this->getCollection(),
            'core'       => $this->getCore(),
        ];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return ConfigInterface
     */
    public function setHost(string $host): ConfigInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return ConfigInterface
     */
    public function setPort(int $port): ConfigInterface
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ConfigInterface
     */
    public function setPath(string $path): ConfigInterface
    {
        $this->path = '/'; // since Solarium 5.0

        return $this;
    }

    /**
     * @return string
     */
    public function getCore(): string
    {
        return $this->core;
    }

    /**
     * @param string $core
     * @return ConfigInterface
     */
    public function setCore(string $core): ConfigInterface
    {
        $this->core = $core;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     * @return ConfigInterface
     */
    public function setCollection(string $collection): ConfigInterface
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param int $engine
     * @return ConfigInterface
     */
    public function setEngine(int $engine): ConfigInterface
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return ConfigInterface
     */
    public function setPageSize(int $pageSize): ConfigInterface
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClearIndexBeforeReindex(): bool
    {
        return (bool)$this->clearIndexBeforeReindex;
    }

    /**
     * @param bool $clearIndexBeforeReindex
     * @return ConfigInterface
     */
    public function setClearIndexBeforeReindex(bool $clearIndexBeforeReindex): ConfigInterface
    {
        $this->clearIndexBeforeReindex = (bool)$clearIndexBeforeReindex;

        return $this;
    }
}
