<?
global $MESS;

IncludeModuleLangFile(__FILE__);

Class report extends CModule
{
	var $MODULE_ID = "report";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $errors;

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("REPORT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("REPORT_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_report WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/db/mysql/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		RegisterModule("report");
		RegisterModuleDependences('report', 'OnReportDelete', 'report', '\Bitrix\Report\Sharing', 'OnReportDelete');

		// visual reports
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('report', 'onReportCategoryCollect', 'report', '\Bitrix\Report\VisualConstructor\EventHandler', 'onCategoriesCollect');
		$eventManager->registerEventHandler('report', 'onReportsCollect', 'report', '\Bitrix\Report\VisualConstructor\EventHandler', 'onReportsCollect');
		$eventManager->registerEventHandler('report', 'onReportViewCollect', 'report', '\Bitrix\Report\VisualConstructor\EventHandler', 'onViewsCollect');
		$eventManager->registerEventHandler('report', 'onWidgetCollect', 'report', '\Bitrix\Report\VisualConstructor\EventHandler', 'onWidgetCollect');

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;



		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/db/mysql/uninstall.sql");
		}

		UnRegisterModuleDependences('report', 'OnReportDelete', 'report', '\Bitrix\Report\Sharing', 'OnReportDelete');
		UnRegisterModule("report");

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
		global $DB;

		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/components",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/components",
				true,
				true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/public/js",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/js",
				true,
				true
			);
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/images",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/js/report/");//scripts
		DeleteDirFilesEx("/bitrix/images/report/");
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION;

		if (!IsModuleInstalled("report"))
		{
			$this->InstallFiles();
			$this->InstallDB();
			$this->InstallEvents();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("REPORT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/step1.php");
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = intval($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("REPORT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			$this->UnInstallEvents();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("REPORT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/report/install/unstep2.php");
		}
	}
}
?>