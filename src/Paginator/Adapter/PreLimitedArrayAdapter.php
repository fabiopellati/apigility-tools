<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 27/06/17
 * Time: 10.38
 */

namespace ApigilityTools\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;

class PreLimitedArrayAdapter implements AdapterInterface
{

    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * PreLimitedArrayAdapter constructor.
     */
    public function __construct($array, $count)
    {

        $this->array = $array;
        $this->count = $count;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        return $this->array;
    }

    public function count()
    {
        return $this->count;
    }
}