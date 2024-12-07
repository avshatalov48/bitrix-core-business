<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="content-form register-form">
<div class="fields">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && is_array($arParams["AUTH_RESULT"]) &&  $arParams["AUTH_RESULT"]["TYPE"] === "OK"):?>
<div class="field"><?echo GetMessage("AUTH_EMAIL_SENT")?></div>
<?else:?>

<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):?>
	<div class="field"><?echo GetMessage("AUTH_EMAIL_WILL_BE_SENT")?></div>
<?endif?>
<noindex>
<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform">
<?
if ($arResult["BACKURL"] <> '')
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="REGISTRATION" />
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_NAME")?></label>
			<div class="form-input"><input type="text" name="USER_NAME" maxlength="50" value="<?=$arResult["USER_NAME"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_LAST_NAME")?></label>
			<div class="form-input"><input type="text" name="USER_LAST_NAME" maxlength="50" value="<?=$arResult["USER_LAST_NAME"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_LOGIN_MIN")?><span class="starrequired">*</span></label>
			<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_PASSWORD_REQ")?><span class="starrequired">*</span></label>
			<div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_CONFIRM")?><span class="starrequired">*</span></label>
			<div class="form-input"><input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" /></div>
		</div>
		<div class="field">
			<label class="field-title">E-Mail<span class="starrequired">*</span></label>
			<div class="form-input"><input type="text" name="USER_EMAIL" maxlength="255" value="<?=$arResult["USER_EMAIL"]?>" /></div>
		</div>
<?// ********************* User properties ***************************************************?>
<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
	<div class="field"><?=trim($arParams["USER_PROPERTY_NAME"]) <> '' ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?></div>
	<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
	<div class="field">
		<label class="field-title">
			<?=$arUserField["EDIT_FORM_LABEL"]?><?if ($arUserField["MANDATORY"]=="Y"):?><span class="required">*</span><?endif;?>
		</label>
		<div class="form-input">
			<?$APPLICATION->IncludeComponent(
				"bitrix:system.field.edit",
				$arUserField["USER_TYPE"]["USER_TYPE_ID"],
				array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "bform"), null, array("HIDE_ICONS"=>"Y"));?>
		</div>
	</div>
	<?endforeach;?>
<?endif;?>
<?// ******************** /User properties ***************************************************

	/* CAPTCHA */
	if ($arResult["USE_CAPTCHA"] == "Y")
	{
		?>
		<div class="field">
			<label class="field-title"><?=GetMessage("CAPTCHA_REGF_PROMT")?><span class="starrequired">*</span></label>
			<div class="form-input"><input type="text" name="captcha_word" maxlength="50" value="" /></div>
			<p style="clear: left;"><input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></p>
		</div>
		<?
	}
	/* CAPTCHA */
	?>
	
	<div class="field field-button"><input type="submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>" /></div>
<div class="field"><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></div>
<div class="field"><span class="starrequired">*</span><?=GetMessage("AUTH_REQ")?></div>

<div class="field"><a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_AUTH")?></b></a></div>
</form>
</noindex>
<script>
document.bform.USER_NAME.focus();
</script>

<?endif?>
</div>
</div>