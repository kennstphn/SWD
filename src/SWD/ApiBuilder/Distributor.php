<?php
namespace SWD\ApiBuilder;

use \SWD\ApiBuilder\Exception as Exceptions;

class Distributor
{
    protected $events;

    /**
     * Distributor constructor.
     * @param array $events
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }


    /** @return EventBase[] */
    function getEvents(){
        return $this->events;
    }

    /**
     * @param array $eventArray
     * @return EventBase
     * @throws Exception\IllegalEventType
     * @throws Exception\MissingEventType
     * @throws \SWD\Structures\Bootstrap\Exception\InvalidSubArray
     * @throws \SWD\Structures\Bootstrap\Exception\MissingArrayKey
     * @throws \SWD\Structures\Bootstrap\Exception\RequiredFieldIsNull
     * @throws \SWD\Structures\Bootstrap\Exception\SubClassInstantiationFailure
     * @throws \SWD\Structures\Bootstrap\Exception\TypeError
     */
    function findEvent(array $eventArray){
        if ( ! array_key_exists('type', $eventArray)){throw new Exceptions\MissingEventType;}
        $class = $eventArray['type'];

        $class = str_replace('.', '\\', $class);
        if ( ! in_array($class,$this->getEvents())) {
            throw new Exceptions\IllegalEventType;
        }

        /** @var EventBase $class */
        return $class::fromArray($eventArray);

    }

}