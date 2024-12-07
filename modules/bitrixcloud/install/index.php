<?php
IncludeModuleLangFile(__FILE__);
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
if (class_exists('bitrixcloud'))
{
	return;
}

class bitrixcloud extends CModule
{
	public $MODULE_ID = 'bitrixcloud';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = 'N';
	public $errors = false;

	public function __construct()
	{
		$arModuleVersion = [];
		include __DIR__ . '/version.php';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = GetMessage('BCL_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('BCL_MODULE_DESCRIPTION_2');
	}

	public function GetModuleTasks()
	{
		return [
			'bitrixcloud_deny' => [
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => [
				]
			],
			'bitrixcloud_control' => [
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'bitrixcloud_monitoring',
					'bitrixcloud_backup',
				]
			],
		];
	}

	public function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_bitrixcloud_option'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		$this->InstallTasks();
		RegisterModule('bitrixcloud');
		RegisterModuleDependences('main', 'OnAdminInformerInsertItems', 'bitrixcloud', 'CBitrixCloudBackup', 'OnAdminInformerInsertItems');
		RegisterModuleDependences('mobileapp', 'OnBeforeAdminMobileMenuBuild', 'bitrixcloud', 'CBitrixCloudMobile', 'OnBeforeAdminMobileMenuBuild');

		return true;
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		UnRegisterModuleDependences('main', 'OnAdminInformerInsertItems', 'bitrixcloud', 'CBitrixCloudBackup', 'OnAdminInformerInsertItems');
		UnRegisterModuleDependences('mobileapp', 'OnBeforeAdminMobileMenuBuild', 'bitrixcloud', 'CBitrixCloudMobile', 'OnBeforeAdminMobileMenuBuild');

		if (!array_key_exists('savedata', $arParams) || $arParams['savedata'] != 'Y')
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/db/' . $connection->getType() . '/uninstall.sql');
		}

		UnRegisterModule('bitrixcloud');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		return true;
	}

	public function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/gadgets', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/gadgets', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js', true, true);
		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFilesEx('/bitrix/js/bitrixcloud/');
		return true;
	}

	public function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('BCL_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/step1.php');
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallFiles();
				}
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('BCL_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/step2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('BCL_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/unstep1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'save_tables' => $_REQUEST['save_tables'],
				]);
				$this->UnInstallFiles();
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('BCL_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrixcloud/install/unstep2.php');
			}
		}
	}
}
