<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

//one css for all system.auth.* forms
$APPLICATION->SetAdditionalCSS("/bitrix/css/main/system.auth/flat/style.css");

if($arResult["PHONE_REGISTRATION"])
{
	CJSCore::Init('phone_auth');
}
?>

<div class="bx-authform">

<?
if(!empty($arParams["~AUTH_RESULT"]["MESSAGE"])):
	$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
?>
	<div class="alert <?=($arParams["~AUTH_RESULT"]["TYPE"] == "OK"? "alert-success":"alert-danger")?>"><?=nl2br(htmlspecialcharsbx($text))?></div>
<?endif?>

<?if($arResult["SHOW_FORM"]):?>

	<h3 class="bx-title"><?=GetMessage("AUTH_CHANGE_PASSWORD")?></h3>

	<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform">
<?if ($arResult["BACKURL"] <> ''): ?>
		<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<? endif ?>
		<input type="hidden" name="AUTH_FORM" value="Y">
		<input type="hidden" name="TYPE" value="CHANGE_PWD">

<?if($arResult["PHONE_REGISTRATION"]):?>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?echo GetMessage("change_pass_phone_number")?></div>
			<div class="bx-authform-input-container">
				<input type="text" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" disabled="disabled" />
				<input type="hidden" name="USER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" />
			</div>
		</div>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?echo GetMessage("change_pass_code")?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="USER_CHECKWORD" maxlength="255" value="<?=$arResult["USER_CHECKWORD"]?>" autocomplete="off" />
			</div>
		</div>
<?else:?>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?=GetMessage("AUTH_LOGIN")?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" />
			</div>
		</div>
<?
	if($arResult["USE_PASSWORD"]):
?>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?echo GetMessage("system_change_pass_current_pass")?></div>
			<div class="bx-authform-input-container">
<?if($arResult["SECURE_AUTH"]):?>
				<div class="bx-authform-psw-protected" id="bx_auth_secure_pass" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>

<script>
document.getElementById('bx_auth_secure_pass').style.display = '';
</script>
<?endif?>
				<input type="password" name="USER_CURRENT_PASSWORD" maxlength="255" value="<?=$arResult["USER_CURRENT_PASSWORD"]?>" autocomplete="new-password" />
			</div>
		</div>
<?
	else:
?>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?=GetMessage("AUTH_CHECKWORD")?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="USER_CHECKWORD" maxlength="255" value="<?=$arResult["USER_CHECKWORD"]?>" autocomplete="off" />
			</div>
		</div>
<?
	endif;
?>
<?endif?>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?></div>
			<div class="bx-authform-input-container">
<?if($arResult["SECURE_AUTH"]):?>
				<div class="bx-authform-psw-protected" id="bx_auth_secure" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>

<script>
document.getElementById('bx_auth_secure').style.display = '';
</script>
<?endif?>
				<input type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" autocomplete="new-password" />
			</div>
		</div>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></div>
			<div class="bx-authform-input-container">
<?if($arResult["SECURE_AUTH"]):?>
				<div class="bx-authform-psw-protected" id="bx_auth_secure_conf" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>

<script>
document.getElementById('bx_auth_secure_conf').style.display = '';
</script>
<?endif?>
				<input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" autocomplete="new-password" />
			</div>
		</div>

<?if ($arResult["USE_CAPTCHA"]):?>
		<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?echo GetMessage("system_auth_captcha")?></div>
			<div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></div>
			<div class="bx-authform-input-container">
				<input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off"/>
			</div>
		</div>

<?endif?>

		<div class="bx-authform-formgroup-container">
			<input type="submit" class="btn btn-primary" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" />
		</div>

		<div class="bx-authform-description-container">
			<?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?>
		</div>

	</form>

<script>
document.bform.USER_CHECKWORD.focus();
</script>

<?if($arResult["PHONE_REGISTRATION"]):?>

<script>
new BX.PhoneAuth({
	containerId: 'bx_chpass_resend',
	errorContainerId: 'bx_chpass_error',
	interval: <?=$arResult["PHONE_CODE_RESEND_INTERVAL"]?>,
	data:
		<?= Json::encode([
			'signedData' => $arResult["SIGNED_DATA"]
		]) ?>,
	onError:
		function(response)
		{
			var errorNode = BX('bx_chpass_error');
			errorNode.innerHTML = '';
			for(var i = 0; i < response.errors.length; i++)
			{
				errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br />';
			}
			errorNode.style.display = '';
		}
});
</script>

<div class="alert alert-danger" id="bx_chpass_error" style="display:none"></div>

<div id="bx_chpass_resend"></div>

<?endif?>

<?endif;?>

	<div class="bx-authform-link-container">
		<a href="<?=$arResult["AUTH_AUTH_URL"]?>"><b><?=GetMessage("AUTH_AUTH")?></b></a>
	</div>

</div>

