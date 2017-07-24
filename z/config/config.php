<?php

/**
 * 系统参数配置
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */

return array(
	
	/**
	 * 路由设置，共有以下4种路由模式：
	 * 0、协议名://主机名/入口名.php?m=模块名称&c=控制器名称&其他参数...
	 * 1、协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value....html
	 * 2、协议名://主机名/模块名称(index时省略)/六位字符串.html
	 * 3、协议名://主机名/入口名/模块名称/控制器名称/key/value/key/value...
	 */
	'route_pattern'			=> 1,
	
	// 调试模式
	'app_debug'				=> true,
	
	// 异步操作的域名(空值表示与当前请求域名一致)
	'async_domain'			=> '',
	
	// 异步操作的端口(空值表示与当前请求端口一致)
	'async_port'			=> '',
	
	// 数据接口入口名
	'api_entry'				=> 'api',
	
	// 数据接口入口对应的应用目录名
	'api_dir'				=> 'api',
	
	// 服务器数据缓存有效期 0表示永久缓存(单位s)
	'data_cache_expire'		=> 43200,
	
	// 服务器静态缓存有效期 0表示永久缓存(单位s)
	'static_cache_expire'	=> 0,
	
	// 客户端本地缓存时间(单位s) 过期前不会重复请求服务器 [使用在header表头中]
	'local_expire'			=> 0,
	
	// 默认时区
	'default_timezone'		=> 'PRC',
	
	// 默认语言
	'default_lang'			=> 'zh-cn',
	
	// 入口文件名与应用位置文件夹名的值对地图
	'entry_maps'			=> array(
								'index'		=> 'app',
								'admin'		=> 'admin'
							),
	
	// 必要的数据库配置
	'server'				=> 'localhost',
	'username'				=> 'root',
	'password'				=> 'root',
	'dbname'				=> 'holon',
	'charset'				=> 'utf8',
	
	// 可选：端口
	'port'					=> 3306,
	
	// 可选：表前缀
	'prefix'				=> 'z_',
	
	// 设置session有效时间(单位s)
	'session_expire'		=> 10800,
	
	// 设置cookie有效时间(单位s)
	'cookie_expire'			=> 10800
	
);
