<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.common",
	"qrcode",
	"ajax",
]);

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<?if ($arResult["MESSAGE"]):?>
	<?ShowMessage($arResult["MESSAGE"]);?>
<?else:?>
<div id="user-otp-container" class="bx-otp-wrap-container <?=LANGUAGE_ID?>" style="padding-top: 0; max-width:1300px;">
	<?
	ShowMessage(array("MESSAGE" => GetMessage("SECURITY_OTP_MANDATORY_REQUIRED"), "TYPE" => "ERROR"));
	?>
	<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
		<noindex>
			<p>
				<a href="<?=$arParams["AUTH_LOGIN_URL"]?>" rel="nofollow"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_BACK")?></a>
			</p>
		</noindex>
	<?endif?>
	<p class="bx-otp-wrap-container-description"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_DESCR1")?></p>
	<div class="bx-otp-wrap-container-getstart">
		<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_CONNECT")?>
		<br />
		<span class="bx-otp-wrap-container-getstart-icon"></span>
	</div>

	<div class="bx-otp-section bx-otp-step-1 /*amination1s1s*/">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_MOBILE")?></h3>
		<span class="bx-otp-section-desc"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_MOBILE2")?></span>

		<ul class="bx-otp-section-market-list">
			<li class="bx-otp-section-market-icon-Apple"><a href="https://itunes.apple.com/<?=(LANGUAGE_ID == "ru" || LANGUAGE_ID == "ua" ? "ru" : "en")?>/app/bitrix24-otp/id929604673?mt=8" target="_blank"></a></li>
			<li class="bx-otp-section-market-icon-Google"><a href="https://play.google.com/store/apps/details?id=com.bitrixsoft.otp" target="_blank"></a></li>
			<?/*if (in_array(LANGUAGE_ID, array("ru", "ua"))):?>
			<li class="bx-otp-section-market-icon-Yandex"><a href=""></a></li>
			<?endif*/?>
		</ul>
		<div class="clb"></div>
	</div>

	<div class="bx-otp-section bx-otp-step-2 /*amination1s1s*/">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_APP_EXECUTE")?></h3>
		<span class="bx-otp-section-desc"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_APP_EXECUTE_TMP")?></span>
	</div>

	<div class="bx-otp-section bx-otp-step-3">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_CHOOSE_TYPE")?></h3>
		<div class="bx-otp-section">
			<div id="connect-by-qr">
				<div class="input-type">
					<span class="current"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_SCAN_CODE')?></span>
					<span class="separator"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_INPUT_METHODS_SEPARATOR')?></span>
					<a href="#" id="connect-mobile-manual-input"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_HAND_TYPE')?></a>
				</div>
				<div class="bx-otp-section-col">
					<span class="bx-otp-section-desc">
						<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_SCAN_DESCR")?>
					</span>
				</div>
				<div class="bx-otp-section-col clb">
					<div class="bx-otp-token-container">
						<div class="bx-otp-token-screen-QR"></div>
						<div class="bx-otp-token-result-QR">
							<div data-role="qr-code-block"></div>
							<!-- QR must be 164x164 px -->
						</div>
					</div>
				</div>
			</div>
			<div id="connect-by-manual-input" style="display: none;">
				<div class="input-type">
					<a href="#" id="connect-mobile-scan-qr"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_SCAN_CODE')?></a>
					<span class="separator"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_INPUT_METHODS_SEPARATOR')?></span>
					<span class="current"><?=GetMessage('SECURITY_OTP_MANDATORY_AUTH_HAND_TYPE')?></span>
				</div>
				<div class="bx-otp-section-col">
					<span class="bx-otp-section-desc">
						<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_HAND_DESCR")?>
						<b><?
							if ($arResult['TYPE'] === \Bitrix\Security\Mfa\Otp::TYPE_TOTP):
								echo getMessage('SECURITY_OTP_MANDATORY_AUTH_CODE_INFO_TOTP');
							elseif ($arResult['TYPE'] === \Bitrix\Security\Mfa\Otp::TYPE_HOTP):
								echo getMessage('SECURITY_OTP_MANDATORY_AUTH_CODE_INFO_HOTP');
							endif;
							?></b>.
					</span>
				</div>
				<div class="bx-otp-section-col">
					<div class="bx-otp-token-container">
						<div class="bx-otp-token-screen-code"></div>
						<table class="bx-otp-token-result-code">
							<tr>
								<td>
									<span><?=$arResult['APP_SECRET_SPACED']?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>

			<div class="clb"></div>
		</div>
	</div>

	<div class="bx-otp-section bx-otp-step-4">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_ENTER_CODE")?></h3>
		<div data-role="error-container" style="color: red; text-align: center; margin-bottom: 10px"></div>
		<p class="bx-otp-section-desc tac lhn">
			<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_CODE_DESCR")?>
		</p>
		<div class="tac" style="margin-bottom: 40px;">
			<input type="text" class="bx-otp-input-custom bx-otp-int big" dir="ltr" data-role="check-code" autocomplete="off" placeholder="<?=($arResult['TWO_CODE_REQUIRED'] ? GetMessage("SECURITY_OTP_MANDATORY_AUTH_ENTER_CODE_PL1") : GetMessage("SECURITY_OTP_MANDATORY_AUTH_ENTER_CODE_PL"))?>">
		</div>
		<?if ($arResult['TWO_CODE_REQUIRED']):?>
			<p class="bx-otp-section-desc tac lhn">
				<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_CODE_DESCR2")?>
			</p>
			<div class="tac" style="margin-bottom: 10px;">
				<input type="text" class="bx-otp-input-custom bx-otp-int big"  dir="ltr" data-role="check-code" autocomplete="off" placeholder="<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_ENTER_CODE_PL2")?>">
			</div>
		<?endif;?>
		<div class="tac">
			<input class="bx-otp-btn green big" type="submit" data-role="check-button" value="<?=GetMessage("SECURITY_OTP_MANDATORY_AUTH_DONE")?>">
		</div>
	</div>
</div>
	<?
	$jsParams = array(
		'data' => array(
			'secret' => $arResult['SECRET'],
			'provisionUri' => $arResult['PROVISION_URI'],
			'type' => $arResult['TYPE']
		),
		'ui' => array(
			'containerId' => 'user-otp-container'
		),
		'actionUrl' => $componentPath.'/ajax.php'
	);
	$jsMessages = array(
		'SECURITY_OTP_ERROR_TITLE' => GetMessage('SECURITY_OTP_MANDATORY_AUTH_ERROR_TITLE'),
		'SECURITY_OTP_UNKNOWN_ERROR' => GetMessage('SECURITY_OTP_MANDATORY_AUTH_UNKNOWN_ERROR')
	);
	?>
	<script>
		BX.ready(function createOtp()
		{
			BX.message(<?=\CUtil::PhpToJSObject($jsMessages)?>);
			new BX.Security.AuthOtpMandatory(<?=\CUtil::PhpToJSObject($jsParams)?>);
		});
	</script>
<?endif?>