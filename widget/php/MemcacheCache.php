<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Cache.php';

class MemcacheCache implements Cache
{
    private $cache;
    private $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
        $this->cache = new Memcache();
        $this->cache->connect($configuration['server'], $configuration['port']) or die ("Could not connect to memcache: ({$configuration['server']}:{$configuration['port']}).");
    }

    public function set($id, $data)
    {
        return $this->cache->set($id, $data, 0, $this->configuration['clean_interval']);
    }

    public function get($id)
    {
        return $this->cache->get($id);
    }
}