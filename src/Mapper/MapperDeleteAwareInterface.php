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
interface MapperDeleteAwareInterface
{
    const EVENT_MAPPER_PRE_DELETE = 'mapper.delete.pre';
    const EVENT_MAPPER_DELETE = 'mapper.delete';
    const EVENT_MAPPER_POST_DELETE = 'mapper.delete.post';

    public function delete($id);
}
