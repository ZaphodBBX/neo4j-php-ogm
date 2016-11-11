<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Issues\Issue30;

use GraphAware\Neo4j\OGM\Tests\Integration\IntegrationTestCase;

class Issue30IT extends IntegrationTestCase
{
    public function testCreation()
    {
        $this->clearDb();

        $user = new User('Chris', 'Will', 'php@graphaware.com', 'password', true);

        $artist = new Artist;
        $artist->setName('Artist Name');
        $artist->setSlug('artist-name');
        $artist->setShortDescription('This is a description');

        $user->addPage(new Page, new Artist());

        $this->em->persist($user);
        $this->em->flush();
    }
}