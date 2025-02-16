<?php

if (class_exists('workflow'))
{
	return;
}

IncludeModuleLangFile(__FILE__);

class workflow extends CModule
{
	public $MODULE_ID = 'workflow';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = 'Y';
	public $errors;

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = GetMessage('FLOW_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('FLOW_MODULE_DESCRIPTION');
		$this->MODULE_CSS = '/bitrix/modules/workflow/workflow.css';
	}

	public function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		$bDBInstall = !$DB->TableExists('b_workflow_document');
		if ($bDBInstall)
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));

			return false;
		}
		else
		{
			RegisterModule('workflow');
			CModule::IncludeModule('workflow');

			if ($bDBInstall)
			{
				$obWorkflowStatus = new CWorkflowStatus();
				$obWorkflowStatus->Add([
					'~TIMESTAMP_X' => $DB->GetNowFunction(),
					'C_SORT' => 300,
					'ACTIVE' => 'Y',
					'TITLE' => GetMessage('FLOW_INSTALL_PUBLISHED'),
					'IS_FINAL' => 'Y',
					'NOTIFY' => 'N',
				]);
				$obWorkflowStatus->Add([
					'~TIMESTAMP_X' => $DB->GetNowFunction(),
					'C_SORT' => 100,
					'ACTIVE' => 'Y',
					'TITLE' => GetMessage('FLOW_INSTALL_DRAFT'),
					'IS_FINAL' => 'N',
					'NOTIFY' => 'N',
				]);
				$obWorkflowStatus->Add([
					'~TIMESTAMP_X' => $DB->GetNowFunction(),
					'C_SORT' => 200,
					'ACTIVE' => 'Y',
					'TITLE' => GetMessage('FLOW_INSTALL_READY'),
					'IS_FINAL' => 'N',
					'NOTIFY' => 'Y',
				]);
			}

			RegisterModuleDependences('main', 'OnPanelCreate', 'workflow', 'CWorkflow', 'OnPanelCreate', '200');
			RegisterModuleDependences('main', 'OnChangeFile', 'workflow', 'CWorkflow', 'OnChangeFile');

			//agents
			CAgent::RemoveAgent('CWorkflow::CleanUp();', 'workflow');
			CAgent::AddAgent('CWorkflow::CleanUp();', 'workflow');

			return true;
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!array_key_exists('savedata', $arParams) || ($arParams['savedata'] != 'Y'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/db/' . $connection->getType() . '/uninstall.sql');
		}

		UnRegisterModuleDependences('main', 'OnPanelCreate', 'workflow', 'CWorkflow', 'OnPanelCreate');
		UnRegisterModuleDependences('main', 'OnChangeFile', 'workflow', 'CWorkflow', 'OnChangeFile');

		UnRegisterModule('workflow');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));

			return false;
		}

		return true;
	}

	protected static $events = "'WF_STATUS_CHANGE', 'WF_NEW_DOCUMENT', 'WF_IBLOCK_STATUS_CHANGE', 'WF_NEW_IBLOCK_ELEMENT'";

	public function InstallEvents()
	{
		global $DB;

		$rs = $DB->Query("SELECT 'x' FROM b_event_type WHERE EVENT_NAME IN (" . static::$events . ') LIMIT 1');
		if (!$rs->Fetch())
		{
			include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/events/set_events.php';
		}

		return true;
	}

	public function UnInstallEvents()
	{
		global $DB;

		$DB->Query('DELETE FROM b_event_message WHERE EVENT_NAME IN (' . static::$events . ') ');
		$DB->Query('DELETE FROM b_event_type WHERE EVENT_NAME IN (' . static::$events . ') ');

		return true;
	}

	public function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/images', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/workflow', true, true);

		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFilesEx('/bitrix/images/workflow/');

		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step;

		$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight('workflow');
		if ($WORKFLOW_RIGHT == 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('FLOW_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/step1.php');
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('FLOW_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/step2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step;

		$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight('workflow');
		if ($WORKFLOW_RIGHT == 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('FLOW_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/unstep1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'savedata' => $_REQUEST['savedata'],
				]);
				//message types and templates
				if ($_REQUEST['save_templates'] != 'Y')
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('FLOW_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/install/unstep2.php');
			}
		}
	}

	public function GetModuleRightList()
	{
		$arr = [
			'reference_id' => ['D', 'R', 'U', 'W'],
			'reference' => [
				'[D] ' . GetMessage('FLOW_DENIED'),
				'[R] ' . GetMessage('FLOW_READ'),
				'[U] ' . GetMessage('FLOW_MODIFY'),
				'[W] ' . GetMessage('FLOW_WRITE')
			],
		];

		return $arr;
	}
}
