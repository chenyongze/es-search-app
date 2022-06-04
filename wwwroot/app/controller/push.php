<?php

namespace app\controller;

use app\BaseController;
use Elastic\Elasticsearch\ClientBuilder;


/**
 * @file: push.php
 * @Date: 2022/06/04 09:07:42
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */
class Push extends BaseController
{
    private $client;
    public function __construct()
    {
        $params = ['es:9200'];
        $this->client = ClientBuilder::create()->setHosts($params)->build();
    }

    // 创建索引
    public function index()
    {
        $docs[] = ['name' => '小甜甜mmmxxx', 'aihao' => '跳舞', 'age' => 27];
        foreach ($docs as $v) {
            //3.添加文档
            $r = $this->addDoc(null, $v);
            dump($r);
        }
    }

    // 添加文档
    public function addDoc($id, $doc, $index_name = 'big_data', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => time(),
            'body' => $doc
        ];
        $response = $this->client->index($params);
        return $response;
    }
}
