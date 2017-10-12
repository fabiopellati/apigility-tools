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
interface MapperFetchAwareInterface
{
    const EVENT_MAPPER_PRE_FETCH = 'mapper.fetch.pre';
    const EVENT_MAPPER_FETCH = 'mapper.fetch';
    const EVENT_MAPPER_POST_FETCH = 'mapper.fetch.post';

    public function fetch($id);

}
