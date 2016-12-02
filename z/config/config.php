<?php

return array(
	
	/**
	 * 是否开启多点部署
	 * @param true 禁用
	 *        单点部署，多个入口。
	 *        如：部署点为entry，对应的应用资源为application，入口可能有index.php，admin.php等
	 *        并且静态资源均保存在部署点entry上(css、js、images、uploads、可download的资源等)
	 * 
	 * @param false 启用
	 *        多点部署，单一入口。
	 *        如：部署点可能有web、wap、api、admin、static等
	 *        必须同时配置static_host，以便于将来做静态cdn加速
	 */
	'multipoint_enable'	=> false,
	
	// 静态资源目录(单点部署时无效)
	'static_path'		=> 'z_static',
	
	// 静态资源的域名前缀(单点部署时无效)
	'static_domain'		=> 'static.z.com',
	
	/**
	 * 路由设置，共有以下三种路由模式：
	 * 0、协议名://主机名/入口文件.php?m=模块名称&c=控制器名称&a=操作名称&其他参数...
	 * 1、协议名://主机名/入口文件/模块名称/控制器名称/操作名称/key/value/key/value...
	 * 2、协议名://主机名/由md5(query_string)后截取得到的长度为6的字符串/页码
	 */
	'route_pattern'		=> 2,
	
	// 调试模式
	'app_debug'			=> true,
	
	// 缓存有效期 0表示永久缓存(单位s)
	'cache_expire'		=> 0,
	
	// 客户端本地缓存时间(单位s) 过期前不会重复请求服务器 [使用在header表头中]
	'local_expire'		=> 0,
	
	// 默认时区
	'default_timezone'	=> 'PRC',
	
	// 默认语言
	'default_lang'		=> 'zh-cn',
	
	// 入口文件名与应用位置文件夹名的值对地图
	'entry_maps'		=> array(
									'index'		=> 'application',
									'api'		=> 'api'
								),
	
	// 必要的数据库配置
	'database_type'		=> 'mysql',
	'database_name'		=> 'holon',
	'server'			=> 'localhost',
	'username'			=> 'root',
	'password'			=> 'root',
	'charset'			=> 'utf8',
	
	//可选：端口
	'port'				=> 3306,
	
	//可选：表前缀
	'prefix'			=> 'z_',
	
	//可选：pdo驱动选项 http://www.php.net/manual/en/pdo.setattribute.php
	'option'			=> array(PDO::ATTR_CASE => PDO::CASE_NATURAL)
	
);
