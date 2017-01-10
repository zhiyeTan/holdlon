<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<div class="rpm-row">
		<p class="rpm-row-title">系统信息</p>
		<ul class="rpm-row-box">
			<?php
			$gd = gd_info();
			$gd_ver = trim(strtr($gd['GD Version'], array('bundled ('=>'', 'compatible)'=>'')));
			?>
			<li><span>操作系统</span><span class="rpm-span-margin"><?php echo PHP_OS;?></span></li>
			<li><span>WEB服务</span><span class="rpm-span-margin"><?php echo strstr($_SERVER['SERVER_SOFTWARE'], 'Apache') ? 'Apache' : 'Nginx';?></span></li>
			<li><span>PHP版本</span><span class="rpm-span-margin"><?php echo PHP_VERSION;?></span></li>
			<li><span>MySQL版本</span><span class="rpm-span-margin"><?php echo \z\core\Model::init()->version();?></span></li>
			<li><span>GD库版本</span><span class="rpm-span-margin"><?php echo $gd_ver;?></span></li>
			<li><span>JPEG支持</span><span class="rpm-span-margin"><?php echo $gd['JPEG Support'] ? '是' : '否';?></span></li>
			<li><span>GIF支持</span><span class="rpm-span-margin"><?php echo $gd['GIF Create Support'] && $gd['GIF Read Support'] ? '是' : '否';?></span></li>
			<li><span>PNG支持</span><span class="rpm-span-margin"><?php echo $gd['PNG Support'] ? '是' : '否';?></span></li>
			<li><span>安全模式</span><span class="rpm-span-margin"><?php echo ini_get('safe_mode') ? '是' : '否';?></span></li>
			<div class="clear"></div>
		</ul>
	</div>
</div>
<?php self::widget('total'); ?>
<?php self::widget('footer'); ?>