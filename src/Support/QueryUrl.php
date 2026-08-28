<?php

namespace CatLab\Laravel\Table\Support;

/**
 * Small helper to derive urls from the current one by changing query
 * parameters: keep everything, override some, drop some.
 */
class QueryUrl
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $query = [];

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $parts = parse_url($url);
        $this->path = $parts['path'] ?? '';

        if (isset($parts['query'])) {
            parse_str($parts['query'], $this->query);
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->query[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $set parameters to add or replace
     * @param string[] $unset parameters to remove
     * @return string
     */
    public function with(array $set = [], array $unset = [])
    {
        $query = $this->query;
        foreach ($unset as $key) {
            unset($query[$key]);
        }

        $query = array_merge($query, $set);
        if (count($query) === 0) {
            return $this->path;
        }

        return $this->path . '?' . http_build_query($query);
    }
}
