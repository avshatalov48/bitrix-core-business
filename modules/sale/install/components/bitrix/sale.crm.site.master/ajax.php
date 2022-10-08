<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Class SaleCrmSiteMasterAjaxController
 */
class SaleCrmSiteMasterAjaxController extends Bitrix\Main\Engine\Controller
{
	/**
	 * @param $key
	 */
	public function updateLicenseKeyAction($key)
	{
		$key = mb_strtoupper($key);
		if ($this->isLicenseKey($key))
		{
			$this->updateLicenseKey($key);
		}
	}

	/**
	 * @param $key
	 * @return false|int
	 */
	private function isLicenseKey($key)
	{
		return preg_match(
			'/^([A-Z0-9]{3}-[A-Z0-9]{2}-[A-Z0-9]{12,16})$/',
			$key
		);
	}

	/**
	 * @param $key
	 */
	private function updateLicenseKey($key)
	{
		if ($fp = fopen($_SERVER['DOCUMENT_ROOT']."/bitrix/license_key.php", "wb"))
		{
			fwrite($fp, '<'.'?$LICENSE_KEY = "'.\EscapePHPString($key).'";?'.'>');
			fclose($fp);
		}
		else
		{
			$this->errorCollection[] = new Bitrix\Main\Error(Loc::getMessage("SALE_CSM_LICENSE_FILE_WRITE_ERROR"));
		}
	}

	public function checkUpdateSystemAction()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

		try
		{
			$stableVersionsOnly = Option::get("main", "stable_versions_only", "N");
		}
		catch (\Exception $ex)
		{
			$stableVersionsOnly = "N";
		}

		$arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, LANGUAGE_ID, $stableVersionsOnly);
		if (isset($arUpdateList["REPAIR"]))
		{
			\CUpdateClient::Repair($arUpdateList["REPAIR"][0]["@"]["TYPE"], $stableVersionsOnly, LANGUAGE_ID);
		}
	}
}