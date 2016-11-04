<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github;

use GraphAware\Neo4j\OGM\Lazy\LazyRelationshipCollection;
use GraphAware\Neo4j\OGM\Tests\Integration\IntegrationTestCase;
use GraphAware\Neo4j\OGM\Tests\Integration\Model\Repository;
use GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubRepository;
use GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubUser;
use GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\Language;
use GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\Organization;

class GithubIntegrationTest extends IntegrationTestCase
{
    public function testSimpleUserIsSaved()
    {
        $this->clearDb();
        $user = new GithubUser('ikwattro');
        $user->setDescription("neo4j consultant");
        $this->em->persist($user);
        $this->em->flush();

        $this->assertGraphExist('(u:User {login:"ikwattro", description:"neo4j consultant"})');
    }

    public function testSimpleUserIsFetched()
    {
        $this->clearDb();
        $this->client->run("CREATE (n:User {login:'ikwattro'})-[:MEMBER_OF]->(o:Organization {name:'GraphAware'})-[:IN_COUNTRY]->(c:Country {name:'UK'}),
        (n2:User {login:'alenegro81'})
        MERGE (n2)-[:MEMBER_OF]->(o)");
        /** @var GithubUser $user */
        $user = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $this->assertCount(1, $user->getOrganizations());
        $this->assertEquals("ikwattro", $user->getOrganizations()[0]->getMembers()[0]->getLogin());
        $this->assertEquals("ikwattro", $user->getOrganizations()[0]->getMembers()[0]->getOrganizations()[0]->getMembers()[0]->getLogin());
        $this->assertInstanceOf(LazyRelationshipCollection::class, $user->getOwnedRepositories());
    }

    public function testSimpleUserIsSavedAndUpdatedPropertiesAreUpdated()
    {
        $this->clearDb();
        $user = new GithubUser('ikwattro');
        $user->setDescription("neo4j consultant");
        $this->em->persist($user);
        $this->em->flush();
        $repository = new GithubRepository('neo4j');
        $user->addStarred($repository);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro", description:"neo4j consultant"})');
        $user->setDescription("neo4j developer");
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro", description:"neo4j developer"})');
    }

    public function testUserIsFetchedFromDatabaseAndUpdated()
    {
        $this->clearDb();
        $user = new GithubUser('ikwattro');
        $user->setDescription("neo4j consultant");
        $this->em->persist($user);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro", description:"neo4j consultant"})');
        $this->em->clear();

        /** @var GithubUser $ikwattro */
        $ikwattro = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $this->assertEquals('ikwattro', $ikwattro->getLogin());
        $this->assertEquals('neo4j consultant', $ikwattro->getDescription());
        $this->assertTrue($ikwattro->getOwnedRepositories() instanceof LazyRelationshipCollection);
        $ikwattro->setDescription("neo4j developer");
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro", description:"neo4j developer"})');
    }

    public function testUserAssignedRepositoryWorkflow()
    {
        $this->clearDb();
        $user = $this->createUser('ikwattro');
        $repo = new GithubRepository('neo4j-reco', $user);
        $this->em->persist($repo);
        $user->getOwnedRepositories()->add($repo);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro"})-[:OWNS]->(r:Repository {name:"neo4j-reco"})');
        $this->em->clear();

        /** @var GithubUser $ikwattro */
        $ikwattro = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $this->assertTrue($ikwattro->getOwnedRepositories()->first() instanceof GithubRepository);
        $this->assertInstanceOf(LazyRelationshipCollection::class, $ikwattro->getOwnedRepositories());
        $this->assertEquals($ikwattro->getLogin(), $ikwattro->getOwnedRepositories()[0]->getOwner()->getLogin());
        $repo = $ikwattro->getOwnedRepositories()[0];
        $ikwattro->removeRepository($repo);
        $this->assertCount(0, $ikwattro->getOwnedRepositories());
        $this->em->flush();
        $this->assertGraphNotExist('(u:User {login:"ikwattro"})-[:OWNS]->(r:Repository {name:"neo4j-reco"})');
        // test nodes are not changed
        $this->assertGraphExist('(u:User {login:"ikwattro"}), (r:Repository {name:"neo4j-reco"})');
    }

    public function testCommit()
    {
        $u1 = new GithubUser('login');
        $u2 = new GithubUser('login2');
        $u3 = new GithubUser('login3');
        $this->em->persist($u1);
        $this->em->persist($u2);
        $this->em->persist($u3);
        $this->em->flush();
    }

    public function testFetchingFromOrganizationAndUpdates()
    {
        $this->clearDb();
        $this->clearDb();
        $this->client->run("CREATE (n:User {login:'ikwattro'})-[:MEMBER_OF]->(o:Organization {name:'GraphAware'})-[:IN_COUNTRY]->(c:Country {name:'UK'}),
        (n2:User {login:'alenegro81'})
        MERGE (n2)-[:MEMBER_OF]->(o)");

        /** @var Organization $org */
        $org = $this->em->getRepository(Organization::class)->findOneBy('name', 'GraphAware');
        $this->assertInstanceOf(LazyRelationshipCollection::class, $org->getMembers());
        $this->assertCount(2, $org->getMembers());
        $this->assertEquals('UK', $org->getCountry()->getName());
        $this->assertEquals('ikwattro', $org->getMember('ikwattro')->getOrganizations()[0]->getMember('ikwattro')->getOrganizations()[0]->getMember('ikwattro')->getLogin());
        $neo = new Organization("neo4j");
        $this->em->persist($neo);
        $org->getMember('ikwattro')->addOrganization($neo);
        $this->em->flush();
        $this->assertGraphExist('(o:Organization {name:"GraphAware"})<-[:MEMBER_OF]-(u:User {login:"ikwattro"})-[:MEMBER_OF]->(o2:Organization {name:"neo4j"})');
    }

    public function testCreatingRepositoryLanguage()
    {
        $this->clearDb();
        $repo = new GithubRepository('neo4j-reco');
        $language = new Language("java");
        $repo->addLangage($language, 3000);
        $this->em->persist($repo);
        $this->em->flush();
        $this->assertGraphExist('(r:Repository {name:"neo4j-reco"})-[r2:WRITTEN_IN {linesOfCode: 3000}]->(l:Language {name:"java"})');
    }

    public function testFetchingRepositoryLanguages()
    {
        $this->clearDb();
        $this->client->run('CREATE (r:Repository {name:"neo4j-reco"})-[r2:WRITTEN_IN {linesOfCode: 3000}]->(l:Language {name:"java"}), 
        (u:User {login:"ikwattro"})-[:OWNS]->(r)');
        /** @var GithubRepository $repo */
        $repo = $this->em->getRepository(GithubRepository::class)->findOneBy('name', 'neo4j-reco');
        $this->assertEquals('ikwattro', $repo->getOwner()->getLogin());
        $this->assertCount(1, $repo->getWrittenLanguages());
        $this->assertCount(1, $repo->getWrittenLanguages()[0]->getLanguage()->getRepositories()[0]->getRepository()->getWrittenLanguages());
        $this->assertEquals(3000, $repo->getWrittenLanguages()[0]->getLinesOfCode());
        $this->assertEquals(3000, $repo->getWrittenLanguages()[0]->getLanguage()->getRepositories()[0]->getRepository()->getWrittenLanguages()[0]->getLinesOfCode());
        $this->assertInstanceOf(LazyRelationshipCollection::class, $repo->getWrittenLanguages()[0]->getLanguage()->getRepositories());
    }

    public function testFetchingLanguages()
    {
        $this->clearDb();
        $this->client->run('CREATE (r:Repository {name:"neo4j-reco"})-[r2:WRITTEN_IN {linesOfCode: 3000}]->(l:Language {name:"java"}), 
        (u:User {login:"ikwattro"})-[:OWNS]->(r)');
        /** @var Language $language */
        $language = $this->em->getRepository(Language::class)->findOneBy('name', 'java');
        $this->assertNotNull($language);
        $this->assertInstanceOf(LazyRelationshipCollection::class, $language->getRepositories());
        $this->assertCount(1, $language->getRepositories());
        $this->assertEquals('neo4j-reco', $language->getRepositories()[0]->getRepository()->getName());
        $this->assertEquals('java', $language->getRepositories()[0]->getRepository()->getWrittenLanguages()[0]->getLanguage()->getName());
        $this->assertCount(1, $language->getRepositories()[0]->getRepository()->getWrittenLanguages()[0]->getLanguage()->getRepositories());
        $this->assertEquals(3000, $language->getRepositories()[0]->getLinesOfCode());
        // This is a current known limitation
        //$this->assertEquals('ikwattro', $language->getRepositories()[0]->getRepository()->getOwner()->getLogin());
    }

    public function testFollowsAndFollowedBy()
    {
        $this->clearDb();
        $this->client->run('CREATE (n:User {login:"ikwattro"})-[:FOLLOWS]->(a:User {login:"alenegro81"})-[:FOLLOWS]->(m:User {login:"michal"}),
        (a)-[:FOLLOWS]->(l:User {login:"luanne"})-[:FOLLOWS]->(n),
        (l)-[:FOLLOWS]->(v:User {login:"vince"}),
        (v)-[:MEMBER_OF]->(o:Organization {name:"GraphAware"}),
        (v)-[:OWNS]->(r:Repository {name:"data-bridge"})-[:WRITTEN_IN {linesOfCode: 12000}]->(l1:Language {name:"java"}),
        (r)-[:WRITTEN_IN {linesOfCode: 100}]->(l2:Language {name:"json"})');

        /** @var GithubUser $ikwattro */
        $ikwattro = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $this->assertNotNull($ikwattro);
        $this->assertInstanceOf(LazyRelationshipCollection::class, $ikwattro->getFollows());
        $this->assertInstanceOf(LazyRelationshipCollection::class, $ikwattro->getFollowedBy());
        $this->assertCount(1, $ikwattro->getFollows());
        $this->assertCount(1, $ikwattro->getFollowedBy());
        $ale = $ikwattro->getFollows()[0];
        $this->assertCount(2, $ale->getFollows());
        $luanne = $ale->getFollow('luanne');
        $this->assertEquals(spl_object_hash($ikwattro), spl_object_hash($luanne->getFollow('ikwattro')));
        $this->assertCount(1, $luanne->getFollow('vince')->getOwnedRepositories());
        $vince = $luanne->getFollow('vince');
        $vince->setDescription("Senior Chimp");
        $this->em->flush();
        $this->assertGraphExist('(v:User {login:"vince", description:"Senior Chimp"})');
        $this->assertCount(2, $vince->getOwnedRepositories()[0]->getWrittenLanguages());
        $this->assertEquals(100, $vince->getOwnedRepositories()[0]->getWrittenLanguage('json')->getLinesOfCode());
    }

    public function testAddingStarred()
    {
        $this->clearDb();
        $repo = new GithubRepository('neo4j-reco');
        $user = new GithubUser('ikwattro');
        $user2 = new GithubUser('alenegro81');
        $this->em->persist($repo);
        $this->em->persist($user);
        $this->em->persist($user2);
        $this->em->flush();
        $this->em->clear();
        /** @var GithubUser $ikwattro */
        $ikwattro = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        /** @var GithubRepository $repo */
        $repo = $this->em->getRepository(GithubRepository::class)->findOneBy('name', 'neo4j-reco');
        $ikwattro->addStarred($repo);
        $this->em->flush();
        /** @var GithubUser $ale */
        $ale = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'alenegro81');
        $ale->addStarred($repo);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro"})-[:STARS]->(r:Repository {name:"neo4j-reco"})<-[:STARS]-(u2:User {login:"alenegro81"})');
        $this->em->clear();
        $vince = new GithubUser('vince');
        $repo = $this->em->getRepository(GithubRepository::class)->findOneBy('name', 'neo4j-reco');
        $vince->addStarred($repo);
        $this->em->persist($vince);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"vince"})-[:STARS]->(r:Repository {name:"neo4j-reco"})');
    }

    public function testGettingAllUsers()
    {
        $this->clearDb();
        $this->client->run('CREATE (n:User {login:"ikwattro"})-[:FOLLOWS]->(a:User {login:"alenegro81"})-[:FOLLOWS]->(m:User {login:"michal"}),
        (a)-[:FOLLOWS]->(l:User {login:"luanne"})-[:FOLLOWS]->(n),
        (l)-[:FOLLOWS]->(v:User {login:"vince"}),
        (v)-[:MEMBER_OF]->(o:Organization {name:"GraphAware"}),
        (v)-[:OWNS]->(r:Repository {name:"data-bridge"})-[:WRITTEN_IN {linesOfCode: 12000}]->(l1:Language {name:"java"}),
        (r)-[:WRITTEN_IN {linesOfCode: 100}]->(l2:Language {name:"json"})');

        /** @var GithubUser[] $users */
        $users = $this->em->getRepository(GithubUser::class)->findAll();
        foreach ($users as $user) {
            $this->assertInstanceOf(LazyRelationshipCollection::class, $user->getOwnedRepositories());
            count($user->getFollows());
            count($user->getFollowedBy());
            $user->setDescription('wazaaaaaaaaaa!!');
            $this->em->persist($user);
        }
        $this->em->flush();
    }

    public function testAddingRelationshipEntities()
    {
        $this->clearDb();
        $this->client->run('CREATE (n:User {login:"ikwattro"})-[:MEMBER_OF]->(o:Organization {name:"GraphAware"}), (n)-[:IN_TEAM {since:123455}]->(t:Team {name:"team1"})');
        /** @var GithubUser $user */
        $user = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $repository = new GithubRepository('neo4j-reco');
        $language = new Language('java');
        $repository->addLangage($language, 3000);
        $user->getOwnedRepositories()->add($repository);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"ikwattro"})-[:IN_TEAM]->(t:Team {name:"team1"})');
    }

    public function testSingleRelationshipsOnRelatedEntitiesAreProxied()
    {
        $this->clearDb();
        $this->client->run('CREATE (n:User {login:"ikwattro"})-[:MEMBER_OF]->(o:Organization {name:"GraphAware"})-[:IN_COUNTRY]->(c:Country {name:"UK"})');
        /** @var GithubUser $user */
        $user = $this->em->getRepository(GithubUser::class)->findOneBy('login', 'ikwattro');
        $this->assertInstanceOf(GithubUser::class, $user);
        $this->assertInstanceOf(Organization::class, $user->getOrganizations()[0]);
        $this->assertEquals('GraphAware', $user->getOrganizations()[0]->getName());
        $this->assertEquals('UK', $user->getOrganizations()[0]->getCountry()->getName());
    }

    /**
     * @param $login
     * @return \GraphAware\Neo4j\OGM\Tests\Integration\UseCase\Github\Model\GithubUser
     */
    private function createUser($login)
    {
        $user = new GithubUser($login);
        $user->setDescription("neo4j consultant");
        $this->em->persist($user);
        $this->em->flush();
        $this->assertGraphExist('(u:User {login:"'.$login.'", description:"neo4j consultant"})');

        return $user;
    }
}