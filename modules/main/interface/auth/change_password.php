<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var string $last_login
 * @var string $authUrl
 */

$bNeedCaptcha = (COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y");
?>

<div id="change_password" class="login-popup-wrap login-popup-replace-wrap">
	<input type="hidden" name="TYPE" value="CHANGE_PWD">
	<div class="login-popup">
		<div class="login-popup-title"><?=GetMessage("AUTH_CHANGE_PASSWORD")?></div>
		<div class="login-popup-title-description"><?=GetMessage('AUTH_CHANGE_PASSWORD_1')?></div>
		<div class="login-popup-replace-fields-wrap" id="change_password_fields">
			<div class="login-popup-field">
				<div class="login-popup-field-title"><?=GetMessage("AUTH_LOGIN")?></div>
				<div class="login-input-wrap">
					<input type="email" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input" name="USER_LOGIN" value="<?echo htmlspecialcharsbx($last_login)?>">
					<div class="login-inp-border"></div>
				</div>
			</div>
			<div class="login-popup-field">
				<div class="login-popup-field-title"><?=GetMessage("AUTH_CHECKWORD")?></div>
				<div class="login-input-wrap">
					<input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input" name="USER_CHECKWORD" value="">
					<div class="login-inp-border"></div>
				</div>
			</div>
			<div class="login-popup-field login-replace-field">
				<div class="login-popup-field-title"><span class="login-replace-title"><?=GetMessage("AUTH_NEW_PASSWORD")?></span><span class="login-replace-title"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></span></div>
				<div class="login-input-wrap">
					<input type="password" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input" name="USER_PASSWORD"><input type="password" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" class="login-input" name="USER_CONFIRM_PASSWORD">
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
		<a href="javascript:void(0)" onclick="toggleAuthForm('forgot_password')" style="display: none;" id="change_password_forgot_link" class="login-popup-forget-pas"><?echo GetMessage("AUTH_GOTO_FORGOT_FORM")?></a>
		<div class="login-btn-wrap" id="change_password_button"><a class="login-popup-link login-popup-return-auth" href="javascript:void(0)" onclick="BX.adminLogin.toggleAuthForm('authorize')"><?=GetMessage('AUTH_GOTO_AUTH_FORM_1')?></a><input type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" class="login-btn"></div>
	</div>
</div>

<script type="text/javascript">
BX.message({
	'AUTH_NEW_PASSWORD_CONFIRM_WRONG':'<?=GetMessageJS('AUTH_NEW_PASSWORD_CONFIRM_WRONG')?>'
});

var obChangeMsg = new BX.authFormChangePasswordMessage('change_password_message', {url:''}),
	obChange = new BX.authFormChangePassword('change_password', {
		url: '<?echo CUtil::JSEscape($authUrl."?change_password=yes".(($s=DeleteParam(array("change_password"))) == ""? "":"&".$s))?>',
		needCaptcha: <?=$bNeedCaptcha?'true':'false'?>,
		message: obChangeMsg
});
BX.adminLogin.registerForm(obChange);
BX.adminLogin.registerForm(obChangeMsg);
</script>
