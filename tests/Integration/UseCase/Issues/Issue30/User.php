<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Issues\Issue30;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @OGM\Node(label="User")
 */
class User
{
    /**
     * @OGM\GraphId()
     * @var int
     */
    protected $id;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $first_name;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $last_name;

    /**
     * @OGM\Property(type="string")
     * @var int
     */
    protected $email;

    /**
     * @OGM\Property(type="boolean")
     * @var boolean
     */
    protected $active;

    /**
     * @OGM\Property(type="string")
     * @var int
     */
    protected $password;

    /**
     * @OGM\Relationship(relationshipEntity="Manager", type="MANAGE", direction="OUTGOING", collection=true)
     * @var ArrayCollection|Page[]
     */
    protected $pages;

    public function __construct($first_name, $last_name, $email, $password, $active)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
        $this->active = $active;
        $this->pages = new ArrayCollection();
    }

    /**
     * @param Page $page
     * @param OGM\Entity $entity
     */
    public function addPage(Page $page, OGM\Entity $entity)
    {
        $this->pages->add(new Manager($this, $page, $entity));
    }
}
