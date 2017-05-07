<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>信息管理平台</title>
		<base href="/" />
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		<link rel="stylesheet" type="text/css" href="css/iconfont.css"/>
		<script type="text/javascript" src="js/jquery-1.10.2.min.js" ></script>
		<script type="text/javascript" src="js/base.js" ></script>
	</head>
	<body>
		<div id="rpm-header">
			<h1><i>HOLDLON</i><span>信息管理中心</span></h1>
			<div class="rpm-info">
				<span>您好，</span><span><?php echo $widgetData['header']['account']; ?></span><span>！</span>
				<a class="rpm-btn-logout" href="<?php echo $widgetData['header']['logoutUrl']; ?>">安全退出</a>
			</div>
			<div class="clear"></div>
		</div>