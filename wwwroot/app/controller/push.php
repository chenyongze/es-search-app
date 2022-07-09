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

    /**
     * 创建索引
     * http://localhost:8697/Push
     * @return void
     */
    public function index()
    {
        $docs[] = ['name' => '张三丰', 'age' => "32", 'pc' => '445万', 'sex' => '1'];
        foreach ($docs as $v) {
            $r = $this->addDoc($v);
            echo json_encode($v) . "<br/>";
        }
    }



    /**
     * http://localhost:8697/Push/createIndex
     * 创建索引
     * @param string $index_name
     * @return void
     */
    public function createIndex($index_name = 'big_data')
    {
        // 只能创建一次
        $params = [
            'index' => $index_name,
            'body' => [
                'settings' => [
                    'number_of_shards' => 15,
                    'number_of_replicas' => 0
                ]
            ]
        ];
        try {
            $res = $this->client->indices()->create($params);
            echo $res;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $msg = json_decode($msg, true);
            return $msg;
        }
    }

    /**
     * http://localhost:8697/Push/deleteIndex
     * 删除索引
     *
     * @param string $index_name
     * @return void
     */
    public function deleteIndex($index_name = 'big_data')
    {
        $params = ['index' => $index_name];
        $response = $this->client->indices()->delete($params);
        echo $response;
    }

    /**
     * 添加文档
     *  
     * @param [type] $doc
     * @param string $index_name
     * @param string $type_name
     * @return void
     */
    private function addDoc($doc, $index_name = 'big_data', $type_name = 'users')
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
