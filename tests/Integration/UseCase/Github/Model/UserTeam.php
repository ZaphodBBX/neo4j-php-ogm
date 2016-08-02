<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class UserTeam
 * @package GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model
 *
 * @OGM\RelationshipEntity(type="IN_TEAM")
 */
class UserTeam
{
    /**
     * @var int@
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var GithubUser
     *
     * @OGM\StartNode(targetEntity="GithubUser")
     *
     */
    protected $user;

    /**
     * @var GithubTeam
     *
     * @OGM\EndNode(targetEntity="GithubTeam")
     */
    protected $team;

    /**
     * @var int
     *
     * @OGM\Property(type="int")
     */
    protected $since;

    public function __construct(GithubUser $user, GithubTeam $team)
    {
        $this->user = $user;
        $this->team = $team;
        $this->since = time();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubTeam
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return int
     */
    public function getSince()
    {
        return $this->since;
    }
}