<?php

use z\core\Router as Router;

$maps = array();


// 文章管理（一级栏目）
$maps['a'] = array(
	'module'	=> 'article',
	'name'		=> '文章管理',
	'display'	=> 1,
	'list'		=> array()
);

// 添加文章（二级栏目）
$maps['a']['list']['arc_list'] = array(
	'name'		=> '文章列表',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'article', 'a'=>'arc_list'))
);

$maps['a']['list']['arc_add'] = array(
	'name'		=> '添加文章',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'article', 'a'=>'arc_add'))
);

$maps['a']['list']['arc_edit'] = array(
	'name'		=> '修改文章',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'article', 'a'=>'arc_edit'))
);

$maps['a']['list']['arc_delete'] = array(
	'name'		=> '删除文章',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'article', 'a'=>'arc_delete'))
);

$maps['a']['list']['arc_cls_list'] = array(
	'name'		=> '文章分类',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'arc_cls', 'a'=>'arc_cls_list'))
);

$maps['a']['list']['arc_cls_add'] = array(
	'name'		=> '添加分类',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'arc_cls', 'a'=>'arc_cls_add'))
);

$maps['a']['list']['arc_cls_edit'] = array(
	'name'		=> '修改分类',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'arc_cls', 'a'=>'arc_cls_edit'))
);

$maps['a']['list']['arc_cls_delete'] = array(
	'name'		=> '删除分类',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'article', 'c'=>'arc_cls', 'a'=>'arc_cls_delete'))
);


// 友情链接
$maps['lk'] = array(
	'module'	=> 'links',
	'name'		=> '友链管理',
	'display'	=> 1,
	'list'		=> array()
);

$maps['lk']['list']['link_list'] = array(
	'name'		=> '友链列表',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'link', 'c'=>'link', 'a'=>'link_list'))
);

$maps['lk']['list']['link_add'] = array(
	'name'		=> '添加友链',
	'display'	=>1,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'link', 'c'=>'link', 'a'=>'link_add'))
);

$maps['lk']['list']['link_edit'] = array(
	'name'		=> '修改友链',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'link', 'c'=>'link', 'a'=>'link_edit'))
);

$maps['lk']['list']['link_delete'] = array(
	'name'		=> '删除友链',
	'display'	=>0,
	'url'		=>Router::create(array('e'=>$_GET['e'], 'm'=>'link', 'c'=>'link', 'a'=>'link_delete'))
);


// 权限管理
$maps['p'] = array(
	'module'	=> 'permission',
	'name'		=> '管理与权限',
	'display'	=> 1,
	'list'		=> array()
);










return $maps;