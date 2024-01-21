<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Web\Json;
use Bitrix\Security\Mfa\Otp;
use Bitrix\Security\Mfa\RecoveryCodesTable;

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

header('Content-Type: application/json');

$request = Bitrix\Main\Context::getCurrent()->getRequest();

if (isset($request['user']))
	$userId = (int) $request['user'];
else
	$userId = (int) $USER->getId();

if (!CModule::IncludeModule('security'))
{
	response(array(
		'status' => 'error',
		'error' => 'SECURITY_NOT_INSTALLED'
	));
}

if (!$request->isPost())
{
	response(array(
		'status' => 'error',
		'error' => 'INVALID_METHOD'
	));
}

if (!check_bitrix_sessid())
{
	response(array(
		'status' => 'error',
		'error' => 'INVALID_SESSID'
	));
}

if (
	!$userId
	|| ($userId != $USER->getId() && !$USER->CanDoOperation('security_edit_user_otp'))
)
{
	response(array(
		'status' => 'error',
		'error' => 'PERMISSIONS_CHECKING_ERROR'
	));
}


switch($request->getPost('action'))
{
	case 'get_vew_params':
		response(array(
			'status' => 'ok',
			'data' => getViewParams($userId, $request->getPost('type'))
		));
		break;

	case 'check_activate':
		if (
			$request->getPost('secret') === null
			|| $request->getPost('sync1') === null
		)
		{
			$result = array(
				'status' => 'error',
				'error' => 'NOT_ENOUGH_PARAMS'
			);
		}
		else
		{
			$fields = array(
				'ACTIVE' => 'Y',
				'USER_ID' => $userId,
				'SECRET' => $_POST['secret'],
				'TYPE' => $_POST['type'],
				'INIT_PARAMS' => [],
				'SYNC1' => $_POST['sync1'],
				'SYNC2' => $_POST['sync2'] ?? '',
			);

			if ($_POST['type'] === Otp::TYPE_TOTP)
			{
				$fields['INIT_PARAMS'] = [
					'startTimestamp' => (int)($_POST['startTimestamp'] ?? 0),
				];
			}

			$result = checkAndActivate($fields);
		}

		response($result);
		break;

	case 'deactivate':
		$result = deactivate($userId, $request->getPost('days'));
		response($result);
		break;

	case 'deffer':
		$result = deffer($userId, $request->getPost('days'));
		response($result);
		break;

	case 'activate':
		$result = activate($userId);
		response($result);
		break;

	case 'get_recovery_codes':
		$result = getRecoveryCodes($userId, $request->getPost('allow_regenerate') === 'Y');
		response($result);
		break;

	case 'regenerate_recovery_codes':
		$result = regenerateRecoveryCodes($userId);
		response($result);
		break;

	default:
		response(array(
			'status' => 'error',
			'error' => 'ACTION_NOT_FOUND'
		));
}

function response($result)
{
	CMain::FinalActions(Json::encode($result));
}

function getViewParams($userId, $type = null)
{
	$otp = Otp::getByUser($userId);
	$otp->regenerate();
	if ($type)
		$otp->setType($type);

	$result = array();
	$result['secret'] = $otp->getHexSecret();
	$result['type'] = $otp->getType();
	$result['appSecret'] = $otp->getAppSecret();
	$result['appSecretSpaced'] = trim(chunk_split($result['appSecret'], 4, ' '));
	$result['provisionUri'] = $otp->getProvisioningUri();
	$result['isTwoCodeRequired'] = $otp->getAlgorithm()->isTwoCodeRequired();

	return $result;
}

function checkAndActivate($fields)
{
	try
	{
		$otp = Otp::getByUser($fields['USER_ID']);

		if(preg_match("/[^[:xdigit:]]/i", $fields['SECRET']))
		{
			$binarySecret = $fields['SECRET'];
		}
		else
		{
			$binarySecret = pack('H*', $fields['SECRET']);
		}

		$otp
			->regenerate($binarySecret)
			->setType($fields['TYPE'])
			->setInitParams($fields['INIT_PARAMS'])
			->syncParameters($fields['SYNC1'], $fields['SYNC2'])
			->save()
		;

		$result = array(
			'status' => 'ok'
		);
	}
	catch (\Bitrix\Security\Mfa\OtpException $e)
	{
		$result = array(
			'status' => 'error',
			'error' => $e->getMessage()
		);
	}

	return $result;
}

function deactivate($userId, $days=0)
{
	/* @global CUser $USER */
	global $USER;

	if (
		(Otp::isMandatoryUsing() || $userId != $USER->GetID())
		&& !$USER->CanDoOperation('security_edit_user_otp')
	)
	{
		return array(
			'status' => 'error',
			'error' => 'permissions check'
		);
	}

	try
	{
		$otp = Otp::getByUser($userId);
		$otp->deactivate($days);

		$result = array(
			'status' => 'ok'
		);
	}
	catch (\Bitrix\Security\Mfa\OtpException $e)
	{
		$result = array(
			'status' => 'error',
			'error' => $e->getMessage()
		);
	}

	return $result;
}

function deffer($userId, $days=0)
{
	/* @global CUser $USER */
	global $USER;

	if (
		(Otp::isMandatoryUsing() || $userId != $USER->GetID())
		&& !$USER->CanDoOperation('security_edit_user_otp')
	)
	{
		return array(
			'status' => 'error',
			'error' => 'permissions check'
		);
	}

	try
	{
		$otp = Otp::getByUser($userId);
		$otp->defer($days);

		$result = array(
			'status' => 'ok'
		);
	}
	catch (\Bitrix\Security\Mfa\OtpException $e)
	{
		$result = array(
			'status' => 'error',
			'error' => $e->getMessage()
		);
	}

	return $result;
}

function activate($userId)
{
	try
	{
		$otp = Otp::getByUser($userId);
		$otp->activate();

		$result = array(
			'status' => 'ok'
		);
	}
	catch (\Bitrix\Security\Mfa\OtpException $e)
	{
		$result = array(
			'status' => 'error',
			'error' => $e->getMessage()
		);
	}

	return $result;
}

function getRecoveryCodes($userId, $isRegenerationAllowed = false)
{
	$codes = RecoveryCodesTable::getList(array(
		'select' => array('CODE', 'USED', 'USING_DATE'),
		'filter' => array('=USER_ID' => $userId)
	));

	$normalizedCodes = array();
	while (($code = $codes->fetch()))
	{
		/** @var Bitrix\Main\Type\DateTime $date */
		$date = $code['USING_DATE'];
		$normalizedCodes[] = array(
			'value' => $code['CODE'],
			'used' => $code['USED'] === 'Y',
			'using_date' => $date?->getTimestamp(),
		);
	}

	if (empty($normalizedCodes) && $isRegenerationAllowed)
		return regenerateRecoveryCodes($userId);
	else
		return array(
			'status' => 'ok',
			'codes' => $normalizedCodes
		);
}

function regenerateRecoveryCodes($userId)
{
	if (!Otp::getByUser($userId)->isActivated())
		ShowError('OTP inactive');

	CUserOptions::SetOption('security', 'recovery_codes_generated', time());
	RecoveryCodesTable::regenerateCodes($userId);
	return getRecoveryCodes($userId);
}
