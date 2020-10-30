<?php

namespace Simplylink\UtilsBundle\Utils\Checklist;

use Simplylink\UtilsBundle\Utils\SLBaseEntity;
use Simplylink\UtilsBundle\Utils\SLBaseUtils;

/**
 * Class BaseCheckListNamespace
 *
 * Base Checklist abstract class.
 * All Parallax checklist implementation must extend this class
 *
 * Parallax Checklist module is manage Checklist for Parallax Entities
 * Each Checklist contains a namespace using as "key" for the list. the namespace must be unique in the project
 * Entity can be attached only 1 time for each Checklist namespace
 *
 * @package Simplylink\UtilsBundle\Utils\Checklist
 */
abstract class BaseCheckListNamespace extends SLBaseUtils
{

    /**
     * @var BaseCheckListEntity
     */
    private $checklistEntity;

    /**
     * BaseCheckListNamespace constructor.
     */
    public function __construct()
    {
        $this->checklistEntity = $this->getChecklistEntityInstance();
        $this->checklistEntity->setNamespace($this->setNamespace());
    }

    /**
     * @return BaseCheckListEntity
     */
    private function getChecklistEntityInstance()
    {
        $class = $this->setChecklistEntityClassName();
        $checklistEntityInstance = new $class();
        return $checklistEntityInstance;
    }


    /**
     * MAX length = 50
     *
     * @return string
     */
    abstract protected function setNamespace();

    /**
     * checklist entity class name
     * @return string
     */
    abstract protected function setChecklistEntityClassName();

    
    /**
     * @param SLBaseEntity $entity
     * @return BaseCheckListNamespace
     */
    public function setEntity(SLBaseEntity $entity)
    {
        $this->checklistEntity
            ->setEntity($entity->getEntityType())
            ->setEntityInternalId($entity->getId());

        return $this;
    }


    /**
     * Mark entity as Checked in the current checklist and save to database
     *
     *
     * @return BaseCheckListEntity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsChecked()
    {
        $em = parent::GetEntityManager();
        $em->persist($this->checklistEntity);
        $em->flush($this->checklistEntity);
        return $this->checklistEntity;
    }

    
    /**
     * Get checked entity if exists in checklist database.
     *
     * @param SLBaseEntity $entity
     * @return null|object
     * @throws \Exception
     */
    public function getCheckedEntity(SLBaseEntity $entity)
    {
        $checked = $this->checklistEntity->getRepository()->findOneBy([
            'entity' => $entity->getEntityType(),
            'entityInternalId' => $entity->getId(),
            'namespace' => $this->setNamespace()
        ]);

        return $checked;
    }

    /**
     * Check if this entity is already exists in the checklist database
     *
     * make request to database
     *
     * @param SLBaseEntity $entity
     * @return bool
     * @throws \Exception
     */
    public function isChecked(SLBaseEntity $entity)
    {
        $checked = $this->getCheckedEntity($entity);
        return ($checked && $checked->getId());
    }



}