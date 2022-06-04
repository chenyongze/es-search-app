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

    /**
     * 搜索数据
     *
     * @return void
     */
    public function index()
    {

        $list = [
            [
                "姓名" => '111',
                "年龄" => 90,
                "生肖" => "狗",
            ],
            [
                "姓名" => '111',
                "年龄" => 90,
                "生肖" => "狗",
            ],
            [
                "姓名" => '111',
                "年龄" => 90,
                "生肖" => "狗",
            ],
            [
                "姓名" => '111',
                "年龄" => 90,
                "生肖" => "狗",
            ],
        ];
        $pageCount = count($list);
        return view('index', [
            'list' => $list,
            'pageCount' => $pageCount,
        ]);
    }
}
