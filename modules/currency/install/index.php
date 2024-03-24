<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\SiteTable;
use Bitrix\Currency\CurrencyClassifier;

Loc::loadMessages(__FILE__);

class currency extends CModule
{
	var $MODULE_ID = 'currency';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $errors = false;

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (!empty($arModuleVersion['VERSION']))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("CURRENCY_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("CURRENCY_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();
		$GLOBALS["errors"] = $this->errors;

		$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = (int)$step;
		if ($step<2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep1.php");
		}
		elseif ($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep2.php");
		}
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$connection = Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_catalog_currency'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/currency/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		ModuleManager::registerModule('currency');

		self::installCurrencies();

		$eventManager = Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockPropertyBuildList', 'currency',
			'\Bitrix\Currency\Integration\IblockMoneyProperty', 'getUserTypeDescription');
		$eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'currency',
			'\Bitrix\Currency\UserField\Money', 'getUserTypeDescription');

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = Main\Application::getConnection();
		$this->errors = false;
		if (Loader::includeModule('currency'))
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".$connection->getType()."/uninstall.sql");
			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode('', $this->errors));
				return false;
			}
		}

		$eventManager = Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', 'currency',
			'\Bitrix\Currency\Integration\IblockMoneyProperty', 'getUserTypeDescription');
		$eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'currency',
			'\Bitrix\Currency\UserField\Money', 'getUserTypeDescription');

		CAgent::RemoveModuleAgents('currency');
		ModuleManager::unRegisterModule('currency');

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/currency", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/currency/install/tools", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/currency/");
		DeleteDirFilesEx("/bitrix/images/currency/");
		DeleteDirFilesEx("/bitrix/js/currency/");
		DeleteDirFilesEx("/bitrix/tools/currency/"); // scripts

		return true;
	}

	protected function installCurrencies()
	{
		if (!Loader::includeModule('currency'))
			return;

		$bitrix24Path = Main\Application::getDocumentRoot().'/bitrix/modules/bitrix24/';
		$bitrix24 = Main\IO\Directory::isDirectoryExists($bitrix24Path);
		unset($bitrix24Path);

		$currencyIterator = \Bitrix\Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY'),
			'limit' => 1
		));
		$currency = $currencyIterator->fetch();
		if (!empty($currency))
			return;

		$baseCurrency = '';
		$b24Area = null;
		if ($bitrix24 && Loader::includeModule('bitrix24'))
		{
			$bitrix24Zone = CBitrix24::getCurrentAreaConfig();
			if (!empty($bitrix24Zone) && is_array($bitrix24Zone))
			{
				$baseCurrency = $bitrix24Zone['CURRENCY'];
				$b24Area = $bitrix24Zone['ID'];
			}
			unset($bitrix24Zone);
		}
		if ($baseCurrency == '')
		{
			$languageId = '';
			$site = SiteTable::getList(array(
				'select' => array('LID', 'LANGUAGE_ID'),
				'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
			))->fetch();
			if (!empty($site))
				$languageId = (string)$site['LANGUAGE_ID'];
			unset($site);

			if ($languageId == '')
				$languageId = 'en';

			$currencyList = array();
			$distrCurrency = array(
				'ua' => 'UAH',
				'kz' => 'KZT',
				'by' => 'BYN',
				'de' => 'EUR',
				'en' => 'USD',
				'la' => 'USD',
				'br' => 'BRL',
				'tc' => 'TWD',
				'sc' => 'CNY',
				'in' => 'INR',
				'hi' => 'INR',
				'ja' => 'JPY',
				'vn' => 'VND',
				'id' => 'IDR',
				'ms' => 'MYR',
				'th' => 'THB'
			);
			if (isset($distrCurrency[$languageId]))
			{
				$baseCurrency = $distrCurrency[$languageId];
			}
			elseif ($languageId == 'ru')
			{
				$languageList = array();
				$languageIterator = LanguageTable::getList(array(
					'select' => array('ID'),
					'filter' => array('@ID' => array('kz', 'by', 'ua'), '=ACTIVE' => 'Y')
				));
				while ($language = $languageIterator->fetch())
					$languageList[$language['ID']] = $language['ID'];
				unset($language, $languageIterator);
				if (isset($languageList['kz']))
					$baseCurrency = 'KZT';
				elseif (isset($languageList['by']))
					$baseCurrency = 'BYN';
				elseif (isset($languageList['ua']))
					$baseCurrency = 'UAH';
				else
					$baseCurrency = 'RUB';
				unset($languageList);
			}
			else
			{
				$baseCurrency = 'USD';
			}
			unset($distrCurrency, $languageId);
		}
		$datetimeEntity = new Main\DB\SqlExpression(Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction());
		$addCurrency = self::getCurrencyListForInstall($baseCurrency);
		foreach ($addCurrency as $fields)
		{
			$fields['CREATED_BY'] = null;
			$fields['MODIFIED_BY'] = null;
			$fields['DATE_CREATE'] = $datetimeEntity;
			$fields['DATE_UPDATE'] = $datetimeEntity;
			$currencyResult = \Bitrix\Currency\CurrencyTable::add($fields);
			if ($currencyResult->isSuccess())
				$currencyList[] = $fields['CURRENCY'];
		}
		unset($currencyResult, $fields);
		unset($addCurrency);

		if (!empty($currencyList))
		{
			Option::set('currency', 'installed_currencies', implode(',', $currencyList), '');
			$languages = [];
			$languageIterator = LanguageTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($existLanguage = $languageIterator->fetch())
				$languages[$existLanguage['ID']] = mb_strtoupper($existLanguage['ID']);
			unset($existLanguage, $languageIterator);
			$whiteList = [
				'FULL_NAME' => true,
				'FORMAT_STRING' => true,
				'DEC_POINT' => true,
				'THOUSANDS_VARIANT' => true,
				'DECIMALS' => true
			];
			foreach($currencyList as $oneCurrency)
			{
				$data = CurrencyClassifier::getCurrency($oneCurrency, array_keys($languages), $b24Area);
				if (empty($data))
					continue;
				foreach ($languages as $languageId => $upperLanguageId)
				{
					if (empty($data[$upperLanguageId]))
						continue;
					$fields = [
						'LID' => $languageId,
						'CURRENCY' => $oneCurrency,
						'CREATED_BY' => null,
						'MODIFIED_BY' => null,
						'DATE_CREATE' => $datetimeEntity,
						'TIMESTAMP_X' => $datetimeEntity,
						'HIDE_ZERO' => 'Y',
						'THOUSANDS_SEP' => null
					] + array_intersect_key($data[$upperLanguageId], $whiteList);
					$fields['FORMAT_STRING'] = str_replace('#VALUE#', '#', $fields['FORMAT_STRING']);
					$resultCurrencyLang = \Bitrix\Currency\CurrencyLangTable::add($fields);
					unset($resultCurrencyLang);
				}
				unset($languageId, $upperLanguageId);
			}
			unset($oneCurrency);
			if (!$bitrix24)
			{
				$checkDate = Main\Type\DateTime::createFromTimestamp(strtotime('tomorrow 00:01:00'));
				CAgent::AddAgent('\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();', 'currency', 'Y', 86400, '', 'Y', $checkDate->toString(), 100, false, true);
				unset($checkDate);
			}
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		}
		unset($datetimeEntity);
	}

	/**
	 * Returns currency list for install.
	 *
	 * @param string $baseCurrency
	 * @return array
	 */
	private static function getCurrencyListForInstall(string $baseCurrency): array
	{
		return match ($baseCurrency)
		{
			'BYN' => [
				['CURRENCY' => 'BYN', 'NUMCODE' => '933', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 0.31, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.31],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 2.14, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.14],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 2.44, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.44]
			],
			'KZT' => [
				['CURRENCY' => 'KZT', 'NUMCODE' => '398', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 5.40, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 5.40],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 371.27, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 371.27],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 423.29, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 423.29]
			],
			'UAH' => [
				['CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 3.98, 'AMOUNT_CNT' => 10, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.398],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 27.39, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 27.39],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 31.22, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 31.22]
			],
			'RUB' => [
				['CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 68.79, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 68.79],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 78.32, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 78.32],
				['CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 25.11, 'AMOUNT_CNT' => 10, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.511],
				['CURRENCY' => 'BYN', 'NUMCODE' => '933', 'AMOUNT' => 32.20, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 32.20]
			],
			'EUR' => [
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 0.88, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.88],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 12.71, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.1271],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 22.47, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2247],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 12.49, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01249]
			],
			'CNY' => [
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'TWD', 'NUMCODE' => '901', 'AMOUNT' => 22.39, 'AMOUNT_CNT' => 100, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2239],
				['CURRENCY' => 'HKD', 'NUMCODE' => '344', 'AMOUNT' => 88.06, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.8806],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 6.89, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.89],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 7.86, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 7.86],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1.77, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.77],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 9.85, 'AMOUNT_CNT' => 100, 'SORT' => 700, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.0985]
			],
			'TWD' => [
				['CURRENCY' => 'TWD', 'NUMCODE' => '901', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 4.47, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.47],
				['CURRENCY' => 'HKD', 'NUMCODE' => '344', 'AMOUNT' => 3.93, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.93],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 30.81, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 30.81],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 35.17, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 35.17],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 7.89, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 7.89],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 43.94, 'AMOUNT_CNT' => 100, 'SORT' => 700, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.4394]
			],
			'INR' => [
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 70.05, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 70.05],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 79.92, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 79.92],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 10.17, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 10.17],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 17.94, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 17.94]
			],
			'BRL' => [
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.90, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.90],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.45, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.45],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 56.69, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.5669],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 5.57, 'AMOUNT_CNT' => 100, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.0557]
			],
			'PLN' => [
				['CURRENCY' => 'PLN', 'NUMCODE' => '985', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.76, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.76],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.29, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.29],
			],
			'TRY' => [
				['CURRENCY' => 'TRY', 'NUMCODE' => '949', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 5.30, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 5.30],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 6.05, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.05]
			],
			'JPY' => [
				['CURRENCY' => 'JPY', 'NUMCODE' => '392', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 110.25, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 110.25],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 125.56, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 125.56],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 15.98, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 15.98],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 28.24, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 28.24],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 1.57, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.57]
			],
			'VND' => [
				['CURRENCY' => 'VND', 'NUMCODE' => '704', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 23279.63, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 23279.63],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 26523.81, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 26523.81],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 3371.85, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3371.85],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 5958.40, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 5958.40],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 331.10, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 331.10]
			],
			'IDR' => [
				['CURRENCY' => 'IDR', 'NUMCODE' => '360', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 6.88, 'AMOUNT_CNT' => 100000, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 688000],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 6.03, 'AMOUNT_CNT' => 100000, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 603000],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 47.48, 'AMOUNT_CNT' => 100000, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 47480],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 26.87, 'AMOUNT_CNT' => 100000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 26870],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 4.83, 'AMOUNT_CNT' => 1000, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4830]
			],
			'MYR' => [
				['CURRENCY' => 'MYR', 'NUMCODE' => '458', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 4.18, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.18],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.77, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.77],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 6.05, 'AMOUNT_CNT' => 10, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.605],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1.07, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.07],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 0.06, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.06]
			],
			'THB' => [
				['CURRENCY' => 'THB', 'NUMCODE' => '764', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 32.55, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 32.55],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 37.16, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 37.16],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 4.72, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.72],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 8.34, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 8.34],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 0.46, 'AMOUNT_CNT' => 1, 'SORT' => 600, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.46]
			],
			'GBP' => [
				['CURRENCY' => 'GBP', 'NUMCODE' => '826', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 0.91, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.91],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 0.77, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.77],
			],
			'MXN' => [
				['CURRENCY' => 'MXN', 'NUMCODE' => '484', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 21.57, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 21.57],
				['CURRENCY' => 'COP', 'NUMCODE' => '170', 'AMOUNT' => 5.68, 'AMOUNT_CNT' => 1000, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.00568],
			],
			'COP' => [
				['CURRENCY' => 'COP', 'NUMCODE' => '170', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3797.1, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3797.1],
				['CURRENCY' => 'MXN', 'NUMCODE' => '484', 'AMOUNT' => 176.21, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 176.21],
			],
			default => [
				['CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1],
				['CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1.14, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.14],
				['CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 15.00, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.15],
				['CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 25.61, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2561],
				['CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 14.28, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01428]
			],
		};
	}
}
