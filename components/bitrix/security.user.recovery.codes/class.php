<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Type;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Security\Mfa\Otp;
use Bitrix\Security\Mfa\RecoveryCodesTable;

Loc::loadMessages(__FILE__);

class CSecurityUserRecoveryCodesComponent
	extends CBitrixComponent
{
	const VIEW_PAGE = 'template';
	const PRINT_PAGE = 'print';

	public function onPrepareComponentParams($arParams)
	{
		/** @global CUser $USER */
		global $USER;

		$result = array(
			'USER_ID' => $USER->getId(),
			'MODE' => $arParams["MODE"] ? $arParams["MODE"] : "",
			'PATH_TO_CODES' => $arParams["PATH_TO_CODES"] ? $arParams["PATH_TO_CODES"] : ""
		);
		return $result;
	}

	public function executeComponent()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$action = $this->request['codesAction'];

		if ($this->arParams["MODE"] == "print")
		{
			$action = "print";
		}
		elseif ($this->arParams["MODE"] == "download")
		{
			$action = "download";
		}

		$APPLICATION->SetTitle(Loc::getMessage("SECURITY_USER_RECOVERY_CODES_TITLE"));
		$this->arResult = $this->toView($action);

		$this->doPostAction($action);
	}

	protected function doPostAction($action)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		switch ($action)
		{
			case 'download':
				$APPLICATION->restartBuffer();
				header('Content-Type: text/plain', true);
				header('Content-Disposition: attachment; filename="recovery_codes.txt"');
				header('Content-Transfer-Encoding: binary');
				header(sprintf('Content-Length: %d', strlen($this->arResult['PLAIN_RESPONSE'])));
				echo $this->arResult['PLAIN_RESPONSE'];
				exit;
				break;
			case 'print':
			//	$APPLICATION->restartBuffer();
				$this->includeComponentTemplate(static::PRINT_PAGE);
			//	exit;
				break;
			case 'view':
			default:
				$this->includeComponentTemplate(static::VIEW_PAGE);
				break;
		}
	}

	/**
	 * @param string $action
	 * @return array
	 */
	protected function toView($action = null)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$error = $this->checkRequirements();
		if ($error)
		{
			return array(
				'MESSAGE' => $error
			);
		};

		$result = array();

		switch ($action)
		{
			case 'download':
				$codes = $this->getRecoveryCodes(true, true);
				$response = '';
				$counter = 0;
				foreach ($codes as $code)
				{
					$counter++;
					$response .= sprintf("%d. %s\r\n", $counter, $code['VALUE']);
				}
				$result['PLAIN_RESPONSE'] = $response;
				break;
			case 'print':
				$result['CODES'] = $this->getRecoveryCodes(true, true);
				break;
			case 'view':
			default:
				$result['CODES'] = $this->getRecoveryCodes(false, true);
				break;
		}

		$result['ISSUER'] = Option::get('main', 'server_name');
		if (!$result['ISSUER'])
			$result['ISSUER'] = Option::get('security', 'otp_issuer', 'Bitrix');

		$result['CREATE_DATE'] = CUserOptions::GetOption('security', 'recovery_codes_generated', null);
		if ($result['CREATE_DATE'])
			$result['CREATE_DATE'] = Type\DateTime::createFromTimestamp($result['CREATE_DATE']);

		return $result;
	}

	/**
	 * @param string $action
	 * @return array
	 */
	protected function toEdit($action = null)
	{
		$error = $this->checkRequirements();
		if ($error)
		{
			return array(
				'status' => 'error',
				'error' => $error
			);
		};

		$result = array();

		switch ($action)
		{
			case 'regenerate':
				$result['status'] = 'ok';
				$result['codes'] = $this->regenerateRecoveryCodes();
				break;
			default:
				$result['status'] = 'error';
				$result['error'] = 'UNKNOWN_ACTION';
				break;
		}
		return $result;
	}

	protected function getRecoveryCodes($isActiveOnly = false, $isRegenerationAllowed = false)
	{
		$query = RecoveryCodesTable::query()
			->addSelect('CODE', 'VALUE')
			->addSelect('USED')
			->addSelect('USING_DATE')
			->addFilter('=USER_ID', $this->arParams['USER_ID'])
		;
		if ($isActiveOnly)
			$query->addFilter('=USED', 'N');

		$codes = $query->exec()->fetchAll();
		if (is_array($codes) && !empty($codes))
		{
			return $codes;
		}
		elseif ($isRegenerationAllowed)
		{
			return $this->regenerateRecoveryCodes();
		}
		else
		{
			return array();
		}

	}

	protected function regenerateRecoveryCodes()
	{
		CUserOptions::SetOption('security', 'recovery_codes_generated', time());
		RecoveryCodesTable::regenerateCodes($this->arParams['USER_ID']);
		return $this->getRecoveryCodes(false, false);
	}

	protected function checkRequirements()
	{
		/** @global CUser $USER */
		global $USER;

		if (!$USER->IsAuthorized())
		{
			return Loc::getMessage("SECURITY_USER_RECOVERY_CODES_AUTH_ERROR");
		}

		if (!CModule::includeModule('security'))
		{
			return Loc::getMessage("SECURITY_USER_RECOVERY_CODES_MODULE_ERROR");
		}

		$otp = Otp::getByUser($USER->getID());

		if (!$otp->isActivated())
		{
			return Loc::getMessage("SECURITY_USER_RECOVERY_CODES_OTP_NOT_ACTIVE");
		}

		if (!Otp::isRecoveryCodesEnabled())
			return Loc::getMessage("SECURITY_USER_RECOVERY_CODES_DISABLED");

		return null;
	}
}
