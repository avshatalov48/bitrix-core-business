<?php
IncludeModuleLangFile(__FILE__);

if (class_exists('clouds'))
{
	return;
}
class clouds extends CModule
{
	public $MODULE_ID = 'clouds';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

		$this->MODULE_NAME = GetMessage('CLO_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('CLO_MODULE_DESCRIPTION');
	}

	public function GetModuleTasks()
	{
		return [
			'clouds_denied' => [
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => [
				],
			],
			'clouds_browse' => [
				'LETTER' => 'F',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'clouds_browse',
				],
			],
			'clouds_upload' => [
				'LETTER' => 'U',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'clouds_browse',
					'clouds_upload',
				],
			],
			'clouds_full_access' => [
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'clouds_browse',
					'clouds_upload',
					'clouds_config',
				],
			],
		];
	}

	public function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		if (!$DB->TableExists('b_clouds_file_bucket'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();

			RegisterModule('clouds');
			CModule::IncludeModule('clouds');
			RegisterModuleDependences('main', 'OnEventLogGetAuditTypes', 'clouds', 'CCloudStorage', 'GetAuditTypes');
			RegisterModuleDependences('main', 'OnBeforeProlog', 'clouds', 'CCloudStorage', 'OnBeforeProlog', 90);
			RegisterModuleDependences('main', 'OnAdminListDisplay', 'clouds', 'CCloudStorage', 'OnAdminListDisplay');
			RegisterModuleDependences('main', 'OnBuildGlobalMenu', 'clouds', 'CCloudStorage', 'OnBuildGlobalMenu');
			RegisterModuleDependences('main', 'OnFileSave', 'clouds', 'CCloudStorage', 'OnFileSave');
			RegisterModuleDependences('main', 'OnAfterFileSave', 'clouds', 'CCloudStorage', 'OnAfterFileSave');
			RegisterModuleDependences('main', 'OnGetFileSRC', 'clouds', 'CCloudStorage', 'OnGetFileSRC');
			RegisterModuleDependences('main', 'OnFileCopy', 'clouds', 'CCloudStorage', 'OnFileCopy');
			RegisterModuleDependences('main', 'OnPhysicalFileDelete', 'clouds', 'CCloudStorage', 'OnFileDelete');
			RegisterModuleDependences('main', 'OnMakeFileArray', 'clouds', 'CCloudStorage', 'OnMakeFileArray');
			RegisterModuleDependences('main', 'OnBeforeResizeImage', 'clouds', 'CCloudStorage', 'OnBeforeResizeImage');
			RegisterModuleDependences('main', 'OnAfterResizeImage', 'clouds', 'CCloudStorage', 'OnAfterResizeImage');
			RegisterModuleDependences('main', 'OnAfterFileDeleteDuplicate', 'clouds', 'CCloudStorage', 'OnAfterFileDeleteDuplicate');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_AmazonS3', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_GoogleStorage', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_OpenStackStorage', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_RackSpaceCloudFiles', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_ClodoRU', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Selectel', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Selectel_S3', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_HotBox', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Yandex', 'GetObjectInstance');
			RegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_S3', 'GetObjectInstance');
			RegisterModuleDependences('perfmon', 'OnGetTableSchema', 'clouds', 'clouds', 'OnGetTableSchema');

			//agents
			CAgent::RemoveAgent('CCloudStorage::CleanUp();', 'clouds');
			CAgent::Add([
				'NAME' => 'CCloudStorage::CleanUp();',
				'MODULE_ID' => 'clouds',
				'ACTIVE' => 'Y',
				'AGENT_INTERVAL' => 86400,
				'IS_PERIOD' => 'N',
			]);

			return true;
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!array_key_exists('save_tables', $arParams) || $arParams['save_tables'] != 'Y')
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/db/' . $connection->getType() . '/uninstall.sql');
			$this->UnInstallTasks();
		}

		UnRegisterModuleDependences('main', 'OnEventLogGetAuditTypes', 'clouds', 'CCloudStorage', 'GetAuditTypes');
		UnRegisterModuleDependences('main', 'OnBeforeProlog', 'clouds', 'CCloudStorage', 'OnBeforeProlog');
		UnRegisterModuleDependences('main', 'OnAdminListDisplay', 'clouds', 'CCloudStorage', 'OnAdminListDisplay');
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', 'clouds', 'CCloudStorage', 'OnBuildGlobalMenu');
		UnRegisterModuleDependences('main', 'OnFileSave', 'clouds', 'CCloudStorage', 'OnFileSave');
		UnRegisterModuleDependences('main', 'OnAfterFileSave', 'clouds', 'CCloudStorage', 'OnAfterFileSave');
		UnRegisterModuleDependences('main', 'OnGetFileSRC', 'clouds', 'CCloudStorage', 'OnGetFileSRC');
		UnRegisterModuleDependences('main', 'OnFileCopy', 'clouds', 'CCloudStorage', 'OnFileCopy');
		UnRegisterModuleDependences('main', 'OnPhysicalFileDelete', 'clouds', 'CCloudStorage', 'OnFileDelete');
		UnRegisterModuleDependences('main', 'OnMakeFileArray', 'clouds', 'CCloudStorage', 'OnMakeFileArray');
		UnRegisterModuleDependences('main', 'OnBeforeResizeImage', 'clouds', 'CCloudStorage', 'OnBeforeResizeImage');
		UnRegisterModuleDependences('main', 'OnAfterResizeImage', 'clouds', 'CCloudStorage', 'OnAfterResizeImage');
		UnRegisterModuleDependences('main', 'OnAfterFileDeleteDuplicate', 'clouds', 'CCloudStorage', 'OnAfterFileDeleteDuplicate');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_AmazonS3', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_GoogleStorage', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_OpenStackStorage', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_RackSpaceCloudFiles', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_ClodoRU', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Selectel', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Selectel_S3', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_HotBox', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_Yandex', 'GetObjectInstance');
		UnRegisterModuleDependences('clouds', 'OnGetStorageService', 'clouds', 'CCloudStorageService_S3', 'GetObjectInstance');
		UnRegisterModuleDependences('perfmon', 'OnGetTableSchema', 'clouds', 'clouds', 'OnGetTableSchema');

		//agents
		CAgent::RemoveAgent('CCloudStorage::CleanUp();', 'clouds');

		UnRegisterModule('clouds');

		if (!defined('BX_CLOUDS_UNINSTALLED'))
		{
			define('BX_CLOUDS_UNINSTALLED', true);
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		return true;
	}

	public function InstallEvents()
	{
		return true;
	}

	public function UnInstallEvents()
	{
		return true;
	}

	public function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);
		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step, $USER, $errors;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('CLO_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/step1.php');
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallFiles();
				}
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('CLO_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/step2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step, $USER, $errors;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('CLO_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/unstep1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'save_tables' => $_REQUEST['save_tables'],
				]);
				$this->UnInstallFiles();
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('CLO_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/clouds/install/unstep2.php');
			}
		}
	}

	public static function OnGetTableSchema()
	{
		return [
			'clouds' => [
				'b_clouds_file_bucket' => [
					'ID' => [
						'b_clouds_file_bucket' => 'FAILOVER_BUCKET_ID',
						'b_clouds_file_upload' => 'BUCKET_ID',
						'b_clouds_copy_queue' => 'SOURCE_BUCKET_ID',
						'b_clouds_copy_queue^' => 'TARGET_BUCKET_ID',
						'b_clouds_delete_queue' => 'BUCKET_ID',
						'b_clouds_rename_queue' => 'BUCKET_ID',
						'b_clouds_file_save' => 'BUCKET_ID',
					]
				],
			],
			'main' => [
				'b_file' => [
					'ID' => [
						'b_clouds_file_resize' => 'FILE_ID',
					]
				],
			],
		];
	}
}
