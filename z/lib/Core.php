<?php

namespace z\lib;

/**
 * 核心函数库
 * 
 * 包含所有核心部分用到的函数
 * 需扩展时应注意方法必须为静态
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Core
{
	/**
	 * 检查文件夹是否存在
	 * 
	 * @access  public
	 * @param   string      $dir  目录路径
	 * @param   true/false  $mk   不存在时是否创建目录，默认true
	 * @return  nohting/boolean
	 */
	public static function chkFolder($dir, $mk = true)
	{
		if(!$mk)
		{
			return is_dir($dir);
		}
		if(!is_dir($dir))
		{
			@mkdir($dir);
			@chmod($dir, 0777);
		}
	}
	
	/**
	 * 读取文档内容
	 * 
	 * @access public
	 * @param  path        $fileName     文件路径
	 * @param  true/false  $isSerialize  是否序列化的数据，默认true
	 * @return string/boolean
	 */
	public static function readFile($fileName, $isSerialize = true)
	{
		if(is_file($fileName) && is_readable($fileName))
		{
			$file = fopen($fileName, 'r');
			if(flock($file, LOCK_SH))
			{
				$data = fread($file, filesize($fileName));
				flock($file, LOCK_UN);
				fclose($file);
				return $isSerialize ? unserialize($data) : $data;
			}
			return false;
		}
		return false;
	}
	
	/**
	 * 快速读取文档内容
	 * 针对小文件，大文件请使用readFile方法
	 * 
	 * @access public
	 * @param  path        $fileName     文件路径
	 * @param  true/false  $isSerialize  是否序列化的数据，默认true
	 * @return string/boolean
	 */
	public static function fastReadFile($fileName, $isSerialize = true)
	{
		if(!is_file($fileName) || !is_readable($fileName))
		{
			return false;
		}
		$data = file_get_contents($fileName);
		return $isSerialize ? unserialize($data) : $data;
	}
	
	/**
	 * 写入文档内容
	 * 
	 * @access public
	 * @param  path        $fileName     文件路径
	 * @param  mixed       $data         需要写入的数据
	 * @param  true/false  $isSerialize  是否序列化的数据，默认true
	 * @param  true/false  $isChange     是否变更内容，默认true
	 * @param  true/false  $isCover     是否覆盖原内容（覆盖或追加），默认true
	 * @return boolean
	 */
	public static function writeFile($fileName, $data, $isSerialize = true, $isChange = true, $isCover = true)
	{
		if(!$isChange && is_file($fileName))
		{
			return true;
		}
    	if(is_file($fileName) && !is_writeable($fileName))
    	{
    	    return false;
    	}
		$mode = $isCover ? 'w' : 'ab';
		$file = fopen($fileName, $mode);
		if(flock($file, LOCK_EX))
		{
			fwrite($file, $isSerialize ? serialize($data) : $data);
			flock($file, LOCK_UN);
			fclose($file);
			return true;
		}
		return false;
	}
	
	
	/**
	 * 快速写入文档内容
	 * 针对小文件，大文件请使用writeFile方法
	 * 
	 * @access public
	 * @param  path        $fileName     文件路径
	 * @param  mixed       $data         需要写入的数据
	 * @param  true/false  $isSerialize  是否序列化的数据，默认true
	 * @param  true/false  $isChange     是否变更内容，默认true
	 * @param  true/false  $isCover     是否覆盖原内容（覆盖或追加），默认true
	 * @return boolean
	 */
	public static function fastWriteFile($fileName, $data, $isSerialize = true, $isChange = true, $isCover = true)
	{
		if(!$isChange && is_file($fileName))
		{
			return true;
		}
		if(is_file($fileName) && !is_writeable($fileName))
		{
			return false;
		}
		$mode = $isCover ? LOCK_EX : FILE_APPEND|LOCK_EX;
		if(file_put_contents($fileName, $isSerialize ? serialize($data) : $data, $mode))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 递归处理目录
	 * @access  public
	 * @param   path     $path   目录路径
	 * @param   boolean  $type   处理方式（默认false删除，true获取文档树）
	 * @param   number   $level  文档相对目录的层级
	 * @return  nothing/array
	 */
	public static function recursiveDealDir($path, $type = false, $level = 0)
	{
		$i = 0;
		$res = array();
		$fp = dir($path);
		while(false != ($item = $fp->read()))
		{
			// 跳过.:
			if($item == '.' || $item == '..')
			{
				continue;
			}
			$tmpPath = $fp->path . Z_DS . $item;
			// 这部分是获取文档树用的
			if($type)
			{
				$res[$i] = array(
					'name'	=> $item,
					'path'	=> $tmpPath,
					'type'	=> is_dir($tmpPath),
					'level'	=> $level
				);
				if(is_dir($tmpPath))
				{
					$res[$i]['children'] = Core::recursiveDealDir($tmpPath, $type, $level+1);
				}
				$i++;
			}
			// 这部分是执行删除操作
			else
			{
				if(is_dir($tmpPath))
				{
					@rmdir($tmpPath);
				}
				else
				{
					@unlink($tmpPath);
				}
			}
		}
		return $type ? $res : '';
	}
}