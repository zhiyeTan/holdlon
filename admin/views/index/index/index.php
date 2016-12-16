<?php
$widget = z\core\widget::init();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>信息管理平台</title>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		<link rel="stylesheet" type="text/css" href="css/iconfont.css"/>
		<script type="text/javascript" src="js/jquery-1.10.2.min.js" ></script>
		<script type="text/javascript" src="js/base.js" ></script>
	</head>
	<body>
		<?php $widget->output('header'); ?>
		<?php $widget->output('sidebar'); ?>
		<div id="rpm-content">
			<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span>{$sidebar.page_name}</span></p>
			<table class="rpm-list-table">
				<tbody>
					<tr>
						<th>{$lang.a_title}</th>
						<th>{$lang.a_cat_id}</th>
						<th>{$lang.handle}</th>
					</tr>
					<?php print_r($data); ?>
					<tr class="rpm-table-single-row">
						<td>{$l.cat_name}</td>
						<td><i class="dashicons dashicons-icon-{if $l.is_hot}yes{else}no{/if}"></i></td>
						<td class="rpm-text-align-left">
							<a class="rpm-handle_btn" href="#" title="删除"><i class="iconfont icon-handle-btn-del"></i></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php $widget->output('total'); ?>
	</body>
</html>