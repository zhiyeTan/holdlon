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
	 * 创建标签
	 * @param: string $label 标签名
	 * @param: array $data 属性值对数组（content为非空标签的内容）
	 */
	public function create($label, $data = null)
	{
		$str = '<' . $label;
		if(is_array($data))
		{
			foreach($data as $k => $v)
			{
				if($k !== 'content')
				{
					$str .= ' ' . $k . '="' . (is_array($v) ? implode(' ', $v) : $v) . '"';
				}
			}
		}
		$str .= in_array($label, $this->nullLabel) && $this->isXhtml ? '/>' : '>';
		if(!in_array($label, $this->nullLabel))
		{
			if(isset($data['content']))
			{
				$str .= $data['content'];
			}
			$str .= '</' . $label . '>';
		}
		return $str;
	}
	
	// TODO 嵌套与循环，循环中的嵌套
	/**
	 * 输出结构
	 * @param: array $nests 标签嵌套的数组（）
	 * @param: 
	 */
	public function output($nests, $cycle)
	{
		
	}
}
