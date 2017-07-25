<?php

require(__DIR__ . Z_DS . 'Loader.php');

class z extends \z\Loader{}

// 自动加载
spl_autoload_register(array('z', 'autoload'), true, true);

// 进行基本配置
z::setup();

// 初始化并运行应用
$app = new z\core\App();

$app->run();