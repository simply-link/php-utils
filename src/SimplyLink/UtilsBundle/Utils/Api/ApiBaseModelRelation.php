<?php

namespace SimplyLink\UtilsBundle\Utils\Api;


use SimplyLink\UtilsBundle\Utils as SLUtils;

/**
 * Class ApiBaseModelRelation
 * @package SimplyLink\UtilsBundle\Utils\Api
 */
class ApiBaseModelRelation extends SLUtils\SLBaseUtils
{
    /**
     * @var string
     */
    protected $reference;

    /**
     * @var array
     */
    protected $list;

    /**
     * @var string
     */
    protected $model;

    /**
     * ApiBaseModelRelation constructor.
     * @param string $reference
     * @param array|object $list
     * @param string $model
     */
    public function __construct($reference, $list, $model)
    {
        $this->reference = $reference;
        $this->list = $list;
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return ApiBaseModelRelation
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array $list
     * @return ApiBaseModelRelation
     */
    public function setList($list)
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return ApiBaseModelRelation
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function isList()
    {
        $isList = (method_exists($this->getList(),'getMapping'));
        return $isList;
    }

    public function getSingle()
    {
        if($this->isList())
            return $this->getList()[0];

        return $this->getList();
    }

    public function isEmpty()
    {
        if(!$this->getList())
            return true;
        else if($this->isList() && $this->getList()->count() < 1)
            return true;

        return false;
    }

}
