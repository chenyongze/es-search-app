
前端页面
http://localhost:8697


<!-- es  -->
http://localhost:9201/_cat/indices?v

<!-- 所有字段 -->
http://localhost:9201/big_data

<!-- 所有数据 -->
http://localhost:9201/big_data/_search

<!-- 简单搜索 -->
http://localhost:9201/big_data/_search?q=id:3



操作手册
    主页面： 
        http://localhost:8697
    查看es运行是否正常：
        http://localhost:9201
    清空数据库：http://localhost:8697/Push/deleteIndex
    添加数据库 ： http://localhost:8697/Push/createIndex
    测试写入数据 ： http://localhost:8697/Push

    批量执行csv 数据
        文件存储：
            csv 存放路径 wwwroot/public/importData/csv
        命令行执行：
          docker exec xgk-o-php php /app/wwwroot/think es:import csv

    批量执行txt 数据
        文件存储：
            txt 存放路径 wwwroot/public/importData/txt
        命令行执行：
           docker exec xgk-o-php php /app/wwwroot/think es:import txt