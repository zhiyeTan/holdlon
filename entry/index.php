<?php

// [ 应用入口文件 ]

// 定义分隔符
define('Z_DS', DIRECTORY_SEPARATOR);

// 定义入口目录(多点部署时不需要定义)
define('ENTRY_PATH', __DIR__);

// 定义当前入口对应的应用目录
define('CURR_PATH', dirname(ENTRY_PATH) . Z_DS . 'app' . Z_DS);

// 加载框架引导文件
require dirname(ENTRY_PATH) . Z_DS . 'z' . Z_DS . 'z.php';

$app = new z\core\App();

$app->run();
