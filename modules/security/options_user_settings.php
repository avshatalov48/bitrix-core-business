<?php

/**
 * @var int $ID - Edited user id
 * @var string $strError - Save error
 * @var CAdminForm $tabControl
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Security\Mfa\Otp;
use Bitrix\Main\Web\Json;

IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule("security") || !CSecurityUser::isActive()):?>
	<tr>
		<td><?=GetMessage("SEC_OTP_NEW_ACCESS_DENIED")?></td>
	</tr>
<?
	return;
endif;
?>
<?if(
	$ID <= 0
	|| ($USER->getID() != $ID && !$USER->CanDoOperation('security_edit_user_otp'))
)
	return;
?>
<?
CJSCore::Init(array('qrcode', 'ajax', 'window'));
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/page/user-edit.js');
$otp = Otp::getByUser($ID);
$deactivateUntil = $otp->getDeactivateUntil();
$availableTypes = Otp::getAvailableTypes();
$availableTypesDescription = Otp::getTypesDescription();
$currentPage = $APPLICATION->GetCurPageParam(
	sprintf('%s_active_tab=%s',$tabControl->name, $tabControl->tabs[$tabControl->tabIndex]['DIV']),
	array(sprintf('%s_active_tab',$tabControl->name))
);
$deactivateDays = array();
$deactivateDays[] = GetMessage("SEC_OTP_NO_DAYS");
for($i=1; $i <= 10; $i++)
{
	$deactivateDays[$i] = FormatDate("ddiff", time()-60*60*24*$i);
}
$jsMessages = array(
	'SEC_OTP_ERROR_TITLE' => GetMessage('SEC_OTP_ERROR_TITLE'),
	'SEC_OTP_UNKNOWN_ERROR' => GetMessage('SEC_OTP_UNKNOWN_ERROR')
);
$jsSettings = array(
	'userId' => (int) $ID,
	'successfulUrl' => $currentPage,
	'deactivateDays' => $deactivateDays,
	'availableTypes' => $availableTypesDescription
)
?>
<script>
	BX.message(<?=Json::encode($jsMessages)?>);
</script>
<script>
	BX.ready(function() {
		var settings = <?=Json::encode($jsSettings)?>;
		new BX.Security.UserEdit.Otp(settings.userId, settings);
	});
</script>
<!--Popup starts-->
<tr style="display: none;">
	<td colspan="2">
<div id="otp-mobile-popup" class="otp-popup otp-mobile" data-title="<?=GetMessage('SEC_OTP_CONNECT_MOBILE_TITLE')?>">
	<div class="otp-description">
		<ol>
			<li><?=GetMessage('SEC_OTP_CONNECT_MOBILE_STEP_1')?></li>
			<li><?=GetMessage('SEC_OTP_CONNECT_MOBILE_STEP_2')?></li>
			<li><?=GetMessage('SEC_OTP_CONNECT_MOBILE_STEP_3')?></li>
		</ol>
	</div>
	<div class="otp-connect">
		<div id="connect-by-qr">
			<div class="input-type">
				<span class="current"><?=GetMessage('SEC_OTP_MOBILE_SCAN_QR')?></span><span class="separator"><?=GetMessage('SEC_OTP_MOBILE_INPUT_METHODS_SEPARATOR')?></span><a href="#" id="connect-mobile-manual-input"><?=GetMessage('SEC_OTP_MOBILE_MANUAL_INPUT')?></a>
			</div>
			<div style="margin-bottom: 20px">
				<?=GetMessage('SEC_OTP_CONNECT_MOBILE_SCAN_QR')?>
			</div>
			<div>
				<div class="input-wrapper">
					<div data-role="qr-code-block" data-autoclear="yes" style="margin-top: 8px; margin-left: 8px;"></div>
				</div>
			</div>
		</div>
		<div id="connect-by-manual-input" style="display: none;">
			<div class="input-type">
				<a href="#" id="connect-mobile-scan-qr"><?=GetMessage('SEC_OTP_MOBILE_SCAN_QR')?></a><span class="separator"><?=GetMessage('SEC_OTP_MOBILE_INPUT_METHODS_SEPARATOR')?></span><span class="current"><?=GetMessage('SEC_OTP_MOBILE_MANUAL_INPUT')?></span>
			</div>
			<div style="margin-bottom: 20px">
				<?=GetMessage('SEC_OTP_CONNECT_MOBILE_MANUAL_INPUT')?>
				<span class="type-title" data-show-type="<?= Otp::TYPE_HOTP ?>"><?=GetMessage('SEC_OTP_CONNECT_MOBILE_MANUAL_INPUT_HOTP')?></span>
				<span class="type-title" data-show-type="<?= Otp::TYPE_TOTP ?>"><?=GetMessage('SEC_OTP_CONNECT_MOBILE_MANUAL_INPUT_TOTP')?></span>
				.
			</div>
			<div>
				<div class="input-wrapper">
					<div data-role="app-code-block" data-autoclear="yes" style="margin-left: auto; margin-right: auto; margin-top: 98px; font-weight: bold; text-align: center;">#APP_CODE#</div>
				</div>
			</div>
		</div>
	</div>
	<div>
		<p>
			<?=GetMessage('SEC_OTP_CONNECT_MOBILE_INPUT_DESCRIPTION')?>
		</p>
		<p>
			<input type="text" dir="ltr" data-autoclear="yes" data-role="check-code" autocomplete="off" placeholder="<?=GetMessage('SEC_OTP_CONNECT_MOBILE_ENTER_CODE')?>">
		</p>
		<p data-require-two-codes="yes">
			<?=GetMessage('SEC_OTP_CONNECT_MOBILE_INPUT_NEXT_DESCRIPTION')?>
		</p>
		<p data-require-two-codes="yes">
			<input type="text" dir="ltr" data-autoclear="yes" data-role="check-code" autocomplete="off" placeholder="<?=GetMessage('SEC_OTP_CONNECT_MOBILE_ENTER_NEXT_CODE')?>">
		</p>
		<div data-role="error-container" class="error-wrapper" data-autoclear="yes"></div>
	</div>
</div>
<div id="otp-device-popup" class="otp-popup otp-device" data-title="<?=GetMessage('SEC_OTP_CONNECT_DEVICE_TITLE')?>">
	<table>
		<tr>
			<td>
				<?=GetMessage("SEC_OTP_TYPE")?>:
			</td>
			<td>
				<?foreach($availableTypes as $value):?>
					<span class="type-title" data-show-type="<?=$value?>">
						<?= ($availableTypesDescription[$value]['title'] ?? $value) ?>
					</span>
				<?endforeach?>
			</td>
		</tr>
		<tr>
			<td>
				<?=GetMessage("SEC_OTP_SECRET_KEY")?>:
			</td>
			<td>
				<input type="text" autocomplete="off" data-autoclear="yes" data-role="secret-code" size="40" maxlength="64" value="">
			</td>
		</tr>
		<tr data-show-type="<?= Otp::TYPE_TOTP ?>">
			<td>
				<?=GetMessage('SEC_OTP_START_TIMESTAMP')?>:
			</td>
			<td>
				<input type="text" autocomplete="off" data-autoclear="yes" data-role="start-timestamp" size="40" maxlength="20" value="">
			</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?=GetMessage("SEC_OTP_INIT")?></td>
		</tr>
		<tr>
			<td>
				<?=GetMessage("SEC_OTP_PASS1")?>:
			</td>
			<td>
				<input type="text" autocomplete="off" data-autoclear="yes" data-role="check-code" size="8" maxlength="8" value="">
			</td>
		</tr>
		<tr data-require-two-codes="yes">
			<td>
				<?=GetMessage("SEC_OTP_PASS2")?>:
			</td>
			<td>
				<input type="text" autocomplete="off" data-autoclear="yes" data-role="check-code" size="8" maxlength="8" value="">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div data-role="error-container" class="error-wrapper" data-autoclear="yes"></div>
			</td>
		</tr>
	</table>
</div>
<div id="otp-recovery-codes" class="otp-popup otp-recovery-codes" data-title="<?=GetMessage('SEC_OTP_RECOVERY_CODES_TITLE')?>">
	<p>
		<?=GetMessage('SEC_OTP_RECOVERY_CODES_DESCRIPTION')?>
	</p>
	<p>
		<?=GetMessage('SEC_OTP_RECOVERY_CODES_WARNING')?>
	</p>
	<div class="input-wrapper">
		<div>
			<ol data-role="recoverycodes-container" class="codes-container"  style="display: none;">
				<li data-role="recoverycode-template" data-autoclear="yes">#CODE#</li>
			</ol>
		</div>
	</div>
	<p>
		<div style="margin-top: 10px">
			<input type="button" data-role="print-codes" value="<?=GetMessage('SEC_OTP_RECOVERY_CODES_PRINT')?>" />
			<input type="button" data-role="save-codes" value="<?=GetMessage('SEC_OTP_RECOVERY_CODES_SAVE_FILE')?>" />
		</div>
	</p>
	<p>
		<div><?=GetMessage('SEC_OTP_RECOVERY_CODES_REGENERATE_DESCRIPTION')?></div>
		<div data-role="error-container" class="error-wrapper" data-autoclear="yes"></div>
		<div>
			<input type="button" data-role="regenerate-codes" value="<?=GetMessage('SEC_OTP_RECOVERY_CODES_REGENERATE')?>" />
		</div>
	</p>
	<p>
		* <?=GetMessage('SEC_OTP_RECOVERY_CODES_NOTE')?>
	</p>
</div>
	</td>
</tr>
<!--Popup ends-->
<?if (!$otp->isActivated()):?>
	<?if (
		Otp::isMandatoryUsing()
		&& $otp->getInitialDate() // User trigger any of OTP mechanisms
		&& $USER->CanDoOperation('security_edit_user_otp')
		&& !$otp->canSkipMandatoryByRights()
	):?>
		<tr>
			<td>
		<?if (!$otp->isMandatorySkipped()):?>
				<?=BeginNote()?>
				<?=getMessage('SEC_OTP_MANDATORY_EXPIRED')?>
				<span class="otp-link-button" id="otp-deffer"><?=GetMessage('SEC_OTP_MANDATORY_DEFFER')?></span>
				<?=EndNote()?>
		<?elseif ($otp->getDeactivateUntil()):?>
				<?=BeginNote()?>
				<?=getMessage('SEC_OTP_MANDATORY_ALMOST_EXPIRED', array('#DATE#' => $otp->getDeactivateUntil()))?>
				<span class="otp-link-button" id="otp-deffer"><?=GetMessage('SEC_OTP_MANDATORY_DEFFER')?></span>
				<?=EndNote()?>
		<?else:?>
				<?=BeginNote()?>
				<?=getMessage('SEC_OTP_MANDATORY_DISABLED')?>
				<span class="otp-link-button" id="otp-mandatory-active">
					<?if (empty($deactivateDays)):?>
						<?=GetMessage('SEC_OTP_MANDATORY_ENABLE_DEFAULT')?>
					<?else:?>
						<?=GetMessage('SEC_OTP_MANDATORY_ENABLE')?>
					<?endif;?>
				</span>
				<?=EndNote()?>
		<?endif;?>
			</td>
		</tr>
	<?endif;?>
	<tr>
		<?if ($otp->isInitialized()):?>
			<td style="text-align: left;">
				<a class="adm-btn-save adm-btn" id="otp-activate"><?=GetMessage('SEC_OTP_ENABLE')?></a>
				<?if ($deactivateUntil):?>
					<span>(<?=getMessage('SEC_OTP_DEACTIVATE_UNTIL', array('#DATE#' => $deactivateUntil))?>)</span>
				<?endif;?>
			</td>
			<td style="text-align: right;">
				<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-device"><?=GetMessage('SEC_OTP_CONNECT_DEVICE')?></a>
				<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-mobile" style="margin-left: 20px;"><?=GetMessage('SEC_OTP_CONNECT_MOBILE')?></a>
			</td>
		<?else:?>
			<td colspan="2">
				<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-device"><?=GetMessage('SEC_OTP_CONNECT_DEVICE')?></a>
				<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-mobile" style="margin-left: 20px;"><?=GetMessage('SEC_OTP_CONNECT_MOBILE')?></a>
			</td>
		<?endif;?>
	</tr>
	<tr>
		<td colspan="2">
			<div style=" padding: 20px;">
				<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_INTRO_TITLE')?></h3>
				<div style="float: left; margin-right: 20px">
					<div style="-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 2px solid #e0e3e5; border-radius: 2px; height: 156px; width: 156px; background: white url(/bitrix/images/security/etoken_pass.png?v2) no-repeat center center;"></div>
				</div>
				<div>
					<?=(IsModuleInstalled('intranet')?
						getMessage('SEC_OTP_DESCRIPTION_INTRO_INTRANET'):
						getMessage('SEC_OTP_DESCRIPTION_INTRO_SITE'))?>
				</div>
				<?
				if (in_array(LANGUAGE_ID, array('en', 'ru', 'de'), true))
					$imageLanguage = LANGUAGE_ID;
				else
					$imageLanguage = \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
				?>
				<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_USING_TITLE')?></h3>
				<div style="float: left; margin-right: 20px">
					<div style="-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 2px solid #e0e3e5; border-radius: 2px; padding: 5px 10px; background: white; height: 150px;">
						<div style="float: left; background: url(/bitrix/images/security/<?=$imageLanguage?>_login_step0.png) no-repeat top right; width: 220px; height: 120px; padding-top: 20px;" ><?=getMessage('SEC_OTP_DESCRIPTION_USING_STEP_0')?></div>
						<div style="float: left; background: url(/bitrix/images/security/<?=$imageLanguage?>_login_step1.png) no-repeat top right; width: 220px; height: 120px; padding-top: 20px; margin-left:20px;"><?=getMessage('SEC_OTP_DESCRIPTION_USING_STEP_1')?></div>
					</div>
				</div>
				<div>
					<?=getMessage('SEC_OTP_DESCRIPTION_USING')?>
				</div>
				<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_ACTIVATION_TITLE')?></h3>
				<div>
					<?=getMessage('SEC_OTP_DESCRIPTION_ACTIVATION')?>
				</div>
				<?=BeginNote()?>
				<h3><?=getMessage('SEC_OTP_DESCRIPTION_ABOUT_TITLE')?></h3>
				<div>
					<?=getMessage('SEC_OTP_DESCRIPTION_ABOUT')?>
				</div>
				<?=EndNote()?>
			</div>
		</td>
	</tr>
<?else:?>
	<?if (Otp::isRecoveryCodesEnabled()):?>
		<?
		$codes = \Bitrix\Security\Mfa\RecoveryCodesTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=USER_ID' => $ID),
			'limit' => 1
		))->fetch();
		if (!$codes):?>
			<tr data-role="otp-recovery-codes-warning">
				<td colspan="2">
					<?CAdminMessage::ShowMessage(array(
						"MESSAGE" => GetMessage('SEC_OTP_WARNING_RECOVERY_CODES'),
						"TYPE" => 'ERROR',
						"HTML" => true
					));?>
				</td>
			</tr>
		<?endif;?>
	<?endif;?>
	<tr>
		<td style="text-align: left;">
			<span><?=GetMessage('SEC_OTP_CONNECTED')?></span>
			<?if(
				!Otp::isMandatoryUsing()
				|| $otp->canSkipMandatory()
				|| $USER->CanDoOperation('security_edit_user_otp')
			):?>
				<span class="otp-link-button" id="otp-deactivate"><?=GetMessage('SEC_OTP_DISABLE')?></span>
			<?endif;?>
			<?if (Otp::isRecoveryCodesEnabled()):?>
				<span class="otp-link-button" id="otp-show-recovery-codes"><?=GetMessage('SEC_OTP_RECOVERY_CODES_BUTTON')?></span>
			<?endif;?>
			<?if ($USER->CanDoOperation('security_edit_user_otp')):?>
				<span class="otp-link-button" id="otp-reinitialize"><?=GetMessage('SEC_OTP_SYNC_NOW')?></span>
			<?endif;?>
		</td>
		<td style="text-align: right;">
			<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-device"><?=GetMessage('SEC_OTP_CONNECT_NEW_DEVICE')?></a>
			<a class="adm-btn-save adm-btn adm-btn-menu" id="otp-connect-mobile" style="margin-left: 20px;"><?=GetMessage('SEC_OTP_CONNECT_NEW_MOBILE')?></a>
		</td>
	</tr>
	<?if ($USER->CanDoOperation('security_edit_user_otp')):?>
		<tr class="heading" style="display:none;" data-show-on-reinitialize="yes">
			<td colspan="2"><?=GetMessage("SEC_OTP_INIT")?></td>
			<input type="hidden" name="profile_module_id[]" value="security">
		</tr>
		<tr style="display:none;" data-show-on-reinitialize="yes">
			<td>
				<?=GetMessage("SEC_OTP_PASS1")?>:
			</td>
			<td>
				<input type="text" autocomplete="off" id="security_SYNC1" name="security_SYNC1" size="8" maxlength="8" value="">
			</td>
		</tr>
		<tr style="display:none;" data-show-on-reinitialize="yes">
			<td>
				<?=GetMessage("SEC_OTP_PASS2")?>:
			</td>
			<td>
				<input type="text" autocomplete="off" id="security_SYNC2" name="security_SYNC2" size="8" maxlength="8" value="">
			</td>
		</tr>
	<?endif;?>
<?endif;?>
