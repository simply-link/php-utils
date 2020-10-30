<?php

namespace Simplylink\UtilsBundle\Utils\Api;


use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionAuthorization;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionValidation;
use Simplylink\UtilsBundle\Utils\SLBaseEntity;
use Simplylink\UtilsBundle\Utils\SLBaseUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


abstract class ApiBaseWebhookController extends ApiBaseController
{

    /**
     * A SLBaseEntity class name
     * @var string
     */
    protected $entityClass;
    
    /**
     * @var string
     */
    protected $formClass;
    
    
    
    public function __construct() {
        $this->entityClass = $this->setRecordEntity();
        $this->formClass = $this->setRecordForm();
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
    
    
    
    
    
    
    public function receiveWebhookAction(Request $request)
    {
    
    }
    
    
    /**
     * @Route("/create")
     * @Method("POST")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime
     */
    public function createRecordAction(Request $request)
    {
        $logger = SLBaseUtils::getLogger();
        $logger->info('receive webhook request ',['API_METHOD' => 'CREATE','params' => $request->getQueryString()]);
        
        
        if(!$this->checkAuthorization($request, self::API_METHOD_CREATE))
        {
            $logger->warning('receive webhook Error - checkAuthorization(CREATE) failed',['API_METHOD' => 'CREATE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }
        
        $record = $this->getEntityInstance();
        $form = $this->createForm($this->formClass,$record,array('csrf_protection' => false));
        
        $this->processApiForm($form, $request,self::API_METHOD_CREATE);
        
        if (!$form->isValid()) {
            $apiError = new SLExceptionValidation();
            $apiError->addFormErrors($form);
            $logger->warning('receive webhook Error - $form->isValid() failed',['API_METHOD' => 'CREATE','errors' => $apiError->getAdditionalInfo()]);
            return $apiError->toResponse();
        }
        
        $validationErrorDescription = null;
        if(!$this->validatePreSaveData($record,$validationErrorDescription,self::API_METHOD_CREATE))
        {
            $logger->warning('receive webhook Error - validatePreSaveData(CREATE) failed',['API_METHOD' => 'CREATE','errors' => $validationErrorDescription]);
            $apiError = new SLExceptionValidation();
            $apiError->addAdditionalInfo(['errors' => $validationErrorDescription]);
            return $apiError->toResponse();
        }
        
        $this->processPreSaveData($record,self::API_METHOD_CREATE);
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();
        
        $this->postSaveActions($record,self::API_METHOD_CREATE);
        
        $response = $this->createApiResponse(null,200);
        return $response;
    }
    
    /**
     * @Route("/update")
     * @Method({"PUT"})
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
        $logger->info('receive webhook request ',['API_METHOD' => 'UPDATE','data' => $this->getJsonDataFromPost($request)]);
        
        if(!$this->checkAuthorization($request, self::API_METHOD_UPDATE)) {
            $logger->warning('receive webhook Error - checkAuthorization(UPDATE) failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE']);
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
            $logger->warning('receive webhook Error - $form->isValid() failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE','errors' => $apiError->getAdditionalInfo()]);
            return $apiError->toResponse();
        }
        
        $validationErrorDescription = null;
        if(!$this->validatePreSaveData($record,$validationErrorDescription,self::API_METHOD_UPDATE))
        {
            $logger->warning('receive webhook Error - validatePreSaveData(UPDATE) failed',['recordId' => $recordId,'API_METHOD' => 'UPDATE','errors' => $validationErrorDescription]);
            $apiError = new SLExceptionValidation();
            $apiError->addAdditionalInfo(['errors' => $validationErrorDescription]);
            return $apiError->toResponse();
        }
        
        $this->processPreSaveData($record,self::API_METHOD_UPDATE);
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();
        
        $this->postSaveActions($record,self::API_METHOD_UPDATE);
    
        $response = $this->createApiResponse(null,200);
        return $response;
    }
    
    /**
     * @Route("/delete")
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
        $logger->info('receive webhook request ',['API_METHOD' => 'DELETE']);
        
        if(!$this->checkAuthorization($request, self::API_METHOD_DELETE))
        {
            $logger->warning('receive webhook Error - checkAuthorization(DELETE) failed',['recordId' => $recordId,'API_METHOD' => 'DELETE']);
            $apiError = new SLExceptionAuthorization();
            return $apiError->toResponse();
        }
        
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $record = $this->getRecordById($recordId);
        $this->validateRecord($request,$record,self::API_METHOD_DELETE);
        
        $entityManager->remove($record);
        
        $entityManager->flush();
    
    
        $response = $this->createApiResponse(null,200);
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
     * @return string
     */
    abstract public function setRecordForm();
    
    
}
