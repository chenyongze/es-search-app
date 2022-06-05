<?php

/**
 * es 特性
 * @file: EsTrait.php
 * @Date: 2022/06/05 16:17:12
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */

namespace app\traits;

use Elastic\Elasticsearch\ClientBuilder;

trait EsTrait
{
    protected $client;
    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(['es:9200'])->build();
    }
}
