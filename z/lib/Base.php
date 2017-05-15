<?php

/**
 * 跳转函数
 * @param: $url string 目标地址
 */
function zHeader($url)
{
	header('Location: ' . $url . PHP_EOL);
	exit(0);
}

/**
 * 无限分类
 * @param: $data array 形如array('id'=>1, 'name'=>'a', 'level'=>1, 'children'=>array(...))的数组
 * @param: $selectedID int 选中的id值
 * @param: $noNext boolean 配合选中值使用，确定是否读取选中分类的子分类
 */
function zInfiniteClass($data, $selectedID = 0, $noNext = 0)
{
	$str = '';
	foreach($data as $v)
	{
		$str .= '<option value="' . $v['id'] . '"' . ($v['id'] == $selectedID ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp;', $v['level']*3) . $v['name'] . '</option>';
		if($v['children'] && !($v['id'] == $selectedID && $noNext))
		{
			$str .= zInfiniteClass($v['children'], $selectedID, $noNext);
		}
	}
	return $str;
}