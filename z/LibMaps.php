<?php

$libMaps = array(
	// TODO 这里添加需要加载的库文件
	'Base.php'
);


foreach($libMaps as $v)
{
	require(LOAD_PATH . 'z' . Z_DS . 'lib' . Z_DS . $v);
}
