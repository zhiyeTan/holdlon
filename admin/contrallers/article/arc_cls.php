<?php

namespace contrallers\article;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;
use z\core\Upload as upload;

use z\basic\arc_category as arcc;

class arc_cls extends \admin\adminContraller
{
	public function arc_cls_list()
	{
		$arcc = arcc::init();
		$model = array();
		$model['colName'] = self::getColName();
		// 获得分类列表
		$model['category'] = addIndent($arcc->getAllCategory());
		return self::render('arc_cls_list', $model);
	}
	public function arc_cls_add()
	{
		$arcc = arcc::init();
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$model = $_POST['form'];
			// 验证令牌
			if(validate::is('token'))
			{
				// 这里继续验证表单元素的合法性
				if($model['name'])
				{
					// 获取数据
					$newData = $model;
					unset($newData['token']);
					// 转换HTML实体
					$newData['content'] = htmlspecialchars($newData['content']);
					$imgs = upload::uploadFileBatch($_FILES, true, true);
					$newData['imgurl'] = $imgs['imgurl'][0];
					if($arcc->add($newData))
					{
						zHeader(self::tipsUrl());
					}
				}
				else
				{
					// TODO 验证失败
					zHeader(self::tipsUrl(1));
				}
			}
			else
			{
				// 这里是重复提交了
				zHeader(self::tipsUrl(2));
			}
		}
		else
		{
			// 加载表单初始化数据
			$model['token'] = session::getToken();
		}
		
		// 获得分类列表
		$model['category'] = zInfiniteClass($arcc->getAllCategory());
		
		// 栏目名称
		$model['colName'] = self::getColName();
		
		return self::render('arc_cls_addNedit', $model);
	}
	public function arc_cls_edit()
	{
		$arcc = arcc::init();
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$model = $_POST['form'];
			// 验证令牌
			if(validate::is('token'))
			{
				// 这里继续验证表单元素的合法性
				if($model['name'])
				{
					// 获取数据
					$newData = $model;
					unset($newData['token'], $newData['imgurl']);
					// 转换HTML实体
					$newData['content'] = htmlspecialchars($newData['content']);
					if(!empty($_FILES['imgurl']))
					{
						$imgs = upload::uploadFileBatch($_FILES, true, true);
						$newData['imgurl'] = $imgs['imgurl'][0];
					}
					// TODO 处理图片上传，并把路径加入到数据数组中
					if($arcc->edit($_GET['id'], $newData))
					{
						zHeader(self::tipsUrl());
					}
				}
				else
				{
					zHeader(self::tipsUrl(1));
				}
			}
			else
			{
				// 这里是重复提交了
				zHeader(self::tipsUrl(2));
			}
		}
		else
		{
			// 加载表单初始化数据
			$model = $arcc->getInfo($_GET['id']);
			// 获得分类列表
			$model['category'] = zInfiniteClass($arcc->getAllCategory(), $model['parent_id'], 1);
			
			$model['token'] = session::getToken();
		}
		// 栏目名称
		$model['colName'] = self::getColName();
		return self::render('arc_cls_addNedit', $model);
	}
	public function arc_cls_delete()
	{
		$arcc = arcc::init();
		$UrlData = array(
			'e' => $_GET['e'],
			'm' => 'index',
			'c' => 'tips'
		);
		// 当状态软删除时则进行真实删除，否则软删除
		$res = $arcc->getStatus($_GET['id']) != 2 ? $arcc->delete($_GET['id']) : $arcc->realDelete($_GET['id']);
		$UrlData['a'] = $res ? 'success' : 'fail';
		zHeader(router::create($UrlData));
	}
}


// 为分类添加缩进
function addIndent($data)
{
	$str = '';
	foreach($data as $v)
	{
		$urlData = array(
			'e'		=> $_GET['e'],
			'm'		=> 'article',
			'c'		=> 'arc_cls',
			'id'	=> $v['id']
		);
		$urlDataEdt = $urlDataDel = $urlData;
		$urlDataEdt['a'] = 'arc_cls_edit';
		$urlDataDel['a'] = 'arc_cls_delete';
		$str .= '<tr class="rpm-table-single-row">';
		$str .= 	'<td style="text-align: left; text-indent: ' . $v['level'] . 'em;">' . $v['name'] . '</td>';
		$str .= 	'<td>' . ($v['status'] ? ($v['status'] == 1 ? '显示' : '已删除') : '隐藏') . '</td>';
		$str .= 	'<td>' . $v['sort'] . '</td>';
		$str .= 	'<td class="rpm-text-align-left">';
		$str .= 		'<a class="rpm-handle_btn" href="' . router::create($urlDataEdt) . '" title="修改"><i class="iconfont icon-handle-btn-edt"></i></a>';
		$str .= 		'<a class="rpm-handle_btn" href="' . router::create($urlDataDel) . '" title="删除"><i class="iconfont icon-handle-btn-del"></i></a>';
		$str .= 	'</td>';
		$str .= '</tr>';
		
		if($v['children'])
		{
			$str .= addIndent($v['children']);
		}
	}
	return $str;
}
