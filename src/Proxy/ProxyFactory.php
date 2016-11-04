<?php

/*
 * This file is part of the GraphAware Neo4j PHP OGM package.
 *
 * (c) GraphAware Ltd <info@graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\OGM\Proxy;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\Metadata\NodeEntityMetadata;
use GraphAware\Neo4j\OGM\Metadata\RelationshipMetadata;
use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\Factory\LazyLoadingGhostFactory;

class ProxyFactory
{
    private $classMetadata;

    private $entityManager;

    private $lazyLoadingFactory;

    public function __construct(EntityManager $entityManager, NodeEntityMetadata $entityMetadata)
    {
        $this->entityManager = $entityManager;
        $this->classMetadata = $entityMetadata;
        $config = new Configuration();
        $dir = sys_get_temp_dir();
        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($dir)));
        $config->setProxiesTargetDir($dir);
        spl_autoload_register($config->getProxyAutoloader());
        $this->lazyLoadingFactory = new LazyLoadingGhostFactory($config);
    }

    public function createProxy($nodeId, RelationshipMetadata $relationshipMetadata)
    {
        if ($relationshipMetadata->isCollection()) {
            throw new \LogicException(sprintf('The relationship is of type collection and should not be proxied'));
        }

        $relationshipType = $relationshipMetadata->getType();
        $propertyName = $relationshipMetadata->getPropertyName();
        $direction = $relationshipMetadata->getDirection();

        $pattern = '-[%s]-';
        switch ($direction) {
            case 'OUTGOING':
                $pattern = '-[%]->';
                break;
            case 'INCOMING':
                $pattern = '<-[%]-';
                break;
        }

        $pattern = sprintf($pattern, $relationshipType);

        $query = sprintf('MATCH (n) WHERE id(n) = {id}
        OPTIONAL MATCH (n)%s(%s) RETURN %s', $pattern, $propertyName, $propertyName);

        $statement = Statement::prepare($query, ['id' => $nodeId]);
        $em = $this->entityManager;
        $classMetadata = $this->classMetadata;
        $targetMetadata = $this->entityManager->getClassMetadata($relationshipMetadata->getTargetEntity());

        $initializer = function($ghostObject, $method, array $parameters, & $initializer, array $properties) use ($em, $statement, $classMetadata, $targetMetadata, $propertyName) {
            $initializer = null;

            $result = $em->getDatabaseDriver()->run($statement->text(), $statement->parameters());
            if ($result->size() > 1) {
                throw new \RuntimeException(sprintf('Expected only one record, got %d', $result->size()));
            }

            $record = $result->firstRecord();
            //$node = $record->nodeValue($)
            foreach ($targetMetadata->getPropertiesMetadata() as $propertyMetadata) {

            }

            return true;
        };

        $proxyOptions = [];
        $instance = $this->lazyLoadingFactory->createProxy($this->classMetadata->getClassName(), $initializer, $proxyOptions);
    }
}