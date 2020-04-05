<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="content-form login-form">
<div class="fields">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
ShowMessage($arResult['ERROR_MESSAGE']);
?>

<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">

	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="AUTH" />
	<?if (strlen($arResult["BACKURL"]) > 0):?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
	<?endif?>
	<?
	foreach ($arResult["POST"] as $key => $value)
	{
	?>
	<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
	<?
	}
	?>
	<div class="field">
		<label class="field-title"><?=GetMessage("AUTH_LOGIN")?></label>
		<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" class="input-field" /></div>
	</div>	
	<div class="field">
		<label class="field-title"><?=GetMessage("AUTH_PASSWORD")?></label>
		<div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="50" class="input-field" />
<?if($arResult["SECURE_AUTH"]):?>
				<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
				<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
				</noscript>
<script type="text/javascript">
document.getElementById('bx_auth_secure').style.display = 'inline-block';
</script>
<?endif?>
		</div>
	</div>
	<?if($arResult["CAPTCHA_CODE"]):?>
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></label>
			<div class="form-input"><input type="text" name="captcha_word" maxlength="50" class="input-field" /></div>
			<p style="clear: left;"><input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" /><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></p>
		</div>
	<?endif;?>
	<?
	if ($arResult["STORE_PASSWORD"] == "Y")
	{
	?>
		<div class="field field-option">
			<input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" /><label for="USER_REMEMBER">&nbsp;<?=GetMessage("AUTH_REMEMBER_ME")?></label>
		</div>
	<?
	}
	?>
	<div class="field field-button">
		<input type="submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" />
	</div>
<?
if ($arParams["NOT_SHOW_LINKS"] != "Y")
{
?><noindex>
	<?
	if($arResult["NEW_USER_REGISTRATION"] == "Y" && $arParams["AUTHORIZE_REGISTRATION"] != "Y")
	{
		?>
		<div class="field">
		<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_REGISTER")?></b></a><br />
		<?=GetMessage("AUTH_FIRST_ONE")?> <a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REG_FORM")?></a>
		</div>
		<?
	}
	?>
<div class="field">
<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></b></a><br />
<?=GetMessage("AUTH_GO")?> <a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_GO_AUTH_FORM")?></a><br />
<?=GetMessage("AUTH_MESS_1")?> <a href="<?=$arResult["AUTH_CHANGE_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_CHANGE_FORM")?></a>
</div>
</noindex><?
}
?>
</form>
<script type="text/javascript">
<?
if (strlen($arResult["LAST_LOGIN"])>0)
{
?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<?
}
else
{
?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<?
}
?>
</script>

</div>

<?if($arResult["AUTH_SERVICES"]):?>
<?
$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "", 
	array(
		"AUTH_SERVICES"=>$arResult["AUTH_SERVICES"],
		"CURRENT_SERVICE"=>$arResult["CURRENT_SERVICE"],
		"AUTH_URL"=>$arResult["AUTH_URL"],
		"POST"=>$arResult["POST"],
	), 
	$component, 
	array("HIDE_ICONS"=>"Y")
);
?>
<?endif?>

</div>