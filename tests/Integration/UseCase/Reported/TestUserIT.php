<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Reported;

use GraphAware\Neo4j\OGM\Tests\Integration\IntegrationTestCase;
use GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Reported\Domain\TestUser;

class TestUserIT extends IntegrationTestCase
{
    public function testUseCase()
    {
        $this->clearDb();

        $u1 = new TestUser();
        $u1->setName('u1');

        $u2 = new TestUser();
        $u2->setName('u2');

        $u3 = new TestUser();
        $u3->setName('u3');
        $u1->addSponsoredChild($u2);
        $u2->addSponsoredChild($u3);

        $this->em->persist($u1);
        $this->em->flush();
    }
}