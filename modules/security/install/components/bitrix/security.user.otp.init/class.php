<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Security\Mfa\Otp;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class CSecurityUserOtpInit
	extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$result = array();
		if (
			$arParams['SUCCESSFUL_URL']
			&& preg_match('#^(?:/|https?://)#', $arParams['SUCCESSFUL_URL'])
		)
		{
			$result['SUCCESSFUL_URL'] = $arParams['SUCCESSFUL_URL'];
		}
		else
		{
			$result['SUCCESSFUL_URL'] = '/';
		}


		if (
			$arParams['SHOW_DESCRIPTION']
			&& preg_match('#^(Y|N)$#', $arParams['SHOW_DESCRIPTION'])
		)
		{
			$result['SHOW_DESCRIPTION'] = $arParams['SHOW_DESCRIPTION'];
		}
		else
		{
			$result['SHOW_DESCRIPTION'] = 'Y';
		}

		return $result;
	}

	public function executeComponent()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (
			$this->request->isPost()
			&& $this->request['action']
		)
		{
			// try to connect
			$result = $this->toEdit();
			$result = Json::encode($result);
			$APPLICATION->RestartBuffer();
			header('Content-Type: application/json', true);
			echo $result;
			die();
		}
		else
		{
			$APPLICATION->SetTitle(Loc::getMessage("SECURITY_OTP_TITLE"));
			// get data for new OTP connection
			$this->arResult = $this->toView();
			$this->IncludeComponentTemplate();
		}
	}

	/**
	 * @return array
	 */
	protected function toView()
	{
		/** @global CUser $USER */
		global $USER;

		if (!$USER->IsAuthorized())
		{
			return array(
				'MESSAGE' => Loc::getMessage("SECURITY_OTP_AUTH_ERROR")
			);
		}

		if (!CModule::includeModule('security'))
		{
			return array(
				'MESSAGE' => Loc::getMessage("SECURITY_OTP_MODULE_ERROR")
			);
		}

		$result = array();
		$otp = Otp::getByUser($USER->getid());
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

	/**
	 * @return array
	 */
	protected function toEdit()
	{
		/** @global CUser $USER */
		global $USER;

		if (!$USER->IsAuthorized())
		{
			return array(
				'status' => 'error',
				'error' => 'auth_error'
			);
		}

		if (!check_bitrix_sessid())
		{
			return array(
				'status' => 'error',
				'error' => 'sessid_check_failed'
			);
		}

		if ($this->request['action'] !== 'otp_check_activate')
		{
			return array(
				'status' => 'error',
				'error' => 'unknown_action'
			);
		}

		if (!CModule::includeModule('security'))
		{
			return array(
				'status' => 'error',
				'error' => 'security_not_installed'
			);
		}

		try
		{
			$otp = Otp::getByUser($USER->getid());
			if(preg_match("/[^[:xdigit:]]/i", $this->request->getPost('secret')))
			{
				$binarySecret = $this->request->getPost('secret');
			}
			else
			{
				$binarySecret = pack('H*', $this->request->getPost('secret'));
			}
			$otp
				->regenerate($binarySecret)
				->syncParameters($this->request->getPost('sync1'), $this->request->getPost('sync2'))
				->save()
			;

			return array(
				'status' => 'ok'
			);
		}
		catch (\Bitrix\Security\Mfa\OtpException $e)
		{
			return array(
				'status' => 'error',
				'error' => $e->getMessage()
			);
		}
	}
}
