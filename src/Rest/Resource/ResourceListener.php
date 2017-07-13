<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 21/02/17
 * Time: 12.24
 */

namespace ApigilityTools\Rest\Resource;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

class ResourceListener
    extends AbstractResourceListener
{

    const EVENT_REQUEST_QUERY = 'event.request.query';

    /**
     * @var \Zend\EventManager\EventManagerAwareInterface
     */
    protected $mapper;

    /**
     * DefaultResourceListener constructor.
     *
     * @param $mapper
     */
    function __construct($mapper)
    {

        if (!$mapper) {
            return new ApiProblem('mapper not injected');
        }
        $this->mapper = $mapper;
    }


    /**
     * Create a resource
     *
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $data = $this->retrieveData($data);
        $id = $this->mapper->create($data);
        $result = $this->fetch($id);

        return $result;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     *
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        $result = $this->mapper->delete($id);

        return $result;

        //        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
//        $result = $this->mapper->deleteList($data);
//        return $result;

        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     *
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $result = $this->mapper->fetch($id);

//        print_r($result->toArray());exit;

        return $result->current();
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     *
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $requestQuery = $this->getEvent()->getRequest()->getQuery();
        $this->mapper->getEvent()->getRequest()->getParameters()->set('request_query', $requestQuery);

//        print_r([__METHOD__=>$requestQuery]);exit;
//        $this->mapper->getEventManager()->triggerEvent(self::EVENT_REQUEST_QUERY, )

        /**
         * @var $result \Zend\Paginator\Paginator
         */
        $result = $this->mapper->fetchAll($params);

        return $result;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        $data = $this->retrieveData($data);
        $this->mapper->patch($id, $data);

        return $this->fetch($id);
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
//        $result = $this->mapper->patchList($data);
//        return $result;

        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
//        $result = $this->mapper->replaceList($data);
//        return $result;
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {

        $data = $this->retrieveData($data);
        $data = $this->prepareDataForExecute($data);
        $result = $this->mapper->update($id, $data);

        return $result;

//        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

    /**
     * Retrieve data
     *
     * Retrieve data from composed input filter, if any; if none, cast the data
     * passed to the method to an array.
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function retrieveData($data)
    {
        $filter = $this->getInputFilter();
        if (null !== $filter) {
            return $filter->getValues();
        }

        return (array)$data;
    }

    protected function prepareDataForExecute(&$data)
    {

        foreach ($data as $key => $value) {
//            if(preg_match('#^\_.+$#', $key)!=0){
            if (is_array($value) || preg_match('#^\_.+$#', $key) != 0) {
                unset($data[$key]);
            }
        }

        return $data;
    }

}