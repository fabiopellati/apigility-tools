<?php

namespace ApigilityTools\Mapper;

interface MapperFetchAllAwareInterface
{
    const EVENT_MAPPER_PRE_FETCH_ALL = 'mapper.fetchAll.pre';
    const EVENT_MAPPER_FETCH_ALL = 'mapper.fetchAll';
    const EVENT_MAPPER_POST_FETCH_ALL = 'mapper.fetchAll.post';

    public function fetchAll();


}
