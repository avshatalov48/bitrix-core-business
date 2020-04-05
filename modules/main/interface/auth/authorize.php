<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$store_password = COption::GetOptionString("main", "store_password", "Y");
$bNeedCaptcha = $APPLICATION->NeedCAPTHAForLogin($last_login);

$authLink = false;
if(
	\Bitrix\Main\Loader::includeModule("socialservices")
	&& class_exists("Bitrix\\Socialservices\\Network") // just to check if socserv update installed
	&& method_exists("Bitrix\\Socialservices\\Network", "displayAdminPopup")
)
{
	$authLink = \Bitrix\Socialservices\Network::getAuthUrl("popup", array("admin"));
}
?>

<div class="login-main-popup-wrap login-popup-wrap<?=$bNeedCaptcha?" login-captcha-popup-wrap" : ""?>" id="authorize">
	<input type="hidden" name="TYPE" value="AUTH">
	<div class="login-popup">
		<div class="login-popup-title"><?=GetMessage('AUTH_TITLE')?></div>
		<div class="login-popup-title-description"><?=GetMessage("AUTH_PLEASE_AUTH")?></div>

		<div class="login-popup-field">
			<div class="login-popup-field-title"><?=GetMessage("AUTH_LOGIN")?></div>
			<div class="login-input-wrap">
				<input type="text" class="login-input" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="USER_LOGIN" value="<?echo htmlspecialcharsbx($last_login)?>" tabindex="1">
				<div class="login-inp-border"></div>
			</div>
		</div>
		<div class="login-popup-field" id="authorize_password">
			<div class="login-popup-field-title"><?=GetMessage("AUTH_PASSWORD")?></div>
			<div class="login-input-wrap">
				<input type="password" class="login-input" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="USER_PASSWORD" tabindex="2">
				<div class="login-inp-border"></div>
			</div>
			<input type="submit" value="" class="login-btn-green" name="Login" tabindex="4" onfocus="BX.addClass(this, 'login-btn-green-hover');" onblur="BX.removeClass(this, 'login-btn-green-hover')">
			<div class="login-loading">
				<img class="login-waiter" alt="" src="/bitrix/panel/main/images/login-waiter.gif">
			</div>
		</div>
<?
$CAPTCHA_CODE = '';
if($bNeedCaptcha)
{
	$CAPTCHA_CODE = $APPLICATION->CaptchaGetCode();
}

?>
		<input type="hidden" name="captcha_sid" value="<?=$CAPTCHA_CODE?>" />
		<div class="login-popup-field login-captcha-field">
			<div class="login-popup-field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></div>
			<div class="login-input-wrap">
				<span class="login-captcha-wrap" id="captcha_image"><?if($bNeedCaptcha):?><img src="/bitrix/tools/captcha.php?captcha_sid=<?=$CAPTCHA_CODE?>" width="180" height="40" alt="CAPTCHA" /><?endif;?></span><input type="text" onfocus="BX.addClass(this.parentNode, 'login-input-active')" onblur="BX.removeClass(this.parentNode, 'login-input-active')" name="captcha_word" class="login-input" tabindex="5" autocomplete="off">
				<div class="login-inp-border"></div>
			</div>
		</div>
<?

if($store_password=="Y"):
	?>
	<div class="login-popup-checbox-block">
		<input type="checkbox" class="adm-designed-checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" tabindex="3" onfocus="BX.addClass(this.nextSibling, 'login-popup-checkbox-label-active')" onblur="BX.removeClass(this.nextSibling, 'login-popup-checkbox-label-active')"><label for="USER_REMEMBER" class="adm-designed-checkbox-label"></label>
		<label for="USER_REMEMBER" class="login-popup-checkbox-label"><?=GetMessage("AUTH_REMEMBER_ME")?></label>
	</div>
	<?
endif;

if($not_show_links!="Y"):
?>
		<a class="login-popup-link login-popup-forget-pas" href="javascript:void(0)" onclick="BX.adminLogin.toggleAuthForm('forgot_password')"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a>
<?
endif;
?>

<?
if($authLink):
	$lang = LANGUAGE_ID == 'ua'
		? LANGUAGE_ID :
		\Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
?>
		<div class="login-popup-field login-auth-serv-icons">
			<a href="" class="login-ss-button bitrix24net-button bitrix24net-button-ru"></a>
		</div>
	<div class="login-popup-network-block">
		<span class="login-popup-network-label"><?=GetMessage("AUTH_NW_SECTION")?></span>
		<span class="login-popup-network-btn login-popup-network-btn-<?=$lang?>" onclick="BX.util.popup('<?=CUtil::JSEscape($authLink)?>', 800, 600);"></span>
	</div>
<?
endif;
?>

	</div>
</div>
<script type="text/javascript">
BX.adminLogin.registerForm(new BX.authFormAuthorize('authorize', {url: '<?echo CUtil::JSEscape($authUrl."?login=yes".(($s=DeleteParam(array("logout", "login"))) == ""? "":"&".$s));?>'}));
</script>
