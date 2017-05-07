<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span><?php echo $data['colName']; ?></span></p>
	<form enctype="multipart/form-data" method="post">
		<div class="rpm-from-row-box">
			<p class="rpm-form-row-title">基本信息</p>
			<ul class="rpm-form-box clearfix">
				<li>
					<label><i>*</i>分类名称</label>
					<input type="text" name="form[name]" value="<?php echo isset($data['name']) ? $data['name'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>封面图片</label>
					<input class="rpm-file-input" type="file" name="form[img_url]" value="">
					<?php if(isset($data['imgurl'])){ ?>
					<a href="<?php echo $data['imgurl']; ?>" target="_blank"><i class="dashicons dashicons-icon-yes"></i></a>
					<?php }else{ ?>
					<i class="dashicons dashicons-icon-no"></i>
					<?php } ?>
				</li>
				<li class="rpm-form-row-full">
					<label for="parent_id">所属分类</label>
					<select name="parent_id" id="parent_id">
						<?php foreach($data['category'] as $k => $v){ ?>
						<option value="0">顶级分类</option>
						<?php } ?>
					</select>
				</li>
			</ul>
		</div>
		
		<div class="rpm-from-row-box">
			<p class="rpm-form-row-title">推广设置</p>
			<ul class="rpm-form-box clearfix">
				<li class="rpm-form-row-full">
					<label>网页标题</label>
					<input class="rpm-longinput" type="text" name="form[seotitle]" value="<?php echo isset($data['seotitle']) ? $data['seotitle'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>网页关键词</label>
					<input class="rpm-longinput" type="text" name="form[keywords]" value="<?php echo isset($data['keywords']) ? $data['keywords'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>网页描述</label>
					<input class="rpm-longinput" type="text" name="form[description]" value="<?php echo isset($data['description']) ? $data['description'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>排序</label>
					<input type="text" name="form[sort]" value="<?php echo isset($data['sort']) ? $data['sort'] : 0; ?>" size="4">
				</li>
				<li class="rpm-form-row-full">
					<label>是否显示</label>
					<input type="checkbox" name="form[static]" value="1"<?php if(isset($data['static']) && $data['static'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
			</ul>
		</div>
		
		<div class="rpm-from-row-box">
			<p class="rpm-form-row-title">栏目内容</p>
			<!-- 加载编辑器的容器 -->
		    <textarea id="content" name="form[content]"><?php echo isset($data['content']) ? $data['content'] : ''; ?></textarea>
		    <!-- 配置文件 -->
		    <script type="text/javascript" src="includes/htmleditor/ueditor.config.js"></script>
		    <!-- 编辑器源码文件 -->
		    <script type="text/javascript" src="includes/htmleditor/ueditor.all.js"></script>
		    <!-- 实例化编辑器 -->
		    <script type="text/javascript">
		    $(function(){
		        var ue = UE.getEditor('content');
		    });
		    </script>
		</div>
		
		
		<p>&nbsp;</p>
		<p class="rpm-from-row-btn-box">
			<input type="hidden" name="form[token]" value="<?php echo $data['token']; ?>">
			<input class="rpm-form-btn-submit" type="submit" value="确定">
			<input class="rpm-form-btn-reset" type="reset" value="重置">
		</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		
	</form>
</div>
<?php self::widget('total'); ?>
<?php self::widget('footer'); ?>