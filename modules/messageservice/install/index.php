<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class messageservice extends CModule
{
	var $MODULE_ID = "messageservice";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("MESSAGESERVICE_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("MESSAGESERVICE_INSTALL_DESCRIPTION");
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $APPLICATION;

		$errors = null;
		if (!$DB->Query("SELECT 'x' FROM b_messageservice_message", true))
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/db/mysql/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("messageservice");

		RegisterModuleDependences('main', 'OnAfterEpilog', 'messageservice', '\Bitrix\MessageService\Queue', 'run');
		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestServiceBuildDescription');
		RegisterModuleDependences('rest', 'OnRestAppDelete', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestAppDelete');
		RegisterModuleDependences('rest', 'OnRestAppUpdate', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestAppUpdate');

		COption::SetOptionString("messageservice", "clean_up_period", "14");

		CAgent::AddAgent('\Bitrix\MessageService\Queue::cleanUpAgent();',"messageservice", "Y", 86400);

		if (CModule::IncludeModule('messageservice'))
		{
			\Bitrix\MessageService\Converter::onInstallModule();
		}

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $APPLICATION;

		$errors = null;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/db/mysql/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
			\Bitrix\Main\Config\Option::delete($this->MODULE_ID);
		}

		UnRegisterModuleDependences('main', 'OnAfterEpilog', 'messageservice', '\Bitrix\MessageService\Queue', 'run');
		UnRegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestServiceBuildDescription');
		UnRegisterModuleDependences('rest', 'OnRestAppDelete', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestAppDelete');
		UnRegisterModuleDependences('rest', 'OnRestAppUpdate', 'messageservice', '\Bitrix\MessageService\RestService', 'onRestAppUpdate');

		UnRegisterModule("messageservice");

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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

			if (is_dir($p = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/components/bitrix"))
			{
				foreach (scandir($p) as $item)
				{
					if ($item == '..' || $item == '.')
						continue;

					DeleteDirFilesEx('/bitrix/components/bitrix/'.$item);
				}
			}
			DeleteDirFilesEx("/bitrix/tools/messageservice");
		}

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->errors = null;

		$this->InstallFiles();
		$this->InstallDB(false);

		$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(Loc::getMessage("MESSAGESERVICE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->errors = array();

		$step = (int)$step;
		if($step<2)
		{
			$GLOBALS["messageservice_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("MESSAGESERVICE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				'savedata' => $_REQUEST['savedata']
			));
			$this->UnInstallFiles();

			$GLOBALS["messageservice_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("MESSAGESERVICE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/messageservice/install/unstep2.php");
		}
	}
}
?>