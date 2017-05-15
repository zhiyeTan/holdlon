<?php

require(__DIR__ . '/Loader.php');

class z extends \z\Loader{}

// 自动加载
spl_autoload_register(array('z', 'autoload'), true, true);

// 进行基本配置
z::setup();

// 加载库文件
require(LOAD_PATH . 'z' . Z_DS . 'LibMaps.php');