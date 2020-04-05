<?if(!check_bitrix_sessid() || !CModule::IncludeModule("iblock") || !CModule::IncludeModule("blog"))
	return;

IncludeModuleLangFile(__FILE__);

//Include Idea API
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/idea/include.php");

Class CIdeaManagmentInstall
{
	private $PublicDir = '#SITE_DIR#services/idea/';

	private $ModuleIblockType = 'services';
	private $ModuleIblockCode = 'idea';
	private $ModuleBlogGroup = false;
	private $ModuleBlogUrl = "idea";
	private $SITE_ID = false;
	private $REWRITE = true;
	private $IO = false;
	private $DOCUMENT_ROOT = false;

	private $arResult = array(
		"SETTINGS" => array(),
		"INSTALLATION" => array(
			"SITE" => array(),
		),
		"ERRORS" => array(),
	);

	public function __construct($arParams)
	{
		$this->SITE_ID = $arParams["SITE_ID"];
		$this->REWRITE = $arParams["REWRITE"];
		$this->ModuleBlogGroup = '['.$this->SITE_ID.'] '.GetMessage("IDEA_BLOG_GROUP_NAME");
		$this->ModuleBlogUrl .= "_".$this->SITE_ID;

		//NULL CACHE
		BXClearCache(True, '/'.$this->SITE_ID.'/idea/');
		BXClearCache(True, '/'.SITE_ID.'/idea/');
		global $CACHE_MANAGER;
		if(CACHED_b_user_field_enum!==false)
			$CACHE_MANAGER->CleanDir("b_user_field_enum");

		//Statuses List (for demo)
		$this->arResult["SETTINGS"]["STATUS"] = CIdeaManagment::getInstance()->Idea()->GetStatusList();
		foreach($this->arResult["SETTINGS"]["STATUS"] as $arStatus)
			$this->arResult["SETTINGS"]["STATUS_ID"][$arStatus["XML_ID"]] = $arStatus["ID"];

		//Lang List
		$l = CLanguage::GetList($by="sort", $order="asc");
		while($r = $l->Fetch())
			$this->arResult["SETTINGS"]["LANG"][] = $r;

		//Sites List
		$oSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
		while ($site = $oSites->Fetch())
			$this->arResult["SETTINGS"]["SITE"][$site["LID"]] = Array(
				"LANGUAGE_ID" => $site["LANGUAGE_ID"],
				"ABS_DOC_ROOT" => $site["ABS_DOC_ROOT"],
				"DIR" => $site["DIR"],
				"SITE_ID" => $site["LID"],
				"SERVER_NAME" =>$site["SERVER_NAME"],
				"NAME" => $site["NAME"],
			);

		if(array_key_exists($this->SITE_ID, $this->arResult["SETTINGS"]["SITE"]))
			$this->PublicDir = str_replace(array("#SITE_DIR#"), array($this->arResult["SETTINGS"]["SITE"][$this->SITE_ID]["DIR"]), $this->PublicDir);

		$site = CFileMan::__CheckSite($this->SITE_ID);
		$this->DOCUMENT_ROOT = CSite::GetSiteDocRoot($site);
		$this->IO = CBXVirtualIo::GetInstance();

		//SetDefault
		$this->arResult["INSTALLATION"]["IBLOCK_TYPE_INSTALL"] = true;
		$this->arResult["INSTALLATION"]["IBLOCK_INSTALL"] = true;
		$this->arResult["INSTALLATION"]["BLOG_GROUP_INSTALL"] = true;
		$this->arResult["INSTALLATION"]["BLOG_INSTALL"] = true;

		$this->CheckParams();
	}

	public function CheckPrevInstallation()
	{
		if(!$this->HaveError())
		{
			$this->CheckIblockType();
			$this->CheckIblock();
			$this->CheckBlogGroup();
			$this->CheckBlog();
		}
	}

	public function HaveError()
	{
		return !empty($this->arResult["ERRORS"]);
	}

	public function GetError()
	{
		return $this->arResult["ERRORS"];
	}

	private function CheckParams()
	{
		global $APPLICATION;

		if(empty($this->arResult["SETTINGS"]["SITE"]) || !in_array($this->SITE_ID, array_keys($this->arResult["SETTINGS"]["SITE"])))
			$this->arResult["ERRORS"][] = GetMessage("IDEA_INSTALL_ERROR_SITE_NOT_FOUND");
		if($APPLICATION->GetGroupRight("iblock") < "W")
			$this->arResult["ERRORS"][] = GetMessage("IDEA_INSTALL_ERROR_IBLOCK_NO_PERMISSTION");
		if($APPLICATION->GetGroupRight("blog") < "W")
			$this->arResult["ERRORS"][] = GetMessage("IDEA_INSTALL_ERROR_BLOG_NO_PERMISSTION");
	}

	private function CheckIblockType()
	{
		if($arIblockType = CIBlockType::GetByID($this->ModuleIblockType)->Fetch()) //IBType alreaddy exists
		{
			$this->arResult["INSTALLATION"]["IBLOCK_TYPE_INSTALL"] = false;
			$this->arResult["INSTALLATION"]["IBLOCK_TYPE_ID"] = $this->ModuleIblockType;
		}
	}

	private function CheckIblock()
	{
		if($arIblock = CIblock::GetList(array(), array("CODE" => $this->ModuleIblockCode))->Fetch())
		{
			$this->arResult["INSTALLATION"]["IBLOCK_INSTALL"] = false;
			$this->arResult["INSTALLATION"]["IBLOCK_ID"] = $arIblock["ID"];
		}
	}

	private function CheckBlogGroup()
	{
		$arFilter = Array(
			"SITE_ID" => $this->SITE_ID,
			"NAME" => $this->ModuleBlogGroup
		);

		if($arBlogGroup = CBlogGroup::GetList(array(), $arFilter)->Fetch())
		{
			$this->arResult["INSTALLATION"]["BLOG_GROUP_INSTALL"] = false;
			$this->arResult["INSTALLATION"]["BLOG_GROUP_ID"] = $arBlogGroup["ID"];
			$this->arResult["INSTALLATION"]["BLOG_GROUP_NAME"] = $this->ModuleBlogGroup;
		}
	}

	private function CheckBlog()
	{
		$arFilter = Array(
			"URL" => $this->ModuleBlogUrl,
			"GROUP_ID" => $this->arResult["INSTALLATION"]["BLOG_GROUP_ID"]
		);

		if($arBlog = CBlog::GetList(array(), $arFilter)->Fetch())
		{
			$this->arResult["INSTALLATION"]["BLOG_INSTALL"] = false;
			$this->arResult["INSTALLATION"]["BLOG_ID"] = $arBlog["ID"];
		}
	}

	public function Install()
	{
		if(!$this->HaveError())
			$this->InstallIblockType();
		if(!$this->HaveError())
			$this->InstallIblock();
		if(!$this->HaveError())
			$this->InstallBlogGroup();
		if(!$this->HaveError())
			$this->InstallBlog();
		if(!$this->HaveError())
		{
			$this->CopyPublucFiles();
			$this->AddMenuItem();
		}
	}

	private function InstallIblockType()
	{
		if($this->arResult["INSTALLATION"]["IBLOCK_TYPE_INSTALL"])
		{
			$arFields = array(
				"ID" => $this->ModuleIblockType,
				"SECTIONS" => "Y",
				"IN_RSS" => "N",
				"SORT" => 100,
				"LANG" => array()
			);

			foreach($this->arResult["SETTINGS"]["LANG"] as $Lang)
			{
				$m = IncludeModuleLangFile(__FILE__, $Lang["LANGUAGE_ID"], true);
				$arFields["LANG"][$Lang["LANGUAGE_ID"]] = array(
					"NAME" => (strlen($m["IDEA_INSTALL_IBLOCK_TYPE"])==0 ? "Idea Management" : $m["IDEA_INSTALL_IBLOCK_TYPE"]),
					"SECTION_NAME" => (strlen($m["IDEA_INSTALL_IBLOCK_SECTION_NAME"])==0 ? "Category" : $m["IDEA_INSTALL_IBLOCK_SECTION_NAME"]),
				);
			}

			$IBT = new CIBlockType();
			if(!$IblockTypeId = $IBT->Add($arFields))
				$this->arResult["ERRORS"][] = $IBT->LAST_ERROR;

			$this->arResult["INSTALLATION"]["IBLOCK_TYPE_ID"] = $IblockTypeId;
		}
	}

	private function InstallIblock()
	{
		if($this->arResult["INSTALLATION"]["IBLOCK_INSTALL"])
		{
			$arFields = array(
				"ACTIVE" => "Y",
				"INDEX_ELEMENT" => "N",
				"WORKFLOW" => "N",
				"NAME" => GetMessage("IDEA_CATEGORY_INFOBLOCK_NAME"),
				"IBLOCK_TYPE_ID" => $this->arResult["INSTALLATION"]["IBLOCK_TYPE_ID"],
				"LID" => array(),
				"CODE" => "idea",
				"FIELDS" => array(
					"SECTION_CODE" => array(
						"IS_REQUIRED" => "Y",
						"DEFAULT_VALUE" => array
						(
							"UNIQUE" => "Y",
							"TRANSLITERATION" => "Y",
							"TRANS_LEN" => 50,
							"TRANS_CASE" => "L",
							"TRANS_SPACE" => "_",
							"TRANS_OTHER" => "_",
							"TRANS_EAT" => "Y",
							"USE_GOOGLE" => "Y",
						)
					)
				),
				"GROUP_ID" => Array("2" => "R")
			);

			foreach($this->arResult["SETTINGS"]["SITE"] as $lid => $arSite)
				$arFields["LID"][] = $lid;


			$IB = new CIBlock;
			if(!$IblockId = $IB->Add($arFields))
				$this->arResult["ERRORS"][] = $IB->LAST_ERROR;

			$this->arResult["INSTALLATION"]["IBLOCK_ID"] = $IblockId;

			if(!$this->HaveError())
			{
				$this->InstallIblockSectionSettings();
				$this->InstallIblockSection();
			}
		}
	}

	private function InstallIblockSectionSettings()
	{
		//Iblock Section Edit form, simplify
		$arIBSFormEditSetting = array(
			array(
				"c" => "form",
				"n" => "form_section_".$this->arResult["INSTALLATION"]["IBLOCK_ID"],
				"d" => "Y",
				"v" => Array(
						"tabs" => "edit1--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_TAB_TITLE")."--,--ID--#--ID--,--ACTIVE--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_ACTIVE")."--,--NAME--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_NAME")."--,--CODE--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_CODE")."--,--IBLOCK_SECTION_ID--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_IBLOCK_SECTION_ID")."--,--SORT--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_SORT")."--;--"
				)

			)
		);
		CUserOptions::SetOptionsFromArray($arIBSFormEditSetting);
	}

	private function InstallIblockSection()
	{
		$arSections = $this->arResult["INSTALLATION"]["IBLOCK_SECTION_ID"] = array();
		$arSections["MAIN_1"] = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->arResult["INSTALLATION"]["IBLOCK_ID"],
			"NAME" => GetMessage("IDEA_CATEGORY_MAIN_1_NAME"),
			"CODE" => "company",
		);
		$arSections["MAIN_1_SUB_1"] = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->arResult["INSTALLATION"]["IBLOCK_ID"],
			"IBLOCK_SECTION_ID" => "MAIN_1",
			"NAME" => GetMessage("IDEA_CATEGORY_MAIN_1_SUB_1_NAME"),
			"CODE" => "inside",
		);
		$arSections["MAIN_1_SUB_2"] = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->arResult["INSTALLATION"]["IBLOCK_ID"],
			"IBLOCK_SECTION_ID" => "MAIN_1",
			"NAME" => GetMessage("IDEA_CATEGORY_MAIN_1_SUB_2_NAME"),
			"CODE" => "outside",
		);
		$arSections["MAIN_2"] = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->arResult["INSTALLATION"]["IBLOCK_ID"],
			"NAME" => GetMessage("IDEA_CATEGORY_MAIN_2_NAME"),
			"CODE" => "our_events",
		);

		$IBS = new CIBlockSection;
		foreach($arSections as $key=>$arSection)
		{
			if(array_key_exists("IBLOCK_SECTION_ID", $arSection) && array_key_exists($arSection["IBLOCK_SECTION_ID"], $this->arResult["INSTALLATION"]["IBLOCK_SECTION_ID"]))
				$arSection["IBLOCK_SECTION_ID"] = $this->arResult["INSTALLATION"]["IBLOCK_SECTION_ID"][$arSection["IBLOCK_SECTION_ID"]];

			$this->arResult["INSTALLATION"]["IBLOCK_SECTION_ID"][$key] = $IBS->Add($arSection);
		}
	}

	private function InstallBlogGroup()
	{
		if($this->arResult["INSTALLATION"]["BLOG_GROUP_INSTALL"])
		{
			global $APPLICATION;

			$arFields = array(
				"SITE_ID" => $this->SITE_ID,
				"NAME" => $this->ModuleBlogGroup
			);

			if(!$BlogGroupId = CBlogGroup::Add($arFields))
			{
				if ($ex = $APPLICATION->GetException())
						$this->arResult["ERRORS"][] = $ex->GetString().' ['.$this->ModuleBlogGroup."]";
				else
						$this->arResult["ERRORS"][] = GetMessage("IDEA_INSTALL_ERROR_BLOG_GROUP_NOT_INSTALLED");
			}

			$this->arResult["INSTALLATION"]["BLOG_GROUP_ID"] = $BlogGroupId;
		}
	}

	private function InstallBlog()
	{
		if($this->arResult["INSTALLATION"]["BLOG_INSTALL"])
		{
			global $DB, $APPLICATION;

			$arFields = array(
				"ACTIVE" => "Y",
				"NAME" => GetMessage("IDEA_BLOG_TITLE"),
				"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
				"=DATE_CREATE" => $DB->CurrentTimeFunction(),
				"URL" => $this->ModuleBlogUrl,
				"SOCNET_GROUP_ID" => 1,
				"GROUP_ID" => $this->arResult["INSTALLATION"]["BLOG_GROUP_ID"],
				"ENABLE_COMMENTS" => "Y",
				"ENABLE_IMG_VERIF" => "Y",
				"EMAIL_NOTIFY" => "Y",
				"ENABLE_RSS" => "Y",
				"ALLOW_HTML" => "Y",
				"PERMS_POST" => array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_WRITE),
				"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE, "2" => BLOG_PERMS_WRITE),
				"PATH" => $this->PublicDir,
			);

			if(!$BlogId = CBlog::Add($arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$this->arResult["ERRORS"][] = $ex->GetString();
				else
					$this->arResult["ERRORS"][] = GetMessage("IDEA_INSTALL_ERROR_BLOG_NOT_INSTALLED");
			}

			$this->arResult["INSTALLATION"]["BLOG_ID"] = $BlogId;

			if(!$this->HaveError())
			{
				$this->InstallBlogPost();
				$this->InstallBlogComment();
			}
		}
	}

	private function InstallBlogPost()
	{
		global $DB, $USER;

		$arBlogMessages = $this->arResult["INSTALLATION"]["BLOG_POST_ID"] = array();
		//1
		$CATEGORY_ID = array();
		$CATEGORY_ID[] = CBlogCategory::Add(array("BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"], "NAME" => GetMessage("IDEA_BLOG_TAG_TITLE_1")));
		$CATEGORY_ID[] = CBlogCategory::Add(array("BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"], "NAME" => GetMessage("IDEA_BLOG_TAG_TITLE_2")));
		$arBlogMessages["NY"] = Array
		(
			"TITLE" => GetMessage("IDEA_BLOG_MESSAGE_TITLE_1"),
			"DETAIL_TEXT" => GetMessage("IDEA_BLOG_MESSAGE_DESCRIPTION_1"),
			"DETAIL_TEXT_TYPE" => "text",
			"=DATE_CREATE" => $DB->GetNowFunction(),
			"DATE_PUBLISH" => ConvertTimeStamp(false, "FULL"),
			"PUBLISH_STATUS" => "P",
			"ENABLE_TRACKBACK" => "N",
			"ENABLE_COMMENTS" => "Y",
			"CATEGORY_ID" => join(',', $CATEGORY_ID),
			"CODE" => "company_new_year_2012",
			"UF_CATEGORY_CODE" => "OUR_EVENTS",
			"UF_STATUS" => $this->arResult["SETTINGS"]["STATUS_ID"]["COMPLETED"],
			"AUTHOR_ID" => $USER->GetID(),
			"BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"],
			"PREVIEW_TEXT_TYPE" => "text",
			"PATH" => $this->PublicDir.'#post_id#/',
			"PERMS_POST" => array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_WRITE),
			"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE, "2" => BLOG_PERMS_WRITE),
		);
		//2
		$CATEGORY_ID = array();
		$CATEGORY_ID[] = CBlogCategory::Add(array("BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"], "NAME" => GetMessage("IDEA_BLOG_TAG_TITLE_3")));
		$arBlogMessages["WORKERS"] = Array
		(
			"TITLE" => GetMessage("IDEA_BLOG_MESSAGE_TITLE_2"),
			"DETAIL_TEXT" => GetMessage("IDEA_BLOG_MESSAGE_DESCRIPTION_2"),
			"DETAIL_TEXT_TYPE" => "text",
			"=DATE_CREATE" => $DB->GetNowFunction(),
			"DATE_PUBLISH" => ConvertTimeStamp(false, "FULL"),
			"PUBLISH_STATUS" => "P",
			"ENABLE_TRACKBACK" => "N",
			"ENABLE_COMMENTS" => "Y",
			"CATEGORY_ID" => join(',', $CATEGORY_ID),
			"CODE" => "new_workers",
			"UF_CATEGORY_CODE" => "OUTSIDE",
			"UF_STATUS" => $this->arResult["SETTINGS"]["STATUS_ID"]["NEW"],
			"AUTHOR_ID" => $USER->GetID(),
			"BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"],
			"PREVIEW_TEXT_TYPE" => "text",
			"PATH" => $this->PublicDir.'#post_id#/',
			"PERMS_POST" => array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_WRITE),
			"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE, "2" => BLOG_PERMS_WRITE),
		);

		foreach($arBlogMessages as $key=>$BlogMessage)
		{
			if($this->arResult["INSTALLATION"]["BLOG_POST_ID"][$key] = CBlogPost::Add($BlogMessage))
			{
				$arPostCategory = explode(',', $BlogMessage["CATEGORY_ID"]);
				foreach ($arPostCategory as $CatId)
					CBlogPostCategory::Add(array(
						"POST_ID" => $this->arResult["INSTALLATION"]["BLOG_POST_ID"][$key],
						"BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"],
						"CATEGORY_ID" => $CatId,
					));
			}
		}
	}

	private function InstallBlogComment()
	{
		global $USER;

		$arBlogComments = $this->arResult["INSTALLATION"]["BLOG_COMMENT_ID"] = array();

		if($this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"])
			$arBlogComments["COMMON"] = array(
				"TITLE" => '',
				"POST_TEXT" => GetMessage("IDEA_BLOG_COMMENT_TEXT_1"),
				"BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"],
				"POST_ID" => $this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"],
				"PARENT_ID" => 0,
				"AUTHOR_ID" => $USER->GetID(),
				"DATE_CREATE" => ConvertTimeStamp(false, "FULL"),
				"PATH" => $this->PublicDir."#post_id#/?commentId=#comment_id###comment_id#",
			);

		if($this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"])
			$arBlogComments["OFFICIAL"] = array(
				"TITLE" => '',
				"POST_TEXT" => GetMessage("IDEA_BLOG_COMMENT_TEXT_2"),
				"BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"],
				"POST_ID" => $this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"],
				"PARENT_ID" => 0,
				"AUTHOR_ID" => $USER->GetID(),
				"DATE_CREATE" => ConvertTimeStamp(false, "FULL"),
				"PATH" => $this->PublicDir."#post_id#/",
			);

		foreach($arBlogComments as $key=>$BlogComment)
		{
			$this->arResult["INSTALLATION"]["BLOG_COMMENT_ID"][$key] = CBlogComment::Add($BlogComment);

			//Make offical answer
			if($key == "OFFICIAL" && $this->arResult["INSTALLATION"]["BLOG_COMMENT_ID"][$key]>0)
			{
				if($arBlogPost = CBlogPost::GetList(array(), array("BLOG_ID" => $this->arResult["INSTALLATION"]["BLOG_ID"], "ID" => $this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"]), false, false, array("ID", CIdeaManagment::UFAnswerIdField, CIdeaManagment::UFStatusField))->Fetch())
				{
					//if Empty value make an array
					if(!is_array($arBlogPost[CIdeaManagment::UFAnswerIdField]))
						$arBlogPost[CIdeaManagment::UFAnswerIdField] = array();

					$arBlogPost[CIdeaManagment::UFAnswerIdField][] = $this->arResult["INSTALLATION"]["BLOG_COMMENT_ID"][$key];
					unset($arBlogPost["ID"]);
					CBlogPost::Update($this->arResult["INSTALLATION"]["BLOG_POST_ID"]["NY"], $arBlogPost);
				}
			}
		}
	}

	private function CopyPublucFiles()
	{
			$target = $this->DOCUMENT_ROOT.$this->PublicDir;
			$source = $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/idea/install/public/idea/";

			CopyDirFiles($source, $target, $this->REWRITE, true);
			if(file_exists($target.'index.php'))
			{
		$arReplacePageIndex = Array(
					"IDEA_SEF_FOLDER" => $this->PublicDir,
					"IDEA_BLOG_CODE" => $this->ModuleBlogUrl,
					"IDEA_IBLOCK_CATEGORY" => $this->arResult["INSTALLATION"]["IBLOCK_ID"],
					"IDEA_BIND_DEFAULT" => $this->arResult["SETTINGS"]["STATUS_ID"]["NEW"],
					#"IDEA_TITLE" => GetMessage("IDEA_PUBLIC_FILE_INDEX_TITLE"),
		);

				$arReplaceFolderSection = Array(
					"IDEA_FOLDER_NAME" => GetMessage("IDEA_PUBLIC_FOLDER_TITLE"),
				);

				//For Easy replace
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard_util.php");
		CWizardUtil::ReplaceMacros($target.'index.php', $arReplacePageIndex);
				CWizardUtil::ReplaceMacros($target.'.section.php', $arReplaceFolderSection);
				//Add Sef
				$arFields = array(
						"CONDITION" => "#^".$this->PublicDir."#",
						"RULE" => "",
						"ID" => "bitrix:idea",
						"PATH" => $this->PublicDir."index.php",
						"SITE_ID" => $this->SITE_ID,
				);
				CUrlRewriter::Add($arFields);
			}
	}

	private function AddMenuItem()
	{
		//Add menu Item
		$MenuFolderPath = $this->IO->CombinePath("/", '/services/');
		$MenuFilePath = $this->IO->CombinePath($MenuFolderPath, ".left.menu.php");
		$AbsMenuFilePath = $this->IO->CombinePath($this->DOCUMENT_ROOT, $MenuFilePath);

		if($this->IO->FileExists($AbsMenuFilePath))
		{
			$MenuResource = CFileMan::GetMenuArray($AbsMenuFilePath);
			$arMenuItems = $MenuResource["aMenuLinks"];
			$bAddMenuItem = true;
			foreach($arMenuItems as $MenuItem)
			{
				if(in_array($MenuItem[1], array($this->PublicDir, $this->PublicDir."index.php")))
				{
					$bAddMenuItem = false;
					break;
				}
			}

			if($bAddMenuItem)
			{
				$arMenuItems[] = array(
					GetMessage("IDEA_PUBLIC_FILE_MENU_TITLE"),
					$this->PublicDir,
					array(),
					array(),
					"CBXFeatures::IsFeatureEnabled('Idea')"
				);

				CFileMan::SaveMenu(Array($this->SITE_ID, $MenuFilePath), $arMenuItems, "");
			}
		}
	}
}

if(strlen($_REQUEST["idea_install"]) > 0)
{
	if($_REQUEST["demo"])
	{
		$CIdeaInstaller = new CIdeaManagmentInstall(
			array(
				"SITE_ID" => $_REQUEST["site_id"],
				"REWRITE" => $_REQUEST["file_rewrite"] == "Y",
			)
		);
		$CIdeaInstaller->CheckPrevInstallation();
		if(!$CIdeaInstaller->HaveError())
			$CIdeaInstaller->Install();

		if($CIdeaInstaller->HaveError())
			echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=> join('<br/>',$CIdeaInstaller->GetError()), "HTML"=>true));
		else
			LocalRedirect('/bitrix/admin/module_admin.php?step=2&lang='.LANGUAGE_ID.'&id=idea&install=Y&'.bitrix_sessid_get());
	}
	else
		LocalRedirect('/bitrix/admin/module_admin.php?step=2&lang='.LANGUAGE_ID.'&id=idea&install=Y&'.bitrix_sessid_get());
}

global $obModule;

//Sites List
$arSite = array();
$oSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
while ($site = $oSites->Fetch())
	$arSite[] = Array(
		"LANGUAGE_ID" => $site["LANGUAGE_ID"],
		"ABS_DOC_ROOT" => $site["ABS_DOC_ROOT"],
		"DIR" => $site["DIR"],
		"SITE_ID" => $site["LID"],
		"SERVER_NAME" =>$site["SERVER_NAME"],
		"NAME" => $site["NAME"],
	);
?>
<form action="<?=$APPLICATION->GetCurPage()?>" name="form1">
	<script language="JavaScript">
	<!--
		CJSIdeaStep1 = {
			NeedCreateNewIB: function(val)
			{
				BX('idea_iblock_type_new_block').style.display = (val=='~idea_iblock_type_create')?'':'none';
			},

			DemoInstallType: function(id, chk)
			{
				if(BX('demo').checked)
				{
					BX('idea_file_rewrite_block').style.display =
					BX('idea_site_id_block').style.display = '';
				}
				else
				{
					BX('idea_file_rewrite_block').style.display =
					BX('idea_site_id_block').style.display = 'none';
				}

			}
		}
	//-->
	</script>
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="<?=$obModule->MODULE_ID?>">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="1">

		<table class="list-table">
			<tr class="head">
				<td colspan="2">
					<input type="checkbox" onclick="CJSIdeaStep1.DemoInstallType(this.id, this.checked)" value="Y" id="demo" name="demo"><label for="demo"><?=GetMessage("IDEA_INSTALL_DEMO_SIMPLE")?></label>
				</td>
			</tr>
			<tr id="idea_site_id_block" style="display: none;">
				<td width="10%" style="white-space:nowrap;"><span class="required">*</span><?=GetMessage("IDEA_INSTALL_SITE_ID")?>:</td>
				<td width="90%">
					<select name="site_id">
						<?foreach($arSite as $Site):?>
							<option value="<?=$Site["SITE_ID"]?>"><?=$Site["NAME"]?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<tr id="idea_file_rewrite_block" style="display: none;">
				<td width="10%" style="white-space:nowrap;"><?=GetMessage("IDEA_INSTALL_FILE_REWRITE")?>:</td>
				<td width="90%"><input type="checkbox" value="Y" name="file_rewrite" checked></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="<?=GetMessage("MOD_INSTALL")?>" name="idea_install"></td>
			</tr>
		</table>
</form>