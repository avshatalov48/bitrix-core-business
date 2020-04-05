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
<?
if($arResult['REQUIRED_BY_MANDATORY'] === true):

	$APPLICATION->IncludeComponent(
		"bitrix:security.auth.otp.mandatory",
		"",
		array(
			"AUTH_LOGIN_URL" => $arResult["~AUTH_LOGIN_URL"],
			"NOT_SHOW_LINKS" => $arParams["NOT_SHOW_LINKS"]
		)
	);

else:

//one css for all system.auth.* forms
$APPLICATION->SetAdditionalCSS("/bitrix/css/main/system.auth/flat/style.css");
?>

<div class="bx-authform">

<?
if(!empty($arParams["~AUTH_RESULT"])):
	$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
?>
	<div class="alert <?=($arParams["~AUTH_RESULT"]["TYPE"] == "OK"? "alert-success":"alert-danger")?>"><?=nl2br(htmlspecialcharsbx($text))?></div>
<?endif?>

	<h3 class="bx-title"><?=GetMessage("AUTH_OTP_PLEASE_AUTH")?></h3>

	<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">

		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="OTP" />

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?=GetMessage("AUTH_OTP_OTP")?></div>
			<div class="bx-authform-input-container">
				<input class="bx-auth-input" type="text" name="USER_OTP" maxlength="50" value="" autocomplete="off" />
			</div>
		</div>

<?if($arResult["CAPTCHA_CODE"]):?>
		<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container">
				<?echo GetMessage("AUTH_OTP_CAPTCHA_PROMT")?>
			</div>
			<div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></div>
			<div class="bx-authform-input-container">
				<input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off" />
			</div>
		</div>
<?endif;?>

<?if($arResult["REMEMBER_OTP"]):?>
		<div class="bx-authform-formgroup-container">
			<div class="checkbox">
				<label class="bx-filter-param-label">
					<input type="checkbox" name="OTP_REMEMBER" value="Y" />
					<span class="bx-filter-param-text"><?=GetMessage("AUTH_OTP_REMEMBER_ME")?></span>
				</label>
			</div>
		</div>
<?endif?>
		<div class="bx-authform-formgroup-container">
			<input type="submit" class="btn btn-primary" name="Otp" value="<?=GetMessage("AUTH_OTP_AUTHORIZE")?>" />
		</div>

<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
		<div class="bx-authform-link-container">
			<a href="<?=$arResult["AUTH_LOGIN_URL"]?>" rel="nofollow"><?echo GetMessage("AUTH_OTP_AUTH_BACK")?></a>
		</div>
<?endif?>

	</form>
</div>

<script type="text/javascript">
try{document.form_auth.USER_OTP.focus();}catch(e){}
</script>
<?endif;?>