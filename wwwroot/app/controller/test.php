<?php

namespace app\controller;

use app\BaseController;

/**
 * @file: Test.php
 * @Date: 2022/06/04 09:07:42
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */
class Test extends BaseController
{
    use \app\traits\EsTrait;
    /**
     * http://localhost:8697/test/createIndex
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
            return $this->client->indices()->create($params);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $msg = json_decode($msg, true);
            return $msg;
        }
    }

    /**
     * http://localhost:8697/test/deleteIndex
     * 删除索引
     *
     * @param string $index_name
     * @return void
     */
    public function deleteIndex($index_name = 'big_data')
    {
        $params = ['index' => $index_name];
        $response = $this->client->indices()->delete($params);
        return $response;
    }

    // 创建文档模板
    public function createMappings($type_name = 'users', $index_name = 'big_data')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'body' => [
                $type_name => [
                    '_source' => [
                        // 'enabled' => true
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'integer', // 整型
                            'index' => 'not_analyzed',
                        ],
                        'name' => [
                            'type' => 'string', // 字符串型
                            'index' => 'analyzed', // 全文搜索
                            'analyzer' => 'ik_max_word'
                        ],
                        'profile' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'ik_max_word'
                        ],
                        'age' => [
                            'type' => 'integer',
                        ],
                    ]
                ]
            ]
        ];
        $response = $this->client->indices()->putMapping($params);
        return $response;
    }

    // 查看映射
    public function getMapping($type_name = 'users', $index_name = 'big_data')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name
        ];
        $response = $this->client->indices()->getMapping($params);
        return $response;
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

    // 判断文档存在
    public function existsDoc($id = 1, $index_name = 'big_data', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->exists($params);
        return $response;
    }


    // 获取文档
    public function getDoc($id = 1, $index_name = 'big_data', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => '_doc',
            'id' => $id
        ];
        $response = $this->client->get($params);
        return $response;
    }

    // 更新文档
    public function updateDoc($id = 1, $index_name = 'big_data', $type_name = 'users')
    {
        // 可以灵活添加新字段,最好不要乱添加
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id,
            'body' => [
                'doc' => [
                    'name' => '大王'
                ]
            ]
        ];

        $response = $this->client->update($params);
        return $response;
    }

    // 查询文档 (分页，排序，权重，过滤)
    public function searchDoc($keywords = "运维", $index_name = "big_data", $type_name = "users", $from = 0, $size = 2)
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            ['match' => ['profile' => [
                                'query' => $keywords,
                                'boost' => 3, // 权重大
                            ]]],
                            ['match' => ['name' => [
                                'query' => $keywords,
                                'boost' => 2,
                            ]]],
                        ],
                    ],
                ],
                'sort' => ['age' => ['order' => 'desc']], 'from' => $from, 'size' => $size
            ]
        ];
        $results = $this->client->search($params);
        return $results;
    }
}
