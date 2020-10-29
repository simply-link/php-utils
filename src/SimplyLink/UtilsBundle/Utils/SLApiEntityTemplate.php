<?php

namespace SimplyLink\UtilsBundle\Utils;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use SimplyLink\UtilsBundle\Utils\Api\BaseApiEntityRepository;

/**
 * Class SLApiEntityTemplate
 *
 * Base entity for doctrine and symfony use
 * Record createdAt and updatedAt for each entity
 * Shortcut and easy to use functions
 *
 * @package SimplyLink\UtilsBundle\Utils

 */
interface SLApiEntityTemplate
{
    /**
     * return the model class name
     *
     * @return string
     */
    public function setEntityApiModel();
    
}