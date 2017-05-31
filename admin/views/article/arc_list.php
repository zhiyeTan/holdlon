<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span><?php echo $data['colName']; ?></span></p>
	<table class="rpm-list-table">
		<tbody>
			<tr>
				<th>文章标题</th>
				<th>所属分类</th>
				<th>文章状态</th>
				<th>操作</th>
			</tr>
			<?php foreach($data['list'] as $v) { ?>
			<tr class="rpm-table-single-row">
				<td style="text-align: left;"><?php echo $v['title']; ?></td>
				<td><?php echo $v['name']; ?></td>
				<td><?php echo $v['status']; ?></td>
				<td class="rpm-text-align-left">
					<a class="rpm-handle_btn" href="<?php echo $v['urlEdit']; ?>" title="修改"><i class="iconfont icon-handle-btn-edt"></i></a>
					<a class="rpm-handle_btn" href="<?php echo $v['urlDelete']; ?>" title="删除"><i class="iconfont icon-handle-btn-del"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php self::widget('total'); ?>
<?php self::widget('footer'); ?>