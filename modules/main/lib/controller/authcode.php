<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Controller;

use Bitrix\Main;
use Bitrix\Main\Component;
use Bitrix\Main\Localization\Loc;
use Bitrix\Security\Mfa;

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
			'signedData' => Component\ParameterSigner::signParameters(
				self::SIGNATURE_SALT,
				['userId' => $result["USER_ID"]]
			),
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

		try
		{
			$params = Component\ParameterSigner::unsignParameters(self::SIGNATURE_SALT, $signedData);
		}
		catch(Main\SystemException $e)
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
			$codeUser = $shortCode->getUser();
			if(!$USER->IsAuthorized() && $codeUser->getActive() && !$codeUser->getBlocked())
			{
				if(Main\Loader::includeModule("security"))
				{
					if(Mfa\Otp::verifyUser(["USER_ID" => $params["userId"]]) == false)
					{
						$this->addError(new Main\Error(Loc::getMessage("main_authcode_otp_required"), 'ERR_OTP_REQUIRED'));

						$this->checkOtpCaptcha();

						return null;
					}
				}
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

	/**
	 * Verifies the code and authorizes the user on success.
	 * @param string $otp OTP code
	 * @param string $captchaSid If needed
	 * @param string $captchaWord If needed
	 * @return bool|null
	 */
	public function loginByOtpAction($otp, $captchaSid = "", $captchaWord = "")
	{
		global $USER;

		$authResult = $USER->LoginByOtp($otp, "N", $captchaWord, $captchaSid);

		if($authResult !== true)
		{
			$this->addError(new Main\Error($authResult["MESSAGE"], "ERR_OTP_CODE"));

			if(Main\Loader::includeModule("security"))
			{
				$this->checkOtpCaptcha();
			}
			return null;
		}

		return true;
	}

	protected function checkOtpCaptcha()
	{
		global $APPLICATION;

		if(Mfa\Otp::isCaptchaRequired())
		{
			$this->addError(
				new Main\Error(
					Loc::getMessage("main_authcode_otp_captcha_required"),
					'ERR_OTP_CAPTCHA_REQUIRED',
					[
						"captchaSid" => $APPLICATION->CaptchaGetCode(),
					]
				)
			);
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
			'loginByOtp' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
		];
	}
}
