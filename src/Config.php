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
    private $core;

    /**
     * Config constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $path
     * @param string $core
     */
    public function __construct($host, $port, $path, $core)
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->core = $core;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getCore()
    {
        return $this->core;
    }

    public function getEngine()
    {
        // TODO: Implement getEngine() method.
    }

    public function setEngine(int $engine)
    {
        // TODO: Implement setEngine() method.
    }

    public function setHost(string $host)
    {
        // TODO: Implement setHost() method.
    }

    public function setPort(int $port)
    {
        // TODO: Implement setPort() method.
    }

    public function setPath(string $path)
    {
        // TODO: Implement setPath() method.
    }

    public function setCore(string $core)
    {
        // TODO: Implement setCore() method.
    }


    public function getPageSize()
    {
        // TODO: Implement getPageSize() method.
    }

    public function setPageSize(int $pageSize)
    {
        // TODO: Implement setPageSize() method.
    }

    public function isClearIndexBeforeReindex()
    {
        // TODO: Implement isClearIndexBeforeReindex() method.
    }

    public function setClearIndexBeforeReindex(bool $clearIndexBeforeReindex)
    {
        // TODO: Implement setClearIndexBeforeReindex() method.
    }

    /**
     * @return array
     */
    public function getConnectionConfigArray(): array
    {
        return [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'core' => $this->getCore(),
        ];
    }

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return [
            'endpoint' => [
                'localhost' => $this->getConnectionConfigArray()
            ]
        ];
    }
}
