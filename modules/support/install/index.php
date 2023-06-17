<?php
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);

if(class_exists("support")) return;

class support extends CModule
{
	var $MODULE_ID = "support";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";

	public function __construct()
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
			$this->MODULE_VERSION = SUPPORT_VERSION;
			$this->MODULE_VERSION_DATE = SUPPORT_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("SUP_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SUP_MODULE_DESCRIPTION");
		$this->MODULE_CSS = "/bitrix/modules/support/support_admin.css";
	}
	
	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$EMPTY = false;
		$errors = false;
		if (!$DB->Query("SELECT 'x' FROM b_ticket", true))
		{
			$EMPTY = true;
		}
		
		if ($EMPTY)
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/db/mysql/install.sql');
		}
		
		if (is_array($errors))
		{
			$APPLICATION->ThrowException(implode(' ', $errors));
			return false;
		}
		
		RegisterModule('support');
		
		RegisterModuleDependences('mail', 'OnGetFilterList', 'support', 'CSupportEMail', 'OnGetFilterList');
		
		CAgent::RemoveModuleAgents( "support" );
		CAgent::AddAgent( "CTicketReminder::AgentFunction();", "support", "N", 60 );
		CAgent::AddAgent('CTicket::CleanUpOnline();', 'support', 'N');
		CAgent::AddAgent('CTicket::AutoClose();', 'support', 'N');

		
		if ($EMPTY)
		{
			if ($arParams['admin'] == 'Y')
			{
				$this->InstallEvents();
			}
			
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/support/install/demodata.php'))
			{
				$DD_ERROR = false;
				include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/support/install/demodata.php');
				if ($DD_ERROR)
				{
					return false;
				}
			}
		}

		// new search
		if (CModule::IncludeModule("search"))
		{
			COption::SetOptionString('support', 'SEARCH_VERSION', '12.0.3');
		}
		
		return true;
	}
	
	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$errors = false;

		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/db/mysql/uninstall.sql');
			if (!is_array($errors))
			{
				@set_time_limit(600);
				COption::RemoveOption('support');
				
				$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'support'");
				while($arRes = $db_res->Fetch())
				{
					CFile::Delete($arRes['ID']);
				}
				
				if ($arParams['admin'] == 'Y')
				{
					$this->UnInstallEvents();
				}
			}
		}
		
		if (is_array($errors))
		{
			$APPLICATION->ThrowException(implode(' ', $errors));
			return false;
		}		
		
		CAgent::RemoveModuleAgents('support');
		UnRegisterModuleDependences('mail', 'OnGetFilterList', 'support', 'CSupportEMail', 'OnGetFilterList');
		UnRegisterModule('support');
		
		return true;
	}
	
	function InstallEvents()
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/events/set_events.php'))
		{
			$SE_ERROR = false;
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/events/set_events.php');
			if ($SE_ERROR)
			{
				return false;
			}
		}
		
		return true;
	}
	
	function UnInstallEvents()
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/events/del_events.php'))
		{
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/events/del_events.php');
		}
		
		return true;
	}	
	
	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/support', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/upload/support/not_image', $_SERVER['DOCUMENT_ROOT'].'/'.COption::GetOptionString('main', 'upload_dir', 'upload').'/support/not_image'); 
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/tools', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools'); 
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		
			//Theme
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/themes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes', true, true);
		}
		
		if ($arParams['install_public'] == 'Y' && !empty($arParams['public_dir']))
		{
			$bReWriteAdditionalFiles = $arParams['public_rewrite'] == 'Y';
			
			$rsSite = CSite::GetList();
			while ($arSite = $rsSite->Fetch())
			{
				CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/public/all/', $arSite['ABS_DOC_ROOT'].$arSite['DIR'].$arParams['public_dir'], $bReWriteAdditionalFiles, true);
			}
		}
		
		return true;
	}
	
	function UnInstallFiles($arParams = array())
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/.default');//css
		DeleteDirFilesEx('/bitrix/themes/.default/icons/support/');//icons
		DeleteDirFilesEx('/bitrix/images/support/');//images
		
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/support/install/tools/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/');
		
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;

		if(!CBXFeatures::IsFeatureEditable("Support"))
		{
			$APPLICATION->ThrowException(GetMessage("SUPPORT_ERROR_EDITABLE"));
			$APPLICATION->IncludeAdminFile(GetMessage("SUP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/step2.php");
		}
		else
		{
			$step = intval($step);
			if($step<2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("SUP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/step1.php");
			}
			elseif($step==2)
			{
				$APPLICATION->ResetException();
				if ($this->InstallDB(array('admin' => 'Y')))
				{
					$this->InstallFiles(
						array(
							'install_public' => $_REQUEST['install_public'],
							'public_dir' => $_REQUEST['public_dir'],
							'public_rewrite' => $_REQUEST['public_rewrite'],
						)
					);
				}
				CBXFeatures::SetFeatureEnabled("Support", true);
				$APPLICATION->IncludeAdminFile(GetMessage("SUP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step;
		
		$step = intval($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("SUP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/unstep1.php");
		elseif($step==2)
		{
			$APPLICATION->ResetException();
			if ($this->UnInstallDB(array('admin' => 'Y', 'savedata' => $_REQUEST['savedata'])))
			{
				$this->UnInstallFiles();
			}
			CBXFeatures::SetFeatureEnabled("Support", false);
			$APPLICATION->IncludeAdminFile(GetMessage("SUP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","T","V","W"),
			"reference" => array(
				"[D] ".GetMessage("SUP_DENIED"),
				"[R] ".GetMessage("SUP_CREATE_TICKET"),
				"[T] ".GetMessage("SUP_SUPPORT_STAFF_MEMBER"),
				"[V] ".GetMessage("SUP_DEMO_ACCESS"),
				"[W] ".GetMessage("SUP_SUPPORT_ADMIN"))
			);
		return $arr;
	}
}
