<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$bNeedCaptcha = (COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y");
?>

<div id="forgot_password" class="login-popup-wrap-with-text">
	<div class="login-popup-wrap login-popup-request-wrap">
		<input type="hidden" name="TYPE" value="SEND_PWD">
		<div class="login-popup">
			<div class="login-popup-title"><?=GetMessage('AUTH_FORGOT_PASSWORD')?></div>
			<div class="login-popup-title-description"><?=GetMessage("AUTH_GET_CHECK_STRING")?></div>
			<div class="login-popup-request-fields-wrap" id="forgot_password_fields">
				<div class="login-popup-field">
					<div class="login-popup-field-title"><?=GetMessage("AUTH_LOGIN")?></div>
					<div class="login-input-wrap">
						<input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input"  name="USER_LOGIN" value="<?echo htmlspecialcharsbx($last_login)?>">
						<div class="login-inp-border"></div>
					</div>
				</div>
				<div class="login-popup-either"><?=GetMessage("AUTH_OR")?></div>
				<div class="login-popup-field">
					<div class="login-popup-field-title">E-mail</div>
					<div class="login-input-wrap">
						<input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input" name="USER_EMAIL">
						<div class="login-inp-border"></div>
					</div>
				</div>
				<input type="hidden" name="captcha_sid" value="" />
				<div class="login-popup-field login-captcha-field">
					<div class="login-popup-field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></div>
					<div class="login-input-wrap">
						<span class="login-captcha-wrap" id="captcha_image"></span><input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="captcha_word" class="login-input" tabindex="5" autocomplete="off">
						<div class="login-inp-border"></div>
					</div>
				</div>
			</div>
			<div class="login-btn-wrap" id="forgot_password_message_button"><a class="login-popup-link login-popup-return-auth" href="javascript:void(0)" onclick="BX.adminLogin.toggleAuthForm('authorize')"><?=GetMessage('AUTH_GOTO_AUTH_FORM_1')?></a><input type="submit" value="<?=GetMessage("AUTH_SEND")?>" class="login-btn" name="send_account_info"></div>
		</div>
	</div>
	<div class="login-popup-request-text" id="forgot_password_note">
		<?=GetMessage("AUTH_FORGOT_PASSWORD_1")?><br>
	</div>
</div>

<script>
var obForgMsg = new BX.authFormForgotPasswordMessage('forgot_password_message', {url:''}),
	obForg = new BX.authFormForgotPassword('forgot_password', {
		url: '<?echo CUtil::JSEscape($authUrl."?forgot_password=yes".(($s=DeleteParam(array("forgot_password"))) == ""? "":"&".$s))?>',
		needCaptcha: <?=$bNeedCaptcha?'true':'false'?>,
		message: obForgMsg
});
BX.adminLogin.registerForm(obForg);
BX.adminLogin.registerForm(obForgMsg);
</script>
