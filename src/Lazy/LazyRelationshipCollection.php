<?php

/**
 * This file is part of the GraphAware Neo4j OGM package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\OGM\Lazy;

use Doctrine\Common\Collections\AbstractLazyCollection;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\Finder\RelationshipEntityFinder;
use GraphAware\Neo4j\OGM\Finder\RelationshipsFinder;
use GraphAware\Neo4j\OGM\Metadata\RelationshipMetadata;

class LazyRelationshipCollection extends AbstractLazyCollection
{
    protected $em;

    protected $finder;

    protected $baseId;

    protected $initialEntity;

    protected $baseEntityClass;

    protected $relationshipMetadata;

    protected $baseInstance;

    public function __construct(EntityManager $em, $baseEntity, $targetEntityClass, RelationshipMetadata $relationshipMetadata, $initialEntity = null)
    {
        $this->finder = $relationshipMetadata->isRelationshipEntity() ? new RelationshipEntityFinder($em, $targetEntityClass, $relationshipMetadata, $baseEntity) : new RelationshipsFinder($em, $targetEntityClass, $relationshipMetadata);
        $this->em = $em;
        $this->collection = new Collection();
        $this->baseId = $this->em->getClassMetadataFor(get_class($baseEntity))->getIdValue($baseEntity);
        $this->initialEntity = $initialEntity;
        $this->baseEntityClass = get_class($baseEntity);
        $this->relationshipMetadata = $relationshipMetadata;
        $this->baseInstance = $baseEntity;
        if (null !== $initialEntity) {
            $this->collection[] = $initialEntity;
        }
    }

    protected function doInitialize()
    {
        $instances = $this->finder->find($this->baseId);
        $oidKeys = [];
        foreach ($this->collection as $elt) {
            var_dump("*" . $elt->getId());
            $oidKeys[] = spl_object_hash($elt);
        }
        foreach ($instances as $instance) {
            if (!in_array(spl_object_hash($instance), $oidKeys)) {
                if (!$this->relationshipMetadata->isRelationshipEntity()) {
                    $this->em->getUnitOfWork()->addManagedRelationshipReference($this->baseInstance, $instance, $this->relationshipMetadata->getPropertyName(), $this->relationshipMetadata);
                    $repo = $this->em->getRepository(get_class($this->baseInstance));
                    $repo->setLazyLoadedInversed($this->relationshipMetadata, $this->baseInstance, $instance);

                }
                $this->collection[] = $instance;
            }
        }
    }

    public function addInit($elt)
    {
        $this->collection[] = $elt;
    }

}