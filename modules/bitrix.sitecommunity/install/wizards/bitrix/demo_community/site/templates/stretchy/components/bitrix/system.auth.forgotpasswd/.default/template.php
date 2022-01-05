<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="content-form forgot-form">
<div class="fields">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
<form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?
if ($arResult["BACKURL"] <> '')
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="SEND_PWD">
	<div class="field"><?=GetMessage("AUTH_FORGOT_PASSWORD_1")?></div>

		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_LOGIN")?></label>
			<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title">E-Mail</label>
			<div class="form-input"><input type="text" name="USER_EMAIL" maxlength="255" /></div>
		</div>
<?if($arResult["USE_CAPTCHA"]):?>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></label>
			<div class="form-input"><input type="text" name="captcha_word" maxlength="50" class="input-field" /></div>
			<p style="clear: left;"><input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" /><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></p>
		</div>
<?endif;?>

		<div class="field field-button"><input type="submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" /></div>

<div class="field"><a href="<?=$arResult["AUTH_AUTH_URL"]?>"><b><?=GetMessage("AUTH_AUTH")?></b></a></div> 
</form>
<script type="text/javascript">
document.bform.USER_LOGIN.focus();
</script>
</div>
</div>