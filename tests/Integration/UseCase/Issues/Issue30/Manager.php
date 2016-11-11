<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Issues\Issue30;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * @OGM\RelationshipEntity(type="MANAGE")
 */
class Manager
{
    /**
     * @OGM\GraphId()
     * @var int
     */
    protected $id;

    /**
     * @OGM\StartNode(targetEntity="User")
     * @var User
     */
    protected $user;

    /**
     * @OGM\EndNode(targetEntity="Page")
     * @var Page
     */
    protected $page;

    protected $entity;

    /**
     * Manage constructor.
     * @param User $user
     * @param Page $page
     * @param OGM\Entity $entity
     */
    public function __construct(User $user, Page $page, OGM\Entity $entity)
    {
        $this->user = $user;
        $this->page = $page;
        $this->entity = $entity;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->type;
    }

    /**
     * @param OGM\Entity $entity
     * @internal param string $type
     */
    public function setEntity(OGM\Entity $entity)
    {
        $this->entity = $entity;
    }
}