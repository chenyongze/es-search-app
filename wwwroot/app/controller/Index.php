<?php

/**
 * @file: Index.php
 * @Date: 2022/06/04 11:25:57
 * @Author: yongze.chen
 * @email: sapphire.php@gmail.com
 * WORKSPACE_NAME: es-search-app
 */


namespace app\controller;


use app\BaseController;
use Elastic\Elasticsearch\ClientBuilder;
use think\facade\Request;
use think\facade\View;

class Index extends BaseController
{
    private $client;
    public function __construct()
    {
        $params = ['es:9200'];
        $this->client = ClientBuilder::create()->setHosts($params)->build();
    }

    /**
     * 搜索数据
     *
     * @return void
     */
    public function index()
    {
        $keywords = Request::get('keywords', "");
        $from = 0;
        $size = 8000;
        $params =  [
            'index' => "big_data",
            'body' => [
                'query' => [
                    "match" => [
                        "name" => [
                            'query' => $keywords,
                            'boost' => 3, // 权重
                        ],
                    ],
                ],
                'from' => $from,
                'size' => $size
            ]
        ];
        $results = $this->client->search($params);
        $sourceList = $results['hits']['hits'] ?? [];
        $list = [];
        array_walk($sourceList, function ($val) use (&$list) {
            $tmp = [];
            foreach ($val['_source'] as $key => $value) {
                $tmp[] = $key . " : " . $value;
            }
            $list[] = implode(" | ", $tmp);
        });
        $pageCount = count($list);
        return view('index', [
            'list' => $list,
            'keywords' => $keywords,
            'pageCount' => $pageCount,
        ]);
    }

    /**
     * search
     *
     * @return void
     */
    public function testSearch()
    {
        $keywords = 'yong';
        $from = 0;
        $size = 5000;
        $params = [
            'index' => "big_data",
            'type' => '_doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            ['match' => ['profile' => [
                                'query' => $keywords,
                                'boost' => 3, // 权重大
                            ],],],
                            ['match' => ['name' => [
                                'query' => $keywords,
                                'boost' => 2,
                            ]]],
                        ],
                    ],
                ],
                // 'sort' => [
                //     'age' => ['order' => 'desc'],
                // ],
                'from' => $from,
                'size' => $size
            ]
        ];

        // $params2 =  [
        //     'index' => "big_data",
        //     // 'type' => '_doc',
        //     'body' => [
        //         'query' => [
        //             "match" => [
        //                 //"name"=>$keywords, 或者
        //                 "name" => [
        //                     'query' => $keywords,
        //                     'boost' => 3, // 权重
        //                 ],
        //             ],
        //         ],
        //         'from' => $from,
        //         'size' => $size
        //     ]
        // ];
        $results = $this->client->search($params);
        dump($results['hits']['hits']);
    }
}
