<?php

namespace CatLab\Laravel\Table\Models;

use CatLab\Charon\Interfaces\ResourceCollection;

/**
 * Class Pagination
 * @package CatLab\CharonFrontend\Models\Table
 */
class Pagination
{
    protected $paginationQueryParameters = [
        'page',
        'before',
        'after'
    ];

    /**
     * @var ResourceCollection
     */
    private $collection = [];

    /**
     * @var string
     */
    private $url;

    /**
     * @var string|false
     */
    private $nextUrl = false;

    /**
     * @var string|false
     */
    private $previousUrl = false;

    /**
     * Pagination constructor.
     * @param ResourceCollection $collection
     * @param $url
     */
    public function __construct(
        ResourceCollection $collection,
        $url
    ) {
        $this->collection = $collection;
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return $this->getNextUrl() !== null;
    }

    /**
     * @return PaginationUrl|null
     */
    public function getNextUrl()
    {
        if ($this->nextUrl === false) {
            $this->nextUrl = null;
            $pagination = $this->collection->getMeta('pagination');
            if ($pagination && isset($pagination['next'])) {
                $queryParameters = $this->extractQueryParameters($pagination['next']);
                $this->nextUrl = $this->buildUrl($queryParameters, 'Next');
            }
        }
        return $this->nextUrl;
    }

    /**
     * @return bool
     */
    public function hasPrevious()
    {
        return $this->getPreviousUrl() !== null;
    }

    /**
     * @return PaginationUrl|null
     */
    public function getPreviousUrl()
    {
        if ($this->previousUrl === false) {
            $this->previousUrl = null;
            $pagination = $this->collection->getMeta('pagination');
            if ($pagination && isset($pagination['previous'])) {
                $queryParameters = $this->extractQueryParameters($pagination['previous']);
                $this->previousUrl = $this->buildUrl($queryParameters, 'Previous');
            }
        }
        return $this->previousUrl;
    }

    /**
     * @param null $maxLinks
     * @return string[]
     */
    public function getPages($maxLinks = 6)
    {
        $out = [];

        $page = $this->collection->getMeta('page');
        if ($page && isset($page['current-page'])) {

            $range = intval(floor($maxLinks / 2));
            $currentPage = intval($page['current-page']);
            $lastPage = intval($page['last-page']);

            $startIndex = $currentPage - $range;
            $endIndex = $currentPage + $range;

            if ($startIndex < 1) {
                $endIndex += 0 - $startIndex;
                $startIndex = 1;
            }

            if ($endIndex > $lastPage) {
                $startIndex -= $endIndex - $lastPage;
                $endIndex = $lastPage;
            }

            if ($startIndex < 1) {
                $startIndex = 1;
            }

            for ($i = $startIndex; $i <= $endIndex; $i ++) {
                $url = $this->buildUrl([ 'page' => $i ], $i);
                $url->setActive($i === $currentPage);
                $out[] = $url;
            }
        }

        return $out;
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function buildUrl(array $parameters, $label)
    {
        // first extract the query parameters that exist now.
        $parts = parse_url($this->url);

        $queryParameters = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParameters);
        }

        // now unset all query parameters that are reserved
        foreach ($this->paginationQueryParameters as $v) {
            unset($queryParameters[$v]);
        }

        // now add the new parameters
        $queryParameters = array_merge($queryParameters, $parameters);
        $queryString = http_build_query($queryParameters);

        return new PaginationUrl($parts['path'] . '?' . $queryString, $label);
    }

    /**
     * @param $apiUrl
     * @return array
     */
    protected function extractQueryParameters($apiUrl)
    {
        $parts = parse_url($apiUrl);

        $queryParameters = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParameters);
        }
        return $queryParameters;
    }
}
