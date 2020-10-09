<?php

includeModuleLangFile(__FILE__);
if (class_exists('abtest'))
	return;

class ABTest extends CModule
{
	var $MODULE_ID = 'abtest';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = getMessage('ABTEST_MODULE_NAME');
		$this->MODULE_DESCRIPTION = getMessage('ABTEST_MODULE_DESCRIPTION');
	}

	function doInstall()
	{
		global $DB, $APPLICATION;

		$this->installFiles();
		$this->installDB();

		$GLOBALS['APPLICATION']->includeAdminFile(
			getMessage('ABTEST_INSTALL_TITLE'),
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/abtest/install/step1.php'
		);
	}

	function installDB()
	{
		global $DB, $APPLICATION;

		$this->errors = false;
		if (!$DB->query("SELECT 'x' FROM b_abtest", true))
		{
			$createTestTemplates = true;
			$this->errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/abtest/install/db/'.mb_strtolower($DB->type).'/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));

			return false;
		}

		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('main', 'OnGetCurrentSiteTemplate', 'abtest', '\Bitrix\ABTest\EventHandler', 'onGetCurrentSiteTemplate');
		$eventManager->registerEventHandler('main', 'OnFileRewrite', 'abtest', '\Bitrix\ABTest\EventHandler', 'onFileRewrite');

		$eventManager->registerEventHandlerCompatible('main', 'OnPageStart', 'abtest', '\Bitrix\ABTest\EventHandler', 'onPageStart');
		$eventManager->registerEventHandlerCompatible('main', 'OnPanelCreate', 'abtest', '\Bitrix\ABTest\EventHandler', 'onPanelCreate');

		$eventManager->registerEventHandlerCompatible('conversion', 'OnGetAttributeTypes', 'abtest', '\Bitrix\ABTest\EventHandler', 'onGetAttributeTypes');
		$eventManager->registerEventHandlerCompatible('conversion', 'OnSetDayContextAttributes', 'abtest', '\Bitrix\ABTest\EventHandler', 'onConversionSetContextAttributes');

		registerModule($this->MODULE_ID);

		$defSite = Bitrix\Main\SiteTable::getList(array(
			'order'  => array('ACTIVE' => 'DESC', 'DEF' => 'DESC', 'SORT' => 'ASC'),
			'select' => array('LID')
		))->fetch();
		if (!empty($createTestTemplates) && CModule::includeModule('abtest') && !empty($defSite))
		{
			$arTestTemplates = array(
				100 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE1_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE1_DESCR'),
					'TEST_DATA' => array('id' => 'sample1', 'list' => array(array('type' => 'template', 'old_value' => '', 'new_value' => ''))),
				),
				200 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE2_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE2_DESCR'),
					'TEST_DATA' => array('id' => 'sample2', 'list' => array(array('type' => 'page', 'old_value' => '', 'new_value' => ''))),
				),
				300 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE3_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE3_DESCR'),
					'TEST_DATA' => array('id' => 'sample3', 'list' => array(array('type' => 'page', 'old_value' => '', 'new_value' => ''))),
				),
				400 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE4_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE4_DESCR'),
					'TEST_DATA' => array('id' => 'sample4', 'list' => array(array('type' => 'page', 'old_value' => '', 'new_value' => ''))),
				),
				500 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE5_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE5_DESCR'),
					'TEST_DATA' => array('id' => 'sample5', 'list' => array(array('type' => 'page', 'old_value' => '', 'new_value' => ''))),
				),
				600 => array(
					'ENABLED'   => 'T',
					'NAME'      => getMessage('ABTEST_SAMPLE6_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE6_DESCR'),
					'TEST_DATA' => array('id' => 'sample6', 'list' => array(array('type' => 'page', 'old_value' => '', 'new_value' => ''))),
				),
				700 => array(
					'ENABLED'   => 'N',
					'NAME'      => getMessage('ABTEST_SAMPLE7_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE7_DESCR'),
					'TEST_DATA' => array('id' => 'sample7', 'list' => array(array('type' => 'composite', 'old_value' => 'N', 'new_value' => 'Y'))),
				),
				800 => array(
					'ENABLED'   => 'N',
					'NAME'      => getMessage('ABTEST_SAMPLE8_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE8_DESCR'),
					'TEST_DATA' => array('id' => 'sample8', 'list' => array(array('type' => 'cdn', 'old_value' => 'N', 'new_value' => 'Y'))),
				),
				900 => array(
					'ENABLED'   => 'N',
					'NAME'      => getMessage('ABTEST_SAMPLE9_NAME'),
					'DESCR'     => getMessage('ABTEST_SAMPLE9_DESCR'),
					'TEST_DATA' => array('id' => 'sample9', 'list' => array(array('type' => 'bigdata', 'old_value' => 'N', 'new_value' => 'Y'))),
				),
			);

			foreach ($arTestTemplates as $sort => $test)
			{
				$test['SITE_ID']  = $defSite['LID'];
				$test['ACTIVE']   = 'N';
				$test['DURATION'] = 0;
				$test['PORTION']  = 30;
				$test['SORT']     = $sort;

				Bitrix\ABTest\ABTestTable::add($test);
			}
		}

		return true;
	}

	function installEvents()
	{
		return true;
	}

	function installFiles()
	{
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/abtest/install/admin',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
			true, true
		);
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/abtest/install/images',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/images',
			true, true
		);
		copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/abtest/install/themes',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes',
			true, true
		);

		return true;
	}

	function doUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;

		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->includeAdminFile(
				getMessage('ABTEST_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/bitrix/modules/abtest/install/unstep1.php'
			);
		}
		elseif ($step == 2)
		{
			$this->uninstallDB(array('savedata' => $_REQUEST['savedata']));
			$this->uninstallFiles();
			$APPLICATION->includeAdminFile(
				getMessage('ABTEST_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/bitrix/modules/abtest/install/unstep2.php'
			);
		}
	}

	function uninstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->runSQLBatch(
				$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/abtest/install/db/'.mb_strtolower($DB->type).'/uninstall.sql'
			);
		}

		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));

			return false;
		}

		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler('main', 'OnGetCurrentSiteTemplate', 'abtest', '\Bitrix\ABTest\EventHandler', 'onGetCurrentSiteTemplate');
		$eventManager->unregisterEventHandler('main', 'OnFileRewrite', 'abtest', '\Bitrix\ABTest\EventHandler', 'onFileRewrite');

		$eventManager->unregisterEventHandler('main', 'OnPageStart', 'abtest', '\Bitrix\ABTest\EventHandler', 'onPageStart');
		$eventManager->unregisterEventHandler('main', 'OnPanelCreate', 'abtest', '\Bitrix\ABTest\EventHandler', 'onPanelCreate');

		$eventManager->unregisterEventHandler('conversion', 'OnGetAttributeTypes', 'abtest', '\Bitrix\ABTest\EventHandler', 'onGetAttributeTypes');
		$eventManager->unregisterEventHandler('conversion', 'OnSetDayContextAttributes', 'abtest', '\Bitrix\ABTest\EventHandler', 'onConversionSetContextAttributes');

		unregisterModule($this->MODULE_ID);

		return true;
	}

	function uninstallEvents()
	{
		return true;
	}

	function uninstallFiles()
	{
		deleteDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/abtest/install/admin',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
		);

		return true;
	}

}
