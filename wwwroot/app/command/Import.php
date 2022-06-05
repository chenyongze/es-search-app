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
            $this->dataSaveTxt($input, $output);
        } elseif ($type == 'csv') {
            $this->dataSaveCsv($input, $output);
        } else {
            $output->writeln('请选择类型');
        }
        $output->writeln('es:import....end....');
    }

    public function dataSaveTxt(Input $input, Output $output)
    {
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $dirs = file(public_path() . 'importData/txt/user.txt');
        $header = explode('|', trim(array_shift($dirs)));
        foreach ($dirs as $value) {
            $data = explode('|', trim($value));
            $insertData = [];
            foreach ($header as $kh => $vh) {
                if (empty($data[$kh])) {
                    break;
                }
                $insertData[$vh] = $data[$kh] ?? '';
            }
            if (!empty($insertData)) {
                $res = $this->addDoc($insertData);
                $output->writeln(json_encode($insertData) . "状态：" . $res);
            } else {
                $output->writeln('no data...');
            }
        }
    }

    /**
     * csv 文件读取
     *
     * @param Input $input
     * @param Output $output
     * @return void
     */
    public function dataSaveCsv(Input $input, Output $output)
    {
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $dirs = $this->getCsvData(public_path() . 'importData/csv/user.csv');
        $header =  array_shift($dirs);
        foreach ($dirs as $value) {
            $data = $value;
            $insertData = [];
            foreach ($header as $kh => $vh) {
                if (empty($data[$kh])) {
                    break;
                }
                $insertData[$vh] = $data[$kh] ?? '';
            }
            if (!empty($insertData)) {
                $res = $this->addDoc($insertData);
                $output->writeln(json_encode($insertData) . "状态：" . $res);
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
