<?php

require(__DIR__ . '/Loader.php');

class z extends \z\Loader{}

// 自动加载
spl_autoload_register(array('z', 'autoload'), true, true);

// 进行基本配置
z::setup();

// 加载类名映射
z::$classMap = require(dirname(__FILE__) . Z_DS . 'ClassMaps.php');
