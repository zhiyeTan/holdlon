<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>信息管理平台</title>
		<style>
			html,body{
				height: 100%;
				overflow: hidden;
			}
		</style>
	</head>
	<body>
		<div style="display: table;width: 100%;height: 100%;">
			<div style="display: table-cell;vertical-align: middle;">
				<div style="margin-top: 40%;">
				<div style="width: 300px; margin: -50% auto 0 auto;">
					<form id="adm_form" <?php echo 'action="'.$data['enterUrl'].'"' ?>>
						<p><label>账号：</label><input type="text" name="form[admin_id]" value=""/></p>
						<p><label>密码：</label><input type="password" name="form[admin_pwd]" value=""/></p>
						<p>
							<input type="hidden" name="form[token]" <?php echo 'value="' . $data['token'] . '"' ?> />
							<input type="button" value="确定" class="login_btn_sub" onclick="check_login();" />
						</p>
					</form>
				</div>
				</div>
			</div>
		</div>
	</body>
</html>