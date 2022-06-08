<?php

declare(strict_types=1);

namespace app\command;

use Elastic\Elasticsearch\ClientBuilder;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * usage:
 *   docker exec xgk-o-php php /app/wwwroot/think es:import csv
 *      csv 存放路径 wwwroot/public/importData/csv
 *   docker exec xgk-o-php php /app/wwwroot/think es:import txt
 *      txt 存放路径 wwwroot/public/importData/txt
 */
class Import extends Command
{
    protected $client = null;
    protected $indexName = 'big_data';
    protected $bathSize = 3000;
    protected $txtDivision = '|';
    protected function configure()
    {
        $this->setName('es:import')
            ->addArgument('type', Argument::OPTIONAL, 'txt or csv')
            ->setDescription('the es:import command');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->client = ClientBuilder::create()->setHosts(['es:9200'])->build();
        $output->writeln('es:import....start....');
        $type = $input->getArgument('type');
        if ($type == 'txt') {
            $this->dataSaveCsvTxt($input, $output, 2);
        } elseif ($type == 'csv') {
            $this->dataSaveCsvTxt($input, $output, 1);
        } else {
            $output->writeln('请选择类型');
        }
        $output->writeln('es:import....end....');
    }

    /**
     * csv 文件读取
     *
     * @param Input $input
     * @param Output $output
     * @return void
     */
    public function dataSaveCsvTxt(Input $input, Output $output, $type = 1)
    {
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $filePath =  $type == 1 ? 'importData/csv/user.csv' : "importData/txt/user.txt";
        $sourceDatas = $type == 1 ? $this->getCsvData(public_path() . $filePath) : file(public_path() . $filePath);
        $header = $type == 1 ? array_shift($sourceDatas) : explode($this->txtDivision, trim(array_shift($sourceDatas)));
        foreach (array_chunk($sourceDatas, $this->bathSize) as $batchDatas) {
            $insertDatas = [];
            foreach ($batchDatas as $value) {
                $data = $type == 1 ? $value : explode($this->txtDivision, trim($value));
                $temp = [];
                foreach ($header as $field => $vh) {
                    if (empty($data[$field])) {
                        continue;
                    }
                    $temp[$vh] = $data[$field] ? (string)$data[$field] : '';
                }
                if (empty($temp)) {
                    continue;
                }
                $insertDatas['body'][] = [
                    'index' => [
                        '_index' => $this->indexName,
                    ]
                ];
                $insertDatas['body'][] = $temp;
            }
            unset($batchDatas);
            if (!empty($insertDatas)) {
                $res = $this->addDocs($insertDatas);
                $output->writeln(json_encode($insertDatas) . "状态：" . $res);
            } else {
                $output->writeln('no data...');
            }
        }
    }

    /**
     * 写入数据
     *
     * @param [type] $id
     * @param [type] $doc
     * @param string $index_name
     * @param string $type_name
     * @return void
     */
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

    /**
     * 批量插入
     *
     * @param array $doc
     * @param string $index_name
     * @param string $type_name
     * @return void
     */
    public function addDocs($docs)
    {
        $response = $this->client->bulk($docs);
        return $response;
    }

    /**
     * 读取csv 文件
     *
     * @param [type] $filePath
     * @return array
     */
    public function getCsvData($filePath)
    {
        $handle = fopen($filePath, "rb");
        $data = [];
        while (!feof($handle)) {
            $data[] = fgetcsv($handle);
        }
        fclose($handle);
        // $data = eval('return ' . iconv('gb2312', 'utf-8', var_export($data, true)) . ';');
        return $data;
    }
}
