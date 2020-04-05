<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class socialnetwork extends CModule
{
	var $MODULE_ID = "socialnetwork";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function socialnetwork()
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
			$this->MODULE_VERSION = SONET_VERSION;
			$this->MODULE_VERSION_DATE = SONET_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("SONET_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SONET_INSTALL_DESCRIPTION");
	}

	function __SetLogFilter($site_id = false)
	{
		$arValue = array(
			array(
				"ID" => "work",
				"SORT" => 100,
				"NAME" => "#WORK#",
				"FILTER" => array(
					"EVENT_ID" => array("tasks", "timeman_entry", "report")
				)
			),
			array(
				"ID" => "favorites",
				"SORT" => 200,
				"NAME" => "#FAVORITES#",
				"FILTER" => array(
					"FAVORITES_USER_ID" => "Y"
				)
			),
			array(
				"ID" => "my",
				"SORT" => 300,
				"NAME" => "#MY#",
				"FILTER" => array(
					"CREATED_BY_ID" => "#CURRENT_USER_ID#"
				)
			)
		);
		if (IsModuleInstalled("blog"))
		{
			$arValue[] = array(
				"ID" => "important",
				"SORT" => 350,
				"NAME" => "#important#",
				"FILTER" => array(
					"EXACT_EVENT_ID" => "blog_post_important"
				)
			);
		}
		if (
			IsModuleInstalled("lists")
			&& IsModuleInstalled("bizproc")
			&& IsModuleInstalled("intranet")
		)
		{
			$arValue[] = array(
				"ID" => "bizproc",
				"SORT" => 400,
				"NAME" => "#BIZPROC#",
				"FILTER" => array(
					"EXACT_EVENT_ID" => "lists_new_element"
				)
			);
		}
		if (IsModuleInstalled("intranet"))
		{
			$arValue[] = array(
				"ID" => "extranet",
				"SORT" => 500,
				"NAME" => "#EXTRANET#",
				"FILTER" => array(
					"SITE_ID" => "#EXTRANET_SITE_ID#",
					"!EXACT_EVENT_ID" => array("lists_new_element", "tasks", "timeman_entry", "report", "crm_activity_add")
				)
			);
		}

		$arFilter = (strlen($site_id) > 0 ? array("ID" => $site_id) : array());

		$dbSites = CSite::GetList(($b = ""), ($o = ""), $arFilter);
		while ($arSite = $dbSites->Fetch())
		{
			CUserOptions::SetOption("socialnetwork", "~log_filter_".$arSite["ID"], $arValue, true, false);
		}
	}

	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION, $install_smiles;

		if (!$DB->Query("SELECT 'x' FROM b_sonet_group", true))
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/db/".$DBType."/install.sql");
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("socialnetwork");
		RegisterModuleDependences("search", "OnBeforeFullReindexClear", "socialnetwork", "CSocNetSearchReindex", "OnBeforeFullReindexClear");
		RegisterModuleDependences("search", "OnBeforeIndexDelete", "socialnetwork", "CSocNetSearchReindex", "OnBeforeIndexDelete");
		RegisterModuleDependences("search", "OnReindex", "socialnetwork", "CSocNetSearch", "OnSearchReindex");
		RegisterModuleDependences("search", "OnSearchCheckPermissions", "socialnetwork", "CSocNetSearch", "OnSearchCheckPermissions");
		RegisterModuleDependences("search", "OnBeforeIndexUpdate", "socialnetwork", "CSocNetSearch", "OnBeforeIndexUpdate");
		RegisterModuleDependences("search", "BeforeIndex", "socialnetwork", "CSocNetSearch", "BeforeIndexLast", 1000); // later than CSocNetSearch::BeforeIndex

		RegisterModuleDependences("search", "OnAfterIndexAdd", "socialnetwork", "CSocNetSearch", "OnAfterIndexAdd");
		RegisterModuleDependences("search", "OnSearchPrepareFilter", "socialnetwork", "CSocNetSearch", "OnSearchPrepareFilter");
		RegisterModuleDependences("main", "OnUserDelete", "socialnetwork", "CSocNetUser", "OnUserDelete");
		RegisterModuleDependences("main", "OnBeforeUserUpdate", "socialnetwork", "CSocNetUser", "OnBeforeUserUpdate");
		RegisterModuleDependences("main", "OnAfterUserUpdate", "socialnetwork", "CSocNetUser", "OnAfterUserUpdate");
		RegisterModuleDependences("main", "OnAfterUserAdd", "socialnetwork", "CSocNetUser", "OnAfterUserAdd");
		RegisterModuleDependences("main", "OnAfterUserLogout", "socialnetwork", "CSocNetUser", "OnAfterUserLogout");
		RegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", 100, "/modules/socialnetwork/prolog_before.php");
		RegisterModuleDependences("main", "OnBeforeLangDelete", "socialnetwork", "CSocNetGroup", "OnBeforeLangDelete");
		RegisterModuleDependences("socialnetwork", "OnSocNetLogFormatEvent", "socialnetwork", "CSocNetLog", "OnSocNetLogFormatEvent");
		RegisterModuleDependences("photogallery", "OnAfterUpload", "socialnetwork", "CSocNetLogTools", "OnAfterPhotoUpload");
		RegisterModuleDependences("photogallery", "OnAfterPhotoDrop", "socialnetwork", "CSocNetLogTools", "OnAfterPhotoDrop");
		RegisterModuleDependences("photogallery", "OnBeforeSectionDrop", "socialnetwork", "CSocNetLogTools", "OnBeforeSectionDrop");
		RegisterModuleDependences("photogallery", "OnAfterSectionDrop", "socialnetwork", "CSocNetLogTools", "OnAfterSectionDrop");
		RegisterModuleDependences("photogallery", "OnAfterSectionEdit", "socialnetwork", "CSocNetLogTools", "OnAfterSectionEdit");
		RegisterModuleDependences("main", "OnAuthProvidersBuildList", "socialnetwork", "CSocNetGroupAuthProvider", "GetProviders");

		RegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetUserToGroup", "OnBeforeConfirmNotify");
		RegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetUserRelations", "OnBeforeConfirmNotify");
		RegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetLogFollow", "OnBeforeConfirmNotify");
		RegisterModuleDependences("im", "OnGetNotifySchema", "socialnetwork", "CSocNetNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("im", "OnAfterChatMuteNotify", "socialnetwork", "CSocNetSubscription", "OnAfterChatMuteNotify");

		RegisterModuleDependences("pull", "OnGetDependentModule", "socialnetwork", "CSocNetPullSchema", "OnGetDependentModule");
		RegisterModuleDependences("main", "OnUserInitialize", "socialnetwork", "CSocNetUser", "OnUserInitialize");
		RegisterModuleDependences("blog", "OnBlogDelete", "socialnetwork", "CSocNetLogComments", "OnBlogDelete", 100);
		RegisterModuleDependences("blog", "OnBlogDelete", "socialnetwork", "CSocNetLog", "OnBlogDelete", 200);
		RegisterModuleDependences("blog", "OnBlogPostMentionNotifyIm", "socialnetwork", "CSocNetLogFollow", "OnBlogPostMentionNotifyIm");
		RegisterModuleDependences("rest", "OnRestServiceBuildDescription", "socialnetwork", "CSocNetLogRestService", "OnRestServiceBuildDescription");
		RegisterModuleDependences("main", "OnAfterRegisterModule", "main", "socialnetwork", "InstallUserFields", 100, "/modules/socialnetwork/install/index.php"); // check webdav UF
		RegisterModuleDependences("forum", "OnAfterCommentAdd", "socialnetwork", "CSocNetForumComments", "onAfterCommentAdd");
		RegisterModuleDependences("forum", "OnAfterCommentUpdate", "socialnetwork", "CSocNetForumComments", "OnAfterCommentUpdate");
		RegisterModuleDependences("main", "OnAfterSetUserGroup", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");
		RegisterModuleDependences("main", "OnAfterSetGroupRight", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");
		RegisterModuleDependences("main", "OnAfterDelGroupRight", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");

		RegisterModuleDependences("main", "OnAfterUserAdd", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\UserToGroup", "onAfterUserAdd");
		RegisterModuleDependences("main", "OnAfterUserUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\UserToGroup", "onAfterUserUpdate");
		RegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onBeforeIBlockSectionUpdate");
		RegisterModuleDependences("iblock", "OnAfterIBlockSectionUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onAfterIBlockSectionUpdate");
		RegisterModuleDependences("iblock", "onBeforeIBlockSectionDelete", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onBeforeIBlockSectionDelete");
		RegisterModuleDependences("iblock", "OnAfterIBlockSectionDelete", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onAfterIBlockSectionDelete");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('mail', 'onReplyReceivedLOG_ENTRY', 'socialnetwork', '\Bitrix\Socialnetwork\Internals\MailHandler', 'handleReplyReceivedLogEntry');
		$eventManager->registerEventHandler('main', 'OnUISelectorActionProcessAjax', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\UISelector\Handler', 'OnUISelectorActionProcessAjax');
		$eventManager->registerEventHandler('main', 'OnUISelectorEntitiesGetList', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\UISelector\Handler', 'OnUISelectorEntitiesGetList');
		$eventManager->registerEventHandler('tasks', 'onTaskUpdateViewed', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Tasks\Task', 'onTaskUpdateViewed');
		$eventManager->registerEventHandler('calendar', 'onViewEvent', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Calendar\CalendarEvent', 'onViewEvent');
		$eventManager->registerEventHandler('main', 'onRatingListViewed', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\RatingVoteList', 'onViewed');
		$eventManager->registerEventHandler('mobile', 'onSetContentView', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Mobile\LogEntry', 'onSetContentView');
		$eventManager->registerEventHandler('mobile', 'onGetContentId', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Mobile\LogEntry', 'onGetContentId');
		$eventManager->registerEventHandler('pull', 'onGetMobileCounter', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Pull\Counter', 'onGetMobileCounter');
		$eventManager->registerEventHandler('pull', 'onGetMobileCounterTypes', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Pull\Counter', 'onGetMobileCounterTypes');
		$eventManager->registerEventHandler('intranet', 'onEmployeeDepartmentsChanged', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Intranet\Structure\Employee', 'onEmployeeDepartmentsChanged');
		$eventManager->registerEventHandler('socialnetwork', '\Bitrix\Socialnetwork\Log::'.\Bitrix\Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE, 'socialnetwork', '\Bitrix\Socialnetwork\Item\LogRight', 'OnAfterLogUpdate');
		$eventManager->registerEventHandler('socialnetwork', '\Bitrix\Socialnetwork\Log::'.\Bitrix\Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE, 'socialnetwork', '\Bitrix\Socialnetwork\Item\LogIndex', 'OnAfterLogUpdate');

		CAgent::AddAgent("CSocNetMessages::SendEventAgent();", "socialnetwork", "N", 600);

		$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_sonet_user", false, 0);
		if (!is_array($arUserOptions) || count($arUserOptions) <= 0)
		{
			$sOptions = 'a:1:{s:7:"GADGETS";a:10:{s:18:"SONET_USER_LINKS@1";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:0;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:20:"SONET_USER_FRIENDS@2";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:1;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:21:"SONET_USER_BIRTHDAY@3";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:2;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:19:"SONET_USER_GROUPS@4";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:3;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:17:"SONET_USER_HEAD@5";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:4;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:19:"SONET_USER_HONOUR@6";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:5;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:20:"SONET_USER_ABSENCE@7";a:4:{s:6:"COLUMN";i:0;s:3:"ROW";i:6;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:17:"SONET_USER_DESC@8";a:4:{s:6:"COLUMN";i:1;s:3:"ROW";i:0;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}s:22:"SONET_USER_ACTIVITY@21";a:3:{s:6:"COLUMN";i:1;s:3:"ROW";i:1;s:4:"HIDE";s:1:"N";}s:7:"TASKS@9";a:4:{s:6:"COLUMN";i:1;s:3:"ROW";i:2;s:8:"USERDATA";N;s:4:"HIDE";s:1:"N";}}}';
			$arOptions = unserialize($sOptions);
			CUserOptions::SetOption("intranet", "~gadgets_sonet_user", $arOptions, false, 0);

			$sOptions = 'a:1:{s:7:"GADGETS";a:7:{s:18:"SONET_GROUP_DESC@1";a:3:{s:6:"COLUMN";i:0;s:3:"ROW";i:0;s:4:"HIDE";s:1:"N";}s:16:"UPDATES_ENTITY@9";a:3:{s:6:"COLUMN";i:0;s:3:"ROW";i:1;s:4:"HIDE";s:1:"N";}s:7:"TASKS@4";a:3:{s:6:"COLUMN";i:0;s:3:"ROW";i:2;s:4:"HIDE";s:1:"N";}s:18:"SONET_GROUP_TAGS@5";a:3:{s:6:"COLUMN";i:0;s:3:"ROW";i:3;s:4:"HIDE";s:1:"N";}s:19:"SONET_GROUP_LINKS@6";a:3:{s:6:"COLUMN";i:1;s:3:"ROW";i:0;s:4:"HIDE";s:1:"N";}s:19:"SONET_GROUP_USERS@7";a:3:{s:6:"COLUMN";i:1;s:3:"ROW";i:1;s:4:"HIDE";s:1:"N";}s:18:"SONET_GROUP_MODS@8";a:3:{s:6:"COLUMN";i:1;s:3:"ROW";i:2;s:4:"HIDE";s:1:"N";}}}';
			$arOptions = unserialize($sOptions);
			CUserOptions::SetOption("intranet", "~gadgets_sonet_group", $arOptions, false, 0);
		}

		$this->__SetLogFilter();

		CModule::IncludeModule("socialnetwork");
		if(strtolower($DB->type) == 'mysql')
		{
			if ($DB->Query("CREATE fulltext index IXF_SONET_LOG_INDEX on b_sonet_log_index (CONTENT)", true))
			{
				\Bitrix\Socialnetwork\LogIndexTable::getEntity()->enableFullTextIndex("CONTENT");
			}

			if ($DB->Query("CREATE fulltext index IXF_SONET_GROUP on b_sonet_group (SEARCH_INDEX)", true))
			{
				\Bitrix\Socialnetwork\WorkgroupTable::getEntity()->enableFullTextIndex("SEARCH_INDEX");
			}
		}

		if (CModule::IncludeModule("search"))
		{
			CSearch::ReIndexModule("socialnetwork");
		}

		if($install_smiles == "Y" || $install_wizard)
		{
			$dbSmile = CSocNetSmile::GetList();
			if(!($dbSmile->Fetch()))
			{
				$arSmile = Array(
					Array(
						"TYPING" => ":D :-D",
						"IMAGE" => "icon_biggrin.gif",
						"FICON_SMILE" => "FICON_BIGGRIN",
					),
					Array(
						"TYPING" => ":) :-)",
						"IMAGE" => "icon_smile.gif",
						"FICON_SMILE" => "FICON_SMILE",
					),
					Array(
						"TYPING" => ":( :-(",
						"IMAGE" => "icon_sad.gif",
						"FICON_SMILE" => "FICON_SAD",
					),
					Array(
						"TYPING" => ":o :-o :shock:",
						"IMAGE" => "icon_eek.gif",
						"FICON_SMILE" => "FICON_EEK",
					),
					Array(
						"TYPING" => "8) 8-)",
						"IMAGE" => "icon_cool.gif",
						"FICON_SMILE" => "FICON_COOL",
					),
					Array(
						"TYPING" => ":{} :-{}",
						"IMAGE" => "icon_kiss.gif",
						"FICON_SMILE" => "FICON_KISS",
					),
					Array(
						"TYPING" => ":oops:",
						"IMAGE" => "icon_redface.gif",
						"FICON_SMILE" => "FICON_REDFACE",
					),
					Array(
						"TYPING" => ":cry: :~(",
						"IMAGE" => "icon_cry.gif",
						"FICON_SMILE" => "FICON_CRY",
					),
					Array(
						"TYPING" => ":evil: >:-<",
						"IMAGE" => "icon_evil.gif",
						"FICON_SMILE" => "FICON_EVIL",
					),
					Array(
						"TYPING" => ";) ;-)",
						"IMAGE" => "icon_wink.gif",
						"FICON_SMILE" => "FICON_WINK",
					),
					Array(
						"TYPING" => ":!:",
						"IMAGE" => "icon_exclaim.gif",
						"FICON_SMILE" => "FICON_EXCLAIM",
					),
					Array(
						"TYPING" => ":?:",
						"IMAGE" => "icon_question.gif",
						"FICON_SMILE" => "FICON_QUESTION",
					),
					Array(
						"TYPING" => ":idea:",
						"IMAGE" => "icon_idea.gif",
						"FICON_SMILE" => "FICON_IDEA",
					),
					Array(
						"TYPING" => ":| :-|",
						"IMAGE" => "icon_neutral.gif",
						"FICON_SMILE" => "FICON_NEUTRAL",
					),
				);
				$arLang = Array();
				$dbLangs = CLanguage::GetList(($b = ""), ($o = ""), array("ACTIVE" => "Y"));
				while ($arLangs = $dbLangs->Fetch())
				{
					IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/smiles.php", $arLangs["LID"]);

					foreach($arSmile as $key => $val)
					{
						$arSmile[$key]["LANG"][] = Array("LID" => $arLangs["LID"], "NAME" => GetMessage($val["FICON_SMILE"]));
					}
				}

				foreach($arSmile as $val)
				{
					$val["SMILE_TYPE"] = "S";
					$val["CLICKABLE"] = "Y";
					$val["SORT"] = 150;
					$val["IMAGE_WIDTH"] = 16;
					$val["IMAGE_HEIGHT"] = 16;
					$id = CSocNetSmile::Add($val);
				}
			}
		}

		$res = $this->InstallUserFields();
		if ($res)
		{
			$this->errors[] = $res;
		}

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		if (CModule::IncludeModule("search"))
			CSearch::DeleteIndex("socialnetwork");

		global $DB, $DBType, $APPLICATION;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$this->UnInstallUserFields();

			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/db/".$DBType."/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		CAgent::RemoveAgent("CSocNetMessages::SendEventAgent();", "socialnetwork");

		UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", "/modules/socialnetwork/prolog_before.php");
		UnRegisterModuleDependences("search", "OnBeforeFullReindexClear", "socialnetwork", "CSocNetSearchReindex", "OnBeforeFullReindexClear");
		UnRegisterModuleDependences("search", "OnBeforeIndexDelete", "socialnetwork", "CSocNetSearchReindex", "OnBeforeIndexDelete");
		UnRegisterModuleDependences("search", "OnReindex", "socialnetwork", "CSocNetSearch", "OnSearchReindex");
		UnRegisterModuleDependences("search", "OnSearchCheckPermissions", "socialnetwork", "CSocNetSearch", "OnSearchCheckPermissions");
		UnRegisterModuleDependences("search", "OnBeforeIndexUpdate", "socialnetwork", "CSocNetSearch", "OnBeforeIndexUpdate");
		UnRegisterModuleDependences("search", "OnAfterIndexAdd", "socialnetwork", "CSocNetSearch", "OnAfterIndexAdd");
		UnRegisterModuleDependences("search", "OnSearchPrepareFilter", "socialnetwork", "CSocNetSearch", "OnSearchPrepareFilter");
		UnRegisterModuleDependences("search", "BeforeIndex", "socialnetwork", "CSocNetSearch", "BeforeIndexLast");

		UnRegisterModuleDependences("main", "OnUserDelete", "socialnetwork", "CSocNetUser", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnBeforeUserUpdate", "socialnetwork", "CSocNetUser", "OnBeforeUserUpdate");
		UnRegisterModuleDependences("main", "OnAfterUserUpdate", "socialnetwork", "CSocNetUser", "OnAfterUserUpdate");
		UnRegisterModuleDependences("main", "OnAfterUserAdd", "socialnetwork", "CSocNetUser", "OnAfterUserAdd");
		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "socialnetwork", "CSocNetGroup", "OnBeforeLangDelete");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetLogFormatEvent", "socialnetwork", "CSocNetLog", "OnSocNetLogFormatEvent");
		UnRegisterModuleDependences("photogallery", "OnAfterUpload", "socialnetwork", "CSocNetLogTools", "OnAfterPhotoUpload");
		UnRegisterModuleDependences("photogallery", "OnAfterPhotoDrop", "socialnetwork", "CSocNetLogTools", "OnAfterPhotoDrop");
		UnRegisterModuleDependences("photogallery", "OnAfterSectionDrop", "socialnetwork", "CSocNetLogTools", "OnAfterSectionDrop");
		UnRegisterModuleDependences("photogallery", "OnBeforeSectionDrop", "socialnetwork", "CSocNetLogTools", "OnBeforeSectionDrop");
		UnRegisterModuleDependences("photogallery", "OnAfterSectionEdit", "socialnetwork", "CSocNetLogTools", "OnAfterSectionEdit");
		UnRegisterModuleDependences("main", "OnAuthProvidersBuildList", "socialnetwork", "CSocNetGroupAuthProvider", "GetProviders");

		UnRegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetUserToGroup", "OnBeforeConfirmNotify");
		UnRegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetUserRelations", "OnBeforeConfirmNotify");
		UnRegisterModuleDependences("im", "OnBeforeConfirmNotify", "socialnetwork", "CSocNetLogFollow", "OnBeforeConfirmNotify");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "socialnetwork", "CSocNetNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("im", "OnAfterChatMuteNotify", "socialnetwork", "CSocNetSubscription", "OnAfterChatMuteNotify");

		UnRegisterModuleDependences("pull", "OnGetDependentModule", "socialnetwork", "CSocNetPullSchema", "OnGetDependentModule");
		UnRegisterModuleDependences("main", "OnUserInitialize", "socialnetwork", "CSocNetUser", "OnUserInitialize");
		UnRegisterModuleDependences("blog", "OnBlogDelete", "socialnetwork", "CSocNetLog", "OnBlogDelete");
		UnRegisterModuleDependences("blog", "OnBlogDelete", "socialnetwork", "CSocNetLogComments", "OnBlogDelete");
		UnRegisterModuleDependences("blog", "OnBlogPostMentionNotifyIm", "socialnetwork", "CSocNetLogFollow", "OnBlogPostMentionNotifyIm");
		UnRegisterModuleDependences("rest", "OnRestServiceBuildDescription", "socialnetwork", "CSocNetLogRestService", "OnRestServiceBuildDescription");
		UnRegisterModuleDependences("main", "OnAfterRegisterModule", "main", "socialnetwork", "InstallUserFields", "/modules/socialnetwork/install/index.php"); // check webdav UF
		UnRegisterModuleDependences("forum", "OnAfterCommentAdd", "socialnetwork", "CSocNetForumComments", "onAfterCommentAdd");
		UnRegisterModuleDependences("forum", "OnAfterCommentUpdate", "socialnetwork", "CSocNetForumComments", "OnAfterCommentUpdate");
		UnRegisterModuleDependences("main", "OnAfterSetUserGroup", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");
		UnRegisterModuleDependences("main", "OnAfterSetGroupRight", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");
		UnRegisterModuleDependences("main", "OnAfterDelGroupRight", "socialnetwork", "CSocNetUser", "DeleteUserAdminCache");
		UnRegisterModuleDependences("main", "OnAfterUserAdd", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\UserToGroup", "onAfterUserAdd");
		UnRegisterModuleDependences("main", "OnAfterUserUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\UserToGroup", "onAfterUserUpdate");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "OnBeforeIBlockSectionUpdate");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockSectionUpdate", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onAfterIBlockSectionUpdate");
		UnRegisterModuleDependences("iblock", "onBeforeIBlockSectionDelete", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onBeforeIBlockSectionDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockSectionDelete", "socialnetwork", "\\Bitrix\\Socialnetwork\\Item\\Workgroup", "onAfterIBlockSectionDelete");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler('mail', 'onReplyReceivedLOG_ENTRY', 'socialnetwork', '\Bitrix\Socialnetwork\Internals\MailHandler', 'handleReplyReceivedLogEntry');
		$eventManager->unregisterEventHandler('main', 'OnUISelectorActionProcessAjax', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\UISelector\Handler', 'OnUISelectorActionProcessAjax');
		$eventManager->unregisterEventHandler('main', 'OnUISelectorEntitiesGetList', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\UISelector\Handler', 'OnUISelectorEntitiesGetList');
		$eventManager->unregisterEventHandler('tasks', 'onTaskUpdateViewed', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Tasks\Task', 'onTaskUpdateViewed');
		$eventManager->unregisterEventHandler('calendar', 'onViewEvent', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Calendar\CalendarEvent', 'onViewEvent');
		$eventManager->unregisterEventHandler('main', 'onRatingListViewed', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Main\RatingVoteList', 'onViewed');
		$eventManager->unregisterEventHandler('mobile', 'onSetContentView', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Mobile\LogEntry', 'onSetContentView');
		$eventManager->unregisterEventHandler('mobile', 'onGetContentId', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Mobile\LogEntry', 'onGetContentId');
		$eventManager->unregisterEventHandler('pull', 'onGetMobileCounter', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Pull\Counter', 'onGetMobileCounter');
		$eventManager->unregisterEventHandler('pull', 'onGetMobileCounterTypes', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Pull\Counter', 'onGetMobileCounterTypes');
		$eventManager->unregisterEventHandler('intranet', 'onEmployeeDepartmentsChanged', 'socialnetwork', '\Bitrix\Socialnetwork\Integration\Intranet\Structure\Employee', 'onEmployeeDepartmentsChanged');
		$eventManager->unregisterEventHandler('socialnetwork', '\Bitrix\Socialnetwork\Log::'.\Bitrix\Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE, 'socialnetwork', '\Bitrix\Socialnetwork\Item\LogRight', 'OnAfterLogUpdate');
		$eventManager->unregisterEventHandler('socialnetwork', '\Bitrix\Socialnetwork\Log::'.\Bitrix\Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE, 'socialnetwork', '\Bitrix\Socialnetwork\Item\LogIndex', 'OnAfterLogUpdate');

		UnRegisterModule("socialnetwork");
		return true;
	}

	function InstallUserFields($id = "all")
	{
		global $APPLICATION, $USER_FIELD_MANAGER;
		$errors = null;

		$id = (empty($id) ? "all" : (in_array($id, array("all", "webdav", "disk", "vote", "intranet"/*, "blog"*/)) ? $id : false));
		if (!!$id)
		{
			$USER_FIELD_MANAGER->CleanCache();
			$USER_FIELD_MANAGER->arUserTypes = '';

			$arFields = array();
			if ($id != "webdav")
			{
				$arFields[] = array(
					"USER_TYPE_ID" => "file",
					"ENTITY_ID" => "SONET_LOG",
					"FIELD_NAME" => "UF_SONET_LOG_FILE",
					"XML_ID" => "UF_SONET_LOG_FILE",
					"MAX_ALLOWED_SIZE" => COption::GetOptionString("socialnetwork", "file_max_size", "5000000"),
					"MULTIPLE" => "Y",
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => "Y",
				);
				$arFields[] = array(
					"USER_TYPE_ID" => "file",
					"ENTITY_ID" => "SONET_COMMENT",
					"FIELD_NAME" => "UF_SONET_COM_FILE",
					"XML_ID" => "UF_SONET_COM_FILE",
					"MAX_ALLOWED_SIZE" => COption::GetOptionString("socialnetwork", "file_max_size", "5000000"),
					"MULTIPLE" => "Y",
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => "Y",
				);
				if (IsModuleInstalled("blog"))
				{
					$arImportantPostUF = array(
						"USER_TYPE_ID" => "integer",
						"ENTITY_ID" => "BLOG_POST",
						"FIELD_NAME" => "UF_BLOG_POST_IMPRTNT",
						"XML_ID" => "UF_BLOG_POST_IMPRTNT",
						"EDIT_FORM_LABEL" => Array(),
						"LIST_COLUMN_LABEL" => Array(),
						"LIST_FILTER_LABEL" => Array());

					$dbLangs = CLanguage::GetList(($b = ""), ($o = ""), array("ACTIVE" => "Y"));
					while ($arLang = $dbLangs->Fetch())
					{
						$messages = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/index.php", $arLang["LID"], true);
						$arImportantPostUF["EDIT_FORM_LABEL"][$arLang["LID"]] = $messages["SONETP_EDIT_FORM_LABEL"];
						$arImportantPostUF["LIST_COLUMN_LABEL"][$arLang["LID"]] = $messages["SONETP_LIST_COLUMN_LABEL"];
						$arImportantPostUF["LIST_FILTER_LABEL"][$arLang["LID"]] = $messages["SONETP_LIST_FILTER_LABEL"];
					}
					$arFields[] = $arImportantPostUF;
				}
			}

			if($id == 'all' || $id == 'disk')
			{
				$errors = self::installDiskUserFields();
			}

			if($id == 'all' || $id == 'intranet')
			{
				$errors = self::installIntranetUserFields();
			}

			if (IsModuleInstalled("webdav"))
			{
				$arFields[] = array(
					"USER_TYPE_ID" => "webdav_element",
					"ENTITY_ID" => "SONET_LOG",
					"FIELD_NAME" => "UF_SONET_LOG_DOC",
					"XML_ID" => "UF_SONET_LOG_DOC",
					"MULTIPLE" => "Y",
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => "Y"
				);
				$arFields[] = array(
					"USER_TYPE_ID" => "webdav_element",
					"ENTITY_ID" => "SONET_COMMENT",
					"FIELD_NAME" => "UF_SONET_COM_DOC",
					"XML_ID" => "UF_SONET_COM_DOC",
					"MULTIPLE" => "Y",
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => "Y"
				);
			}

			if (IsModuleInstalled("vote"))
			{
				$arFields[] = array(
					"USER_TYPE_ID" => "vote",
					"ENTITY_ID" => "BLOG_POST",
					"FIELD_NAME" => "UF_BLOG_POST_VOTE",
					"XML_ID" => "UF_BLOG_POST_VOTE",
					"SETTINGS" => array(
						"CHANNEL_ID" => "add",
						"CHANNEL_TITLE" => "UF_BLOG_POST_VOTE",
						"CHANNEL_SYMBOLIC_NAME" => "UF_BLOG_POST_VOTE",
						"CHANNEL_USE_CAPTCHA" => "N",
						"NOTIFY" => (IsModuleInstalled("im") ? "I" : "N"),
						"UNIQUE" => 8,
						"UNIQUE_IP_DELAY" => array(
							"DELAY" => "10",
							"DELAY_TYPE" => "D"
						)
					)
				);
			}

			$arFields[] = array(
				"USER_TYPE_ID" => 'url_preview',
				"ENTITY_ID" => "SONET_LOG",
				"FIELD_NAME" => "UF_SONET_LOG_URL_PRV",
				"XML_ID" => "UF_SONET_LOG_URL_PRV",
				"MULTIPLE" => 'N',
				"MANDATORY" => "N",
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "N",
				"EDIT_IN_LIST" => "Y",
			);

			$arFields[] = array(
				"USER_TYPE_ID" => 'url_preview',
				"ENTITY_ID" => "SONET_COMMENT",
				"FIELD_NAME" => "UF_SONET_COM_URL_PRV",
				"XML_ID" => "UF_SONET_COM_URL_PRV",
				"MULTIPLE" => 'N',
				"MANDATORY" => "N",
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "N",
				"EDIT_IN_LIST" => "Y",
			);

			$obUserField = new CUserTypeEntity;
			foreach ($arFields as $arField)
			{
				$rsData = CUserTypeEntity::GetList(array("ID" => "ASC"), $arField);
				if (!($rsData && ($arRes = $rsData->Fetch()) && !!$arRes))
				{
					$intID = $obUserField->Add($arField, false);
					if (
						false == $intID
						&& ($strEx = $APPLICATION->GetException())
					)
					{
						$errors = $strEx->GetString();
					}
					else if (
						$arField["FIELD_NAME"] == "UF_BLOG_POST_IMPRTNT" 
						&& $GLOBALS["DB"]->TableExists("b_uts_blog_post") 
						&& !$GLOBALS["DB"]->IndexExists("b_uts_blog_post", array("UF_BLOG_POST_IMPRTNT", "VALUE_ID"))
					)
					{
						$GLOBALS["DB"]->Query("CREATE INDEX UX_UF_BLOG_POST_IMPRTNT ON b_uts_blog_post(UF_BLOG_POST_IMPRTNT, VALUE_ID)", true);
					}
				}
				else if ($arField["FIELD_NAME"] == "UF_BLOG_POST_VOTE")
				{
					$obUserField->Update($arRes["ID"], $arField);
				}
			}
		}
		return $errors;
	}

	public static function installDiskUserFields()
	{
		global $APPLICATION;
		$errors = null;

		if(!IsModuleInstalled('disk'))
		{
			return null;
		}

		$props = array(
			array(
				"ENTITY_ID" => "SONET_COMMENT",
				"FIELD_NAME" => "UF_SONET_COM_VER",
				"USER_TYPE_ID" => "disk_version"
			),
			array(
				"ENTITY_ID" => "SONET_LOG",
				"FIELD_NAME" => "UF_SONET_LOG_DOC",
				"USER_TYPE_ID" => "disk_file"
			),
			array(
				"ENTITY_ID" => "SONET_COMMENT",
				"FIELD_NAME" => "UF_SONET_COM_DOC",
				"USER_TYPE_ID" => "disk_file"
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

	public static function installIntranetUserFields()
	{
		global $APPLICATION, $DB;
		$errors = null;

		if(!IsModuleInstalled('intranet'))
		{
			return null;
		}

		if(!CModule::IncludeModule('iblock'))
		{
			return null;
		}

		$iblockId = false;

		$res = $DB->Query("select ID from b_iblock where CODE='departments' AND IBLOCK_TYPE_ID='structure'", true);
		if (
			($ar = $res->Fetch())
			&& intval($ar['ID']) > 0
		)
		{
			$iblockId = $ar['ID'];
		}

		if(intval($iblockId) <= 0)
		{
			return null;
		}

		$arUFSettings = array(
			'DISPLAY' => 'LIST',
			'LIST_HEIGHT' => '8',
			'IBLOCK_ID' => $iblockId,
			'ACTIVE_FILTER' => 'Y'
		);

		$props = array(
			array(
				"ENTITY_ID" => "SONET_GROUP",
				"FIELD_NAME" => "UF_SG_DEPT",
				"USER_TYPE_ID" => "iblock_section",
				"MULTIPLE" => "Y",
				"SETTINGS" => $arUFSettings
			)
		);
		$uf = new CUserTypeEntity;
		foreach ($props as $prop)
		{
			$rsData = CUserTypeEntity::getList(array("ID" => "ASC"), array("ENTITY_ID" => $prop["ENTITY_ID"], "FIELD_NAME" => $prop["FIELD_NAME"]));
			if (!($rsData && ($arRes = $rsData->Fetch())))
			{
				$arUFFields = array(
					"ENTITY_ID" => $prop["ENTITY_ID"],
					"FIELD_NAME" => $prop["FIELD_NAME"],
					"XML_ID" => $prop["FIELD_NAME"],
					"USER_TYPE_ID" => $prop["USER_TYPE_ID"],
					"SORT" => 100,
					"MULTIPLE" => $prop["MULTIPLE"],
					"MANDATORY" => "N",
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => "N",
					"EDIT_IN_LIST" => "Y",
					"IS_SEARCHABLE" => "N",
					"SETTINGS" => $prop["SETTINGS"],
				);

				$dbLangs = CLanguage::GetList(($b = ""), ($o = ""), array("ACTIVE" => "Y"));
				while ($arLang = $dbLangs->Fetch())
				{
					$messages = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/index.php", $arLang["LID"], true);
					$arUFFields["EDIT_FORM_LABEL"][$arLang["LID"]] = $messages["SONET_".$prop["FIELD_NAME"]."_EDIT_FORM_LABEL"];
					$arUFFields["LIST_COLUMN_LABEL"][$arLang["LID"]] = $messages["SONET_".$prop["FIELD_NAME"]."_LIST_COLUMN_LABEL"];
					$arUFFields["LIST_FILTER_LABEL"][$arLang["LID"]] = $messages["SONET_".$prop["FIELD_NAME"]."_LIST_FILTER_LABEL"];
				}

				$intID = $uf->add($arUFFields, false);

				if (false == $intID && ($strEx = $APPLICATION->getException()))
				{
					$errors[] = $strEx->getString();
				}
			}
		}

		return $errors;
	}

	function UnInstallUserFields($id = "all")
	{
		$id = (empty($id) ? "all" : (in_array($id, array("all", "webdav"/*, "blog"*/)) ? $id : false));
		if (!!$id)
		{
			$arFields = array(
				array(
					"ENTITY_ID" => "SONET_LOG",
					"FIELD_NAME" => "UF_SONET_LOG_FILE",
					"XML_ID" => "UF_SONET_LOG_FILE"
				),
				array(
					"ENTITY_ID" => "SONET_LOG",
					"FIELD_NAME" => "UF_SONET_LOG_DOC",
					"XML_ID" => "UF_SONET_LOG_DOC"
				),
				array(
					"ENTITY_ID" => "SONET_COMMENT",
					"FIELD_NAME" => "UF_SONET_COM_FILE",
					"XML_ID" => "UF_SONET_COM_FILE"
				),
				array(
					"ENTITY_ID" => "SONET_COMMENT",
					"FIELD_NAME" => "UF_SONET_COM_DOC",
					"XML_ID" => "UF_SONET_COM_DOC"
				),
				array(
					"ENTITY_ID" => "BLOG_POST",
					"FIELD_NAME" => "UF_BLOG_POST_IMPRTNT",
					"XML_ID" => "UF_BLOG_POST_IMPRTNT"
				),
				array(
					"ENTITY_ID" => "BLOG_POST",
					"FIELD_NAME" => "UF_BLOG_POST_VOTE",
					"XML_ID" => "UF_BLOG_POST_VOTE"
				),
				array(
					"ENTITY_ID" => "SONET_LOG",
					"FIELD_NAME" => "UF_SONET_LOG_URL_PRV",
					"XML_ID" => "UF_SONET_LOG_URL_PRV"
				),
				array(
					"ENTITY_ID" => "SONET_COMMENT",
					"FIELD_NAME" => "UF_SONET_COM_URL_PRV",
					"XML_ID" => "UF_SONET_COM_URL_PRV"
				)
			);

			if ($id == "webdav")
				$arFields = array(
					array(
						"ENTITY_ID" => "SONET_LOG",
						"FIELD_NAME" => "UF_SONET_LOG_DOC",
						"XML_ID" => "UF_SONET_LOG_DOC"
					),
					array(
						"ENTITY_ID" => "SONET_COMMENT",
						"FIELD_NAME" => "UF_SONET_COM_DOC",
						"XML_ID" => "UF_SONET_COM_DOC"
					),
				);

			foreach ($arFields as $arField)
			{
				$rsData = CUserTypeEntity::GetList(array("ID" => "ASC"), $arField);
				if ($arRes = $rsData->Fetch())
				{
					$ent = new CUserTypeEntity;
					$ent->Delete($arRes['ID']);
					if ($arField["FIELD_NAME"] == "UF_BLOG_POST_IMPRTNT" && $GLOBALS["DB"]->TableExists("b_uts_blog_post") &&
						$GLOBALS["DB"]->IndexExists("b_uts_blog_post", array("UF_BLOG_POST_IMPRTNT", "VALUE_ID")))
					{
						$GLOBALS["DB"]->Query("DROP INDEX UX_UF_BLOG_POST_IMPRTNT ON b_uts_blog_post", true);
					}
				}
			}
		}
	}

	function InstallEvents()
	{
		global $DB;

		$sIn = "'SONET_NEW_MESSAGE', 'SONET_INVITE_FRIEND', 'SONET_INVITE_GROUP', 'SONET_AGREE_FRIEND', 'SONET_BAN_FRIEND', 'SONET_NEW_EVENT_GROUP', 'SONET_NEW_EVENT_USER'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			$pathInMessage = (array_key_exists("public_path", $_REQUEST) ? $_REQUEST["public_path"] : "");
			if (strlen($pathInMessage) <= 0)
			{
				if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet"))
					$pathInMessage = "/company/personal/";
				else
					$pathInMessage = "/club/";
			}
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/events/del_events.php");
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork", true, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/sounds",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/sounds", true, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/activities", $_SERVER["DOCUMENT_ROOT"]."/bitrix/activities", true, true);
		}
		return true;
	}

	function InstallPublic()
	{
		$arSite = array();
		$arSites = array();
		$arInstallParams = array();

		//getting params from $_REQUEST
		$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
		while ($arSite = $dbSites->GetNext())
		{
			if (strlen($_REQUEST{"install_site_id_".$arSite["ID"]}) > 0)
			{
				$arInstallParams[$arSite["ID"]]["install_site_id"] = $_REQUEST{"install_site_id_".$arSite["ID"]};
				$arInstallParams[$arSite["ID"]]["installPath"] = $_REQUEST{"public_path_".$arSite["ID"]};
				$arInstallParams[$arSite["ID"]]["install404"] = (($_REQUEST{"is404_".$arSite["ID"]} == "Y") ? true : false);
				$arInstallParams[$arSite["ID"]]["installRewrite"] = (($_REQUEST{"public_rewrite_".$arSite["ID"]} == "Y") ? true : false);
				$arSites[] = $arSite;
			}
		}

		//running installation script for each site
		foreach ($arSites as $site)
		{
			$installSiteID = $arInstallParams[$site["ID"]]["install_site_id"];
			$installPath = $arInstallParams[$site["ID"]]["installPath"];
			$install404 = $arInstallParams[$site["ID"]]["install404"];
			$installRewrite = $arInstallParams[$site["ID"]]["installRewrite"];

			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/install_demo.php");
		}
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/socialnetwork/");//icons
		DeleteDirFilesEx("/bitrix/images/socialnetwork/");//images
		DeleteDirFilesEx("/bitrix/sounds/socialnetwork/");//sounds

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if ($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage("SONET_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/step1.php");
		elseif($step==2)
		{
			$this->InstallFiles();
			$this->InstallDB(false);
			$this->InstallEvents();
			$this->InstallPublic();
			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("SONET_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("SONET_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/unstep1.php");
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			if($_REQUEST["saveemails"] != "Y")
				$this->UnInstallEvents();

			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("SONET_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", "K", "R", "W"),
			"reference" => array(
					"[D] ".GetMessage("SONETP_PERM_D"),
					"[K] ".GetMessage("SONETP_PERM_K"),
					"[R] ".GetMessage("SONETP_PERM_R"),
					"[W] ".GetMessage("SONETP_PERM_W")
				),
			"use_site" => array("K", "W")
			);
		return $arr;
	}
}
?>