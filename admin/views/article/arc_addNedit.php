<?php self::widget('header'); ?>
<?php self::widget('sidebar'); ?>
<div id="rpm-content">
	<p class="rpm-stair-page-title"><i class="dashicons dashicons-page-crumbs"></i><span><?php echo $data['colName']; ?></span></p>
	<form enctype="multipart/form-data" method="post">
		<div class="rpm-from-row-box">
			<p class="rpm-form-row-title">基本信息</p>
			<ul class="rpm-form-box clearfix">
				<li>
					<label><i>*</i>文章标题</label>
					<input type="text" name="form[title]" value="<?php echo isset($data['title']) ? $data['title'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>简略标题</label>
					<input type="text" name="form[brief_title]" value="<?php echo isset($data['brief_title']) ? $data['brief_title'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>来源</label>
					<input type="text" name="form[source]" value="<?php echo isset($data['source']) ? $data['source'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>作者</label>
					<input type="text" name="form[author]" value="<?php echo isset($data['author']) ? $data['author'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>封面图片</label>
					<input class="rpm-file-input" type="file" name="imgurl" value="">
					<?php if(isset($data['imgurl']) && $data['imgurl']){ ?>
					<a href="<?php echo $data['imgurl']; ?>" target="_blank"><i class="dashicons dashicons-icon-yes"></i></a>
					<?php }else{ ?>
					<i class="dashicons dashicons-icon-no"></i>
					<?php } ?>
				</li>
				<li class="rpm-form-row-full">
					<label><i>*</i>所属分类</label>
					<select name="form[cat_id]">
						<?php echo $data['category']; ?>
					</select>
				</li>
				<li>
					<label>允许评论</label>
					<input type="checkbox" name="form[comment]" value="1"<?php if(isset($data['comment']) && $data['comment'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
			</ul>
		</div>
		
		<div class="rpm-from-row-box">
			<p class="rpm-form-row-title">推广设置</p>
			<ul class="rpm-form-box clearfix">
				<li class="rpm-form-row-full">
					<label>内容摘要</label>
					<input class="rpm-longinput" type="text" name="form[abstract]" value="<?php echo isset($data['abstract']) ? $data['abstract'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>网页关键词</label>
					<input class="rpm-longinput" type="text" name="form[keywords]" value="<?php echo isset($data['keywords']) ? $data['keywords'] : ''; ?>">
				</li>
				<li class="rpm-form-row-full">
					<label>网页描述</label>
					<input class="rpm-longinput" type="text" name="form[description]" value="<?php echo isset($data['description']) ? $data['description'] : ''; ?>">
				</li>
				<li>
					<label>是否显示</label>
					<input type="checkbox" name="form[status]" value="1"<?php if(isset($data['status']) && $data['status'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
				<li>
					<label>是否最新</label>
					<input type="checkbox" name="form[is_new]" value="1"<?php if(isset($data['is_new']) && $data['is_new'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
				<li>
					<label>是否热门</label>
					<input type="checkbox" name="form[is_hot]" value="1"<?php if(isset($data['is_hot']) && $data['is_hot'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
				<li>
					<label>是否置顶</label>
					<input type="checkbox" name="form[is_top]" value="1"<?php if(isset($data['is_top']) && $data['is_top'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
				<li>
					<label>是否推荐</label>
					<input type="checkbox" name="form[is_push]" value="1"<?php if(isset($data['is_push']) && $data['is_push'] == 1){ ?> checked="checked"<?php } ?>>
				</li>
				<li>
					<label>是否精华</label>
					<input type="checkbox" name="form[is_best]" value="1"<?php if(isset($data['is_best']) && $data['is_best'] == 1){ ?> checked="checked"<?php } ?>>
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