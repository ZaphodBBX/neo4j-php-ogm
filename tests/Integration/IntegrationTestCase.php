<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration;

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\OGM\Manager;

class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \GraphAware\Neo4j\Client\Client
     */
    protected $client;

    /**
     * @var \GraphAware\Neo4j\OGM\Manager
     */
    protected $em;

    public function setUp()
    {
        $this->client = ClientBuilder::create()
            ->addConnection('default', 'bolt://localhost')
            ->build();

        $this->em = new Manager($this->client);
    }

    public function clearDb()
    {
        $this->client->run('MATCH (n) DETACH DELETE n');
    }

    public function resetEm()
    {
        $this->em->clear();
    }
}