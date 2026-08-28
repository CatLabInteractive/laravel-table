<?php

namespace CatLab\Laravel\Table\Models;

/**
 * A table header: its label and, when sorting is enabled for it, the url
 * that sorts on it and the direction the table is currently sorted in.
 */
class Column
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string|null
     */
    private $sortUrl;

    /**
     * @var string|null
     */
    private $sortDirection;

    /**
     * @param string $key
     * @param string|null $sortUrl
     * @param string|null $sortDirection
     */
    public function __construct($key, $sortUrl = null, $sortDirection = null)
    {
        $this->key = $key;
        $this->sortUrl = $sortUrl;
        $this->sortDirection = $sortDirection;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        return $this->sortUrl !== null;
    }

    /**
     * @return string|null
     */
    public function getSortUrl()
    {
        return $this->sortUrl;
    }

    /**
     * @return bool
     */
    public function isSortedAscending()
    {
        return $this->sortDirection === self::ASC;
    }

    /**
     * @return bool
     */
    public function isSortedDescending()
    {
        return $this->sortDirection === self::DESC;
    }
}
