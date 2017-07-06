<?php

namespace ApigilityTools\Mapper;

interface MapperPatchAwareInterface
{
    const EVENT_MAPPER_PRE_PATCH = 'mapper.patch.pre';
    const EVENT_MAPPER_PATCH = 'mapper.patch';
    const EVENT_MAPPER_POST_PATCH = 'mapper.patch.post';

    public function patch($id, $data);


}
