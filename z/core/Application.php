<?php

namespace z\core;

use z;

class Application
{
	public function run()
	{
		Driver::init()->trigger();
	}
}
