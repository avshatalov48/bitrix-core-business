<?
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\SiteTable;

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

	function currency()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (!empty($arModuleVersion['VERSION']))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = CURRENCY_VERSION;
			$this->MODULE_VERSION_DATE = CURRENCY_VERSION_DATE;
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

		$this->errors = false;

		if (!$DB->Query("SELECT COUNT(CURRENCY) FROM b_catalog_currency", true)):
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/install.sql");
		endif;

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		ModuleManager::registerModule('currency');
		self::installCurrencies();

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockPropertyBuildList', 'currency',
			'\Bitrix\Currency\Integration\IblockMoneyProperty', 'getUserTypeDescription');
		$eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'currency',
			'\Bitrix\Currency\UserField\Money', 'getUserTypeDescription');

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		if (Loader::includeModule('currency'))
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/uninstall.sql");
			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode('', $this->errors));
				return false;
			}
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();
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

		$bitrix24Path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bitrix24/';
		$bitrix24 = file_exists($bitrix24Path) && is_dir($bitrix24Path);
		unset($bitrix24Path);

		$currencyIterator = \Bitrix\Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY'),
			'limit' => 1
		));
		$currency = $currencyIterator->fetch();
		if (!empty($currency))
			return;

		$baseCurrency = '';
		if ($bitrix24 && Loader::includeModule('bitrix24'))
		{
			$bitrix24Zone = CBitrix24::getCurrentAreaConfig();
			if (!empty($bitrix24Zone) && is_array($bitrix24Zone))
				$baseCurrency = $bitrix24Zone['CURRENCY'];
		}
		if ($baseCurrency == '')
		{
			$languageID = '';
			$site = SiteTable::getList(array(
				'select' => array('LID', 'LANGUAGE_ID'),
				'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
			))->fetch();
			if (!empty($site))
				$languageID = (string)$site['LANGUAGE_ID'];
			unset($site);

			if ($languageID == '')
				$languageID = 'en';

			$currencyList = array();
			switch ($languageID)
			{
				case 'ua':
					$baseCurrency = 'UAH';
					break;
				case 'de':
					$baseCurrency = 'EUR';
					break;
				case 'en':
					$baseCurrency = 'USD';
					break;
				case 'la':
					$baseCurrency = 'USD';
					break;
				case 'tc':
				case 'sc':
					$baseCurrency = 'CNY';
					break;
				case 'in':
					$baseCurrency = 'INR';
					break;
				case 'kz':
					$baseCurrency = 'KZT';
					break;
				case 'br':
					$baseCurrency = 'BRL';
					break;
				case 'by':
					$baseCurrency = 'BYN';
					break;
				case 'ru':
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
					break;
				default:
					$baseCurrency = 'USD';
					break;
			}
			unset($languageID);
		}
		$datetimeEntity = new Main\DB\SqlExpression(Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction());
		switch ($baseCurrency)
		{
			case 'BYR':
			case 'BYN':
				$addCurrency = array(
					array('CURRENCY' => 'BYN', 'NUMCODE' => '933', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 0.31, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.31),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 2.00, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.00),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 2.22, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.22)
				);
				break;
			case 'KZT':
				$addCurrency = array(
					array('CURRENCY' => 'KZT', 'NUMCODE' => '398', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 4.67, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.67),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 350.58, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 350.58),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 390.37, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 390.37)
				);
				break;
			case 'UAH':
				$addCurrency = array(
					array('CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 3.61, 'AMOUNT_CNT' => 10, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.361),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 2322.93, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 23.2293),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 2548.19, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 25.4819)
				);
				break;
			case 'RUB':
				$addCurrency = array(
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 64.81, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 64.81),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 71.71, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 71.71),
					array('CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 26.04, 'AMOUNT_CNT' => 10, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.604),
					array('CURRENCY' => 'BYN', 'NUMCODE' => '933', 'AMOUNT' => 32.34, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 32.34)
				);
				break;
			case 'EUR':
				$addCurrency = array(
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 0.91, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.91),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 14.35, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.1435),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 23.21, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2321),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 13.97, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01397)
				);
				break;
			case 'CNY':
				$addCurrency = array(
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 6.36, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.36),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 6.97, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.97),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1.61, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.61),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 9.74, 'AMOUNT_CNT' => 100, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.09737)
				);
				break;
			case 'INR':
				$addCurrency = array(
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 65.31, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 65.31),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 71.56, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 71.56),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 10.27, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 10.27),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 16.56, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 16.56)
				);
				break;
			case 'BRL':
				$addCurrency = array(
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.90, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.90),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.29, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.29),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 61.44, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.6144),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 5.99, 'AMOUNT_CNT' => 100, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.0599),
				);
				break;
			case 'PLN':
				$addCurrency = array(
					array('CURRENCY' => 'PLN', 'NUMCODE' => '985', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.77, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.77),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.22, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.22),
				);
				break;
			case 'TRY':
				$addCurrency = array(
					array('CURRENCY' => 'TRY', 'NUMCODE' => '949', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.51, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.51),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 3.92, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.92),
				);
				break;
			case 'USD':
			default:
				$addCurrency = array(
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1.10, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.10),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 15.73, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.1573),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 25.35, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2535),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 15.31, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01531)
				);
				break;
		}
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

		if (!empty($currencyList))
		{
			Option::set('currency', 'installed_currencies', implode(',', $currencyList), '');
			$languageIterator = LanguageTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($existLanguage = $languageIterator->fetch())
			{
				$messList = Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/currency/install_lang.php', $existLanguage['ID']);
				foreach($currencyList as $oneCurrency)
				{
					$fields = array(
						'LID' => $existLanguage['ID'],
						'CURRENCY' => $oneCurrency,
						'THOUSANDS_SEP' => false,
						'DECIMALS' => 2,
						'HIDE_ZERO' => 'Y',
						'FORMAT_STRING' => $messList['CUR_INSTALL_'.$oneCurrency.'_FORMAT_STRING'],
						'FULL_NAME' => $messList['CUR_INSTALL_'.$oneCurrency.'_FULL_NAME'],
						'DEC_POINT' => $messList['CUR_INSTALL_'.$oneCurrency.'_DEC_POINT'],
						'THOUSANDS_VARIANT' => $messList['CUR_INSTALL_'.$oneCurrency.'_THOUSANDS_SEP'],
						'CREATED_BY' => null,
						'MODIFIED_BY' => null,
						'DATE_CREATE' => $datetimeEntity,
						'TIMESTAMP_X' => $datetimeEntity
					);
					$resultCurrencyLang = \Bitrix\Currency\CurrencyLangTable::add($fields);
					unset($resultCurrencyLang);
				}
				unset($oneCurrency, $messList);
			}
			unset($existLanguage, $languageIterator);
			if (!$bitrix24)
			{
				$checkDate = Main\Type\DateTime::createFromTimestamp(strtotime('tomorrow 00:01:00'));;
				CAgent::AddAgent('\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();', 'currency', 'Y', 86400, '', 'Y', $checkDate->toString(), 100, false, true);
				unset($checkDate);
			}
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		}
		unset($datetimeEntity);
	}
}