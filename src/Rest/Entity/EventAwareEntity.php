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

namespace ApigilityTools\Rest\Entity;

use MessageExchangeEventManager\Event\EventAwareTrait;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\Response\ResponseInterface;
use MessageExchangeEventManager\Resultset\ResultsetInterface;
use MessageExchangeEventManager\SingletonEventManagerAwareTrait;
use Zend\Stdlib\ArrayObject;

class EventAwareEntity
    extends ArrayObject
    implements ResultsetInterface
{
    use SingletonEventManagerAwareTrait;
    use EventAwareTrait;

    const EVENT_GET_ARRAY_COPY = 'getArrayCopy';
    const EVENT_EXCHANGE_ARRAY = 'exchangeArray';

    /**
     * @param array|\Zend\Stdlib\ArrayObject $input
     *
     * @return array|void
     * @throws \ApigilityTools\Exception\RuntimeException
     */
    public function exchangeArray($input)
    {
        $this->getEvent()->getRequest()->getParameters()->set('input', $input);
        $response = $this->triggerEvent(self::EVENT_EXCHANGE_ARRAY, $this->getEvent());
        $content = $response->getContent();
        if (is_array($content)) {
            $storage = parent::exchangeArray($content);
        } else {
            $storage = parent::exchangeArray($input);
        }

        return $storage;
    }

    /**
     * @return mixed
     * @throws \ApigilityTools\Exception\RuntimeException
     */
    public function getArrayCopy()
    {
        $arrayCopy = parent::getArrayCopy();
        $this->getEvent()->getRequest()->getParameters()->set('arrayCopy', $arrayCopy);
        $response = $this->triggerEvent(self::EVENT_GET_ARRAY_COPY, $this->getEvent());
        $array = $response->getContent();
        if (is_array($array)) {
            return $array;

        } else {
            return $arrayCopy;
        }
    }

    /**
     *
     * @return array|mixed
     */
    public function fetchAll()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param mixed $data
     */
    public function initialize($data)
    {
        $this->exchangeArray($data);
    }

    /**
     * @param                                                   $eventName
     *
     * @param \MessageExchangeEventManager\Event\EventInterface $event
     *
     * @return mixed
     * @throws \Exception
     */
    protected function triggerEvent($eventName, EventInterface $event)
    {
        $event->setName($eventName);
        $responses = $this->getEventManager()->triggerEvent($event);
        if ($responses->count() === 0) {
            return $event->getResponse();
        } else {
            $response = $responses->last();
        }
        if (!$response instanceof ResponseInterface) {

            throw new \Exception('last response unattended, is attended MessageExchangeEventManager\Response\ResponseInterface',
                                 500);
        }

        return $response;
    }

}
