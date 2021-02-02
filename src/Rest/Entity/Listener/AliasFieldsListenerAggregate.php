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
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

/**
 * lo scopo di questo listener è quello di disaccoppiare la logica di filtraggio dell'id
 * per SELECT, UPDATE, DELETE
 *
 * per consentire di manipolare l'id filtrato prima dell'esecuzione della query nel caso ad esempio delle chiavi
 * composite
 */
class AliasFieldsListenerAggregate
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
        $alias = $request->getParameters()->get('alias');
        foreach ($alias as $f1 => $f) {
            if (!empty($arrayCopy[$f])) {
                $arrayCopy[$f1] = $arrayCopy[$f];
            }
        }
        $request->getParameters()->set('arrayCopy', $arrayCopy);
        $response->setContent($arrayCopy);

        return $response;

    }

}
