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

namespace ApigilityTools\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;

class PreLimitedArrayAdapter
    implements AdapterInterface
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