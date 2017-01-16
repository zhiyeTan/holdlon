<?php

namespace z\core;

class Html
{
	// 是否XHTML文档类型
	private $isXhtml = false;
	// 空标签集
	private $nullLabel = array('area', 'base', 'br', 'col', 'colgroup', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr');
	
	
	/**
	 * 注释
	 * @param: string $str 内容
	 */
	public function annotation($str)
	{
		echo '<!--' . $str . '-->' . PHP_EOL;
		return $this;
	}
	
	/**
	 * DOCTYPE 声明
	 * @param: string $type 类型（5、4s、4t、4f、1s、1t、1f、1.1）
	 */
	public function docType($type)
	{
		switch(strtolower($type))
		{
			// html 4.01 strict
			case '4s':
				$dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				break;
			// html 4.01 transitional
			case '4t':
				$dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				break;
			// html 4.01 frameset
			case '4f':
				$dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
				break;
			// xhtml 1.0 strict
			case '1s':
				$this->isXhtml = true;
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				break;
			// xhtml 1.0 transitional
			case '1t':
				$this->isXhtml = true;
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				break;
			// xhtml 1.0 frameset
			case '1f':
				$this->isXhtml = true;
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				break;
			// xhtml 1.1
			case '1.1':
				$this->isXhtml = true;
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				break;
			// html 5
			default:
				$dtd = '<!DOCTYPE html>';
		}
		echo $dtd . PHP_EOL;
		return $this;
	}
	
	/**
	 * 生成指定节点结构
	 * 
	 */
	public function createNode($structure)
	{
		$sKeys = array('label', 'content', 'children');
		$str = '';
		if(!is_array($structure))
		{
			return ;
		}
		// 判断是否索引数组
		$keys = array_keys($structure);
		if($keys != array_keys($keys))
		{
			// 修正关联数组为索引数组
			$structure = array($structure);
		}
		// 遍历结构
		// $structure = array(0 => array('label'=>'li', 'content'=>'', 'children'=>array()))
		foreach($structure as $v)
		{
			if(!empty($v['label']))
			{
				$str .= '<' . $v['label'];
				// 构造标签属性
				foreach($v as $kk => $vv)
				{
					if(!in_array($kk, $sKeys))
					{
						$str .= ' ' . $kk . '="' . (is_array($vv) ? implode(' ', $vv) : $vv) . '"';
					}
				}
				// 构造完整的标签（或起始标签）
				$str .= in_array($v['label'], $this->nullLabel) && $this->isXhtml ? '/>' : '>';
				// 如果存在子结构，则递归得到结构
				if(!empty($v['children']))
				{
					$str .= $this->createNode($v['children']);
				}
				// 如果是非空标签，为标签添加内容，并追加结束标签
				if(!in_array($v['label'], $this->nullLabel))
				{
					if(isset($v['content']))
					{
						$str .= $v['content'];
					}
					$str .= '</' . $v['label'] . '>';
				}
			}
		}
		return $str;
	}
	
	/**
	 * 生成循环体
	 */
	public function cycleNode($data, $structure)
	{
		$str = '';
		$node = $this->createNode($structure);
		foreach($data as $v)
		{
			preg_match_all('/\$(.*?)\$/', $node, $res);
			$tmp = array();
			foreach($res[0] as $rk => $rv)
			{
				$tmp[$rv] = isset($v[$res[1][$rk]]) ? $v[$res[1][$rk]] : '';
			}
			$str .= strtr($node, $tmp);
		}
		return $str;
	}
	
	
}
