<?php
/**
 * lo scopo di questo listener è quello di disaccoppiare la logica di filtraggio dell'id
 * per SELECT, UPDATE, DELETE
 *
 * per consentire di manipolare l'id filtrato prima dell'esecuzione della query nel caso ad esempio delle chiavi
 * composite
 *
 *
 */

namespace ApigilityTools\Listener\Entity;

use ApigilityTools\Rest\EventAwareEntity;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\Exception\InvalidArgumentException;

class CompositeKeysListenerAggregate
    extends AbstractListenerAggregate
{
    /**
     * @var string
     */
    protected $identifierDelimiter;

    /**
     * @var array
     */
    protected $keys = [];

    public function __construct($keys, $identifierDelimiter = '_')
    {
        $this->identifierDelimiter = $identifierDelimiter;
        if (is_array($keys)) {
            $this->keys = $keys;

        } else if (is_string($keys)) {
            $this->keys = explode($identifierDelimiter, $keys);
        }

        if (!is_array($this->keys) || count($this->keys) === 0) {
            throw new InvalidArgumentException('keys must be array or valid string ', 500);
        }
    }


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
        if (is_array($arrayCopy)) {
            $values = [];
            foreach ($this->keys as $key) {
                $values[$key] = $arrayCopy[$key];
            }
            $newKey = implode($this->identifierDelimiter, $this->keys);
            $newValue = implode($this->identifierDelimiter, $values);
            $arrayCopy[$newKey] = $newValue;
            $arrayCopy['id'] = $newValue;
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

//        $input=$request->getParameters()->get('input');
//        if(is_array($input)){
//            $response->setContent($input);
//        }
        return $response;

    }

}
