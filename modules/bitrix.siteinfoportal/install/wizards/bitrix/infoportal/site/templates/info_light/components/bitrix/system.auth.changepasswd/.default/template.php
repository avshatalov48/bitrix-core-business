<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="content-form changepswd-form">
<div class="fields">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
<form method="post" action="<?=$arResult["AUTH_FORM"]?>" name="bform">
<?if ($arResult["BACKURL"] <> ''): ?>
<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<? endif ?>
<input type="hidden" name="AUTH_FORM" value="Y"/>
<input type="hidden" name="TYPE" value="CHANGE_PWD"/>
<div class="field">
	<label class="field-title"><?=GetMessage("AUTH_LOGIN")?><span class="starrequired">*</span></label>
	<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" /></div>
</div>
<div class="field">
	<label class="field-title"><?=GetMessage("AUTH_CHECKWORD")?><span class="starrequired">*</span></label>
	<div class="form-input"><input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" /></div>
</div>
<div class="field">
	<label class="field-title"><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?><span class="starrequired">*</span></label>
	<div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" /></div>
	<div class="description">&mdash; <?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></div>
</div>
<div class="field">
	<label class="field-title"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?><span class="starrequired">*</span></label>
	<div class="form-input"><input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>"  /></div>
</div>
<?if($arResult["USE_CAPTCHA"]):?>
<div class="field">
	<label class="field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?><span class="starrequired">*</span></label>
	<div class="form-input"><input type="text" name="captcha_word" maxlength="50" class="input-field" /></div>
	<p style="clear: left;"><input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" /><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></p>
</div>
<?endif;?>
<div class="field field-button"><input type="submit" class="input-submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" /></div>


<div class="field"><a href="<?=$arResult["AUTH_AUTH_URL"]?>"><b><?=GetMessage("AUTH_AUTH")?></b></a></div>

</form>

<script type="text/javascript">
document.bform.USER_LOGIN.focus();
</script>
</div>
</div>