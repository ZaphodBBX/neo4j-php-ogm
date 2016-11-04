<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Reported\Domain;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @OGM\Node(label="TestUser")
 */
class TestUser
{

    /**
     * @OGM\GraphId()
     *
     * @var int
     */
    protected $id;


    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="TEST_USER_HAS_SPONSORED_CHILDREN", direction="OUTGOING", targetEntity="TestUser", collection=true, mappedBy="sponsoredBy")
     * @OGM\Lazy()
     * @OGM\OrderBy(property="name", order="ASC")
     *
     * @var ArrayCollection|TestUser[]
     */
    protected $sponsoredChildren;

    public function __construct()
    {
        $this->sponsoredChildren = new ArrayCollection();
    }



    /**
     *
     * @param
     *            User
     */
    public function addSponsoredChild(TestUser $obj)
    {
        if (! $this->sponsoredChildren->contains($obj)) {
            $this->sponsoredChildren->add($obj);
        }
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param
     *            $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param
     *            $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return  ArrayCollection|TestUser[]
     */
    public function getSponsoredChildren()
    {
        return $this->sponsoredChildren;
    }

    /**
     *
     * @param $sponsoredChildren
     */
    public function setSponsoredChildren($sponsoredChildren)
    {
        $this->sponsoredChildren = $sponsoredChildren;
        return $this;
    }
}