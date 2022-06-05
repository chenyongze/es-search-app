<?php

namespace app\controller;

use app\BaseController;

/**
 * @file: push.php
 * @Date: 2022/06/04 09:07:42
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */
class Push extends BaseController
{
    use \app\traits\EsTrait;
    // 创建索引
    public function index()
    {
        $docs[] = ['name' => 'yongze', 'age' => "32", 'sex' => '1'];
        foreach ($docs as $v) {
            $r = $this->addDoc($v);
            dump($r);
        }
    }

    // 添加文档
    public function addDoc($doc, $index_name = 'big_data', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'body' => $doc
        ];
        $response = $this->client->index($params);
        return $response;
    }
}
