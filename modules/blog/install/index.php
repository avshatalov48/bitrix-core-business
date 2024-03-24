<?
global $MESS;
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/index.php");


Class blog extends CModule
{
	var $MODULE_ID = "blog";
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

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("BLOG_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BLOG_INSTALL_DESCRIPTION");
	}

	public static function installDiskUserFields()
	{
		global $APPLICATION;
		$errors = null;

		if(!IsModuleInstalled('disk'))
		{
			return;
		}

		$props = array(
			array(
				"ENTITY_ID" => "BLOG_POST",
				"FIELD_NAME" => "UF_BLOG_POST_FILE",
				"USER_TYPE_ID" => "disk_file"
			),
			array(
				"ENTITY_ID" => "BLOG_COMMENT",
				"FIELD_NAME" => "UF_BLOG_COMMENT_FILE",
				"USER_TYPE_ID" => "disk_file"
			),
			array(
				"ENTITY_ID" => "BLOG_COMMENT",
				"FIELD_NAME" => "UF_BLOG_COMMENT_FH",
				"USER_TYPE_ID" => "disk_version"
			),
		);
		$uf = new CUserTypeEntity;
		foreach ($props as $prop)
		{
			$rsData = CUserTypeEntity::getList(array("ID" => "ASC"), array("ENTITY_ID" => $prop["ENTITY_ID"], "FIELD_NAME" => $prop["FIELD_NAME"]));
			if (!($rsData && ($arRes = $rsData->Fetch())))
			{
				$intID = $uf->add(array(
					"ENTITY_ID" => $prop["ENTITY_ID"],
					"FIELD_NAME" => $prop["FIELD_NAME"],
					"XML_ID" => $prop["FIELD_NAME"],
					"USER_TYPE_ID" => $prop["USER_TYPE_ID"],
					"SORT" => 100,
					"MULTIPLE" => ($prop["USER_TYPE_ID"] == "disk_version" ? "N" : "Y"),
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => ($prop["USER_TYPE_ID"] == "disk_file" ? "Y" : "N")
				), false);

				if (false == $intID && ($strEx = $APPLICATION->getException()))
				{
					$errors[] = $strEx->getString();
				}
			}
		}

		return $errors;
	}

	public static function installMailUserFields(&$errors = [])
	{
		global $APPLICATION;

		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('mail'))
		{
			return;
		}

		$rsUserType = \CUserTypeEntity::getList(
			array(),
			array(
				'ENTITY_ID'  => 'BLOG_POST',
				'FIELD_NAME' => 'UF_MAIL_MESSAGE',
			)
		);
		if (!$rsUserType->fetch())
		{
			$userType = new \CUserTypeEntity();
			$intID = $userType->add(array(
				'ENTITY_ID'     => 'BLOG_POST',
				'FIELD_NAME'    => 'UF_MAIL_MESSAGE',
				'USER_TYPE_ID'  => 'mail_message',
				'XML_ID'        => '',
				'SORT'          => 100,
				'MULTIPLE'      => 'N',
				'MANDATORY'     => 'N',
				'SHOW_FILTER'   => 'N',
				'SHOW_IN_LIST'  => 'N',
				'EDIT_IN_LIST'  => 'Y',
				'IS_SEARCHABLE' => 'N',
			));
			if (false == $intID)
			{
				if ($strEx = $APPLICATION->getException())
				{
					$errors[] = $strEx->getString();
				}
			}
		}

		return $errors;
	}

	public static function InstallUserFields($id = "all")
	{
		global $USER_FIELD_MANAGER;
		$errors = null;

		if($id == 'disk' || $id == 'all')
		{
			self::installDiskUserFields();
		}
		if ($id == 'mail' || $id == 'all')
		{
			self::installMailUserFields($errors);
		}
		if($id == 'all')
		{
			$arFields = array(
				"BLOG_POST" => array(
					"ENTITY_ID" => "BLOG_POST",
					"FIELD_NAME" => "UF_BLOG_POST_DOC",
					"XML_ID" => "UF_BLOG_POST_DOC",
					"USER_TYPE_ID" => "file",
					"MULTIPLE" => "Y",
				),
				"BLOG_COMMENT" => array(
					"ENTITY_ID" => "BLOG_COMMENT",
					"FIELD_NAME" => "UF_BLOG_COMMENT_DOC",
					"XML_ID" => "UF_BLOG_COMMENT_DOC",
					"USER_TYPE_ID" => "file",
					"MULTIPLE" => "Y",
				),
				"UF_BLOG_POST_URL_PRV" => array(
					"ENTITY_ID" => "BLOG_POST",
					"FIELD_NAME" => "UF_BLOG_POST_URL_PRV",
					"XML_ID" => "UF_BLOG_POST_URL_PRV",
					"USER_TYPE_ID" => "url_preview",
					"MULTIPLE" => "N",
				),
				"UF_BLOG_COMM_URL_PRV" => array(
					"ENTITY_ID" => "BLOG_COMMENT",
					"FIELD_NAME" => "UF_BLOG_COMM_URL_PRV",
					"XML_ID" => "UF_BLOG_COMM_URL_PRV",
					"USER_TYPE_ID" => "url_preview",
					"MULTIPLE" => "N",
				)
			);

			$arFieldProps = Array(
				"SORT" => 100,
				"MANDATORY" => "N",
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "N",
				"EDIT_IN_LIST" => "Y",
				"IS_SEARCHABLE" => "Y",
				"SETTINGS" => array(),
				"EDIT_FORM_LABEL" => "",
				"LIST_COLUMN_LABEL" => "",
				"LIST_FILTER_LABEL" => "",
				"ERROR_MESSAGE" => "",
				"HELP_MESSAGE" => "",
				"MAX_ALLOWED_SIZE" => COption::GetOptionString("blog", "image_max_size", "5000000"),
			);

			foreach ($arFields as $fieldName => $arField)
			{
				$rsData = CUserTypeEntity::GetList(array($by=>$order), $arField);
				if ($arRes = $rsData->Fetch())
				{
					$intID = $arRes['ID'];
				}
				else
				{
					$arProps = $arFieldProps + $arField;
					$obUserField  = new CUserTypeEntity;
					$intID = $obUserField->Add($arProps, false);

					if (false == $intID)
					{
						if ($strEx = $GLOBALS['APPLICATION']->GetException())
						{
							$errors = $strEx->GetString();
						}
					}
				}
			}

			if (is_null($errors))
			{
				$rsData = CUserTypeEntity::GetList(
					array($by=>$order),
					array(
						"ENTITY_ID" => "BLOG_POST",
						"XML_ID" => "UF_GRATITUDE"
					)
				);
				if ($arRes = $rsData->Fetch())
					$intID = $arRes['ID'];
				else
				{
					$arFieldProps = Array(
						"ENTITY_ID" => "BLOG_POST",
						"FIELD_NAME" => "UF_GRATITUDE",
						"XML_ID" => "UF_GRATITUDE",
						"USER_TYPE_ID" => "integer",
						"SORT" => 100,
						"MULTIPLE" => "N",
						"MANDATORY" => "N",
						"SHOW_FILTER" => "N",
						"SHOW_IN_LIST" => "N",
						"EDIT_IN_LIST" => "Y",
						"IS_SEARCHABLE" => "N",
						"SETTINGS" => array(),
						"EDIT_FORM_LABEL" => "",
						"LIST_COLUMN_LABEL" => "",
						"LIST_FILTER_LABEL" => "",
						"ERROR_MESSAGE" => "",
						"HELP_MESSAGE" => "",
					);

					$obUserField  = new CUserTypeEntity;
					$intID = $obUserField->Add($arFieldProps, false);

					if (
						(false == $intID)
						&& ($strEx = $GLOBALS['APPLICATION']->GetException())
					)
						$errors = $strEx->GetString();
				}
			}
		}

		return $errors;
	}

	function InstallDB($install_wizard = true)
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$errors = null;

		if (!$DB->TableExists('b_blog_user_group'))
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/blog/install/' . $connection->getType() . '/install.sql');
			COption::SetOptionString("blog", "socNetNewPerms", "Y");
		}

		if (empty($errors))
		{
			$errors = static::InstallUserFields();
		}

		if (!empty($errors))
		{
			if (is_array($errors))
			{
				$errors = implode("", $errors);
			}
			$APPLICATION->ThrowException($errors);
			return false;
		}

		RegisterModule("blog");
		RegisterModuleDependences("search", "OnReindex", "blog", "CBlogSearch", "OnSearchReindex");
		RegisterModuleDependences("main", "OnUserDelete", "blog", "\Bitrix\Blog\BlogUser", "onUserDelete");
		RegisterModuleDependences("main", "OnSiteDelete", "blog", "CBlogSitePath", "DeleteBySiteID");

		RegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "blog", "CBlogSoNetPost", "OnGroupDelete");

		RegisterModuleDependences("socialnetwork", "OnSocNetFeaturesAdd", "blog", "CBlogSearch", "SetSoNetFeatureIndexSearch");
		RegisterModuleDependences("socialnetwork", "OnSocNetFeaturesUpdate", "blog", "CBlogSearch", "SetSoNetFeatureIndexSearch");
		RegisterModuleDependences("socialnetwork", "OnBeforeSocNetFeaturesPermsAdd", "blog", "CBlogSearch", "OnBeforeSocNetFeaturesPermsAdd");
		RegisterModuleDependences("socialnetwork", "OnSocNetFeaturesPermsAdd", "blog", "CBlogSearch", "SetSoNetFeaturePermIndexSearch");
		RegisterModuleDependences("socialnetwork", "OnBeforeSocNetFeaturesPermsUpdate", "blog", "CBlogSearch", "OnBeforeSocNetFeaturesPermsUpdate");
		RegisterModuleDependences("socialnetwork", "OnSocNetFeaturesPermsUpdate", "blog", "CBlogSearch", "SetSoNetFeaturePermIndexSearch");

		RegisterModuleDependences("main", "OnAfterAddRating", 	"blog", "CRatingsComponentsBlog", "OnAfterAddRating", 200);
		RegisterModuleDependences("main", "OnAfterUpdateRating", "blog", "CRatingsComponentsBlog", "OnAfterUpdateRating", 200);
		RegisterModuleDependences("main", "OnSetRatingsConfigs", "blog", "CRatingsComponentsBlog", "OnSetRatingConfigs", 200);
		RegisterModuleDependences("main", "OnGetRatingsConfigs", "blog", "CRatingsComponentsBlog", "OnGetRatingConfigs", 200);
		RegisterModuleDependences("main", "OnGetRatingsObjects", "blog", "CRatingsComponentsBlog", "OnGetRatingObject", 200);

		RegisterModuleDependences("main", "OnGetRatingContentOwner", "blog", "CRatingsComponentsBlog", "OnGetRatingContentOwner", 200);
		RegisterModuleDependences("im", "OnGetNotifySchema", "blog", "CBlogNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("im", "OnAnswerNotify", "blog", "CBlogNotifySchema", "CBlogEventsIMCallback");

		RegisterModuleDependences("main", "OnAfterRegisterModule", "main", "blog", "installUserFields", 100, "/modules/blog/install/index.php"); // check UF

		RegisterModuleDependences('conversion', 'OnGetCounterTypes' , 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onGetCounterTypes');
		RegisterModuleDependences('conversion', 'OnGetRateTypes' , 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onGetRateTypes');
		RegisterModuleDependences('blog', 'OnPostAdd', 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onPostAdd');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('mail', 'onReplyReceivedBLOG_POST', 'blog', '\Bitrix\Blog\Internals\MailHandler', 'handleReplyReceivedBlogPost');
		$eventManager->registerEventHandler('mail', 'onForwardReceivedBLOG_POST', 'blog', '\Bitrix\Blog\Internals\MailHandler', 'handleForwardReceivedBlogPost');
		$eventManager->registerEventHandler('socialnetwork', 'onLogIndexGetContent', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\Log', 'onIndexGetContent');
		$eventManager->registerEventHandler('socialnetwork', 'onLogCommentIndexGetContent', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\LogComment', 'onIndexGetContent');
		$eventManager->registerEventHandler('socialnetwork', 'onContentViewed', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\ContentViewHandler', 'onContentViewed');

		CModule::IncludeModule("blog");
		if (CModule::IncludeModule("search"))
			CSearch::ReIndexModule("blog");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();

		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			if ($DB->TableExists("b_blog_smile") || $DB->TableExists("B_BLOG_SMILE"))
			{
				$DB->Query("DELETE FROM b_blog_smile");
				$DB->Query("DROP TABLE b_blog_smile");
				$DB->Query("DROP TABLE b_blog_smile_lang");
			}
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/blog/install/' . $connection->getType() . '/uninstall.sql');

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
			else
			{
				$this->UnInstallUserFields();
			}

		}
		if (CModule::IncludeModule("search"))
			CSearch::DeleteIndex("blog");

		UnRegisterModuleDependences("search", "OnReindex", "blog", "CBlogSearch", "OnSearchReindex");
		UnRegisterModuleDependences("main", "OnUserDelete", "blog", "\Bitrix\Blog\BlogUser", "onUserDelete");
		UnRegisterModuleDependences("main", "OnSiteDelete", "blog", "CBlogSitePath", "DeleteBySiteID");

		UnRegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "blog", "CBlogSoNetPost", "OnGroupDelete");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetFeaturesAdd", "blog", "CBlogSearch", "SetSoNetFeatureIndexSearch");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetFeaturesUpdate", "blog", "CBlogSearch", "SetSoNetFeatureIndexSearch");
		UnRegisterModuleDependences("socialnetwork", "OnBeforeSocNetFeaturesPermsAdd", "blog", "CBlogSearch", "OnBeforeSocNetFeaturesPermsAdd");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetFeaturesPermsAdd", "blog", "CBlogSearch", "SetSoNetFeaturePermIndexSearch");
		UnRegisterModuleDependences("socialnetwork", "OnBeforeSocNetFeaturesPermsUpdate", "blog", "CBlogSearch", "OnBeforeSocNetFeaturesPermsUpdate");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetFeaturesPermsUpdate", "blog", "CBlogSearch", "SetSoNetFeaturePermIndexSearch");

		UnRegisterModuleDependences("main", "OnAfterAddRating",    "blog", "CRatingsComponentsBlog", "OnAfterAddRating");
		UnRegisterModuleDependences("main", "OnAfterUpdateRating", "blog", "CRatingsComponentsBlog", "OnAfterUpdateRating");
		UnRegisterModuleDependences("main", "OnSetRatingsConfigs", "blog", "CRatingsComponentsBlog", "OnSetRatingConfigs");
		UnRegisterModuleDependences("main", "OnGetRatingsConfigs", "blog", "CRatingsComponentsBlog", "OnGetRatingConfigs");
		UnRegisterModuleDependences("main", "OnGetRatingsObjects", "blog", "CRatingsComponentsBlog", "OnGetRatingObject");
		
		UnRegisterModuleDependences("main", "OnGetRatingContentOwner", "blog", "CRatingsComponentsBlog", "OnGetRatingContentOwner");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "blog", "CBlogNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("im", "OnAnswerNotify", "blog", "CBlogNotifySchema", "CBlogEventsIMCallback");

		UnRegisterModuleDependences("main", "OnAfterRegisterModule", "main", "blog", "installUserFields", "/modules/blog/install/index.php"); // check UF

		UnRegisterModuleDependences('conversion', 'OnGetCounterTypes' , 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onGetCounterTypes');
		UnRegisterModuleDependences('conversion', 'OnGetRateTypes' , 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onGetRateTypes');
		UnRegisterModuleDependences('blog', 'OnPostAdd', 'blog', '\Bitrix\Blog\Internals\ConversionHandlers', 'onPostAdd');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler('mail', 'onReplyReceivedBLOG_POST', 'blog', '\Bitrix\Blog\Internals\MailHandler', 'handleReplyReceivedBlogPost');
		$eventManager->unregisterEventHandler('mail', 'onForwardReceivedBLOG_POST', 'blog', '\Bitrix\Blog\Internals\MailHandler', 'handleForwardReceivedBlogPost');
		$eventManager->unregisterEventHandler('socialnetwork', 'onLogIndexGetContent', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\Log', 'onIndexGetContent');
		$eventManager->unregisterEventHandler('socialnetwork', 'onLogCommentIndexGetContent', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\LogComment', 'onIndexGetContent');
		$eventManager->unregisterEventHandler('socialnetwork', 'onContentViewed', 'blog', '\Bitrix\Blog\Integration\Socialnetwork\ContentViewHandler', 'onContentViewed');

		UnRegisterModule("blog");

		return true;
	}

	function UnInstallUserFields()
	{
		global $USER_FIELD_MANAGER;
		$errors = null;

		$arFields = array(
			"BLOG_POST" => array(
				"ENTITY_ID" => "BLOG_POST",
				"FIELD_NAME" => "UF_BLOG_POST_DOC",
				"XML_ID" => "UF_BLOG_POST_DOC"
			),
			"BLOG_COMMENT" => array(
				"ENTITY_ID" => "BLOG_COMMENT",
				"FIELD_NAME" => "UF_BLOG_COMMENT_DOC",
				"XML_ID" => "UF_BLOG_COMMENT_DOC"
			),
			"UF_BLOG_POST_URL_PRV" => array(
				"ENTITY_ID" => "BLOG_POST",
				"FIELD_NAME" => "UF_BLOG_POST_URL_PRV",
				"XML_ID" => "UF_BLOG_POST_URL_PRV",
			),
			"UF_BLOG_COMM_URL_PRV" => array(
				"ENTITY_ID" => "BLOG_COMMENT",
				"FIELD_NAME" => "UF_BLOG_COMM_URL_PRV",
				"XML_ID" => "UF_BLOG_COMM_URL_PRV",
			),
			'UF_MAIL_MESSAGE' => array(
				'ENTITY_ID'  => 'BLOG_POST',
				'FIELD_NAME' => 'UF_MAIL_MESSAGE',
			),
		);

		foreach ($arFields as $fieldName => $arField)
		{
			$rsData = CUserTypeEntity::GetList(array($by=>$order), $arField);
			if ($arRes = $rsData->Fetch())
			{
				$ent = new CUserTypeEntity;
				$ent->Delete($arRes['ID']);
			}
		}
		return $errors;
	}

	function InstallEvents()
	{

		global $DB;
		$sIn = "'NEW_BLOG_COMMENT', 'NEW_BLOG_COMMENT2COMMENT', 'NEW_BLOG_MESSAGE'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/events/del_events.php");
		return true;
	}

	function InstallFiles()
	{
		global $install_public, $public_rewrite, $public_dir;
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/blog", true, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/public/templates", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", true, true);
		}

		$install_public = (($install_public == "Y") ? "Y" : "N");
		$errors = false;

		$arSite = Array();
		$public_installed = false;

		$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
		while ($site = $dbSites->Fetch())
		{
			$arSite[] = Array(
				"LANGUAGE_ID" => $site["LANGUAGE_ID"],
				"ABS_DOC_ROOT" => $site["ABS_DOC_ROOT"],
				"DIR" => $site["DIR"],
				"SITE_ID" => $site["LID"],
				"SERVER_NAME" =>$site["SERVER_NAME"],
				"NAME" => $site["NAME"]
			);
		}

		foreach($arSite as $fSite)
		{
			global ${"install_public_".$fSite["SITE_ID"]};
			global ${"public_path_".$fSite["SITE_ID"]};
			global ${"public_rewrite_".$fSite["SITE_ID"]};
			global ${"is404_".$fSite["SITE_ID"]};

			if (${"install_public_".$fSite["SITE_ID"]} == "Y" && !empty(${"public_path_".$fSite["SITE_ID"]}))
			{
				$public_dir = ${"public_path_".$fSite["SITE_ID"]};
				$bReWritePublicFiles = ${"public_rewrite_".$fSite["SITE_ID"]};
				$folder = (${"is404_".$fSite["SITE_ID"]}=="Y")?"SEF":"NSEF";

				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/public/".$folder, $fSite['ABS_DOC_ROOT'].$fSite["DIR"].$public_dir, $bReWritePublicFiles, true);
				if ($folder == "SEF")
				{
					if (file_exists($fSite['ABS_DOC_ROOT'].$fSite["DIR"].$public_dir."/index.php"))
					{

						if (!function_exists("file_get_contents"))
						{
							function file_get_contents($filename)
							{
								$fd = fopen("$filename", "rb");
								$content = fread($fd, filesize($filename));
								fclose($fd);
								return $content;
							}
						}

						$file = file_get_contents($fSite['ABS_DOC_ROOT'].$fSite["DIR"].$public_dir."/index.php");
						if ($file)
						{
							$file = str_replace("#SEF_FOLDER#", "/".$public_dir."/", $file);
							if ($f = fopen($fSite['ABS_DOC_ROOT'].$fSite["DIR"].$public_dir."/index.php", "w"))
							{
								@fwrite($f, $file);
								@fclose($f);
							}
						}
					}
					$arFields = array(
						"CONDITION" => "#^/".$public_dir."/#",
						"RULE" => "",
						"ID" => "bitrix:blog",
						"PATH" => "/".$public_dir."/index.php"
					);
					CUrlRewriter::Add($arFields);
				}
				$public_installed = true;
			}
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
			DeleteDirFilesEx("/bitrix/themes/.default/icons/blog/");//icons
			DeleteDirFilesEx("/bitrix/images/blog/");//images
		}

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$step = intval($step);
		if ($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage("BLOG_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/step1.php");
		elseif($step==2)
		{
			$this->InstallFiles();
			$this->InstallDB(false);
			$this->InstallEvents();
			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("BLOG_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = intval($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("BLOG_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/unstep1.php");
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			if($_REQUEST["saveemails"] != "Y")
				$this->UnInstallEvents();

			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("BLOG_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", /*"K",*/ "N", "R", "W"),
			"reference" => array(
					"[D] ".GetMessage("BLI_PERM_D"),
					//"[K] ".GetMessage("BLI_PERM_K"),
					"[N] ".GetMessage("BLI_PERM_N"),
					"[R] ".GetMessage("BLI_PERM_R"),
					"[W] ".GetMessage("BLI_PERM_W")
				)
			);
		return $arr;
	}
}
?>