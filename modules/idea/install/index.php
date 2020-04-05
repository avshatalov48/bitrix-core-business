<?
IncludeModuleLangFile(__FILE__);

if(class_exists("idea")) 
	return;

Class idea extends CModule
{
	var $MODULE_ID = "idea";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
		var $errors;

	function idea()
	{
		$arModuleVersion = array();

		include(__DIR__."/version.php");
		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		elseif (defined('IDEA_VERSION') && defined('IDEA_VERSION_DATE'))
		{
			$this->MODULE_VERSION = IDEA_VERSION;
			$this->MODULE_VERSION_DATE = IDEA_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("IDEA_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IDEA_MODULE_DESCRIPTION");
	}

	function GetIdeaUserFields()
	{
		//UF_CATEGORY_CODE - Idea category, depends of Iblock section tree
		//UF_ANSWER_ID - Offical answer in idea post
		//UF_ORIGINAL_ID - Original Idea ID, uses for duplicate collecting
		//UF_STATUS - Current status of Idea
		$ImportantUserFields = array(
			"UF_CATEGORY_CODE" => false,
			"UF_ANSWER_ID" => false,
			"UF_ORIGINAL_ID" => false,
			"UF_STATUS" => false,
		);
		$keysUserFields = array_keys($ImportantUserFields);

		global $USER_FIELD_MANAGER;
		$oUserFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST");
		foreach($oUserFields as $UserFieldName => $arUserField)
			if(in_array($UserFieldName, $keysUserFields))
				$ImportantUserFields[$UserFieldName] = true;

		return $ImportantUserFields;
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!$DB->Query("SELECT 'x' FROM b_idea_email_subscribe", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/".ToLower($DBType)."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		//Install User Fields
		$this->InstallUserFields();

		RegisterModule($this->MODULE_ID);
		CModule::IncludeModule($this->MODULE_ID);
		RegisterModuleDependences('socialnetwork', 'OnFillSocNetLogEvents', $this->MODULE_ID, 'CIdeaManagmentSonetNotify', 'AddLogEvent');

		return true;
	}

	function InstallUserFields()
	{
		$ImportantUserFields = $this->GetIdeaUserFields();
		$messages = array();
		$db_res = \Bitrix\Main\Localization\LanguageTable::getList(array('order'=>array('SORT'=>'ASC')));
		while($ar = $db_res->fetch())
		{
			$messages[$ar["LID"]] = IncludeModuleLangFile(__FILE__, $ar["LID"], true);
		}

		foreach($ImportantUserFields as $UserFieldName => $Exists)
		{
			if(!$Exists)
			{
				$EDIT_FORM_LABEL = array();
				foreach ($messages as $lid => $mess)
					$EDIT_FORM_LABEL[$lid] = $mess["IDEA_".$UserFieldName."_DESCRIPTION"];
				$UserType = new CUserTypeEntity();
				switch ($UserFieldName)
				{
					case "UF_CATEGORY_CODE":
						GetMessage("IDEA_UF_CATEGORY_CODE_DESCRIPTION");
						$UserType->Add(array(
							"ENTITY_ID" => "BLOG_POST",
							"FIELD_NAME" => $UserFieldName,
							"USER_TYPE_ID" => "string",
							"IS_SEARCHABLE" => "N",
							"EDIT_FORM_LABEL" => $EDIT_FORM_LABEL
						));
						break;
					case "UF_ANSWER_ID":
						GetMessage("IDEA_UF_ANSWER_ID_DESCRIPTION");
						$UserType->Add(array(
							"ENTITY_ID" => "BLOG_POST",
							"FIELD_NAME" => $UserFieldName,
							"USER_TYPE_ID" => "integer",
							"IS_SEARCHABLE" => "N",
							"MULTIPLE" => "Y",
							"EDIT_FORM_LABEL" => $EDIT_FORM_LABEL
						));
						break;
					case "UF_ORIGINAL_ID":
						GetMessage("IDEA_UF_ORIGINAL_ID_DESCRIPTION");
						$UserType->Add(array(
							"ENTITY_ID" => "BLOG_POST",
							"FIELD_NAME" => $UserFieldName,
							"USER_TYPE_ID" => "string",
							"IS_SEARCHABLE" => "N",
							"EDIT_FORM_LABEL" => $EDIT_FORM_LABEL
						));
						break;
					case "UF_STATUS":
						GetMessage("IDEA_UF_STATUS_DESCRIPTION");
						$ID = $UserType->Add(array(
							"ENTITY_ID" => "BLOG_POST",
							"FIELD_NAME" => $UserFieldName,
							"USER_TYPE_ID" => "enumeration",
							"IS_SEARCHABLE" => "N",
							"EDIT_FORM_LABEL" => $EDIT_FORM_LABEL
						));

						if(intval($ID)>0)
						{
							$UserTypeEnum = new CUserFieldEnum();
							$UserTypeEnum->SetEnumValues($ID, array(
								"n0" => array(
									"SORT" => 100,
									"XML_ID" => "NEW",
									"VALUE" => GetMessage("IDEA_UF_STATUS_NEW_TITLE"),
									"DEF" => "Y",
								),
								"n1" => array(
									"SORT" => 200,
									"XML_ID" => "PROCESSING",
									"VALUE" => GetMessage("IDEA_UF_STATUS_PROCESSING_TITLE"),
									"DEF" => "N",
								),
								"n2" => array(
									"SORT" => 300,
									"XML_ID" => "COMPLETED",
									"VALUE" => GetMessage("IDEA_UF_STATUS_COMPLETED_TITLE"),
									"DEF" => "N",
								),
							));
						}
						break;
				}
			}
		}
	}

	function UnInstallUserFields()
	{
		$keysUserFields = array(
			"UF_CATEGORY_CODE",
			"UF_ANSWER_ID",
			"UF_ORIGINAL_ID",
			"UF_STATUS",
		);

		global $USER_FIELD_MANAGER;
		$oUserFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST");
		$ent = new CUserTypeEntity;
		foreach($oUserFields as $UserFieldName => $arUserField)
			if(in_array($UserFieldName, $keysUserFields))
			{
				$ent->Delete($arUserField["ID"]);
			}
		return;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		$arSQLErrors = array();

		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$this->UnInstallUserFields();
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/".ToLower($DBType)."/uninstall.sql");
		}
		if(!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		$this->UnInstallUserFields();
		UnRegisterModuleDependences('socialnetwork', 'OnFillSocNetLogEvents', $this->MODULE_ID, 'CIdeaManagmentSonetNotify', 'AddLogEvent');
		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	function InstallEvents()
	{
		include_once(__DIR__."/events.php");
		return true;
	}

	function UnInstallEvents()
	{
		//Comment
		$EM = new CEventMessage;
		$oEventMessgae = $EM->GetList($by = "", $order = "", array("EVENT_NAME" => "ADD_IDEA_COMMENT"));
		while($arEvent = $oEventMessgae->Fetch())
			$EM->Delete($arEvent["ID"]);

		$ET = new CEventType;
		$ET->Delete("ADD_IDEA_COMMENT");

		//Idea
		$oEventMessgae = $EM->GetList($by = "", $order = "", array("EVENT_NAME" => "ADD_IDEA"));
		while($arEvent = $oEventMessgae->Fetch())
			$EM->Delete($arEvent["ID"]);

		$ET->Delete("ADD_IDEA");

		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->errors = false;
		$step = IntVal($step);
		$GLOBALS["obModule"] = $this;

		if(!check_bitrix_sessid())
			$this->errors[] = GetMessage("ERR_SESSION_EXPIRED");
		if(!IsModuleInstalled("iblock"))
			$this->errors[] = GetMessage("ERR_IBLOCK_MODULE_NOT_INSTALLED");
		if(!IsModuleInstalled("blog"))
			$this->errors[] = GetMessage("ERR_BLOG_MODULE_NOT_INSTALLED");

		if($this->errors !== false)
		{
			//Installation error
			$APPLICATION->IncludeAdminFile(GetMessage("ERR_IDEA_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step2.php");
		}
		else
		{
			if($step<2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("IDEA_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
			}
			elseif($step == 2)
			{
				if($this->InstallFiles())
				{
					$this->InstallDB();
					$this->InstallEvents();
				}
				$APPLICATION->IncludeAdminFile(GetMessage("IDEA_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $APPLICATION;
		if (!check_bitrix_sessid())
			return false;
		$GLOBALS["errors"] = false;
		$step = intval($_REQUEST["step"]);
		if($step < 2)
		{
			$GLOBALS["APPLICATION"]->IncludeAdminFile(GetMessage("FORUM_DELETE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/idea/install/unstep1.php");
		}
		else
		{
			if ($this->UnInstallDB(array("savedata" => $_REQUEST["savedata"])))
			{
				$this->UnInstallEvents();
				$this->UnInstallFiles();
			}
			$GLOBALS["CACHE_MANAGER"]->CleanAll();
			$GLOBALS["stackCacheManager"]->CleanAll();
			$GLOBALS["errors"] = $this->errors;
			$GLOBALS["APPLICATION"]->IncludeAdminFile(GetMessage("FORUM_DELETE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/idea/install/unstep2.php");
		}
	}
}