<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\ConfigInterface;
use G4NReact\MsCatalog\Helper as MsCatalogHelper;

/**
 * Class Config
 * @package G4NReact\MsCatalogSolr
 */
class Config
{
    const MODE_PUSHER = 'pusher';
    const MODE_PULLER = 'puller';

    /**
     * @var string|null
     */
    protected $mode = null;

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
        if (!$this->mode) {
            if ($this->config->getPullerEngine() === MsCatalogHelper::ENGINE_SOLR_VALUE) {
                $this->mode = self::MODE_PULLER;
            } elseif ($this->config->getPusherEngine() === MsCatalogHelper::ENGINE_SOLR_VALUE) {
                $this->mode = self::MODE_PUSHER;
            } else {
                // log error, throw exception etc.
                return [];
            }
        }

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
        return ($this->mode === self::MODE_PULLER)
            ? ($this->config->getPullerEngineParams()['connection']['host'] ?? '')
            : ($this->config->getPusherEngineParams()['connection']['host'] ?? '');
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return ($this->mode === self::MODE_PULLER)
            ? ($this->config->getPullerEngineParams()['connection']['port'] ?? 0)
            : ($this->config->getPusherEngineParams()['connection']['port'] ?? 0);
    }

    /**
     * @return string
     */
    public function getCore(): string
    {
        return ($this->mode === self::MODE_PULLER)
            ? ($this->config->getPullerEngineParams()['connection']['core'] ?? '')
            : ($this->config->getPusherEngineParams()['connection']['core'] ?? '');
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return ($this->mode === self::MODE_PULLER)
            ? ($this->config->getPullerEngineParams()['connection']['collection'] ?? '')
            : ($this->config->getPusherEngineParams()['connection']['collection'] ?? '');
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return ($this->mode === self::MODE_PULLER)
            ? (int)$this->config->getPullerPageSize()
            : (int)$this->config->getPusherPageSize();
    }

    /**
     * @return bool
     */
    public function isClearIndexBeforeReindex(): bool
    {
        return ($this->mode === self::MODE_PUSHER)
            ? (bool)$this->config->getPusherDeleteIndex()
            : false;
    }
}
