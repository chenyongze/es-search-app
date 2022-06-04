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
        $docs[] = ['id' => 1, 'name' => '小明', 'profile' => '我做的ui界面强无敌。', 'age' => 23];
        $docs[] = ['id' => 2, 'name' => '小张', 'profile' => '我的php代码无懈可击。', 'age' => 24];
        $docs[] = ['id' => 3, 'name' => '小王', 'profile' => 'C的生活，快乐每一天。', 'age' => 29];
        $docs[] = ['id' => 4, 'name' => '小赵', 'profile' => '就没有我做不出的前端页面。', 'age' => 26];
        $docs[] = ['id' => 5, 'name' => '小吴', 'profile' => 'php是最好的语言。', 'job' => 21];
        $docs[] = ['id' => 6, 'name' => '小翁', 'profile' => '别烦我，我正在敲bug呢！', 'age' => 25];
        $docs[] = ['id' => 7, 'name' => '小杨', 'profile' => '为所欲为，不行就删库跑路', 'age' => 27];
        $docs[] = ['id' => 8, 'name' => 'yong', 'profile' => 'test......', 'age' => 27];
        $docs[] = ['id' => 9, 'name' => '88888', 'profile' => 'testssss', 'age' => 27];
        $docs[] = ['id' => 10, 'name' => '小嘻嘻嘻', 'profile' => 'testssss', 'age' => 27];
        $docs[] = ['id' => 11, '名字' => '小嘻嘻嘻', 'profile' => 'testssss', 'age' => 27];
        foreach ($docs as $k => $v) {
            //3.添加文档
            $r = $this->addDoc($v['id'], $v);
            dump($r);
        }
    }

    /**
     * http://localhost:8697/push/createIndex
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
     * http://localhost:8697/push/deleteIndex
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
    public function exists_doc($id = 1, $index_name = 'big_data', $type_name = 'users')
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
    public function get_doc($id = 1, $index_name = 'big_data', $type_name = 'users')
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
    public function update_doc($id = 1, $index_name = 'big_data', $type_name = 'users')
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

    // 删除文档
    public function delete_doc($id = 1, $index_name = 'big_data', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->delete($params);
        return $response;
    }

    // 查询文档 (分页，排序，权重，过滤)
    public function search_doc($keywords = "运维", $index_name = "big_data", $type_name = "users", $from = 0, $size = 2)
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
