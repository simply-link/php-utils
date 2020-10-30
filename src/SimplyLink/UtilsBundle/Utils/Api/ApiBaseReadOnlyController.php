<?php

namespace Simplylink\UtilsBundle\Utils\Api;


use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionAuthorization;
use Simplylink\UtilsBundle\Utils\SLBaseEntity;
use Simplylink\UtilsBundle\Utils\SLBaseUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


abstract class ApiBaseReadOnlyController extends ApiBaseController
{

    /**
     * A SLBaseEntity class name
     * @var string
     */
    protected $entityClass;

    /**
     * A ApiBaseModel class name
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $singleRouteName;



    public function __construct() {
        $this->entityClass = $this->setRecordEntity();
        $this->modelClass = $this->setRecordModel();
    }


    /**
     * @param $record
     * @return ApiBaseModel
     */
    protected function getModelInstance($record)
    {
        $model = new $this->modelClass($record);
        return $model;
    }

    /**
     * @return SLBaseEntity
     */
    protected function getEntityInstance()
    {
        $entity = new $this->entityClass();
        return $entity;
    }


    /**
     * @Route("")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function getListAction(Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'GET_LIST','params' => $request->getQueryString()]);
    
    
        if(!$this->checkAuthorization($request, self::API_METHOD_GET_LIST))
        {
            $logger->warning('API Error - checkAuthorization(GET_LIST) failed',['API_METHOD' => 'GET_LIST']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }


        $recordsListQB = $this->getRecordsList($request);

        $paginationResult = new ApiBasePagination($recordsListQB,$request);
        $paginationResult->ApplyModelOnRecords($this->modelClass);

        $response = $this->createApiResponse($paginationResult);
        return $response;
    }

    /**
     * @Route("/{recordId}")
     * @Method("GET")
     *
     * @param int $recordId
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function getSingleRecordAction($recordId,Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'GET_SINGLE']);
    
        
        if(!$this->checkAuthorization($request, self::API_METHOD_GET_SINGLE))
        {
            $logger->warning('API Error - checkAuthorization(GET_SINGLE) failed',['API_METHOD' => 'GET_SINGLE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }
        
        $record = $this->getRecordById($recordId);
        $this->validateRecord($request,$record,self::API_METHOD_GET_SINGLE);
        $model = $this->getModelInstance($record);
        $model->applyRelationLinks();
        $response = $this->createApiResponse($model);
        return $response;
    }


    /**
     * Get entity record by ID.
     * If not found throw NotFoundException
     *
     * @param $recordId
     * @return null|object|NotFoundHttpException
     * @throws \Exception
     */
    protected function getRecordById($recordId)
    {
        $record = $this->getEntityInstance()->GetRepository()->find($recordId);

        if (!$record)
            throw $this->createNotFoundException(sprintf('No record found with id "%s"', $recordId));

        return $record;
    }



    /**
     * Get list of records
     *
     * @param Request $request
     * @return \Doctrine\ORM\QueryBuilder
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument
     */
    protected function getRecordsList(Request $request)
    {
        $filterParams = $this->setFiltersForListRequest($request);
        return $this->getEntityInstance()->getRepository()->findAllForApiQueryBuilder($request,$filterParams);
    }


    /**
     * To Override
     * @param Request $request
     * @return array
     */
    protected function setFiltersForListRequest(Request $request) {return [];}


    /**
     * To Override
     * add additional validation for the records
     * throw exception if validation fails
     * @param Request $request
     * @param SLBaseEntity|mixed
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     */
    protected function validateRecord(Request $request, $record, $apiMethod){}


    /**
     * Fill Record Entity class - type of SLBaseEntity
     * @return string
     */
    abstract public function setRecordEntity();

    /**
     * Fill Record Model class - type of ApiBaseModel
     * @return string
     */
    abstract public function setRecordModel();



}
