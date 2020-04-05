<?global $DOCUMENT_ROOT, $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists("photogallery")) return;

Class photogallery extends CModule
{
	var $MODULE_ID = "photogallery";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	function photogallery()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = FORUM_VERSION;
			$this->MODULE_VERSION_DATE = FORUM_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("P_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("P_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		RegisterModule("photogallery");
		RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "photogallery", "CPhotogalleryElement", "OnBeforeIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "photogallery", "CPhotogalleryElement", "OnAfterIBlockElementAdd");
		RegisterModuleDependences("search", "BeforeIndex", "photogallery", "CRatingsComponentsPhotogallery", "BeforeIndex");
		RegisterModuleDependences("im", "OnGetNotifySchema", "photogallery", "CPhotogalleryNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "photogallery", "\\Bitrix\\Photogallery\\Integration\\Socialnetwork\\Group", "onSocNetGroupDelete");
		return true;
	}

	function UnInstallDB()
	{
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "photogallery", "CPhotogalleryElement", "OnBeforeIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "photogallery", "CPhotogalleryElement", "OnAfterIBlockElementAdd");
		UnRegisterModuleDependences("search", "BeforeIndex", "photogallery", "CRatingsComponentsPhotogallery", "BeforeIndex");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "photogallery", "CPhotogalleryNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "photogallery", "\\Bitrix\\Photogallery\\Integration\\Socialnetwork\\Group", "onSocNetGroupDelete");
		UnRegisterModule("photogallery");
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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		if (!check_bitrix_sessid())
			return false;
		global $APPLICATION;
		if (IsModuleInstalled("iblock"))
		{
			$step = IntVal($_REQUEST["step"]);

			if ($step < 2)
				$APPLICATION->IncludeAdminFile(GetMessage("PHOTO_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/step1.php");
			elseif($step == 2)
				$APPLICATION->IncludeAdminFile(GetMessage("PHOTO_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/step2.php");
			elseif ($step == 3)
			{
				$this->InstallDB();
				$this->InstallEvents();
				$this->InstallFiles();
				LocalRedirect("module_admin.php?lang=".LANGUAGE_ID);
			}
		}
		elseif (!IsModuleInstalled("photogallery"))
		{
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
		}
	}

	function DoUninstall()
	{
		if (!check_bitrix_sessid())
			return false;
		$this->UnInstallDB();
		$this->UnInstallEvents();
		$this->UnInstallFiles();
	}
}
?>