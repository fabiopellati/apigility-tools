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
interface MapperFetchAllAwareInterface
{
    const EVENT_MAPPER_PRE_FETCH_ALL = 'mapper.fetchAll.pre';
    const EVENT_MAPPER_FETCH_ALL = 'mapper.fetchAll';
    const EVENT_MAPPER_POST_FETCH_ALL = 'mapper.fetchAll.post';

    public function fetchAll();

}
