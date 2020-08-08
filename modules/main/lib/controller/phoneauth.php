<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Controller;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class PhoneAuth extends Main\Engine\Controller
{
	const SIGNATURE_SALT = 'phone_auth_sms';

	public function resendCodeAction($signedData)
	{
		if(($params = static::extractData($signedData)) === false)
		{
			$this->addError(new Main\Error(Loc::getMessage("main_register_incorrect_request"), "ERR_SIGNATURE"));
			return null;
		}
		if($params["phoneNumber"] == '')
		{
			$this->addError(new Main\Error(Loc::getMessage("main_register_incorrect_request"), "ERR_PARAMS"));
			return null;
		}
		if($params["smsTemplate"] == '')
		{
			$params["smsTemplate"] = "SMS_USER_CONFIRM_NUMBER";
		}

		$result = \CUser::SendPhoneCode($params["phoneNumber"], $params["smsTemplate"]);

		if(!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'DATA_SIGN' => static::signData([
				'phoneNumber' => $params["phoneNumber"],
				'smsTemplate' => $params["smsTemplate"]
			]),
			'DATE_SEND' => \CUser::PHONE_CODE_RESEND_INTERVAL,
		];
	}

	public function confirmAction($code, $signedData)
	{
		global $USER;

		try
		{
			$signer = new Main\Security\Sign\Signer();
			$userId = $signer->unsign($signedData, static::SIGNATURE_SALT);
		}
		catch(\Bitrix\Main\SystemException $exception)
		{
			$this->addError(new Main\Error(Loc::getMessage('main_register_incorrect_request'), 'ERR_SIGNATURE'));
			return null;
		}

		if(!preg_match('/^[0-9]{6}$/', $code))
		{
			$this->addError(new Main\Error(Loc::getMessage('main_err_confirm_code_format'), 'ERR_CONFIRM_CODE'));
			return null;
		}

		$phoneRecord = Main\UserPhoneAuthTable::getList([
			'filter' => [
				'=USER_ID' => $userId
			],
			'select' => ['USER_ID', 'PHONE_NUMBER', 'USER.ID', 'USER.ACTIVE'],
		])->fetchObject();

		if(!$phoneRecord)
		{
			$this->addError(new Main\Error(Loc::getMessage('main_register_no_user'), 'ERR_NOT_FOUND'));
			return null;
		}

		if(\CUser::VerifyPhoneCode($phoneRecord->getPhoneNumber(), $code))
		{
			if($phoneRecord->getUser()->getActive() && !$USER->IsAuthorized())
			{
				$USER->Authorize($userId);
			}

			return true;
		}
		else
		{
			$this->addError(new Main\Error(Loc::getMessage('main_err_confirm'), 'ERR_CONFIRM_CODE'));
			return null;
		}
	}

	public function configureActions()
	{
		return [
			'resendCode' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'confirm' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
		];
	}

	/**
	 * @param array $data 'phoneNumber' key is required
	 * @return string
	 */
	public static function signData(array $data)
	{
		$signer = new Main\Security\Sign\Signer();
		$string = base64_encode(serialize($data));
		return $signer->sign($string, static::SIGNATURE_SALT);
	}

	/**
	 * @param string $signedData
	 * @return bool|array
	 */
	public static function extractData($signedData)
	{
		try
		{
			$signer = new Main\Security\Sign\Signer();
			$string = $signer->unsign($signedData, static::SIGNATURE_SALT);
			return unserialize(base64_decode($string));
		}
		catch(Main\SystemException $exception)
		{
			return false;
		}
	}
}
