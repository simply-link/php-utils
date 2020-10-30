<?php

namespace Simplylink\UtilsBundle\Utils;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Simplylink\UtilsBundle\Utils\Api\BaseApiEntityRepository;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;

/**
 * Class SLBaseEntity
 *
 * Base entity for doctrine and symfony use
 * Record createdAt and updatedAt for each entity
 * Shortcut and easy to use functions
 *
 * @package Simplylink\UtilsBundle\Utils
 * @ORM\HasLifecycleCallbacks
 */
class SLBaseEntity extends SLBaseUtils
{

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;


    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

  
    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return SLBaseEntity
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return SLBaseEntity
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|BaseApiEntityRepository
     * @throws \Exception
     *
     * @deprecated
     */
    public function getRepository()
    {
        $entityManager = $this->GetEntityManager();
        return $entityManager->getRepository($this->GetRepositoryName());
    }

    /**
     * Get The repository name of the entity (self)
     *
     * @return string Repository Name
     * @throws \Exception
     *
     * @deprecated
     */
    public function GetRepositoryName()
    {
        $entityManager = $this->GetEntityManager();
        $repoName = null;
        try {
            $entityName = $entityManager->getMetadataFactory()->getMetadataFor(get_class($this))->getName();

            $namespace = array_slice(explode('\\', $entityName), 0, -1);
            $classname = join('', array_slice(explode('\\', $entityName), -1));

            $repoName = '';
            foreach ($namespace as $namespaceName) {
                $repoName .= $namespaceName;
                if (strpos($namespaceName, 'Bundle') !== false) {
                    break;
                }
            }
            $repoName .= ':' . $classname;

        } catch (ORM\MappingException $e) {
            throw new \Exception('Given object ' . get_class($this) . ' is not a Doctrine Entity. ');
        }

        return $repoName;
    }
    

    /**
     * Check asserts for entity data
     *
     * Use this function  before persist to database
     * Output array of errors
     *
     * @param SLBaseEntity $entity
     * @param $errors
     * @return bool
     */
    public function validateEntityAsserts(SLBaseEntity $entity, &$errors = null)
    {
        $container = SLBaseUtils::GetKernelContainer();
        $validator = $container->get('validator');

        $errors = $validator->validate($entity);

        if (count($errors) > 0)
            return false;

        return true;
    }


    /**
     * Convert entity to string
     *
     * @return string Id number as a string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }


    /**
     * Save entity to database
     */
    public function save()
    {
        $entityManager = $this->GetEntityManager();
        if(!$this->getId())
            $entityManager->persist($this); // new entity
        $entityManager->flush($this);
    }

    /**
     * Update the updatedAt field for relations to current entities
     *
     * Use this function to keep all relations updated for sync changes requests
     *
     * @ORM\PostUpdate()
     * @ORM\PostPersist()
     */
    public function updateRelationEntitiesOnUpdate()
    {
        $entities = $this->getRelationEntitiesForUpdate();
        $this->updateRelationEntities($entities);
    }

    /**
     * Get all relation entities you wish to update the updatedAt field on this entity update
     *
     * @return array $entities - [SLBaseEntity | PersistentCollection]
     */
    protected function getRelationEntitiesForUpdate()
    {
        return [];
    }


    /**
     * Update relation entities -> set updatedAt as now.
     *
     * @param array $entities - [SLBaseEntity | PersistentCollection]
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateRelationEntities(array $entities)
    {
        foreach ($entities as $collection)
        {
            if(get_class($collection) == PersistentCollection::class)
            {
                foreach ($collection as $entity)
                {
                    if(is_subclass_of($entity,SLBaseEntity::class))
                        $this->updateEntityUpdatedAt($entity);
                }
            }
            else
            {

                $entity = $collection;
                if(is_subclass_of($entity,SLBaseEntity::class))
                    $this->updateEntityUpdatedAt($entity);

            }
        }
    }

    /**
     * Update relation entities -> set updatedAt as now
     *
     * @param SLBaseEntity $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateEntityUpdatedAt(SLBaseEntity $entity)
    {
        $entity->setUpdatedAt(new \DateTime());
        $entity->save();
    }
    
    
    /**
     * @return int
     */
    public function getId()
    {
        return -1;
    }
    
    
    /**
     * Check if field has changed since the last ORM flush
     *
     * Get the changes in a specific field in entity, fill the $beforeValue and $afterValue with the values of the field.
     *
     * @param string $fieldName
     * @param mixed $beforeValue
     * @param mixed $afterValue
     * @return bool Return true if the field has changed
     * @throws Exceptions\SLExceptionInvalidArgument
     */
    public function isFieldChanged($fieldName,&$beforeValue, &$afterValue)
    {
        $em = self::GetEntityManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener
        $changeSet = $uow->getEntityChangeSet($this);
        
        $changeArray = GenericDataManager::getArrayValueForKey($changeSet,$fieldName, NULL);
        
        if($changeArray && is_array($changeArray) && count($changeArray) > 0)
        {
            $beforeValue = GenericDataManager::getFirstElementOfArray($changeArray);
            $afterValue = end($changeArray);
            return true;
        }
        
        
        return false;
    }
    
    /**
     * Convert entity to model only if entity implements SLApiEntityTemplate
     * @return mixed
     * @throws SLExceptionInvalidArgument
     */
    public function convertToModel()
    {
        if (in_array(SLApiEntityTemplate::class, class_implements($this))) {
            $modelClass = $this->setEntityApiModel();
            $modelInstance = new $modelClass($this);
            return $modelInstance;
        }
        else
        {
            throw new SLExceptionInvalidArgument('entity does not implement SLApiEntityTemplate','unexpected error, please contact admin@simplylink.com');
        }
    }
    
}