<?

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (class_exists("b24connector"))
	return;

class b24connector extends CModule
{
	var $MODULE_ID = "b24connector";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	var $errors = false;

	public function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__.'/version.php');
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("B24C_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("B24C_MODULE_DESCRIPTION");
	}

	function GetModuleRightList()
	{
		return array(
			"reference_id" => array("D", "R", "W"),
			"reference" => array(
				'[D] '.Loc::getMessage("B24C_RIGHT_DENIED"),
				'[R] '.Loc::getMessage("B24C_RIGHT_READ"),
				'[W] '.Loc::getMessage("B24C_RIGHT_FULL")
		));
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!$DB->Query("SELECT 'x' FROM b_b24connector_buttons", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/db/".$DBType."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
		}

		\Bitrix\Main\ModuleManager::registerModule("b24connector");
		return true;
	}

	function UnInstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if($DB->Query("SELECT 'x' FROM b_b24connector_buttons", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/db/".$DBType."/uninstall.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
		}

		\Bitrix\Main\ModuleManager::unRegisterModule("b24connector");
		return true;
	}

	function InstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('main', 'OnBuildGlobalMenu', 'b24connector', '\Bitrix\B24Connector\Helper', 'onBuildGlobalMenu');
		$eventManager->registerEventHandlerCompatible('main', 'OnBeforeProlog', 'b24connector', '\Bitrix\B24Connector\Helper', 'onBeforeProlog');

		return true;
	}

	function UnInstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnBuildGlobalMenu', 'b24connector', '\Bitrix\B24Connector\Helper', 'onBuildGlobalMenu');
		$eventManager->unRegisterEventHandler('main', 'OnBeforeProlog', 'b24connector', '\Bitrix\B24Connector\Helper', 'onBeforeProlog');
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		}

		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
			DeleteDirFilesEx("/bitrix/js/b24connector/");
			DeleteDirFilesEx("/bitrix/css/b24connector/");
			DeleteDirFilesEx("/bitrix/images/b24connector/");
			DeleteDirFilesEx("/bitrix/themes/.default/icons/b24connector/");
		}

		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(Loc::getMessage("B24C_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/step1.php");
			}
			elseif ($step == 2)
			{
				if (!IsModuleInstalled("b24connector"))
				{
					$this->InstallDB();
					$this->InstallEvents();
					$this->InstallFiles();
					$GLOBALS["errors"] = $this->errors;
					$APPLICATION->IncludeAdminFile(Loc::getMessage("B24C_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/step2.php");
				}
			}
		}
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION, $step;

		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(Loc::getMessage("B24C_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/unstep1.php");
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB();
				$this->UnInstallEvents();
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(Loc::getMessage("B24C_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/install/unstep2.php");
			}
		}
	}
}
?>
