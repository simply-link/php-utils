<?php

namespace Simplylink\UtilsBundle\Utils\Api;


use Simplylink\UtilsBundle\Utils as SLUtils;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiBaseModel
 * @package Simplylink\UtilsBundle\Utils\Api
 */
abstract class ApiBaseModel extends SLUtils\SLBaseUtils
{
    /**
     * ApiBaseModel constructor.
     *
     * @param SLUtils\SLBaseEntity $entity
     */
    function __construct($entity)
    {
        $this->id = $entity->getId();
        $this->entity = $entity;
    }

    /**
     * @JMS\Exclude();
     */
    protected $entity;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     *
     */
    protected $_links = array();

    /**
     * @var array
     * @JMS\Exclude();
     */
    protected $relations = array();



    public function getLink()
    {
        return $this->_links;
    }


    /**
     * Generate Link for single record
     */
    protected function generateSingleLink()
    {
        $router = $this->GetKernelContainer()->get('router');
        $routeUrl = $router->generate($this->getRouteNameForSingleRecord(),$this->getRoutePropertiesArray(),UrlGeneratorInterface::ABSOLUTE_URL);
        return $routeUrl;
    }

    /**
     * Generate Link for lists of records
     */
    protected function generateListLink()
    {
        $router = $this->GetKernelContainer()->get('router');
        $propertiesArray = $this->getRoutePropertiesArray();
        unset($propertiesArray['recordId']);
        $routeUrl = $router->generate($this->getRouteNameForList(),$propertiesArray,UrlGeneratorInterface::ABSOLUTE_URL);
        return $routeUrl;
    }

    /**
     * Get route name for single record
     *
     * @return string
     */
    public function getRouteNameForSingleRecord()
    {
        return $this->getBaseRouteName() .  "_getsinglerecord";
    }

    /**
     * Get route name for list of records
     *
     * @return string
     */
    public function getRouteNameForList()
    {
        return $this->getBaseRouteName() . "_getlist";
    }

    /**
     * get base route name for entity.
     * EX: for entity ProductImages return 'api_productimages'
     * @return string
     */
    private function getBaseRouteName()
    {
        $reflect = new \ReflectionClass($this->entity);
        return "api_" . strtolower($reflect->getShortName());
    }

    /**
     * Generate Array with model items for entities list
     *
     * @param $entitiesList - entities list
     * @param string $modelClass - model class
     * @return array
     */
    protected function generateArrayModel($entitiesList,$modelClass)
    {
        $result = array();

        foreach ($entitiesList as $record) {
            $result[] = new $modelClass($record);
        }

        return $result;
    }

    /**
     * Add Link To _links array with
     *
     * @param string $ref - KEY
     * @param string $url - VALUE
     */
    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }


    /**
     * fill relation links in model
     */
    public function applyRelationLinks()
    {

        if($this->id)
            $this->addLink('self', $this->generateSingleLink());

        $this->addRelationsLinks();
    }

    /**
     * Add Relations links to model
     */
    private function addRelationsLinks()
    {
        $relations = $this->setRelationLinks();

        if(!($relations && is_array($relations)))
        {
            return;
        }

        foreach ($relations as $relation)
        {
            if(!$relation->isEmpty())
            {
                $single = $relation->getSingle();

                $modelClass = $relation->getModel();
    
                $modelInstance = (new $modelClass($single));
                /** REMOVABLE - JUST FOR IDE */ if (false) {$modelInstance = new ApiModelPlaceholder(null);}
    
                if($relation->isList())
                    $relationUrl = $modelInstance->generateListLink();
                else
                {
                    $relationUrl = $modelInstance->generateSingleLink();
                }

                $this->addLink($relation->getReference(), $relationUrl);
            }
        }
    }

    /**
     * Fill properties for router to generate link for single record.
     * @return array
     */
    abstract protected function getRoutePropertiesArray();

    /**
     * @return ApiBaseModelRelation[]
     */
    abstract protected function setRelationLinks();


}
