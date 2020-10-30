<?php


namespace Simplylink\UtilsBundle\Utils\Api;

use Simplylink\UtilsBundle\Utils as SLUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


abstract class ApiBaseController extends Controller
{
    const API_METHOD_GET_SINGLE = 1;
    const API_METHOD_GET_LIST = 2;
    const API_METHOD_CREATE = 3;
    const API_METHOD_CREATE_BULK = 4;
    const API_METHOD_UPDATE = 5;
    const API_METHOD_DELETE = 6;

    
    /**
     * Create Response for Api by serialize the data and response back with content type.
     * 
     * @param $data - send any type
     * @param boolean $shouldSerialize
     * @param string $format - select format to response (json,xml)
     * @param integer $statusCode - HTTP status code response
     * @return Response
     * @throws SLUtils\Exceptions\SLExceptionRuntime
     */
    public function createApiResponse($data,$statusCode = 200, $shouldSerialize = true , $format = 'json')
    {
        if($format == 'xml')
            return SLUtils\SLResponseManager::XMLResponse($data, $statusCode, $shouldSerialize);
        
        return SLUtils\SLResponseManager::JSONResponse($data, $statusCode, $shouldSerialize);
    }
    
    /**
     * encoding the json from response into array, and submit it into form.
     * add additional data by injectDataToForm($request).
     * if request method = PATCH -> then clear missing in form set to true;
     * 
     * @param Form $form
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @param Request $request
     */
    public function processApiForm(&$form, Request $request, $apiMethod)
    {
        $data = $this->getJsonDataFromPost($request);
        $this->processApiFormWithData($form,$data,$request,$apiMethod);
    }


    /**
     * submit data into form.
     * add additional data by injectDataToForm($request).
     * if request method = PATCH -> then clear missing in form set to true;
     *
     * @param Form $form
     * @param array $data
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @param Request $request
     */
    public function processApiFormWithData(&$form, array $data,Request $request,$apiMethod)
    {
        $additionalData = $this->injectDataToForm($request,$apiMethod);
        if($additionalData && is_array($additionalData) && count($additionalData))
        {
            foreach ($additionalData as $key => $value) {
                $data[$key] = $value;
            }
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }
   
    
    /**
     * To Override
     * Inject additional data into form
     * 
     * @param Request $request
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @return array [KEY => VALUE]
     */
    protected function injectDataToForm(Request $request,$apiMethod){return null;}
    
    
    /**
     * Get json text from request body and convert it to array
     * @param Request $request
     * @return array
     */
    protected function getJsonDataFromPost(Request $request)
    {
        $body = $request->getContent();
        $bodyData = json_decode($body, true);   
        return $bodyData;
    }


    /**
     * Return boolean if data is array of entities for bulk insert
     *
     * @param $data
     * @return bool
     */
    protected function isBulkInsert($data)
    {
        if(SLUtils\GenericDataManager::isArrayAssoc($data))
            return false;
        return true;
    }


    /**
     * To Override
     * check if current user is authorized for apiMethod
     *
     * @param Request $request
     * @param int $apiMethod - API_METHOD_GET_SINGLE, API_METHOD_GET_LIST, API_METHOD_CREATE, API_METHOD_CREATE_BULK, API_METHOD_UPDATE, API_METHOD_DELETE
     * @return bool
     */
    protected function checkAuthorization(Request $request , $apiMethod){ return true; }

}