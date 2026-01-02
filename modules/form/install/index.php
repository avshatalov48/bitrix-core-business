<?php

if (class_exists("form"))
{
	return;
}

IncludeModuleLangFile(__FILE__);

class form extends CModule
{
	var $MODULE_ID = "form";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("FORM_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("FORM_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION, $errors;

		$errors = false;

		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if ($FORM_RIGHT >= "W")
		{
			$this->InstallFiles();
			$this->InstallDB();

			$APPLICATION->IncludeAdminFile(
				GetMessage("FORM_INSTALL_TITLE"),
				$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/step2.php"
			);
		}
	}

	function InstallDB()
	{
		global $APPLICATION, $DB, $errors;

		$errors = false;

		if (!$DB->Query("SELECT 'x' FROM b_form", true))
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/db/mysql/install.sql");
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("form");
		RegisterModuleDependences("sender", "OnConnectorList", "form", "\\Bitrix\\Form\\SenderEventHandler", "onConnectorListForm");

		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/form", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/tools", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $step, $errors;

		$errors = false;

		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if ($FORM_RIGHT >= "W")
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("FORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/unstep1.php");
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					"savedata" => $_REQUEST["savedata"] ?? null,
				]);

				$this->UnInstallFiles([
					"savedata" => $_REQUEST["savedata"] ?? null,
				]);

				$APPLICATION->IncludeAdminFile(GetMessage("FORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/unstep2.php");
			}
		}
	}

	function UnInstallDB($arParams = [])
	{
		global $APPLICATION, $DB, $errors;

		$errors = false;

		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			// delete whole base
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/db/mysql/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		UnRegisterModuleDependences("sender", "OnConnectorList", "form", "\\Bitrix\\Form\\SenderEventHandler", "onConnectorListForm");
		COption::RemoveOption("form");
		UnRegisterModule("form");

		return true;
	}

	function UnInstallFiles($arParams = [])
	{
		global $DB;

		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			// delete all images
			$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'form'");
			while ($arRes = $db_res->Fetch())
			{
				CFile::Delete($arRes["ID"]);
			}
		}

		// Delete files
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/form/");//icons
		DeleteDirFilesEx("/bitrix/images/form/");//images
		DeleteDirFilesEx("/bitrix/js/form/");//javascript

		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/form/install/tools/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/");

		// delete temporary template files - for old template system
		DeleteDirFilesEx(BX_PERSONAL_ROOT . "/tmp/form/");

		return true;
	}

	function GetModuleRightList()
	{
		$arr = [
			"reference_id" => ["D", "R", "W"],
			"reference" => [
				"[D] " . GetMessage("FORM_DENIED"),
				"[R] " . GetMessage("FORM_OPENED"),
				"[W] " . GetMessage("FORM_FULL")],
		];
		return $arr;
	}
}
