<?php

namespace Simplylink\UtilsBundle\Utils\Api;

use Doctrine\ORM\QueryBuilder;
use Simplylink\UtilsBundle\Utils\Exceptions\BaseSLException;
use Simplylink\UtilsBundle\Utils\SLBaseUtils;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ApiBasePagination extends SLBaseUtils
{

    const API_PAGE_SIZE = 250;


    private $total;
    private $count;
    private $offset;
    private $_links = array();
    private $records;

    /**
     * @var array
     */
    private $errors = array();


    /**
     * @JMS\Exclude();
     */
    private $pagerfanta;

    /**
     * @JMS\Exclude();
     */
    private $request;

    /**
     * @JMS\Exclude();
     */
    private $page;




    function __construct(QueryBuilder $recordsListQB, Request $request)
    {
        $this->page = $this->getCurrentPageNum($request);

        $adapter = new DoctrineORMAdapter($recordsListQB);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(self::API_PAGE_SIZE);
        $pagerfanta->setCurrentPage($this->page);


        $this->pagerfanta = $pagerfanta;
        $this->request = $request;



        $this->total = $this->pagerfanta->getNbResults();
        $this->setRecords($this->pagerfanta->getCurrentPageResults());
        $this->offset = (($this->page-1) * self::API_PAGE_SIZE);

        $this->CreatePagingLinks();

    }

    /**
     * get page from query - if null set 1 as default
     * @param Request $request
     * @return integer - page
     */
    private function getCurrentPageNum(Request $request)
    {
        return $request->query->getInt('page', 1);
    }


    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Update records list and count - if records variable is not array -> convert to array for JMS serialization
     *
     * @param $records - records list
     * @return ApiBasePagination
     */
    public function setRecords($records)
    {
        if(!$records)
            $records = array();
        if(!is_array($records))
        {
            $recordsArray = [];
            foreach ($records as $record)
            {
                $recordsArray[] = $record;
            }
            $records = $recordsArray;
        }

        $this->records = $records;
        $this->count = count($this->records);

        return $this;
    }

    /**
     * Convert records to models - update records list and count
     * @param string $modelClass - Model Class Name
     * @return ApiBasePagination
     */
    public function ApplyModelOnRecords($modelClass)
    {
        $modelArray = [];
        foreach ($this->records as $record)
        {
            $modelOutput = $this->getModelInstance($record,$modelClass);
            $modelOutput->applyRelationLinks();
            $modelArray[] = $modelOutput;
        }

        $this->setRecords($modelArray);

        return $this;
    }

    /**
     * Create Paging Links for first,last,prev,next page
     * Add Links to Link Array
     * @return ApiBasePagination
     */
    public function CreatePagingLinks()
    {
        if($this->pagerfanta->getNbResults() > self::API_PAGE_SIZE)
        {
            if($this->page > 1)
                $this->addPagingLink('first', 1);

            if($this->pagerfanta->hasPreviousPage())
                $this->addPagingLink('prev',  $this->pagerfanta->getPreviousPage());

            if($this->pagerfanta->hasNextPage())
                $this->addPagingLink('next',  $this->pagerfanta->getNextPage());

            $this->addPagingLink('last',  $this->pagerfanta->getNbPages());
        }

        return $this;
    }

    /**
     * Add link to Link Array with ref & url to page number
     * @param string $ref
     * @param integer $pageNumber
     */
    private function addPagingLink($ref,$pageNumber)
    {
        $this->addLink($ref, $this->createLinkUrlForPage($this->request, $pageNumber,true));
    }


    /**
     * Generate link for specific page from current route
     *
     * @param Request $request
     * @param int $targetPage
     * @param boolean $addParameters - add filters parameters from current request
     * @return String - Generated Url
     */
    private function createLinkUrlForPage(Request $request,$targetPage, $addParameters = false)
    {
        // dynamically read all params from GET query and add them to query builder
        $queryParams = [];

        if($addParameters)
            $queryParams = $request->query->all();

        $queryParams['page'] = $targetPage;

        $router = $this->GetKernelContainer()->get("router");
        $route = $router->match($request->getPathInfo());
        $routeName = $route['_route'];
        $routeParams = array();


        foreach ($route as $key => $value)
        {
            if($key != "_controller" && $key != "_route")
            {
                $routeParams[$key] = $value;
            }
        }

        return $router->generate($routeName, array_merge(
            $routeParams,
            $queryParams
        ),UrlGeneratorInterface::ABSOLUTE_URL);

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
     * @param $record
     * @param $modelClass - ApiBaseModel class name
     * @return ApiBaseModel
     */
    protected function getModelInstance($record,$modelClass)
    {
        $model = new $modelClass($record);
        return $model;
    }

    /**
     * @return BaseSLException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param BaseSLException $error
     * @return ApiBasePagination
     */
    public function addError(BaseSLException $error)
    {
        $this->errors[] = $error;
        return $this;
    }


}