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

namespace ApigilityTools\Rest\Entity\Listener;

use ApigilityTools\Rest\Entity\EventAwareEntity;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\Exception\InvalidArgumentException;

/**
 * lo scopo di questo listener è quello di disaccoppiare la logica di filtraggio dell'id
 * per SELECT, UPDATE, DELETE
 *
 * per consentire di manipolare l'id filtrato prima dell'esecuzione della query nel caso ad esempio delle chiavi
 * composite
 */
class CompositeKeysListenerAggregate
    extends AbstractListenerAggregate
{

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(EventAwareEntity::EVENT_GET_ARRAY_COPY, [$this, 'onGetArrayCopy'],
                                             $priority);
        $this->listeners[] = $events->attach(EventAwareEntity::EVENT_EXCHANGE_ARRAY, [$this, 'onExchangeArray'],
                                             $priority);
    }

    /**
     *
     * @param Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    public function onGetArrayCopy(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $arrayCopy = $request->getParameters()->get('arrayCopy');
        $identifierName = $request->getParameters()->get('identifierName');
        $identifierDelimiter = $request->getParameters()->get('identifierDelimiter');
        if (empty($identifierDelimiter)) {
            $identifierDelimiter = '_';
        }
        $keys = explode($identifierDelimiter, $identifierName);
        if (!is_array($keys) || count($keys) === 0) {
            throw new InvalidArgumentException('keys must be array or valid string ', 500);
        }
        if (is_array($arrayCopy)) {
            $values = [];
            foreach ($keys as $key) {
                $values[$key] = $arrayCopy[$key];
            }
            $newValue = implode($identifierDelimiter, $values);
            $arrayCopy[$identifierName] = $newValue;
            $arrayCopy['id'] = $newValue;
            $request->getParameters()->set('arrayCopy', $arrayCopy);
            $response->setContent($arrayCopy);
        }

        return $response;

    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onExchangeArray(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $input = $request->getParameters()->get('input');
        if (is_array($input)) {
            $response->setContent($input);
        }

        return $response;

    }

}
