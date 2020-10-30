<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 29/10/2017
 * Time: 11:59
 */

namespace Simplylink\UtilsBundle\Utils\Monolog\Processor;

class HerokuProcessor
{
    /**
     * @var array|\ArrayAccess
     */
    protected $serverData;
    
    /**
     * Default fields
     *
     * Array is structured as [key in record.extra => key in $serverData]
     *
     * @var array
     */
    protected $extraFields = array(
        
        'heroku_user' => 'USER',
        'user_ip'     => 'HTTP_CF_CONNECTING_IP',
        'request_id' => 'HTTP_X_REQUEST_ID',
        'authorization' => 'HTTP_AUTHORIZATION',
    );
    
    
    /**
     * @param array|\ArrayAccess $serverData  Array or object w/ ArrayAccess that provides access to the $_SERVER data
     * @param array|null         $extraFields Field names and the related key inside $serverData to be added. If not provided it defaults to: url, ip, http_method, server, referrer
     */
    public function __construct($serverData = null, array $extraFields = null)
    {
        if (null === $serverData) {
            $this->serverData = &$_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
        }
        
        if (null !== $extraFields) {
            if (isset($extraFields[0])) {
                foreach (array_keys($this->extraFields) as $fieldName) {
                    if (!in_array($fieldName, $extraFields)) {
                        unset($this->extraFields[$fieldName]);
                    }
                }
            } else {
                $this->extraFields = $extraFields;
            }
        }
    }
    
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // skip processing if for some reason request data
        // is not present (CLI or wonky SAPIs)
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }
        
        $record['extra'] = $this->appendExtraFields($record['extra']);
        
        return $record;
    }
    
    /**
     * @param  string $extraName
     * @param  string $serverName
     * @return $this
     */
    public function addExtraField($extraName, $serverName)
    {
        $this->extraFields[$extraName] = $serverName;
        
        return $this;
    }
    
    /**
     * @param  array $extra
     * @return array
     */
    private function appendExtraFields(array $extra)
    {
        foreach ($this->extraFields as $extraName => $serverName) {
            $extra[$extraName] = isset($this->serverData[$serverName]) ? $this->serverData[$serverName] : null;
        }
        
        if (isset($this->serverData['UNIQUE_ID'])) {
            $extra['unique_id'] = $this->serverData['UNIQUE_ID'];
        }
        
        return $extra;
    }
}