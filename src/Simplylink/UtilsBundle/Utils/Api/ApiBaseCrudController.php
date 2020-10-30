<?php

namespace Simplylink\UtilsBundle\Utils\Api;


use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionAuthorization;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionUnexpectedValue;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionValidation;
use Simplylink\UtilsBundle\Utils\SLBaseEntity;
use Simplylink\UtilsBundle\Utils\SLBaseUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


abstract class ApiBaseCrudController extends ApiBaseReadOnlyController
{
    protected $formClass;


    public function __construct() {
        parent::__construct();
        $this->formClass = $this->setRecordForm();
    }


    /**
     * @Route("/_bulk")
     * @Method("POST")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function createBulkRecordsAction(Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'CREATE_BULK']);
    
        
        if(!$this->checkAuthorization($request, self::API_METHOD_CREATE_BULK))
        {
            $logger->warning('API Error - checkAuthorization(CREATE_BULK) failed',['API_METHOD' => 'CREATE_BULK']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }


        $data = $this->getJsonDataFromPost($request);

        if($data === null)
        {
            $logger->warning('API Error - getJsonDataFromPost() failed',['API_METHOD' => 'CREATE_BULK']);
            // redirect to error
            $apiError = SLExceptionUnexpectedValue::expectedValidJSONString();
            return $apiError->toResponse();
        }

        $isBulk = $this->isBulkInsert($data);

        if(!$isBulk)
        {
            $logger->warning('API Error - isBulkInsert() failed',['API_METHOD' => 'CREATE_BULK']);
            // redirect to error
            $apiError = new SLExceptionInvalidArgument('Api data is not valid for BULK','invalid argument');
            return $apiError->toResponse();
        }

        if(count($data) > ApiBasePagination::API_PAGE_SIZE)
        {
            $logger->warning('API Error - Request too large',['API_METHOD' => 'CREATE_BULK']);
            // error - bulk too large
            $apiError = new SLExceptionUnexpectedValue('Request too large','Invalid request');
            return $apiError->toResponse();
        }


        $errors = [];
        $createdIds = [0];
        foreach ($data as $recordData)
        {
            $record = $this->getEntityInstance();
            $form = $this->createForm($this->formClass,$record,array('csrf_protection' => false));

            $this->processApiFormWithData($form, $recordData, $request,self::API_METHOD_CREATE_BULK);

            if (!$form->isValid()) {

                $apiError = new SLExceptionValidation();
                $apiError->addFormErrors($form);
                $apiError->addAdditionalInfo(['context' => $recordData]);
                $errors[] = $apiError;
                continue;
            }
    
            $validationErrorDescription = null;
            if(!$this->validatePreSaveData($record,$validationErrorDescription,self::API_METHOD_CREATE_BULK))
            {
                $apiError = new SLExceptionValidation();
                $apiError->addAdditionalInfo(['errors' => $validationErrorDescription]);
                $errors[] = $apiError;
                continue;
            }
            
            $this->processPreSaveData($record,self::API_METHOD_CREATE_BULK);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($record);
            $entityManager->flush();
    
            $this->postSaveActions($record,self::API_METHOD_CREATE_BULK);

            $createdIds[] = $record->getId();
        
        }

        $recordsListQB = $this->getEntityInstance()->getRepository()->findByIdsForApiQueryBuilder($createdIds);
        $paginationResult = new ApiBasePagination($recordsListQB,$request);
        $paginationResult->ApplyModelOnRecords($this->modelClass);

        foreach ($errors as $error)
        {
            $paginationResult->addError($error);
        }


        $response = $this->createApiResponse($paginationResult,201);
        return $response;
    }


    /**
     * @Route("")
     * @Method("POST")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function createRecordAction(Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'CREATE','params' => $request->getQueryString()]);
        
        
        if(!$this->checkAuthorization($request, self::API_METHOD_CREATE))
        {
            $logger->warning('API Error - checkAuthorization(CREATE) failed',['API_METHOD' => 'CREATE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }

        $record = $this->getEntityInstance();
        $form = $this->createForm($this->formClass,$record,array('csrf_protection' => false));
        
        $this->processApiForm($form, $request,self::API_METHOD_CREATE);

        if (!$form->isValid()) {
            $apiError = new SLExceptionValidation();
            $apiError->addFormErrors($form);
            $logger->warning('API Error - $form->isValid() failed',['API_METHOD' => 'CREATE','errors' => $apiError->getAdditionalInfo()]);
            return $apiError->toResponse();
        }
    
        $validationErrorDescription = null;
        if(!$this->validatePreSaveData($record,$validationErrorDescription,self::API_METHOD_CREATE))
        {
            $logger->warning('API Error - validatePreSaveData(CREATE) failed',['API_METHOD' => 'CREATE','errors' => $validationErrorDescription]);
            $apiError = new SLExceptionValidation();
            $apiError->addAdditionalInfo(['errors' => $validationErrorDescription]);
            return $apiError->toResponse();
        }
        
        $this->processPreSaveData($record,self::API_METHOD_CREATE);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();
    
        $this->postSaveActions($record,self::API_METHOD_CREATE);

        $model = $this->getModelInstance($record);
        $response = $this->createApiResponse($model,201);

        $response->headers->set('Location', $model->getLink());
        return $response;
    }

    /**
     * @Route("/{recordId}")
     * @Method({"PUT", "PATCH"})
     *
     * @param int $recordId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function updateRecordAction($recordId,Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'UPDATE','data' => $this->getJsonDataFromPost($request)]);
        
        if(!$this->checkAuthorization($request, self::API_METHOD_UPDATE)) {
            $logger->warning('API Error - checkAuthorization(UPDATE) failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }

        
        $record = $this->getRecordById($recordId);
        $this->validateRecord($request,$record,self::API_METHOD_UPDATE);

        $form = $this->createForm($this->formClass,$record,array('csrf_protection' => false));
        $this->processApiForm($form, $request,self::API_METHOD_UPDATE);


        if (!$form->isValid()) {
            $apiError = new SLExceptionValidation();
            $apiError->addFormErrors($form);
            $logger->warning('API Error - $form->isValid() failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE','errors' => $apiError->getAdditionalInfo()]);
            return $apiError->toResponse();
        }
        
        $validationErrorDescription = null;
        if(!$this->validatePreSaveData($record,$validationErrorDescription,self::API_METHOD_UPDATE))
        {
            $logger->warning('API Error - validatePreSaveData(UPDATE) failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE','errors' => $validationErrorDescription]);
            $apiError = new SLExceptionValidation();
            $apiError->addAdditionalInfo(['errors' => $validationErrorDescription]);
            return $apiError->toResponse();
        }

        $this->processPreSaveData($record,self::API_METHOD_UPDATE);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();
        
        $this->postSaveActions($record,self::API_METHOD_UPDATE);
        
        $model = $this->getModelInstance($record);
        $response = $this->createApiResponse($model);
        return $response;
    }

    /**
     * @Route("/{recordId}")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param int $recordId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function deleteRecordAction(Request $request, $recordId)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('start api request ',['API_METHOD' => 'DELETE']);
        
        if(!$this->checkAuthorization($request, self::API_METHOD_DELETE))
        {
            $logger->warning('API Error - checkAuthorization(DELETE) failed',['recordId' => $recordId,'API_METHOD' => 'DELETE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }

        
        $entityManager = $this->getDoctrine()->getManager();

        $record = $this->getRecordById($recordId);
        $this->validateRecord($request,$record,self::API_METHOD_DELETE);

        $entityManager->remove($record);

        $entityManager->flush();

        $this->postDeleteActions();

        $response = $this->createApiResponse(null,202);
        return $response;
    }


    /**
     * To Override
     * process the data before saving to database
     *
     * @param SLBaseEntity|mixed $record
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     */
    protected function processPreSaveData(&$record,$apiMethod)
    {
    
    }
    
    
    /**
     * Validate the data before proceed to saving process
     *
     * To Override
     *
     * @param SLBaseEntity|mixed $record
     * @param string $errorDescription
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @return bool
     */
    protected function validatePreSaveData($record,&$errorDescription,$apiMethod)
    {
        return true;
    }
    
    
    /**
     * Preform actions after record has been save to database.
     *
     * To Override
     *
     * @param SLBaseEntity|mixed $record
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @return void
     */
    protected function postSaveActions($record,$apiMethod)
    {
    }
    
    /**
     * Preform actions after record has been deleted from the database.
     *
     * To Override
     *
     * @return void
     */
    protected function postDeleteActions()
    {
    }
    
    /**
     * @return string
     */
    abstract public function setRecordForm();


}
