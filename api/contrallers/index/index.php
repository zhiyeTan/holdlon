<?php

namespace contrallers\index;

class index
{
	public static function index()
	{
		echo serialize(array(0,1,2));
	}
}
