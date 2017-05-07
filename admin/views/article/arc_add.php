<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span><?php echo $data['colName']; ?></span></p>
	<table class="rpm-list-table">
		<tbody>
			<tr>
				<th>文章标题</th>
				<th>文章状态</th>
				<th>操作</th>
			</tr>
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
<?php self::widget('total'); ?>
<?php self::widget('footer'); ?>