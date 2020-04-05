<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */
?>
<?if ($arResult['REQUIRED_BY_MANDATORY'] === true):?>
<?$APPLICATION->IncludeComponent(
	"bitrix:security.auth.otp.mandatory",
	"",
	array(
		"AUTH_LOGIN_URL" => $arResult["~AUTH_LOGIN_URL"],
		"NOT_SHOW_LINKS" => $arParams["NOT_SHOW_LINKS"]
	)
);?>
<?else:?>
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>

<div class="bx-auth">
	<div class="bx-auth-note"><?=GetMessage("AUTH_OTP_PLEASE_AUTH")?></div>

	<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">

		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="OTP" />

		<table class="bx-auth-table">
			<tr>
				<td class="bx-auth-label"><?=GetMessage("AUTH_OTP_OTP")?></td>
				<td><input class="bx-auth-input" type="text" name="USER_OTP" maxlength="50" value="" autocomplete="off" /></td>
			</tr>
<?if($arResult["CAPTCHA_CODE"]):?>
				<tr>
					<td></td>
					<td><input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></td>
				</tr>
				<tr>
					<td class="bx-auth-label"><?echo GetMessage("AUTH_OTP_CAPTCHA_PROMT")?>:</td>
					<td><input class="bx-auth-input" type="text" name="captcha_word" maxlength="50" value="" size="15" /></td>
				</tr>
<?endif;?>
<?if($arResult["REMEMBER_OTP"]):?>
			<tr>
				<td></td>
				<td><input type="checkbox" id="OTP_REMEMBER" name="OTP_REMEMBER" value="Y" /><label for="OTP_REMEMBER">&nbsp;<?=GetMessage("AUTH_OTP_REMEMBER_ME")?></label></td>
			</tr>
<?endif?>
			<tr>
				<td></td>
				<td class="authorize-submit-cell"><input type="submit" name="Otp" value="<?=GetMessage("AUTH_OTP_AUTHORIZE")?>" /></td>
			</tr>
		</table>

<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
		<noindex>
			<p>
				<a href="<?=$arResult["AUTH_LOGIN_URL"]?>" rel="nofollow"><?echo GetMessage("AUTH_OTP_AUTH_BACK")?></a>
			</p>
		</noindex>
<?endif?>

	</form>
</div>

<script type="text/javascript">
try{document.form_auth.USER_OTP.focus();}catch(e){}
</script>
<?endif;?>