<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span><?php echo $data['colName']; ?></span></p>
	<table class="rpm-list-table">
		<tbody>
			<tr>
				<th>分类名称</th>
				<th>状态</th>
				<th>排序</th>
				<th style="text-indent: ;">操作</th>
			</tr>
			<?php echo $data['category']; ?>
		</tbody>
	</table>
</div>
<?php self::widget('total'); ?>
<?php self::widget('footer'); ?>