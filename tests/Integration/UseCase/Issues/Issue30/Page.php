<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Issues\Issue30;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * @OGM\Node(label="Page")
 */
class Page
{
    /**
     * @OGM\GraphId()
     * @var int
     */
    protected $id;
}