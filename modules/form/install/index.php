<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("form")) return;

class form extends CModule
{
	var $MODULE_ID = "form";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	function form()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = FORM_VERSION;
			$this->MODULE_VERSION_DATE = FORM_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("FORM_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("FORM_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step, $errors, $public_dir;

		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if ($FORM_RIGHT>="W")
		{
			/*
			$step = IntVal($step);
			if($step<2)
			{
				$APPLICATION->IncludeAdminFile(
					GetMessage("FORM_INSTALL_TITLE"),
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/step1.php"
				);
			}
			elseif($step==2)
			{
			*/
				$errors = false;

				$this->InstallFiles();
				$this->InstallDB();

				//$GLOBALS["errors"] = $this->errors;

				// if there wasn't db install example
				// if ($EMPTY=="Y" && file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/db/".strtolower($DB->type)."/example.sql"))
				// {
					// $errors2 = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/db/".strtolower($DB->type)."/example.sql");
					// if (is_array($errors2)) $errors = array_merge($errors, $errors2);
				// }

				$APPLICATION->IncludeAdminFile(
					GetMessage("FORM_INSTALL_TITLE"),
					$DOCUMENT_ROOT."/bitrix/modules/form/install/step2.php"
				);
			//}
		}
	}

	function InstallDB()
	{
		global $APPLICATION, $DB, $errors;

		if (!$DB->Query("SELECT 'x' FROM b_form", true)) $EMPTY = "Y"; else $EMPTY = "N";

		$errors = false;

		if ($EMPTY=="Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/db/".mb_strtolower($DB->type)."/install.sql");
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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/form", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/upload/form/not_image", $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/form/not_image/");
		}
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step, $errors;

		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if ($FORM_RIGHT>="W")
		{
			$step = intval($step);
			if($step<2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("FORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/unstep1.php");
			}
			elseif($step==2)
			{
				$errors = false;

				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));

				$this->UnInstallFiles(array(
					"savedata" => $_REQUEST["savedata"],
				));

				$APPLICATION->IncludeAdminFile(GetMessage("FORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/unstep2.php");
			}
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB, $errors;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$errors = false;
			// delete whole base
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/db/".mb_strtolower($DB->type)."/uninstall.sql");

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

	function UnInstallFiles($arParams = array())
	{
		global $DB;

		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			// delete all images
			$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'form'");
			while($arRes = $db_res->Fetch()) CFile::Delete($arRes["ID"]);
		}

		// Delete files
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/form/");//icons
		DeleteDirFilesEx("/bitrix/images/form/");//images
		DeleteDirFilesEx("/bitrix/js/form/");//javascript

		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/");

		// delete temporary template files - for old template system
		DeleteDirFilesEx(BX_PERSONAL_ROOT."/tmp/form/");

		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function GetModuleRightList()
	{
		global $MESS;
		$arr = array(
			"reference_id" => array("D","R","W"),
			"reference" => array(
				"[D] ".GetMessage("FORM_DENIED"),
				"[R] ".GetMessage("FORM_OPENED"),
				"[W] ".GetMessage("FORM_FULL"))
			);
		return $arr;
	}
}
