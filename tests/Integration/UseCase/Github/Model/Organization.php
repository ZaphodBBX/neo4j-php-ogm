<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Organization
 * @package GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model
 *
 * @OGM\Node(label="Organization")
 */
class Organization
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    private $id;

    /**
     * @var string
     *
     * @OGM\Property(type="string")
     */
    private $name;

    /**
     * @var GithubUser[]
     *
     * @OGM\Relationship(targetEntity="GithubUser", type="MEMBER_OF", collection=true, direction="INCOMING", mappedBy="organizations")
     * @OGM\Lazy()
     */
    private $members;

    /**
     * @var Country
     *
     * @OGM\Relationship(targetEntity="Country", type="IN_COUNTRY", direction="OUTGOING")
     */
    private $country;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubUser[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\Country $country
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
    }
}