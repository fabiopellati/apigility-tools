<?php

namespace ApigilityTools\Mapper;

interface MapperCreateAwareInterface
{
    const EVENT_MAPPER_PRE_CREATE = 'mapper.create.pre';
    const EVENT_MAPPER_CREATE = 'mapper.create';
    const EVENT_MAPPER_POST_CREATE = 'mapper.create.post';

    public function create($data);

}
