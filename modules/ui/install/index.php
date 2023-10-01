<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

if (class_exists("ui"))
{
	return;
}

class UI extends \CModule
{
	public $MODULE_ID = 'ui';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	private $errors;

	protected $events = [
		'main' => [
			'OnUserDelete' => ['\Bitrix\UI\Integration\Main\User', 'onDelete'],
			'OnFileDelete' => ['\Bitrix\UI\Avatar\Mask\Item', 'onFileDelete']
		],
		'rest' => [
			'onRestAppDelete' => ['\Bitrix\UI\Integration\Rest\App', 'onRestAppDelete'],
			'OnRestAppInstall' => ['\Bitrix\UI\Integration\Rest\App', 'OnRestAppInstall'],
			// import/export
			'onRestApplicationConfigurationGetManifest' => ['\Bitrix\UI\Integration\Rest\MaskManifest', 'onRestApplicationConfigurationGetManifest'],
			'onRestApplicationConfigurationGetManifestSetting' => ['\Bitrix\UI\Integration\Rest\MaskManifest', 'onRestApplicationConfigurationGetManifestSetting'],
			'onRestApplicationConfigurationExport' => ['\Bitrix\UI\Integration\Rest\MaskManifest', 'onRestApplicationConfigurationExport'],
			'onRestApplicationConfigurationEntity' => ['\Bitrix\UI\Integration\Rest\MaskManifest', 'onRestApplicationConfigurationEntity'],
			'onRestApplicationConfigurationImport' => ['\Bitrix\UI\Integration\Rest\MaskManifest', 'onRestApplicationConfigurationImport'],
		],
	];

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("UI_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("UI_INSTALL_DESCRIPTION");
	}

	function doInstall()
	{
		$this->installDB();
		$this->installFiles();
		$this->installEvents();
		$this->installInitialData();
	}

	function doUninstall()
	{

	}

	function installFiles()
	{
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true
		);

		return true;
	}

	function installDB()
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_ui_entity_editor_config'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/ui/install/db/' . $connection->getType() . '/install.sql');
		}

		if (is_array($this->errors))
		{
			throw new SystemException(implode(' ', $this->errors));
		}

		ModuleManager::registerModule($this->MODULE_ID);

		\CAgent::addAgent('\Bitrix\UI\FileUploader\TempFileAgent::clearOldRecords();', 'ui', 'N', 1800);

		$eventManager = Bitrix\Main\EventManager::getInstance();
		foreach ($this->events as $module => $events)
		{
			foreach ($events as $eventCode => $callback)
			{
				$eventManager->registerEventHandler(
					$module,
					$eventCode,
					$this->MODULE_ID,
					$callback[0],
					$callback[1]
				);
			}
		}

		return true;
	}

	function installInitialData()
	{
		include_once __DIR__ . '/initialdata/masks.php';
	}

	function uninstallDB()
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();

		$this->errors = $DB->RunSQLBatch(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/ui/install/db/' . $connection->getType() . '/uninstall.sql'
		);

		if (is_array($this->errors))
		{
			throw new SystemException(implode(' ', $this->errors));
		}

		return true;
	}

	function uninstallEvents()
	{
		return true;
	}

	function uninstallFiles()
	{
		return true;
	}

}
