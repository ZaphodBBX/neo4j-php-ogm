<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class GithubTeam
 * @package GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model
 *
 * @OGM\Node(label="UserTeam")
 */
class GithubTeam
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $name;

    /**
     * @var UserTeam
     *
     * @OGM\Relationship(relationshipEntity="UserTeam", direction="INCOMING", collection=false, mappedBy="team")
     */
    protected $userTeam;

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
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\UserTeam
     */
    public function getUserTeam()
    {
        return $this->userTeam;
    }

    /**
     * @param \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\UserTeam $userTeam
     */
    public function setUserTeam($userTeam)
    {
        $this->userTeam = $userTeam;
    }
}