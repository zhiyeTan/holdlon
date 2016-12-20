<?php

namespace models\index;
use \z\basic\admin as admin;

class index
{
	public function index()
	{
		$data = array();
		$data = admin::R();
		return $data;
	}
	public function login()
	{
		
	}
}
