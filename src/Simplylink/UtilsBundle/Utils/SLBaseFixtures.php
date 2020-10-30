<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 16/08/2017
 * Time: 12:48
 */

namespace Simplylink\UtilsBundle\Utils;


use Doctrine\Common\Persistence\ObjectManager;

abstract class SLBaseFixtures
{
    /**
     * @param ObjectManager $manager
     * @param SLBaseEntity $entity
     */
    public function createEntityIfNotExists(ObjectManager $manager, SLBaseEntity $entity)
    {
        $entityExists = $manager->getRepository(get_class($entity))->find($entity->getId());
        if($entityExists)
        {
            $entity->setCreatedAt($entityExists->getCreatedAt());
            $entity->setUpdatedAt(new \DateTime());
            $entity = $manager->merge($entity);
        }
        else
        {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}