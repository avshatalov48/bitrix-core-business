<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Security\Mfa\Otp;

class CSecurityUserOtpInitAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function setOtpAction($secret, $sync1, $sync2 = "", $otpAction)
	{
		/** @global CUser $USER */
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->addError(new \Bitrix\Main\Error('auth_error'));
			return false;
		}

		if ($otpAction !== 'otp_check_activate')
		{
			$this->addError(new \Bitrix\Main\Error('unknown_action'));
			return false;
		}

		if (!\Bitrix\Main\Loader::includeModule('security'))
		{
			$this->addError(new \Bitrix\Main\Error('security_not_installed'));
			return false;
		}

		try
		{
			$otp = Otp::getByUser($USER->getid());
			if(preg_match("/[^[:xdigit:]]/i", $secret))
			{
				$binarySecret = $secret;
			}
			else
			{
				$binarySecret = pack('H*', $secret);
			}
			$otp
				->regenerate($binarySecret)
				->syncParameters($sync1, $sync2)
				->save()
			;

			return array(
				'status' => 'ok'
			);
		}
		catch (\Bitrix\Security\Mfa\OtpException $e)
		{
			$this->addError(new \Bitrix\Main\Error($e->getMessage()));
			return false;
		}
	}
}
