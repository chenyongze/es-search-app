<?php

namespace app\index\controller;

use Elasticsearch\ClientBuilder;

class Search
{
    private $client;
    // 构造函数
    public function __construct()
    {
        $params = array(
            '127.0.0.1:9200'
        );
        $this->client = ClientBuilder::create()->setHosts($params)->build();
    }

    // 创建索引
    public function index()
    { // 只能创建一次
        $r = $this->delete_index();
        $r = $this->create_index();  //1.创建索引
        $r = $this->create_mappings(); //2.创建文档模板
        $r = $this->get_mapping();
        $docs = [];
        $docs[] = ['id' => 1, 'name' => '小明', 'profile' => '我做的ui界面强无敌。', 'age' => 23];
        $docs[] = ['id' => 2, 'name' => '小张', 'profile' => '我的php代码无懈可击。', 'age' => 24];
        $docs[] = ['id' => 3, 'name' => '小王', 'profile' => 'C的生活，快乐每一天。', 'age' => 29];
        $docs[] = ['id' => 4, 'name' => '小赵', 'profile' => '就没有我做不出的前端页面。', 'age' => 26];
        $docs[] = ['id' => 5, 'name' => '小吴', 'profile' => 'php是最好的语言。', 'job' => 21];
        $docs[] = ['id' => 6, 'name' => '小翁', 'profile' => '别烦我，我正在敲bug呢！', 'age' => 25];
        $docs[] = ['id' => 7, 'name' => '小杨', 'profile' => '为所欲为，不行就删库跑路', 'age' => 27];
        foreach ($docs as $k => $v) {
            $r = $this->add_doc($v['id'], $v);   //3.添加文档
        }
        $r = $this->search_doc("删库 别烦我");  //4.搜索结果
    }

    // 创建索引
    public function create_index($index_name = 'test_ik')
    { // 只能创建一次
        $params = [
            'index' => $index_name,
            'body' => [
                'settings' => [
                    'number_of_shards' => 5,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        try {
            return $this->client->indices()->create($params);
        } catch (Elasticsearch\Common\Exceptions\BadRequest400Exception $e) {
            $msg = $e->getMessage();
            $msg = json_decode($msg, true);
            return $msg;
        }
    }

    // 删除索引
    public function delete_index($index_name = 'test_ik')
    {
        $params = ['index' => $index_name];
        $response = $this->client->indices()->delete($params);
        return $response;
    }

    // 创建文档模板
    public function create_mappings($type_name = 'users', $index_name = 'test_ik')
    {

        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'body' => [
                $type_name => [
                    '_source' => [
                        'enabled' => true
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
    public function get_mapping($type_name = 'users', $index_name = 'test_ik')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name
        ];
        $response = $this->client->indices()->getMapping($params);
        return $response;
    }

    // 添加文档
    public function add_doc($id, $doc, $index_name = 'test_ik', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id,
            'body' => $doc
        ];

        $response = $this->client->index($params);
        return $response;
    }

    // 判断文档存在
    public function exists_doc($id = 1, $index_name = 'test_ik', $type_name = 'users')
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
    public function get_doc($id = 1, $index_name = 'test_ik', $type_name = 'users')
    {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->get($params);
        return $response;
    }

    // 更新文档
    public function update_doc($id = 1, $index_name = 'test_ik', $type_name = 'users')
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
    public function delete_doc($id = 1, $index_name = 'test_ik', $type_name = 'users')
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
    public function search_doc($keywords = "运维", $index_name = "test_ik", $type_name = "users", $from = 0, $size = 2)
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
