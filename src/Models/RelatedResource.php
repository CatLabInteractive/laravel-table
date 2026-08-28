<?php

namespace CatLab\Laravel\Table\Models;

/**
 * A related resource as shown inside a relationship cell.
 */
class RelatedResource
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @param string $label
     * @param string|null $url
     */
    public function __construct($label, $url = null)
    {
        $this->label = $label;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function hasUrl()
    {
        return $this->url !== null;
    }
}
