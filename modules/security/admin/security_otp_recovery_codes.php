<?php
use Bitrix\Main\Config\Option;
use Bitrix\Security\Mfa\Otp;
use Bitrix\Security\Mfa\RecoveryCodesTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

IncludeModuleLangFile(__FILE__);
$request = Bitrix\Main\Context::getCurrent()->getRequest();
$userId = (int) ($request['user']?: $USER->getId());
$userOtp = Otp::getByUser($userId);

if (!CModule::includeModule('security'))
	ShowError('Security module not installed');

if (!$userOtp->isActivated())
	ShowError('OTP inactive');

if (!Otp::isRecoveryCodesEnabled())
	ShowError('OTP Recovery codes are disabled');

if (
	!$userId
	|| ($userId != $USER->getId() && !$USER->CanDoOperation('security_edit_user_otp'))
)
{
	ShowError('Not enough permissions');
}

if (isset($request['action']) && $request['action'] === 'download')
{
	$codes = getRecoveryCodes($userId);
	$response = '';
	$counter = 0;
	foreach ($codes as $code)
	{
		$counter++;
		$response .= sprintf("%d. %s\r\n", $counter, $code);
	}

	header('Content-Type: text/plain', true);
	header('Content-Disposition: attachment; filename="recovery_codes.txt"');
	header('Content-Transfer-Encoding: binary');
	header(sprintf('Content-Length: %d', strlen($response)));
	echo $response;
	die;
}

function getRecoveryCodes($userId)
{
	$codes = RecoveryCodesTable::getList(array(
		'select' => array('CODE'),
		'filter' => array('=USER_ID' => $userId, '=USED' => 'N')
	));

	$normalizedCodes = array();
	while (($code = $codes->fetch()))
	{
		$normalizedCodes[] = $code['CODE'];
	}

	return $normalizedCodes;
}

$codes = getRecoveryCodes($userId);
$issuer = $userOtp->getIssuer();
$label = $userOtp->getLabel();

$createdDate = CUserOptions::GetOption('security', 'recovery_codes_generated', null);
if ($createdDate)
	$createdDate = FormatDate('FULL', $createdDate);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title><?=getMessage('SEC_OTP_RECOVERY_TITLE')?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
	<script type="application/javascript">
		var __readyHandler = null;

		/* ready */
		if (document.addEventListener)
		{
			__readyHandler = function()
			{
				document.removeEventListener('DOMContentLoaded', __readyHandler, false);
				onReady();
			}
		}
		else if (document.attachEvent)
		{
			__readyHandler = function()
			{
				if (document.readyState === 'complete')
				{
					document.detachEvent('onreadystatechange', __readyHandler);
					onReady();
				}
			}
		}

		function bindReady()
		{
			if (document.readyState === 'complete')
			{
				return onReady();
			}

			if (document.addEventListener)
			{
				document.addEventListener('DOMContentLoaded', __readyHandler, false);
			}
			else if (document.attachEvent) // IE
			{
				document.attachEvent('onreadystatechange', __readyHandler);
			}
		}

		function onReady()
		{
			setTimeout(window.print, 100);
			setTimeout(window.close, 1000);
		}

		bindReady();
	</script>
</head>
<body>
	<h3>
		<?=getMessage('SEC_OTP_RECOVERY_TITLE')?>
	</h3>
	<p>
		<?=getMessage('SEC_OTP_RECOVERY_ISSUER', array(
			'#ISSUER#' => htmlspecialcharsbx($issuer)
		))?>
		<br />
		<?=getMessage('SEC_OTP_RECOVERY_LOGIN', array(
			'#LOGIN#' => htmlspecialcharsbx($label)
		))?>
		<?if ($createdDate):?>
			<br />
			<?=getMessage('SEC_OTP_RECOVERY_CREATED', array(
				'#DATE#' => htmlspecialcharsbx($createdDate)
			))?>
		<?endif?>
	<ol>
		<?foreach ($codes as $code):?>
			<li style="clear: both;"><?=htmlspecialcharsbx($code)?></li>
		<?endforeach;?>
	</ol>
	<p>
		<?=getMessage('SEC_OTP_RECOVERY_NOTE')?>
	</p>
</body>
</html>