<?php

use z\core\Router as router;

$logoutUrlDate = array(
	'e' => 'admin',
	'm' => 'index',
	'c' => 'index',
	'a' => 'logout'
);
		
return array(
	'account'	=> $_SESSION['account'],
	'logoutUrl'	=> router::create($logoutUrlDate)
);
