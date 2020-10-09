<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(class_exists('wiki')) return;

Class wiki extends CModule
{
	var $MODULE_ID = 'wiki';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $error = '';

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
		else
		{
			$this->MODULE_VERSION = WIKI_VERSION;
			$this->MODULE_VERSION_DATE = WIKI_VERSION_DATE;
		}

		$this->MODULE_NAME = Loc::getMessage('WIKI_INSTALL_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('WIKI_INSTALL_DESCRIPTION');
	}

	function InstallDB()
	{
		COption::SetOptionString('wiki', 'GROUP_DEFAULT_RIGHT', 'R');
		RegisterModule('wiki');
		RegisterModuleDependences('main', 'OnAddRatingVote', 'wiki', 'CRatingsComponentsWiki', 'OnAddRatingVote', 200);
		RegisterModuleDependences('main', 'OnCancelRatingVote', 'wiki', 'CRatingsComponentsWiki', 'OnCancelRatingVote', 200);
		RegisterModuleDependences('search', 'BeforeIndex', 'wiki', 'CRatingsComponentsWiki', 'BeforeIndex');
		RegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'wiki', 'CWikiSocNet', 'BeforeIndexSocNet');
		RegisterModuleDependences("im", "OnGetNotifySchema", "wiki", "CWikiNotifySchema", "OnGetNotifySchema");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('socialnetwork', 'onLogIndexGetContent', 'wiki', '\Bitrix\Wiki\Integration\Socialnetwork\Log', 'onIndexGetContent');
		return true;
	}

	function UnInstallDB()
	{
		COption::RemoveOption('wiki');
		UnRegisterModule('wiki');
		UnRegisterModuleDependences('main', 'OnAddRatingVote', 'wiki', 'CRatingsComponentsWiki', 'OnAddRatingVote');
		UnRegisterModuleDependences('main', 'OnCancelRatingVote', 'wiki', 'CRatingsComponentsWiki', 'OnCancelRatingVote');
		UnRegisterModuleDependences('search', 'BeforeIndex', 'wiki', 'CRatingsComponentsWiki', 'BeforeIndex');
		UnRegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'wiki', 'CWikiSocNet', 'BeforeIndexSocNet');
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "wiki", "CWikiNotifySchema", "OnGetNotifySchema");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('socialnetwork', 'onLogIndexGetContent', 'wiki', '\Bitrix\Wiki\Integration\Socialnetwork\Log', 'onIndexGetContent');
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
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/wiki', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/themes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFilesEx('/bitrix/images/wiki/');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/.default');//css
		DeleteDirFilesEx('/bitrix/themes/.default/icons/wiki/');//icons
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		$step = intval($step);

		if(!CBXFeatures::IsFeatureEditable('Wiki'))
		{
			$this->error = Loc::getMessage('MAIN_FEATURE_ERROR_EDITABLE');
			$GLOBALS['errors'] = $this->error;
			$APPLICATION->IncludeAdminFile(Loc::getMessage('WIKI_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/step3.php');
		}
		elseif ($step < 2)
			$APPLICATION->IncludeAdminFile(Loc::getMessage('WIKI_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/step.php');
		elseif ($step == 2)
			$APPLICATION->IncludeAdminFile(Loc::getMessage('WIKI_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/step2.php');
		else
		{
			$this->InstallDB();
			$this->InstallFiles();
			CBXFeatures::SetFeatureEnabled('Wiki', true);
			$APPLICATION->IncludeAdminFile(Loc::getMessage('WIKI_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/step3.php');
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $DB;
		if (CModule::IncludeModule('socialnetwork'))
		{
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/include.php');
			CWikiSocnet::EnableSocnet(false);
		}
		$this->UnInstallFiles();
		$this->UnInstallDB();
		CBXFeatures::SetFeatureEnabled('Wiki', false);
		$APPLICATION->IncludeAdminFile(Loc::getMessage('WIKI_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/install/unstep.php');
	}

	function GetModuleRightList()
	{
		$arr = array(
			'reference_id' => array('D', 'R', 'W', 'Y'),
			'reference' => array(
					'[D] '.Loc::getMessage('WIKI_PERM_D'),
					'[R] '.Loc::getMessage('WIKI_PERM_R'),
					'[W] '.Loc::getMessage('WIKI_PERM_W'),
					//'[X] '.Loc::getMessage('WIKI_PERM_X'),
					'[Y] '.Loc::getMessage('WIKI_PERM_Y'),
					//'[Z] '.Loc::getMessage('WIKI_PERM_Z')
				)
			);
		return $arr;
	}

}
?>