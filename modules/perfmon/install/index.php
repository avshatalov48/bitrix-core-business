<?php
IncludeModuleLangFile(__FILE__);

if (class_exists('perfmon'))
{
	return;
}
class perfmon extends CModule
{
	public $MODULE_ID = 'perfmon';
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

		$this->MODULE_NAME = GetMessage('PERF_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('PERF_MODULE_DESCRIPTION');
	}

	public function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		if (!$DB->TableExists('b_perf_hit'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			RegisterModule('perfmon');
			CModule::IncludeModule('perfmon');
			RegisterModuleDependences('perfmon', 'OnGetTableSchema', 'perfmon', 'perfmon', 'OnGetTableSchema');
			return true;
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		UnRegisterModuleDependences('main', 'OnPageStart', 'perfmon', 'CPerfomanceKeeper', 'OnPageStart');
		UnRegisterModuleDependences('main', 'OnEpilog', 'perfmon', 'CPerfomanceKeeper', 'OnEpilog');
		UnRegisterModuleDependences('main', 'OnAfterEpilog', 'perfmon', 'CPerfomanceKeeper', 'OnBeforeAfterEpilog');
		UnRegisterModuleDependences('main', 'OnAfterEpilog', 'perfmon', 'CPerfomanceKeeper', 'OnAfterAfterEpilog');
		UnRegisterModuleDependences('main', 'OnLocalRedirect', 'perfmon', 'CPerfomanceKeeper', 'OnAfterAfterEpilog');
		UnRegisterModuleDependences('perfmon', 'OnGetTableSchema', 'perfmon', 'perfmon', 'OnGetTableSchema');

		if (!array_key_exists('savedata', $arParams) || $arParams['savedata'] != 'Y')
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/db/' . $connection->getType() . '/uninstall.sql');
		}

		UnRegisterModule('perfmon');

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
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/images', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/wizards', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/wizards', true, true);
		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
		DeleteDirFilesEx('/bitrix/images/perfmon/');

		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step, $errors;
		$PERF_RIGHT = CMain::GetGroupRight('perfmon');
		if ($PERF_RIGHT >= 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('PERF_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/step1.php');
			}
			elseif ($step == 2)
			{
				$this->InstallFiles([
				]);
				$this->InstallDB([
				]);
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('PERF_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/step2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step, $errors;
		$PERF_RIGHT = CMain::GetGroupRight('perfmon');
		if ($PERF_RIGHT == 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('PERF_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/unstep1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'savedata' => $_REQUEST['savedata'],
				]);
				$this->UnInstallFiles();
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('PERF_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/install/unstep2.php');
			}
		}
	}

	public function GetModuleRightList()
	{
		$arr = [
			'reference_id' => ['D','R','W'],
			'reference' => [
				'[D] ' . GetMessage('PERF_DENIED'),
				'[R] ' . GetMessage('PERF_VIEW'),
				'[W] ' . GetMessage('PERF_ADMIN'),
			]
		];
		return $arr;
	}

	public static function OnGetTableSchema()
	{
		return [
			'perfmon' => [
				'b_perf_hit' => [
					'ID' => [
						'b_perf_component' => 'HIT_ID',
						'b_perf_sql' => 'HIT_ID',
						'b_perf_cache' => 'HIT_ID',
						'b_perf_error' => 'HIT_ID',
					],
					'DATE_HIT' => [
						'~edit_mode' => 'datetime', //"date"
					],
					'IS_ADMIN' => [
						'~edit_mode' => 'checkbox',
					],
					'SCRIPT_NAME' => [
						'~edit_mode' => 'text', //"textarea"
					],
					'REQUEST_URI' => [
						'~edit_mode' => 'text',
						'~text_size' => 80,
					],
					'CACHE_TYPE' => [
						'~edit_mode' => 'select',
						'~select_values' => [
							'A' => 'Auto',
							'Y' => 'Yes',
							'N' => 'No',
						],
					],
					'CACHE_SIZE' => [
						'~edit_mode' => 'read_only',
					],
				],
				'b_perf_component' => [
					'ID' => [
						'b_perf_sql' => 'COMPONENT_ID',
						'b_perf_cache' => 'COMPONENT_ID',
					],
				],
				'b_perf_sql' => [
					'ID' => [
						'b_perf_sql_backtrace' => 'SQL_ID',
						'b_perf_index_suggest_sql' => 'SQL_ID',
					],
				],
				'b_perf_index_suggest' => [
					'ID' => [
						'b_perf_index_suggest_sql' => 'SUGGEST_ID',
					],
				],
			],
			'cluster' => [
				'b_cluster_dbnode' => [
					'ID' => [
						'b_perf_sql' => 'NODE_ID',
					]
				],
			],
		];
	}
}
