<?php

namespace Simplylink\UtilsBundle\Utils;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SLBaseUtils
 *
 * Base Utils used to get fundamentals objects for Symfony
 *
 * @package Simplylink\UtilsBundle\Utils
 */
class SLBaseUtils {

    /**
     * @return \AppKernel
     */
    public function GetGlobalKernel() {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel;
    }

    /**
     * @return \AppKernel
     */
    public static function GetKernel() {
        return (new SLBaseUtils())->GetGlobalKernel();
    }

    /**
     * Get container from kernel
     * 
     * @global \AppKernel $kernel
     * @return ContainerInterface container
     */
    public static function GetKernelContainer() {
        $kernel = self::GetKernel();
        return $kernel->getContainer();
    }

    /**
     * Get Doctrine Entity Manager 
     * 
     * @global \AppKernel $kernel
     * @return EntityManager|ObjectManager|mixed
     *
     */
    public static function GetEntityManager() {
        $entityManager = self::GetKernelContainer()->get('doctrine.orm.entity_manager');
        return $entityManager;
    }


    /**
     * @param $paramName
     * @return string|null
     */
    public static function getApplicationParameter($paramName)
    {
        if(self::GetKernelContainer()->hasParameter($paramName))
            return self::GetKernelContainer()->getParameter($paramName);

        return null;
    }

    /**
     * Get Current authenticate user
     * @return mixed|null
     */
    public static function getCurrentAuthenticateUser()
    {
        $token = self::GetKernelContainer()->get('security.token_storage')->getToken();
        if ($token && is_object($token->getUser())) {
            return $token->getUser();
        }
        return null;
    }

    /**
     * Write to Log (var/logs)
     *
     * @param string $info
     * @param string $error
     *
     * @deprecated
     */
    public static function writeErrorLog($info, $error) {
        $logger = self::getLogger();
        $logger->info($info);
        $logger->error($error);
    }
    

    /**
     * @return \Monolog\Logger|mixed
     */
    public static function getLogger() {
        $logger = self::GetKernelContainer()->get('logger');
        return $logger;
    }

}
