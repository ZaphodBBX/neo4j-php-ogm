<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Issues\Issue30;

use GraphAware\Neo4j\OGM\Annotations as OGM;

class Artist implements OGM\Entity
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $slug;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $short_description;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * @param string $short_description
     */
    public function setShortDescription($short_description)
    {
        $this->short_description = $short_description;
    }
}