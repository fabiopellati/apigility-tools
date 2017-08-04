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

namespace ApigilityTools\Mapper\Listener;

use ApigilityTools\Exception\InvalidParamException;
use ApigilityTools\Mapper\MapperDeleteAwareInterface;
use ApigilityTools\Mapper\MapperFetchAwareInterface;
use ApigilityTools\Mapper\MapperUpdateAwareInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class ComposedKeysListener
    extends AbstractListenerAggregate
{


    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(MapperDeleteAwareInterface::EVENT_MAPPER_PRE_DELETE, [$this, 'onDelete'], $priority);
        $this->listeners[] = $events->attach(MapperFetchAwareInterface::EVENT_MAPPER_PRE_FETCH, [$this, 'onSelect'], $priority);
        $this->listeners[] = $events->attach(MapperUpdateAwareInterface::EVENT_MAPPER_PRE_UPDATE, [$this, 'onUpdate'], $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onSelect(Event $e)
    {
        $request = $e->getRequest();
        $id = $request->getParameters()->get('id');
        if (empty($id)) {
            return $e->getResponse();
        }

        return $this->onEvent($e);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onDelete(Event $e)
    {
        return $this->onEvent($e);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onUpdate(Event $e)
    {
        return $this->onEvent($e);

    }


    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @todo linea 116 questa eccezione non ha effetti sulla cascata
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            $id = $request->getParameters()->get('id');
            $identifierDelimiter = $request->getParameters()->get('identifierDelimiter');
            if (!empty($identifierDelimiter)) {
                $identifierDelimiter = '_';
            }
            $identifierName = $request->getParameters()->get('identifierName');
            $keys=[];
            if (!empty($identifierName)) {
                $keys = explode($identifierDelimiter, $identifierName);
            }

            $values = explode($identifierDelimiter, $id);
            if (count($values) != count($keys)) {
                throw new InvalidParamException('Id parameter contains a wrong number of elements', 500);
            }

            $composedKey=array_combine($keys,$values);
            $request->getParameters()->set('composedKey', $composedKey);
            $request->getParameters()->set('constraint', [$identifierName => $id]);

        } catch (\Exception $error) {
            $response->setcontent($error);
            $e->stopPropagation();
        }

        return $response;

    }


}
