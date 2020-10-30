<?php

namespace Simplylink\UtilsBundle\Utils\Checklist;

use Doctrine\ORM\Mapping as ORM;
use Simplylink\UtilsBundle\Utils\SLBaseEntity;

/**
 * Class BaseCheckListEntity
 *
 * Base entity class for Parallax Checklist module
 *
 * @package Simplylink\UtilsBundle\Utils\Checklist
 */
class BaseCheckListEntity extends SLBaseEntity
{

    /**
     * @var Entities
     *
     * @ORM\ManyToOne(targetEntity="Parallax\GenericBundle\Entity\Entities")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     */
    protected $entity;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity_internal_id", type="integer")
     */
    protected $entityInternalId;

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=50)
     */
    protected $namespace;

    /**
     * @return Entities
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param Entities $entity
     * @return BaseCheckListEntity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntityInternalId()
    {
        return $this->entityInternalId;
    }

    /**
     * @param int $entityInternalId
     * @return BaseCheckListEntity
     */
    public function setEntityInternalId($entityInternalId)
    {
        $this->entityInternalId = $entityInternalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return BaseCheckListEntity
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }



}