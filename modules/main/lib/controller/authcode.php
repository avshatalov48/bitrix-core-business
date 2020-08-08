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

class AuthCode extends Main\Engine\Controller
{
	const SIGNATURE_SALT = 'phone_auth_email';

	/**
	 * Sends a email with a generated code.
	 * @param string $login User input
	 * @return array|null
	 */
	public function sendEmailAction($login)
	{
		if($login == '')
		{
			$this->addError(new Main\Error(Loc::getMessage("main_authcode_incorrect_request"), "ERR_PARAMS"));
			return null;
		}

		$result = \CUser::SendPassword($login, "", false, "", 0, "", true);

		/** @var Main\Result $checkResult */
		$checkResult = $result["RESULT"];
		if($checkResult)
		{
			$intervals = $checkResult->getData();
		}
		else
		{
			$intervals = [];
		}

		if($result["TYPE"] == "ERROR")
		{
			$errorCode = ($checkResult? "ERR_TIMEOUT" : "ERR_NOT_FOUND");
			$this->addError(new Main\Error($result["MESSAGE"], $errorCode, $intervals));
			return null;
		}

		return [
			'signedData' => static::signData(['userId' => $result["USER_ID"]]),
			'intervals' => $intervals,
		];
	}

	/**
	 * Verifies the code and authorizes the user on success.
	 * @param string $code User input
	 * @param string $signedData Expected to be sent back from sendEmail() result
	 * @return bool|null
	 */
	public function confirmAction($code, $signedData)
	{
		global $USER;

		if(($params = static::extractData($signedData)) === false)
		{
			$this->addError(new Main\Error(Loc::getMessage("main_authcode_incorrect_request"), "ERR_SIGNATURE"));
			return null;
		}

		if(!preg_match('/^[0-9]{6}$/', $code))
		{
			$this->addError(new Main\Error(Loc::getMessage("main_authcode_incorrect_code"), "ERR_FORMAT_CODE"));
			return null;
		}

		$context = new Main\Authentication\Context();
		$context->setUserId($params["userId"]);

		$shortCode = new Main\Authentication\ShortCode($context);

		$result = $shortCode->verify($code);

		if($result->isSuccess())
		{
			if(!$USER->IsAuthorized() && $shortCode->getUser()->getActive())
			{
				$USER->Authorize($params["userId"]);
			}
			return true;
		}
		else
		{
			//replace the error message with the more specific one
			if($result->getErrorCollection()->getErrorByCode("ERR_CONFIRM_CODE") !== null)
			{
				$this->addError(new Main\Error(Loc::getMessage("main_authcode_incorrect_code_input"), 'ERR_CONFIRM_CODE'));
			}
			if($result->getErrorCollection()->getErrorByCode("ERR_RETRY_COUNT") !== null)
			{
				$this->addError(new Main\Error(Loc::getMessage("main_authcode_retry_count"), "ERR_RETRY_COUNT"));
			}
			return null;
		}
	}

	public function configureActions()
	{
		return [
			'sendEmail' => [
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
	 * @param array $data
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
