<div id="rpm-sidebar">
	<p class="rpm-sidebar-title"><i class="iconfont">&#xe694;</i></p>
	<ul>
		<?php
		$currName = '';
		foreach($widgetData['sidebar'] as $k => $v){
		?>
			<li><a href="javascript:;"><h2><i class="dashicons dashicons-menu-<?php echo $k; ?>"></i><?php echo $v['name']; ?></h2></a></li>
			<?php
			if($v['module'] == $_GET['m'])
			{
				$currName = $v['name'];
			}
			?>
		<?php } ?>
	</ul>
</div>
<div id="rpm-sidebar-stair"<?php if(!$currName){ ?> style="display: none;"<?php } ?>>
	<p id="rpm-sidebar-stair-title"><?php if($currName){ ?>文章管理<?php } ?></p>
	<?php foreach($widgetData['sidebar'] as $k => $v){ ?>
	<ul<?php if($v['module'] != $_GET['m']){ ?> style="display: none;"<?php } ?>>
		<?php
		foreach($v['list'] as $kk => $vv){
		if($vv['display']){
		?>
		<li><a<?php if($_GET['a'] == $kk){ ?> class="on-sidebar-seconda"<?php } ?> href="<?php echo $vv['url']; ?>"><?php echo $vv['name']; ?></a></li>
		<?php }} ?>
	</ul>
	<?php } ?>
</div>