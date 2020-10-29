<?php
/**
 * Created by PhpStorm.
 * User: Ron Friedman
 * Date: 9/18/2016
 * Time: 16:54
 */

namespace SimplyLink\UtilsBundle\Utils\Api;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\QueryBuilder;
use SimplyLink\UtilsBundle\Utils\GenericDataManager;
use Symfony\Component\HttpFoundation\Request;

class BaseApiEntityRepository extends EntityRepository
{
    
    /**
     * @param Request $request
     * @param array $filters
     * @return QueryBuilder
     * @throws \SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument
     */
    public function findAllForApiQueryBuilder(Request $request, array $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('record');

        $this->addRelationsForApiQueryBuilder($queryBuilder);

        // dynamically read all params from GET query and add them to query builder
        $queryParams = $request->query->all();
        // add custom filters
        $allParams = array_merge($queryParams,$filters);

        /** remove saved parameters -> exclude from QueryBuilder */
        $savedParams = ['page'];
        foreach ($savedParams as $key => $param){
            if (array_key_exists($param,$allParams)){
                unset($allParams[$param]);
            }
        }


        /** since => search for records where updateAt > since(timestamp) */
        $sinceParam = 'since';
        if(array_key_exists($sinceParam,$allParams))
        {
            $timestamp = $allParams[$sinceParam];
            $date = new \DateTime("@$timestamp");
            $queryBuilder->andWhere('record.updatedAt' . ' > :' . $sinceParam)
                ->setParameter($sinceParam, $date);

            unset($allParams[$sinceParam]);
        }


        /** until => search for records where updateAt > since(timestamp) */
        $untilParam = 'until';
        if(array_key_exists($untilParam,$allParams))
        {
            $timestamp = $allParams[$untilParam];
            $date = new \DateTime("@$timestamp");
            $queryBuilder->andWhere('record.updatedAt' . ' < :' . $untilParam)
                ->setParameter($untilParam, $date);

            unset($allParams[$untilParam]);
        }



        /** orderBy - add order by to query. 2 fields required: orderBy, orderBySort */
        $orderByParam = 'orderBy';
        if(array_key_exists($orderByParam,$allParams))
        {
            $orderBy = $allParams[$orderByParam];
            if($this->checkIfFieldExists($orderBy))
            {
                $sort = strtoupper(trim($request->query->get('orderBySort','ASC')));
                if($sort != 'ASC' && $sort != 'DESC')
                    $sort='ASC';

                $queryBuilder->addOrderBy('record.' . $orderBy,$sort);
            }
            unset($allParams[$orderByParam]);
        }


        $metaData = $this->getClassMetadata();


        /** Loop over params to add to QueryBuilder */
        if(count($allParams) > 0)
        {
            $paramCount = 0;
            foreach ($allParams as $key => $value) {

                if(!($value !== null && strlen($value) > 0))
                    continue;

                $conditionNotEqual = false;

                if(substr($key, -1) == '!') {
                    $conditionNotEqual=true;
                    $key = substr($key, 0, strlen($key) - 1);
                }




                $fieldMapping = null;
                $fieldType = null;
                try {
                    $fieldMapping = $metaData->getFieldMapping($key);
                    $fieldType = GenericDataManager::getArrayValueForKey($fieldMapping,'type');
                } catch (MappingException $e){
                    // field does not exists.
                    try {
                        $fieldMapping = $metaData->getAssociationMapping($key);
                        if ($fieldMapping)
                            $fieldType = 'integer';
                    }
                    catch (MappingException $e){
                        // field does not exists.
                    }

                }



                $isSubEntityFilter = (strpos($key,'_') !== false);
                // TODO: check if sub entity field exists
                if($isSubEntityFilter)
                {
                    $key = str_replace('_','.',$key);
                }

                if(!$isSubEntityFilter)
                {
                    if(!($fieldType && $fieldMapping))
                        continue;
                }




                try{
                    $groupParamValue = false;

                    $fieldCondition = ($conditionNotEqual ? '!' : '') . '=';

                    if($fieldType == 'string')
                    {
                        $fieldCondition = ($conditionNotEqual ? 'NOT ' : '') . 'LIKE';
                        $value = '%' . $value . '%';
                    }
                    else if($fieldType == 'integer')
                    {
                        if (strpos($value, ',') !== false)
                        {
                            $fieldCondition = ($conditionNotEqual ? 'NOT ' : '') . 'IN';
                            $value = array_values(explode(',',$value));
                            $groupParamValue = true;
                        }
                    }


                    $entityPrefix = ($isSubEntityFilter) ? '' : 'record.';

                    $paramName = 'param' . $paramCount;
                    $queryBuilder->andWhere($entityPrefix . $key . ' ' . $fieldCondition . ' ' . ($groupParamValue ? '(' : '') .  ':' . $paramName .  ($groupParamValue ? ')' : ''))
                        ->setParameter($paramName, $value);

                    $paramCount++;
                }
                catch (\Exception $e){
                    // ignore
                }
            }
        }
        return $queryBuilder;
    }


    /**
     * @param array $ids
     * @return QueryBuilder
     */
    public function findByIdsForApiQueryBuilder(array $ids)
    {
        $queryBuilder = $this->createQueryBuilder('record');
        $queryBuilder->where($queryBuilder->expr()->in('record.id', $ids));
        return $queryBuilder;
    }


    /**
     * Check if field exists in entity
     * @param $field
     * @return bool - exists or not
     */
    private function checkIfFieldExists($field)
    {
        $metaData = $this->getClassMetadata();
        try {
            $fieldMapping = $metaData->getFieldMapping($field);
            if($fieldMapping)
                return true;
        } catch (MappingException $e){
            // field does not exists.
            try {
                $fieldMapping = $metaData->getAssociationMapping($field);
                if ($fieldMapping)
                    return true;
            }
            catch (MappingException $e){
                // field does not exists.
            }

        }

        return false;
    }


    /**
     * Add relations for QueryBuilder in order to filter by relations on API
     * @param QueryBuilder $queryBuilder
     */
    protected function addRelationsForApiQueryBuilder(QueryBuilder &$queryBuilder)
    {
        return;
    }


}