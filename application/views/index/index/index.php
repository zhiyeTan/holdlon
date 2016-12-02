<!DOCTYPE HTML>
<html>
	<head>
		<title>index</title>
	</head>
	<body>
		<?php z\core\Widget::init()->output('header'); ?>
		<?php
		foreach($data as $v)
		{
			echo $v, '<br/>';
		}
		?>
	</body>
</html>
