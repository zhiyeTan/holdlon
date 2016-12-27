<?php

namespace z\core;

use z\core\Session as session;

class Validate
{
	
	/**
	 * 验证字段值是否为有效格式
	 * @access protected
	 * @param string    $rule   验证规则
	 * @param mixed     $value  字段值
	 * @return bool
	 */
	public static function is($rule, $value = null)
	{
		switch ($rule)
		{
			case 'require':
				// 必须
				$result = !empty($value) || '0' == $value;
				break;
			case 'accepted':
				// 接受
				$result = in_array($value, array('1', 'on', 'yes'));
				break;
			case 'date':
				// 是否是一个有效日期
				$result = false !== strtotime($value);
				break;
			case 'alpha':
				// 只允许字母
				$result = self::regex($value, '/^[A-Za-z]+$/');
				break;
			case 'alphaNum':
				// 只允许字母和数字
				$result = self::regex($value, '/^[A-Za-z0-9]+$/');
				break;
			case 'alphaDash':
				// 只允许字母、数字和下划线 破折号
				$result = self::regex($value, '/^[A-Za-z0-9\-\_]+$/');
				break;
			case 'chs':
				// 只允许汉字
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}]+$/u');
				break;
			case 'chsAlpha':
				// 只允许汉字、字母
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u');
				break;
			case 'chsAlphaNum':
				// 只允许汉字、字母和数字
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u');
				break;
			case 'chsDash':
				// 只允许汉字、字母、数字和下划线_及破折号-
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u');
				break;
			case 'ip':
				// 是否为IP地址
				$result = self::filter($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
				break;
			case 'url':
				// 是否为一个URL地址
				$result = self::filter($value, FILTER_VALIDATE_URL);
				break;
			case 'float':
				// 是否为float
				$result = self::filter($value, FILTER_VALIDATE_FLOAT);
				break;
			case 'number':
				$result = is_numeric($value);
				break;
			case 'integer':
				// 是否为整型
				$result = self::filter($value, FILTER_VALIDATE_INT);
				break;
			case 'email':
				// 是否为邮箱地址
				$result = self::filter($value, FILTER_VALIDATE_EMAIL);
				break;
			case 'boolean':
				// 是否为布尔值
				$result = in_array($value, array(0, 1, true, false));
				break;
			case 'token':
				$result = self::token();
				break;
			case 'form':
				// 是否标准表单
				$result = self::form();
				break;
			case 'formToken':
				// 表单是否包含令牌
				$result = self::hasToken();
				break;
			default:
				// 正则验证
				$result = self::regex($value, $rule);
		}
		return $result;
	}

	/**
	 * 使用正则验证数据
	 * @access protected
	 * @param mixed     $value  字段值
	 * @param mixed     $rule  验证规则 正则规则或者预定义正则名
	 * @return mixed
	 */
	protected static function regex($value, $rule)
	{
		if(0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule))
		{
			// 不是正则表达式则两端补上/
			$rule = '/^' . $rule . '$/';
		}
		return 1 === preg_match($rule, (string) $value);
	}
	
	/**
	 * 使用filter_var方式验证
	 * @access protected
	 * @param mixed     $value  字段值
	 * @param mixed     $rule  验证规则
	 * @return bool
	 */
	protected static function filter($value, $rule)
	{
		if(is_string($rule) && strpos($rule, ','))
		{
			list($rule, $param) = explode(',', $rule);
		}
		elseif (is_array($rule))
		{
			$param = isset($rule[1]) ? $rule[1] : null;
		}
		else
		{
			$param = null;
		}
		return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
	}
	
	// 验证表单令牌
	protected static function token()
	{
		$token = session::get('__token__');
		if(!$token)
		{
			// 令牌无效
			return false;
		}
		if($token === $_POST['form']['token'])
		{
			// 验证完成即销毁，防止重复提交
			session::delete('__token__');
			return true;
		}
		// 重置令牌
		session::delete('__token__');
		return false;
	}
	
	// 验证是否标准表单提交
	protected static function form()
	{
		if(isset($_POST['form']))
		{
			return true;
		}
		return false;
	}
	
	// 验证表单是否包含令牌
	protected static function hasToken()
	{
		if(isset($_POST['form']['token']))
		{
			return true;
		}
		return false;
	}
}
