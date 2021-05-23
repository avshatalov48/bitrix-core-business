<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Security\Mfa\Otp;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CSecurityAuthOtpMandatory
	extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{

		$result = array(
			'AUTH_LOGIN_URL' => $arParams['AUTH_LOGIN_URL']
		);
		if (in_array($arParams['NOT_SHOW_LINKS'], array('Y', 'N'), true))
			$result['NOT_SHOW_LINKS'] = $arParams['NOT_SHOW_LINKS'];
		else
			$result['NOT_SHOW_LINKS'] = 'N';

		return $result;
	}

	public function executeComponent()
	{
		// get data for new OTP connection
		$this->arResult = $this->toView();
		$this->IncludeComponentTemplate();
	}

	/**
	 * @return array
	 */
	protected function toView()
	{
		/* @global CUser $USER */
		global $USER;

		if (!CModule::includeModule('security'))
		{
			return array(
				'MESSAGE' => Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_MODULE_ERROR')
			);
		}

		if (!Otp::isOtpRequiredByMandatory())
		{
			return array(
				'MESSAGE' =>  Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_NOT_REQUIRED')
			);
		}

		if ($USER->IsAuthorized())
		{
			return array(
				'MESSAGE' =>  Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_AUTH_ERROR')
			);
		}

		$deferredParams = Otp::getDeferredParams();
		if (!$deferredParams['USER_ID'])
		{
			return array(
				'MESSAGE' => Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_UNKNOWN_ERROR')
			);
		}

		$result = array();
		$otp = Otp::getByUser($deferredParams['USER_ID']);
		$otp->regenerate();
		$result['SECRET'] = $otp->getHexSecret();
		$result['TYPE'] = $otp->getType();
		$result['APP_SECRET'] = $otp->getAppSecret();
		$result['APP_SECRET_SPACED'] = chunk_split($result['APP_SECRET'], 4, ' ');
		$result['PROVISION_URI'] = $otp->getProvisioningUri();
		$result['SUCCESSFUL_URL'] = $this->arParams['SUCCESSFUL_URL'];
		$result['TWO_CODE_REQUIRED'] = $otp->getAlgorithm()->isTwoCodeRequired();
		$result['OTP'] = $otp;
		return $result;
	}
}
