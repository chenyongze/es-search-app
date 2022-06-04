<?php

/**
 * @file: look.php
 * @Date: 2022/06/04 09:19:18
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */

namespace app\controller;

use app\BaseController;
use Elastic\Elasticsearch\ClientBuilder;
use think\facade\View;

class Look extends BaseController
{
    private $client;
    public function __construct()
    {
        $params = ['es:9200'];
        $this->client = ClientBuilder::create()->setHosts($params)->build();
    }

    public function index()
    {
        return view('index', [
            'name' => 'big_data',
        ]);
    }
}
