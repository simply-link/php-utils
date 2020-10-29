<?php 

namespace SimplyLink\UtilsBundle\Utils;

use JMS\Serializer\SerializationContext;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SLResponseManager
 *
 * SLResponseManager handle responses in many formats.
 * Response manager compatible with symfony
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class SLResponseManager extends SLBaseUtils
{
    /**
     * Return HTTPResponse: force-download, with file attachment, response code: 200
     * 
     * @param string $csvData
     * @param int $statusCode
     * @return Response
     * @throws SLExceptionInvalidArgument
     */
    public static function CSVResponse($csvData,$statusCode = 200)
    {
        if(!is_string($csvData))
            throw SLExceptionInvalidArgument::expectedString('$csvData',$csvData);
        
        $headers = array('Content-Type' => 'application/force-download',
        'Content-Disposition' => 'attachment; filename="export.csv"');

        return new Response($csvData,$statusCode,$headers);
    }
    
    /**
     * Convert Response for symfony with object data converted Json (Using JMS-Serializer)
     *
     *
     * @param object $data The object for response
     * @param int $statusCode Status code to response
     * @param bool $shouldSerialize Data is already serialized or need to be
     * @return Response
     * @throws SLExceptionRuntime
     */
    public static function JSONResponse($data,$statusCode = 200, $shouldSerialize = true )
    {
        try{
            return self::JMSResponse($data, $statusCode,$shouldSerialize, 'json');
        }
        catch (SLExceptionInvalidArgument $e)
        {
            throw new SLExceptionRuntime($e->getMessage(),$e->getUserText());
        }
    }
    
    /**
     * Convert Response for symfony with object data converted XML (Using JMS-Serializer)
     *
     * @param object $data The object for response
     * @param int $statusCode Status code to response
     * @param bool $shouldSerialize Data is already serialized or need to be
     * @return Response
     * @throws SLExceptionRuntime
     */
    public static function XMLResponse($data,$statusCode = 200, $shouldSerialize = true )
    {
        try{
            return self::JMSResponse($data, $statusCode,$shouldSerialize, 'xml');
        }
        catch (SLExceptionInvalidArgument $e)
        {
            throw new SLExceptionRuntime($e->getMessage(),$e->getUserText());
        }
        
    }
    
    /**
     * @param object $data The object for response
     * @param int $statusCode Status code to response
     * @param bool $shouldSerialize Data is already serialized or need to be
     * @param string $format format to serialize (JMS-Serializer format)
     * @return Response
     * @throws SLExceptionInvalidArgument
     */
    private static function JMSResponse($data,$statusCode, $shouldSerialize , $format)
    {
        if($shouldSerialize)
            $data = self::serialize($data,null,$format);

        return new Response($data, $statusCode, array(
            'Content-Type' => 'application/' . $format
        ));
    }
    
    /**
     * Serialized data in requested format
     *
     * @param $data - send any type
     * @param array $exclusionStrategy - array of classes for exclusion strategy
     * @param string $format - select format to response (json,xml)
     * @param bool $serializeNull
     * @return string - serialized data in requested format
     * @throws SLExceptionInvalidArgument
     */
    public static function serialize($data, array $exclusionStrategy = null, $format = 'json', $serializeNull = true)
    {
        if(!is_string($format))
            throw SLExceptionInvalidArgument::expectedString('$format',$format);
        
        $context = new SerializationContext();
        $context->setSerializeNull($serializeNull) // serialize null fields
                ->enableMaxDepthChecks();
        
        
        if($exclusionStrategy)
        {
            foreach ($exclusionStrategy as $strategy)
            {
                $context->addExclusionStrategy($strategy);
            }
        }
        
        $container =  parent::GetKernelContainer();
        return $container->get('jms_serializer')
            ->serialize($data, $format, $context);
    }
}

