<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\ConfigInterface;

/**
 * Class Config
 * @package G4NReact\MsCatalogSolr
 */
class Config
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Config constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
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
            'collection' => $this->getCollection(),
            'core'       => $this->getCore(),
        ];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->config->getEngineParams()['connection']['host'] ?? '';
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->config->getEngineParams()['connection']['port'] ?? 0;
    }

    /**
     * @return string
     */
    public function getCore(): string
    {
        return $this->config->getEngineParams()['connection']['core'] ?? '';
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->config->getEngineParams()['connection']['collection'] ?? '';
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        /** @ToDo: Implement puller page size */
        return (int)$this->config->getPusherPageSize();
    }

    /**
     * @return bool
     */
    public function isClearIndexBeforeReindex(): bool
    {
        return (bool)$this->config->getPusherDeleteIndex();
    }
}
