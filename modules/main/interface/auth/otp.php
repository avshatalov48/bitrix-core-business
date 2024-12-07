<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$store_password = (COption::GetOptionString('security', 'otp_allow_remember') === 'Y');
$bNeedCaptcha = (CModule::IncludeModule("security") && \Bitrix\Security\Mfa\Otp::isCaptchaRequired());
?>

<div class="login-main-popup-wrap login-popup-wrap<?=$bNeedCaptcha?" login-captcha-popup-wrap" : ""?>" id="otp">
	<input type="hidden" name="TYPE" value="OTP">
	<div class="login-popup">
		<div class="login-popup-title"><?=GetMessage('AUTH_TITLE')?></div>
		<div class="login-popup-title-description"><?=GetMessage("AUTH_PLEASE_AUTH")?></div>
		<div class="login-popup-field">
			<div class="login-popup-field-title"><?=GetMessage("AUTH_OTP_PASS")?></div>
			<div class="login-input-wrap">
				<input type="text" class="login-input" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="USER_OTP" value="" tabindex="1" autocomplete="off">
				<div class="login-inp-border"></div>
			</div>
			<input type="submit" value="" class="login-btn-green" name="Login" tabindex="5" onfocus="BX.addClass(this, 'login-btn-green-hover');" onblur="BX.removeClass(this, 'login-btn-green-hover')">
			<div class="login-loading">
				<img class="login-waiter" alt="" src="/bitrix/panel/main/images/login-waiter.gif">
			</div>
		</div>
<?
if($store_password):
?>
		<div class="login-popup-checbox-block">
			<input type="checkbox" class="adm-designed-checkbox" id="OTP_REMEMBER" name="OTP_REMEMBER" value="Y" tabindex="3" onfocus="BX.addClass(this.nextSibling, 'login-popup-checkbox-label-active')" onblur="BX.removeClass(this.nextSibling, 'login-popup-checkbox-label-active')"><label for="OTP_REMEMBER" class="adm-designed-checkbox-label"></label>
			<label for="OTP_REMEMBER" class="login-popup-checkbox-label"><?=GetMessage("AUTH_OTP_REMEMBER_ME")?></label>
		</div>
<?
endif;

$CAPTCHA_CODE = '';
if($bNeedCaptcha)
	$CAPTCHA_CODE = $APPLICATION->CaptchaGetCode();

?>
		<input type="hidden" name="captcha_sid" value="<?=$CAPTCHA_CODE?>" />
		<div class="login-popup-field login-captcha-field">
			<div class="login-popup-field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></div>
			<div class="login-input-wrap">
				<span class="login-captcha-wrap" id="captcha_image"><?if($bNeedCaptcha):?><img src="/bitrix/tools/captcha.php?captcha_sid=<?=$CAPTCHA_CODE?>" width="180" height="40" alt="CAPTCHA" /><?endif;?></span><input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="captcha_word" class="login-input" tabindex="4" autocomplete="off">
				<div class="login-inp-border"></div>
			</div>
		</div>
<?
if($not_show_links!="Y"):
?>
		<a class="login-popup-link login-popup-forget-pas" href="javascript:void(0)" onclick="BX.adminLogin.toggleAuthForm('authorize')"><?=GetMessage("AUTH_GOTO_AUTH_FORM_1")?></a>
<?
endif;
?>
	</div>
</div>
<script>
BX.adminLogin.registerForm(new BX.authFormOtp('otp', {url: '<?echo CUtil::JSEscape($authUrl.(($s=DeleteParam(array("logout", "login"))) == ""? "":"?".$s));?>'}));
</script>
