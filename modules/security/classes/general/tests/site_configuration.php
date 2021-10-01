<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage security
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\SystemException;

/**
 * Class CSecuritySiteConfigurationTest
 * @since 12.5.0
 */
class CSecuritySiteConfigurationTest
	extends CSecurityBaseTest
{
	protected $internalName = "SiteConfigurationTest";

	protected $tests = array(
		"securityLevel" => array(
			"method" => "checkSecurityLevel"
		),
		"errorReporting" => array(
			"method" => "checkErrorReporting",
			"base_message_key" => "SECURITY_SITE_CHECKER_ERROR_REPORTING",
			"critical" => CSecurityCriticalLevel::MIDDLE
		),
		"exceptionDebug" => array(
			"method" => "checkExceptionDebug",
			"base_message_key" => "SECURITY_SITE_CHECKER_EXCEPTION_DEBUG",
			"critical" => CSecurityCriticalLevel::HIGHT
		),
		"dbDebug" => array(
			"method" => "checkDbDebug",
			"base_message_key" => "SECURITY_SITE_CHECKER_DB_DEBUG",
			"critical" => CSecurityCriticalLevel::HIGHT
		),
		"dbPassword" => array(
			"method" => "checkDbPassword"
		),
		"scriptExtension" => array(
			"method" => "checkScriptExtension"
		),
		"modulesVersion" => array(
			"method" => "checkModulesVersion"
		)
	);

	protected static $expectedScriptExtensions = "php,php3,php4,php5,php6,phtml,pl,asp,aspx,cgi,dll,exe,ico,shtm,shtml,fcg,fcgi,fpl,asmx,pht,py,psp";

	public function __construct()
	{
		IncludeModuleLangFile(__FILE__);
	}

	/**
	 * Check if saved script file extension is up to date
	 */
	protected function checkScriptExtension()
	{
		$actualExtensions = getScriptFileExt();
		$missingExtensions = array_diff(
			explode(",", self::$expectedScriptExtensions),
			$actualExtensions
		);

		if(!empty($missingExtensions))
		{
			$this->addUnformattedDetailError(
				"SECURITY_SITE_CHECKER_DANGER_EXTENSIONS",
				CSecurityCriticalLevel::HIGHT,
				getMessage("SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_ADDITIONAL", array(
					"#EXPECTED#" => self::$expectedScriptExtensions,
					"#ACTUAL#" => join(",", $actualExtensions),
					"#MISSING#" => join(",", $missingExtensions)
				))
			);
			return self::STATUS_FAILED;
		}

		return self::STATUS_PASSED;
	}

	protected function checkSecurityLevel()
	{
		$isFailed = false;
		if(!CSecurityFilter::IsActive())
		{
			$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_WAF_OFF", CSecurityCriticalLevel::HIGHT);
			$isFailed = true;
		}
		if(!CSecurityRedirect::IsActive())
		{
			$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_REDIRECT_OFF", CSecurityCriticalLevel::MIDDLE);
			$isFailed = true;
		}

		$adminPolicy = \CCheckListTools::AdminPolicyLevel();
		if($adminPolicy != "high")
		{
			$this->addUnformattedDetailError(
				"SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL",
				$adminPolicy == "middle" ? CSecurityCriticalLevel::LOW : CSecurityCriticalLevel::HIGHT
			);
			$isFailed = true;
		}

		if($isFailed)
			return self::STATUS_FAILED;
		else
			return self::STATUS_PASSED;
	}

	/**
	 * Return true if debug = off
	 *
	 * @return bool
	 * @since 14.0.0
	 */
	protected function checkDbDebug()
	{
		/** @global CDataBase $DB */
		global $DB;

		if($DB->debug)
			return self::STATUS_FAILED;
		else
			return self::STATUS_PASSED;
	}

	/**
	 * Return true if error_reporting = 0
	 *
	 * @return bool
	 * @since 14.0.0
	 */
	protected function checkErrorReporting()
	{
		$validErrorReporting = E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE;
		if (
			COption::GetOptionInt("main", "error_reporting", $validErrorReporting) != $validErrorReporting
			&& COption::GetOptionInt("main","error_reporting","") != 0
		)
			return self::STATUS_FAILED;
		else
			return self::STATUS_PASSED;
	}

	/**
	 * Return true if exception_handling debug = false
	 *
	 * @return bool
	 * @since 14.0.0
	 */
	protected function checkExceptionDebug()
	{
		$exceptionConfig = \Bitrix\Main\Config\Configuration::getValue('exception_handling');
		if(
			is_array($exceptionConfig)
			&& isset($exceptionConfig['debug'])
			&& $exceptionConfig['debug']
		)
			return self::STATUS_FAILED;
		else
			return self::STATUS_PASSED;
	}

	/**
	 * Return true if module updates available
	 *
	 * @return bool
	 * @since 14.0.2
	 */
	protected function checkModulesVersion()
	{
		try
		{
			$updates = static::getAvailableUpdates();
			if(!empty($updates))
			{
				$this->addUnformattedDetailError(
					"SECURITY_SITE_CHECKER_MODULES_VERSION",
					CSecurityCriticalLevel::HIGHT,
					getMessage("SECURITY_SITE_CHECKER_MODULES_VERSION_ARRITIONAL", array(
						"#MODULES#" => nl2br(htmlspecialcharsbx(join("\n", $updates)))
					))
				);
				return self::STATUS_FAILED;
			}
		}
		catch (SystemException $e)
		{
			$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR", CSecurityCriticalLevel::HIGHT);
			return self::STATUS_FAILED;
		}

		return self::STATUS_PASSED;
	}

	protected function checkDbPassword()
	{
		/** @global CDataBase $DB */
		global $DB;
		$password = $DB->DBPassword;
		$sign = ",.#!*%$:-^@{}[]()'\"-+=<>?`&;";
		$dit = "1234567890";
		if(trim($password) == "")
		{
			$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_DB_EMPTY_PASS", CSecurityCriticalLevel::HIGHT);
		}
		else
		{
			if($password == mb_strtolower($password))
			{
				$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS", CSecurityCriticalLevel::HIGHT);
			}
			if(strpbrk($password, $sign) === false)
			{
				$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS", CSecurityCriticalLevel::HIGHT);
			}
			if(strpbrk($password, $dit) === false)
			{
				$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_DB_NO_DIT_PASS", CSecurityCriticalLevel::HIGHT);
			}
			if (mb_strlen($password) < 8)
			{
				$this->addUnformattedDetailError("SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS", CSecurityCriticalLevel::HIGHT);
			}
		}
	}

	/**
	 * @since 14.0.7
	 * @return array
	 * @throws Bitrix\Main\SystemException
	 */
	protected static function getAvailableUpdates()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php');

		$errors = null;
		$installedModules = CUpdateClient::GetCurrentModules($errors);
		if ($errors !== null)
			throw new SystemException($errors);

		$stableVersionsOnly = COption::GetOptionString('main', 'stable_versions_only', 'Y');
		$errors = null;
		$updateList = CUpdateClient::GetUpdatesList($errors, LANG, $stableVersionsOnly);
		if ($errors !== null)
			throw new SystemException($errors);

		if (
			!isset($updateList['MODULES'])
			|| !is_array($updateList['MODULES'])
			|| !isset($updateList['MODULES'][0]['#'])
		)
		{
			throw new SystemException('Empty update modules list');
		}

		$result = array();
		if (!$updateList['MODULES'][0]['#'])
		{
			return $result;
		}

		if (
			!isset($updateList['MODULES'][0]['#']['MODULE'])
			|| !is_array($updateList['MODULES'][0]['#']['MODULE'])
		)
		{
			throw new SystemException('Empty update module list');
		}

		foreach ($updateList['MODULES'][0]['#']['MODULE'] as $module)
		{
			if (array_key_exists($module['@']['ID'], $installedModules))
				$result[] = $module['@']['ID'];
		}

		return $result;
	}
}
