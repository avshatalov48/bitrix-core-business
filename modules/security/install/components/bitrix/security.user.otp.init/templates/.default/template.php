<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

CJSCore::Init(array('qrcode', 'ajax', 'popup'));
\Bitrix\Main\UI\Extension::load("ui.common");

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

<div id="user-otp-container" class="security-user-otp <?=LANGUAGE_ID?>">
	<?if ($arParams['SHOW_DESCRIPTION'] === 'Y'):?>
	<div class="ui-text-1 ui-color-medium"><?=GetMessage("SECURITY_OTP_DESCR")?></div>
	<?endif?>

	<div class="bx-otp-wrap-container-getstart">
		<?=GetMessage("SECURITY_OTP_CONNECT")?>
		<br />
		<span class="bx-otp-wrap-container-getstart-icon"></span>
	</div>

	<div class="bx-otp-section bx-otp-step-1 /*amination1s1s*/">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_MOBILE")?></h3>
		<span class="bx-otp-section-desc"><?=GetMessage("SECURITY_OTP_MOBILE2")?></span>

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
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_APP_EXECUTE")?></h3>
		<span class="bx-otp-section-desc"><?=GetMessage("SECURITY_OTP_APP_EXECUTE_TMP")?></span>
	</div>

	<div class="bx-otp-section bx-otp-step-3">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_CHOOSE_TYPE")?></h3>
		<div class="bx-otp-section">

			<div class="bx-otp-section-col">
				<h4 class="bx-otp-section-title-small"><?=GetMessage("SECURITY_OTP_SCAN_CODE")?></h4>
						<span class="bx-otp-section-desc">
							<?=GetMessage("SECURITY_OTP_SCAN_DESCR")?>
						</span>
			</div>
			<div class="bx-otp-section-col ">
				<h4 class="bx-otp-section-title-small"><?=GetMessage("SECURITY_OTP_HAND_TYPE")?></h4>
						<span class="bx-otp-section-desc">
							<?=GetMessage("SECURITY_OTP_HAND_DESCR")?>
							<b><?
							if ($arResult['TYPE'] === \Bitrix\Security\Mfa\Otp::TYPE_TOTP):
								echo getMessage('SECURITY_OTP_CODE_INFO_TOTP');
							elseif ($arResult['TYPE'] === \Bitrix\Security\Mfa\Otp::TYPE_HOTP):
								echo getMessage('SECURITY_OTP_CODE_INFO_HOTP');
							endif;
							?></b>.
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
			<div class="clb"></div>
		</div>
	</div>

	<div class="bx-otp-section bx-otp-step-4">
		<div class="bx-otp-step-num"></div>
		<h3 class="bx-otp-section-title"><?=GetMessage("SECURITY_OTP_ENTER_CODE")?></h3>
		<div data-role="error-container" style="color: red; text-align: center; margin-bottom: 10px"></div>
		<p class="bx-otp-section-desc tac lhn">
			<?=GetMessage("SECURITY_OTP_CODE_DESCR")?>
		</p>
		<div class="tac" style="margin-bottom: 40px;">
			<input type="text" class="bx-otp-input-custom bx-otp-int big" dir="ltr" data-role="check-code" autocomplete="off" placeholder="<?=($arResult['TWO_CODE_REQUIRED'] ? GetMessage("SECURITY_OTP_ENTER_CODE_PL1") : GetMessage("SECURITY_OTP_ENTER_CODE_PL"))?>">
		</div>
		<?if ($arResult['TWO_CODE_REQUIRED']):?>
			<p class="bx-otp-section-desc tac lhn">
				<?=GetMessage("SECURITY_OTP_CODE_DESCR2")?>
			</p>
			<div class="tac" style="margin-bottom: 10px;">
				<input type="text" class="bx-otp-input-custom bx-otp-int big"  dir="ltr" data-role="check-code" autocomplete="off" placeholder="<?=GetMessage("SECURITY_OTP_ENTER_CODE_PL2")?>">
			</div>
		<?endif;?>
		<div class="tac">
			<input class="bx-otp-btn green big" type="submit" data-role="check-button" value="<?=GetMessage("SECURITY_OTP_DONE")?>">
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
		'successfulUrl' => $arResult['SUCCESSFUL_URL'],
		'signedParameters' => $this->getComponent()->getSignedParameters(),
		'componentName' => $this->getComponent()->getName(),
		'needRedirectAfterConnection' => $arResult["REDIRECT_AFTER_CONNECTION"] == "Y" ? "Y" : "N"
	);
	$jsMessages = array(
		'SECURITY_OTP_ERROR_TITLE' => GetMessage('SECURITY_OTP_ERROR_TITLE'),
		'SECURITY_OTP_UNKNOWN_ERROR' => GetMessage('SECURITY_OTP_UNKNOWN_ERROR')
	);
	?>
	<script>
		BX.ready(function createOtp()
		{
			BX.message(<?=\CUtil::PhpToJSObject($jsMessages)?>);
			new BX.Security.UserOtp.Init(<?=\CUtil::PhpToJSObject($jsParams)?>);
		});
	</script>
<?endif?>