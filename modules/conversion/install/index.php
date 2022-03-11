<?php

global $MESS;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (class_exists('conversion')) return;

Class conversion extends CModule
{
	var $MODULE_ID = 'conversion';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

		$this->MODULE_NAME = Loc::getMessage('CONVERSION_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('CONVERSION_MODULE_DESC');
	}

	function InstallDB($params = array())
	{
		global $DB;

		if (! $DB->Query("SELECT 'x' FROM b_conv_context", true))
		{
			Option::set('conversion', 'START_DATE_TIME', date('Y-m-d H:i:s'));

			if (ModuleManager::isModuleInstalled('sale') && ($currency = Option::get('sale', 'default_currency')))
			{
				Option::set('conversion', 'BASE_CURRENCY', $currency);
			}
			elseif (Bitrix\Main\Loader::includeModule('currency'))
			{
				Option::set('conversion', 'BASE_CURRENCY', Bitrix\Currency\CurrencyManager::getBaseCurrency());
			}

			if ($params['GENERATE_INITIAL_DATA'] !== 'Y')
			{
				Option::set('conversion', 'GENERATE_INITIAL_DATA', 'generated');
			}

			$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/db/mysql/install.sql');
		}

		ModuleManager::registerModule('conversion');

		RegisterModuleDependences('conversion', 'OnGetCounterTypes'        , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetCounterTypes'        );
		RegisterModuleDependences('conversion', 'OnGetAttributeTypes'      , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetAttributeTypes'      );
		RegisterModuleDependences('conversion', 'OnGetAttributeGroupTypes' , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetAttributeGroupTypes' );
		RegisterModuleDependences('conversion', 'OnSetDayContextAttributes', 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onSetDayContextAttributes');
		RegisterModuleDependences('main'      , 'OnProlog'                 , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onProlog'                 );

		return true;
	}

	function UnInstallDB($params = array())
	{
		UnRegisterModuleDependences('conversion', 'OnGetCounterTypes'        , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetCounterTypes'        );
		UnRegisterModuleDependences('conversion', 'OnGetAttributeTypes'      , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetAttributeTypes'      );
		UnRegisterModuleDependences('conversion', 'OnGetAttributeGroupTypes' , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onGetAttributeGroupTypes' );
		UnRegisterModuleDependences('conversion', 'OnSetDayContextAttributes', 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onSetDayContextAttributes');
		UnRegisterModuleDependences('main'      , 'OnProlog'                 , 'conversion', '\Bitrix\Conversion\Internals\Handlers', 'onProlog'                 );

		ModuleManager::unRegisterModule('conversion');

		if ($params['SAVE_TABLES'] !== 'Y')
		{
			global $DB;
			$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/db/mysql/uninstall.sql');

			Option::delete('conversion', array('name' => 'START_DATE_TIME'      ));
			Option::delete('conversion', array('name' => 'BASE_CURRENCY'        ));
			Option::delete('conversion', array('name' => 'GENERATE_INITIAL_DATA'));
		}

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

	function InstallFiles($arParams = array())
	{
		if ($_ENV['COMPUTERNAME'] != 'BX')
		{
			$root = $_SERVER['DOCUMENT_ROOT'];
			CopyDirFiles($root.'/bitrix/modules/conversion/install/admin' , $root.'/bitrix/admin' , true, true);
			CopyDirFiles($root.'/bitrix/modules/conversion/install/tools' , $root.'/bitrix/tools' , true, true);
			CopyDirFiles($root.'/bitrix/modules/conversion/install/js'    , $root.'/bitrix/js'    , true, true);
			CopyDirFiles($root.'/bitrix/modules/conversion/install/themes', $root.'/bitrix/themes', true, true);
			CopyDirFiles($root.'/bitrix/modules/conversion/install/images', $root.'/bitrix/images', true, true);
		}

		return true;
	}

	function UnInstallFiles()
	{
		if ($_ENV['COMPUTERNAME'] != 'BX')
		{
			$root = $_SERVER['DOCUMENT_ROOT'];
			DeleteDirFiles($root.'/bitrix/modules/conversion/install/admin' , $root.'/bitrix/admin' );
			DeleteDirFiles($root.'/bitrix/modules/conversion/install/tools' , $root.'/bitrix/tools' );
			DeleteDirFiles($root.'/bitrix/modules/conversion/install/js'    , $root.'/bitrix/js'    );
			DeleteDirFiles($root.'/bitrix/modules/conversion/install/themes', $root.'/bitrix/themes');
			DeleteDirFiles($root.'/bitrix/modules/conversion/install/images', $root.'/bitrix/images');
		}

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		if ($step === '2')
		{
			global $GENERATE_INITIAL_DATA;
			$this->InstallDB(array('GENERATE_INITIAL_DATA' => $GENERATE_INITIAL_DATA));
			$this->InstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage('CONVERSION_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/step2.php');
		}
		else // step 1
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage('CONVERSION_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/step1.php');
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		if ($step === '2')
		{
			global $SAVE_TABLES;
			$this->UnInstallDB(array('SAVE_TABLES' => $SAVE_TABLES));
			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage('CONVERSION_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/unstep2.php');
		}
		else // step 1
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage('CONVERSION_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/install/unstep1.php');
		}
	}
}

