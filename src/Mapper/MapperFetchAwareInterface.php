<?php

namespace ApigilityTools\Mapper;

interface MapperFetchAwareInterface
{
    const EVENT_MAPPER_PRE_FETCH = 'mapper.fetch.pre';
    const EVENT_MAPPER_FETCH = 'mapper.fetch';
    const EVENT_MAPPER_POST_FETCH = 'mapper.fetch.post';

    public function fetch($id);


}
