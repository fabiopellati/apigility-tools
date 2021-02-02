<?php
/**
 *
 * apigility-tools (https://github.com/fabiopellati/apigility-tools)
 *
 * @link      https://github.com/fabiopellati/apigility-tools for the canonical source repository
 * @copyright Copyright (c) 2017 Fabio Pellati (https://github.com/fabiopellati)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 */

namespace ApigilityTools\Mapper;

use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;

/**
 * Class DefaultTableGatewayMapper
 * espone metodi per l'interazione con la base dati con comportamenti standard
 *
 * si possono modificare i comportamenti dei metodi in override sulle classi estese
 * oppure alterare i select intercettando gli eventi
 *
 * @package ApigilityTools
 */
class Mapper
    implements MapperFetchAwareInterface, MapperFetchAllAwareInterface,
               MapperUpdateAwareInterface, MapperPatchAwareInterface, MapperCreateAwareInterface, MapperDeleteAwareInterface
{

    use EventManagerAwareTrait;
    use EventRunAwareTrait;

    /**
     * @var \MessageExchangeEventManager\Event\Event
     */
    protected $event;

    /**
     *  constructor.
     *
     * @param \MessageExchangeEventManager\Event\EventInterface $event
     *
     * @internal param \ToolkitApi\Toolkit $instance
     */
    public function __construct(EventInterface $event)
    {

        $this->event = $event;
        $this->getEvent()->setTarget($this);
    }

    /**
     * @return \MessageExchangeEventManager\Event\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Create a new resource.
     *
     * @param array|object $data Data representing the resource to create.
     *
     * @return array|object Newly created resource.
     */
    public function create($data)
    {
        $this->getEvent()->getRequest()->getParameters()->set('data', $data);
        $response = $this->runEvent($this->getEvent(), [
            self::EVENT_MAPPER_PRE_CREATE,
            self::EVENT_MAPPER_CREATE,
            self::EVENT_MAPPER_POST_CREATE,
        ]);

        return $response->getContent();

    }

    /**
     *
     * Replace an existing resource.
     *
     * @param int|string   $id   Identifier of resource.
     * @param array|object $data Data with which to replace the resource.
     *
     * @return array|object Updated resource.
     */
    public function update($id, $data)
    {
        $this->getEvent()->getRequest()->getParameters()->set('id', $id);
        $this->getEvent()->getRequest()->getParameters()->set('data', $data);
        $response = $this->runEvent($this->getEvent(), [
            self::EVENT_MAPPER_PRE_UPDATE,
            self::EVENT_MAPPER_UPDATE,
            self::EVENT_MAPPER_POST_UPDATE,
        ]);

        return $response->getContent();
    }

    /**
     *
     * Update an existing resource.
     *
     * @param int|string   $id   Identifier of resource.
     * @param array|object $data Data with which to update the resource.
     *
     * @return array|object Updated resource.
     */
    public function patch($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     *
     * Delete an existing resource.
     *
     * @
     * @param int|string $id Identifier of resource.
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->getEvent()->getRequest()->getParameters()->set('id', $id);
        $response = $this->runEvent($this->getEvent(), [
            self::EVENT_MAPPER_PRE_DELETE,
            self::EVENT_MAPPER_DELETE,
            self::EVENT_MAPPER_POST_DELETE,
        ]);

        return $response->getContent();

    }

    /**
     *
     * Fetch a resource
     *
     * @param  mixed $id
     *
     * @return mixed|\Laminas\ApiTools\ApiProblem\ApiProblem
     */
    public function fetch($id)
    {

        $this->getEvent()->getRequest()->getParameters()->set('id', $id);
        $response = $this->runEvent($this->getEvent(), [
            self::EVENT_MAPPER_PRE_FETCH,
            self::EVENT_MAPPER_FETCH,
            self::EVENT_MAPPER_POST_FETCH,
        ]);

        return $response->getContent();

    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     *
     * @return mixed|\Laminas\ApiTools\ApiProblem\ApiProblem
     */
    public function fetchAll($params = [])
    {

        $this->getEvent()->getRequest()->getParameters()->set('params', $params);
        $response = $this->runEvent($this->getEvent(), [
            self::EVENT_MAPPER_PRE_FETCH_ALL,
            self::EVENT_MAPPER_FETCH_ALL,
            self::EVENT_MAPPER_POST_FETCH_ALL,
        ]);

        return $response->getContent();

    }

}
