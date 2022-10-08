<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("webdav")):
	ShowError(GetMessage("SONET_WD_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("SONET_IB_MODULE_IS_NOT_INSTALLED"));
	return 0;
endif;

if (!function_exists("__wd_replace_user_and_groups"))
{
	function __wd_replace_user_and_groups(&$val, $key, $params = array())
	{
		if ($val == 1)
			$val = $params["moderator"];
		elseif ($val == "user_1")
			$val = "user_".$params["owner"];
		elseif ($key == "MailText")
		{
			$val = str_replace(
				"/company/personal/bizproc/{=Workflow:id}/",
				$params["path"],
				$val);
		}
		return true;
	}
}

if (!function_exists("__wd_create_default_bp_user_and_groups"))
{
	function __wd_create_default_bp_user_and_groups($arr)
	{
		if($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.BX_ROOT.'/modules/bizproc/templates'))
		{
			$documentType = array("webdav", "CIBlockDocumentWebdavSocnet", $arr["document_type"]);

			while(false !== ($file = readdir($handle)))
			{
				if(!is_file($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bizproc/templates/'.$file))
				{
					continue;
				}


				$arFields = false;
				include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bizproc/templates/'.$file);
				if(is_array($arFields))
				{
					$arFields["DOCUMENT_TYPE"] = $documentType;
					$arFields["SYSTEM_CODE"] = $file;
					$arFields["USER_ID"]	= $GLOBALS['USER']->GetID();
					array_walk_recursive($arFields["TEMPLATE"], "__wd_replace_user_and_groups", $arr);
					if ($file == "status.php")
					{
						$arFields["AUTO_EXECUTE"] = CBPDocumentEventType::Create;
						if (!empty($arFields["PARAMETERS"]) && !empty($arFields["PARAMETERS"]["Approvers"]))
						{
							$name = "";
							if ($GLOBALS["USER"]->IsAuthorized() && $arr["owner"] == $GLOBALS["USER"]->GetID())
							{
								$name = trim($GLOBALS["USER"]->GetFirstName()." ".$GLOBALS["USER"]->GetLastName());
								$name = (empty($name) ? $GLOBALS["USER"]->GetLogin() : $name);
							}
							else
							{
								$dbUser = CUser::GetByID($arr["owner"]);
								$arUser = $dbUser->Fetch();
								$name = trim($arUser["NAME"]." ".$arUser["LAST_NAME"]);
								$name = (empty($name) ? $arUser["LOGIN"] : $name);
							}

							$arFields["PARAMETERS"]["Approvers"]["Default"] = $name.' ['.$arr["owner"].']';
						}
					}

					try
					{
						CBPWorkflowTemplateLoader::Add($arFields);
					}
					catch (Exception $e)
					{
					}
				}
			}
			closedir($handle);
		}
	}
}

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/../lang/".LANGUAGE_ID."/include/webdav.php")));

__IncludeLang($file);

$object = (mb_strpos($componentPage, "group_files") !== false ? "group" : "user");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["IBLOCK_TYPE"] = intval($object == "user" ? $arParams["FILES_USER_IBLOCK_TYPE"] : $arParams["FILES_GROUP_IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($object == "user" ? $arParams["FILES_USER_IBLOCK_ID"] : $arParams["FILES_GROUP_IBLOCK_ID"]);
$arParams['USE_AUTH'] = ($arParams['FILES_USE_AUTH'] == "Y" ? "Y" : "N");
$arParams["NAME_FILE_PROPERTY"] = mb_strtoupper(trim(empty($arParams["FILE_NAME_FILE_PROPERTY"])? "FILE" : $arParams["FILE_NAME_FILE_PROPERTY"]));
$arParams["FILES_PATH_TO_SMILE"] = "/bitrix/images/forum/smile/";
$arResult['BASE_URL'] = ($object == "user" ? $arParams["FILES_USER_BASE_URL"] : $arParams["FILES_GROUP_BASE_URL"]);
if ($arParams["SEF_MODE"] == "Y"):
	$arResult['BASE_URL'] = ($object == "user" ? $arResult["PATH_TO_USER_FILES"] : $arResult["PATH_TO_GROUP_FILES"]);
endif;	
$arResult['BASE_URL'] = rtrim(str_replace(
	array("#user_id#", "#group_id#", "#path#"),
	array($arResult["VARIABLES"]["user_id"], $arResult["VARIABLES"]["group_id"], ""), $arResult['BASE_URL']), '/');
/***************** ADDITIONAL **************************************/
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if ($object == 'user')
	CIBlockWebdavSocnet::UserERights($arParams['IBLOCK_ID']);
elseif ($object == 'group')
	CIBlockWebdavSocnet::GroupERights($arParams['IBLOCK_ID']);

$res = CIBlockWebdavSocnet::GetUserMaxPermission(
	$object,
	($object == "user" ? $arResult["VARIABLES"]["user_id"] : $arResult["VARIABLES"]["group_id"]),
	$USER->GetID(),
	$arParams['IBLOCK_ID']
);
$arParams["PERMISSION"] = $res["PERMISSION"];
$arParams["CHECK_CREATOR"] = $res["CHECK_CREATOR"];
$arParams["STR_TITLE"] = GetMessage("SONET_FILES");
$arParams["SHOW_WEBDAV"] = "Y";
/********************************************************************
				/Input params
********************************************************************/

$arError = array();

/********************************************************************
				Check Socnet Permission and Main Data
********************************************************************/

if ($object == 'user')
{
}
elseif ($object == 'group')
{
	if (!CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]))
	{
		$arError[] = array(
			"id" => "group_not_exists", 
			"text" => GetMessage("SONET_GROUP_NOT_EXISTS")
		);
	}
}

/************** Can View *******************************************/
if ($arParams["PERMISSION"] < "R")
{
	$arError[] = array(
		"id" => "access_denied",
		"text" => ($object == "user" ? GetMessage("SONET_USER_FILES_ACCESS_DENIED") :  GetMessage("SONET_GROUP_FILES_ACCESS_DENIED"))
	);
}
/************** Active Feature *************************************/
elseif (
	(
		$object == "user"
		&& !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "files")
	)
	|| (
		$object == "group"
		&& !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "files")
	)
)
{
	$arError[] = array(
		"id" => "fiture_is_not_active",
		"text" => GetMessage("SONET_FILES_IS_NOT_ACTIVE")
	);
}
/************** Check Iblock ID ************************************/
elseif (
	(
		$object == "user" 
		&& $arParams["FILES_USER_IBLOCK_ID"] <= 0
	) 
	|| (
		$object == "group" 
		&& $arParams["FILES_GROUP_IBLOCK_ID"] <= 0
	)
)
{
	$arError[] = array(
		"id" => "iblock_id_empty",
		"text" => GetMessage("SONET_IBLOCK_ID_EMPTY")
	);
}
elseif (
	$arParams["USE_AUTH"] == "Y" 
	&& (
		CWebDavBase::IsDavHeaders() 
		|| (
			$_SERVER['REQUEST_METHOD'] != "GET" 
			&& $_SERVER['REQUEST_METHOD'] != "POST"
		)
	) 
	&& !$USER->IsAuthorized()
)
{
	$APPLICATION->RestartBuffer();
	CWebDavBase::SetAuthHeader();
	header('Content-length: 0');
	die();
}

/************** Set Page Title or Add Navigation *******************/
if ($arParams["SET_NAV_CHAIN"] == "Y" || $arParams["SET_TITLE"] == "Y")
{

	$strTitle = "";
	if($object == "group")
	{
		$arResult["GROUP"] = $arGroup = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
		$db_res = CSocNetFeatures::GetList(
			array(),
			array(
				"ENTITY_ID" => $arResult["GROUP"]["ID"],
				"ENTITY_TYPE" => SONET_ENTITY_GROUP,
				"FEATURE" => "files"));
		if ($db_res && $arResult["GROUP"]["FEATURE"] = $db_res->GetNext()):
			$arParams["STR_TITLE"] = $arResult["GROUP"]["FEATURE"]["FEATURE_NAME"] = (empty($arResult["GROUP"]["FEATURE"]["FEATURE_NAME"]) ?
				$arParams["STR_TITLE"] : $arResult["GROUP"]["FEATURE"]["FEATURE_NAME"]);
		else:
			$arResult["GROUP"]["FEATURE"] = array(
				"FEATURE_NAME" => $arParams["STR_TITLE"]);
		endif;

		$strTitle = $arGroup["~NAME"].": ".$arParams["STR_TITLE"];
		$strTitleShort = $arParams["STR_TITLE"];

		if ($arParams["SET_NAV_CHAIN"] == "Y")
		{
			$APPLICATION->AddChainItem($arGroup["NAME"], CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP"],
				array("group_id" => $arGroup["ID"])));
			$APPLICATION->AddChainItem($arParams["STR_TITLE"], CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP_FILES"],
				array("group_id" => $arGroup["ID"], "path" => "")));
		}
	}
	else
	{
		if ($arParams["NAME_TEMPLATE"] == '')
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"),
			array("", ""),
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

		$name = "";
		if ($USER->IsAuthorized() && $arResult["VARIABLES"]["user_id"] == $USER->GetID())
		{
			$arTmpUser = array(
				"NAME" => $USER->GetFirstName(),
				"LAST_NAME" => $USER->GetLastName(),
				"SECOND_NAME" => $USER->GetParam("SECOND_NAME"),
				"LOGIN" => $USER->GetLogin(),
			);
			$name = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		else
		{
			$dbUser = CUser::GetByID($arResult["VARIABLES"]["user_id"]);
			$arUser = $dbUser->Fetch();
			$name = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);
		}
		$arResult["USER"] = array(
			"ID" => $arResult["VARIABLES"]["user_id"],
			"NAME" => $name);
		$db_res = CSocNetFeatures::GetList(
			array(),
			array(
				"ENTITY_ID" => $arResult["USER"]["ID"],
				"ENTITY_TYPE" => SONET_ENTITY_USER,
				"FEATURE" => "files"));
		if ($db_res && $arResult["USER"]["FEATURE"] = $db_res->GetNext()):
			$arParams["STR_TITLE"] = $arResult["USER"]["FEATURE"]["FEATURE_NAME"] = (empty($arResult["USER"]["FEATURE"]["FEATURE_NAME"]) ?
				$arParams["STR_TITLE"] : $arResult["USER"]["FEATURE"]["FEATURE_NAME"]);
		else:
			$arResult["USER"]["FEATURE"] = array(
				"FEATURE_NAME" => $arParams["STR_TITLE"]);
		endif;


		$name = trim($name);
		$strTitle = $name.": ".$arParams["STR_TITLE"];
		$strTitleShort = $arParams["STR_TITLE"];

		if ($arParams["SET_NAV_CHAIN"] == "Y")
		{
			$APPLICATION->AddChainItem($name, CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"],
				array("user_id" => $arResult["VARIABLES"]["user_id"])));
			$APPLICATION->AddChainItem($arParams["STR_TITLE"], CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_FILES"],
					array("user_id" => $arResult["VARIABLES"]["user_id"], "path" => "")));
		}
	}
	if ($arParams["SET_TITLE"] == "Y")
	{
		if ($arParams["HIDE_OWNER_IN_TITLE"] == "Y")
		{
			$APPLICATION->SetPageProperty("title", $strTitle);
			$APPLICATION->SetTitle($strTitleShort);
		}
		else
		{
			$APPLICATION->SetTitle($strTitle);
		}

		if (
			$componentPage == "user_files" 
			&& (
				empty($arResult["VARIABLES"]["path"]) 
				|| $arResult["VARIABLES"]["path"] == "index.php"
			)
		)
		{
			$arParams["SET_TITLE"] = "N";
		}
	}
}

if (!empty($arError))
{
	$e = new CAdminException($arError);
	$arParams["ERROR_MESSAGE"] = $e->GetString();
	return -1;
}
/********************************************************************
				/Check Socnet Permission and Main Data
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
define("WEBDAV_SETTINGS_LIMIT_INCLUDE", "Y");
$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/webdav_settings.php")));
require_once($file);

/************** Path ***********************************************/
$sBaseUrl = $APPLICATION->GetCurDir();
$arParsedUrl = parse_url($_SERVER['REQUEST_URI']);
$page = ($arParsedUrl ? $arParsedUrl['path'] : $_SERVER['REQUEST_URI']);
/************** Initial object *************************************/
$arParams["DOCUMENT_TYPE"] = array("webdav", "CIBlockDocumentWebdavSocnet", "iblock_".$arParams["IBLOCK_ID"]."_".$object."_".
		intval($object == "user" ? $arResult["VARIABLES"]["user_id"] : $arResult["VARIABLES"]["group_id"]));

$arBizProcParameters = array(
	"object" => $object,
	"owner" => ($object == "user" ? $arResult["VARIABLES"]["user_id"] : $arResult["GROUP"]["OWNER_ID"]),
	"moderator" => mb_strtolower($object == "user"? SONET_RELATIONS_TYPE_NONE : SONET_ROLES_MODERATOR),
	"path" => ($object == "user" ? $arResult["PATH_TO_USER_FILES_WEBDAV_BIZPROC_VIEW"] : $arResult["PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_VIEW"]),
	"document_type" => $ob->wfParams['DOCUMENT_TYPE'][2]);
$user_id_str = (intval($arResult["VARIABLES"]["user_id"]) > 0 ? $arResult["VARIABLES"]["user_id"] : $GLOBALS["USER"]->GetId());
$arBizProcParameters["path"] = str_replace(
	array(
		"#user_id#",
		"#group_id#",
		"#element_id#"),
	array(
		$user_id_str,
		$arResult["VARIABLES"]["group_id"],
		"{=Document:ID}"),
	$arBizProcParameters["path"]);
/************** Root Section ***************************************/


$arParams["ROOT_SECTION_ID"] = __wd_get_root_section(
	$arParams["IBLOCK_ID"],
	$object,
	(($object=='user') ? $arResult["VARIABLES"]["user_id"] : $arResult["VARIABLES"]["group_id"])
);

/*if ($arParams["ROOT_SECTION_ID"] === true) // created new section
{
	BXClearCache(true, $ob->CACHE_PATH);
	LocalRedirect($APPLICATION->GetCurPageParam("", array("create_lib", "sessid")));
}*/

if (
	$object == "user"
	&& $arParams["ROOT_SECTION_ID"] !== "NO_OBJECT"
)
{
	CIBlockWebdavSocnet::CreateSharedFolder(
		$arParams["IBLOCK_ID"],
		$arParams["ROOT_SECTION_ID"],
		$arResult["VARIABLES"]["user_id"]
	);
}

$arParams["OBJECT"] = $ob = new CWebDavIblock($arParams['IBLOCK_ID'], $arResult['BASE_URL'], 
	$arParams + array(
		"SHORT_PATH_TEMPLATE" => "/".($object == "user" ? $arDefaultUrlTemplates404["user_files_short"] : $arDefaultUrlTemplates404["group_files_short"]),
		"ATTRIBUTES" => ($object == "user" ? array('user_id' => $arResult["VARIABLES"]["user_id"]) : array('group_id' => $arResult["VARIABLES"]["group_id"]))
	));

if ($arParams["ROOT_SECTION_ID"] === true) // created new section
{
	BXClearCache(true, $ob->CACHE_PATH);
	LocalRedirect($APPLICATION->GetCurPageParam("", array("create_lib", "sessid")));
}

if (!empty($ob->arError))
{
	$e = new CAdminException($ob->arError);
	$GLOBALS["APPLICATION"]->ThrowException($e);
	$res = $GLOBALS["APPLICATION"]->GetException();
	if ($res)
	{
		ShowError($res->GetString());
		return false;
	}
}
elseif ($ob->permission <= "D")
{
	ShowError(GetMessage("WD_ACCESS_DENIED"));
	return false;
}

//=====
if(class_exists("CWebDavExtLinks"))
{
if(array_key_exists("GetExtLink", $_REQUEST) && intval($_REQUEST["GetExtLink"]) == 1)
{
	CUtil::JSPostUnescape();
	CWebDavExtLinks::CheckSessID();
	CWebDavExtLinks::CheckRights($ob);
	$o = array();
	$o["PASSWORD"] = (array_key_exists("PASSWORD", $_REQUEST) ? $_REQUEST["PASSWORD"] : "");
	$o["LIFETIME_NUMBER"] = (array_key_exists("LIFETIME_NUMBER", $_REQUEST) ? intval($_REQUEST["LIFETIME_NUMBER"]) : 0);
	$o["LIFETIME_TYPE"] = (array_key_exists("LIFETIME_TYPE", $_REQUEST) ? $_REQUEST["LIFETIME_TYPE"] : "notlimited");
	$o["URL"] = CHTTP::urnDecode($ob->_path);
	$o["BASE_URL"] = $arResult['BASE_URL'];
	$o["DESCRIPTION"] = (array_key_exists("DESCRIPTION", $_REQUEST) ? $_REQUEST["DESCRIPTION"] : "");
	$fileOptT = CWebDavExtLinks::GetFileOptions($ob);
	$o["F_SIZE"] = $fileOptT["F_SIZE"];
	CWebDavExtLinks::GetExtLink($arParams, $o);
}

if(!empty($_REQUEST['toWDController']) || !empty($_REQUEST['showInViewer']) || !empty($_REQUEST['editIn']) || !empty($_REQUEST['history']))
{
	include_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/webdav/tools/google/document_controller.php';
}

if(array_key_exists("GetDialogDiv", $_REQUEST) && intval($_REQUEST["GetDialogDiv"]) == 1)
{
	CWebDavExtLinks::CheckSessID();
	CWebDavExtLinks::CheckRights($ob);
	CWebDavExtLinks::PrintDialogDiv($ob);
}

if(array_key_exists("DeleteLink", $_REQUEST) && $_REQUEST["DeleteLink"] <> '')
{
	CWebDavExtLinks::CheckSessID();
	CWebDavExtLinks::CheckRights($ob);
	CWebDavExtLinks::DeleteLink($_REQUEST["DeleteLink"]);
}

if(array_key_exists("DeleteAllLinks", $_REQUEST) && $_REQUEST["DeleteAllLinks"] <> '')
{
	CWebDavExtLinks::CheckSessID();
	CWebDavExtLinks::CheckRights($ob);
	CWebDavExtLinks::DeleteAllLinks($_REQUEST["DeleteAllLinks"], $ob);
}

}
//=====

$ob->file_prop = $arParams["NAME_FILE_PROPERTY"];
$ob->replace_symbols = ($arParams["REPLACE_SYMBOLS"] == "Y" ? true : false);

$arParams['WORKFLOW'] = $ob->workflow;
$arResult['CURRENT_PATH'] = $ob->_path;

$res = $ob->SetRootSection($arParams["ROOT_SECTION_ID"]); 

/********************************************************************
				/
********************************************************************/
if (($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') && !$ob->IsDavHeaders()) 
{
	if ($componentPage == "user_files" || $componentPage == "group_files")
	{
		$arResult["VARIABLES"]["SECTION_ID"] = 0;

		if ($arParams["SEF_MODE"] != "Y")
		{
			$res = explode("/", urldecode($_REQUEST["path"]));
			$result = array();
			foreach ($res as $r)
			{
				$result[] = urlencode($APPLICATION->ConvertCharset($r, SITE_CHARSET, 'UTF-8'));
			}
			$arResult["VARIABLES"]["PATH"] = implode("/", $result);
			$ob->SetPath("/".$arResult["VARIABLES"]["PATH"]);
		}

		$ob->IsDir(array('check_permissions' => false));
		if ($ob->arParams['is_file'])
		{
			if(\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false) && CModule::includeModule('disk'))
			{
				if(empty($ob->arParams['element_array']['ID']) && !empty($arResult["VARIABLES"]["element_id"]))
				{
					$ob->IsDir(array("element_id" => $arResult["VARIABLES"]["element_id"]));
				}

				/** @var \Bitrix\Disk\File $diskFile */
				$diskFile = \Bitrix\Disk\File::load(array('XML_ID' => $ob->arParams['element_array']['ID']), array('STORAGE'));
				if($diskFile)
				{
					if($diskFile->canRead($diskFile->getStorage()->getCurrentUserSecurityContext()))
					{
						CFile::viewByUser($diskFile->getFile(), array("force_download" => false));
					}
				}
			}

			$APPLICATION->RestartBuffer();
			$ob->base_GET();
			die();
		}
		elseif ($ob->arParams['is_dir'])
		{
			$arResult["VARIABLES"]["SECTION_ID"] = $ob->arParams["item_id"];
		}
	}
	elseif ($componentPage == "user_files_short" || $componentPage == "group_files_short")
	{
		if ($arResult["VARIABLES"]["element_id"] > 0)
		{
			$ob->IsDir(array("element_id" => $arResult["VARIABLES"]["element_id"]));
			if ($ob->arParams['is_file'])
			{
				$APPLICATION->RestartBuffer();
				$ob->base_GET();
				die();
			}
		}
		$arResult["VARIABLES"]["SECTION_ID"] = intval($arResult["VARIABLES"]["section_id"]);
		$componentPage = str_replace("_short", "", $componentPage);
	}
	elseif ($componentPage == "user_files_element_history_get" || $componentPage == "group_files_element_history_get")
	{
		if(\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false) && CModule::includeModule('disk'))
		{
			if(empty($ob->arParams['element_array']['ID']) && !empty($arResult["VARIABLES"]["element_id"]))
			{
				$ob->IsDir(array("element_id" => $arResult["VARIABLES"]["element_id"]));
			}

			/** @var \Bitrix\Disk\File $diskFile */
			$diskFile = \Bitrix\Disk\File::load(array('XML_ID' => $ob->arParams['element_array']['ID']), array('STORAGE'));
			if($diskFile)
			{
				if($diskFile->canRead($diskFile->getStorage()->getCurrentUserSecurityContext()))
				{
					CFile::viewByUser($diskFile->getFile(), array("force_download" => false));
				}
			}
		}

		$APPLICATION->RestartBuffer();
		$ob->SendHistoryFile($arResult["VARIABLES"]["element_id"], 0, false, $_REQUEST);
		die();
	}
	elseif ($componentPage == "user_files_webdav_bizproc_history_get" || $componentPage == "group_files_webdav_bizproc_history_get")
	{
		$APPLICATION->RestartBuffer();
		$ob->SendHistoryFile($arResult["VARIABLES"]["element_id"], $arResult["VARIABLES"]["id"]);
		die();
	}

	elseif (($componentPage == "user_files_section_edit" || $componentPage == "group_files_section_edit") &&
		mb_strtoupper($_REQUEST["use_light_view"]) == "Y")
	{
		$componentPage .= "_simple";
	}
	elseif (($componentPage == "user_files_element_comment") || ($componentPage == "group_files_element_comment"))
	{
		$topicID = intval($arResult["VARIABLES"]['topic_id']);
		$messageID = intval($arResult["VARIABLES"]['message_id']);
		if (
			($topicID > 0) &&
			($messageID > 0) &&
			CModule::IncludeModule('forum')
		)
		{
			$dbMessage = CForumMessage::GetList(array(), array(
				'FORUM_ID' => $arParams['FILES_FORUM_ID'],
				'TOPIC_ID' => $topicID,
				'NEW_TOPIC' => 'Y',
				'PARAM1' => 'IB'
			));
			if ($dbMessage && $arMessage = $dbMessage->Fetch())
			{
				$elementID = intval($arMessage['PARAM2']);
				if ($elementID > 0)
				{
					// check if this iblock
					$dbElement = CIBlockElement::GetList(array(), array('ID' => $elementID), false, false, array('IBLOCK_ID'));
					if (
						$dbElement
						&& ($arElement = $dbElement->Fetch())
						&& ($arElement['IBLOCK_ID'] == $arParams['IBLOCK_ID'])
					)
					{
						$elementUrl = '';
						if (
							is_array($arResult['USER']) &&
							isset($arResult['USER']['ID'])
						)
						{
							$elementUrl = str_replace("#user_id#", $arResult['USER']['ID'], $arResult['PATH_TO_USER_FILES_ELEMENT']);
						}
						elseif (
							is_array($arResult['GROUP']) &&
							isset($arResult['GROUP']['ID'])
						)
						{
							$elementUrl = str_replace("#group_id#", $arResult['GROUP']['ID'], $arResult['PATH_TO_GROUP_FILES_ELEMENT']);
						}

						if (!empty($elementUrl))
						{
							$arParams["FORM_ID"] = "webdavForm".$arParams["IBLOCK_ID"];
							$elementUrl = str_replace('#element_id#', $elementID, $elementUrl);
							$elementUrl .= (mb_strpos($elementUrl, '?') !== false ? '&' : '?');
							$elementUrl .= $arParams["FORM_ID"].'_active_tab=tab_comments';

							LocalRedirect($elementUrl);
						}
					}
				}
			}
		}
		LocalRedirect($arParams['SEF_FOLDER']);
	}
}
elseif ($ob->IsMethodAllow($_SERVER['REQUEST_METHOD'])) 
{
	$APPLICATION->RestartBuffer();
	$fn = 'base_' . $_SERVER['REQUEST_METHOD'];
	call_user_func(array(&$ob, $fn));
	die();
}
else
{
	CHTTP::SetStatus('405 Method not allowed');
	header('Allow: ' . join(',', array_keys($ob->allow)));
	$this->IncludeComponentTemplate('notallowed');
	return 1;
}

/********************************************************************
				/Default params
********************************************************************/
/********************************************************************
				Path
********************************************************************/
foreach ($arDefaultUrlTemplates404 as $url => $value)
{
	if (mb_strpos($componentPage, "user_files") === false && mb_strpos($componentPage, "group_files") === false &&
		mb_strpos($componentPage, "bizproc") === false)
		continue;
	$user_id_str = (intval($arResult["VARIABLES"]["user_id"]) > 0 ? $arResult["VARIABLES"]["user_id"] : $GLOBALS["USER"]->GetId());
	$arResult["~PATH_TO_".mb_strtoupper($url)] = str_replace(
		array(
			"#user_id#",
			"#group_id#",
			"#path#",
			"#section_id#",
			"#element_id#",
			"#element_name#",
			"#action#",
			"#id#",
			"#task_id#"),
		array(
			$user_id_str,
			$arResult["VARIABLES"]["group_id"],
			"#PATH#",
			"#SECTION_ID#",
			"#ELEMENT_ID#",
			"#ELEMENT_NAME#",
			"#ACTION#",
			"#ID#",
			"#ID#"),
		$arResult["PATH_TO_".mb_strtoupper($url)]);
}

if ($ob->workflow == 'bizproc' || $ob->workflow == 'bizproc_limited')
{
	$arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY"] = $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY"];
	$arResult["~PATH_TO_USER_FILES_ELEMENT_HISTORY"] = $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_HISTORY"];
}

$arResult["~PATH_TO_USER"] = str_replace("#user_id#", "#USER_ID#", (empty($arResult["PATH_TO_USER"]) ? $arParams["PATH_TO_USER"] : $arResult["PATH_TO_USER"]));
$arResult["VARIABLES"]["ROOT_SECTION_ID"] = $arParams["ROOT_SECTION_ID"];
if (empty($arResult["VARIABLES"]["SECTION_ID"]))
	$arResult["VARIABLES"]["SECTION_ID"] = $arResult["VARIABLES"]["section_id"];
$arResult["VARIABLES"]["ELEMENT_ID"] = $arResult["VARIABLES"]["element_id"];
$arResult["VARIABLES"]["ID"] = $arResult["VARIABLES"]["id"];
$arResult["VARIABLES"]["ACTION"] = $arResult["VARIABLES"]["action"];
$arResult["VARIABLES"]["PERMISSION"] = $arParams["PERMISSION"];
$arResult["VARIABLES"]["CHECK_CREATOR"] = $arParams["CHECK_CREATOR"];
$arResult["VARIABLES"]["BASE_URL"] = $arResult['BASE_URL'];
$arResult["VARIABLES"]["STR_TITLE"] = $arParams["STR_TITLE"];
$arResult["VARIABLES"]["PAGE_NAME"] = mb_strtoupper(str_replace(array("user_files_", "user_files", "group_files_", "group_files"), "", $componentPage));
$arResult["VARIABLES"]["PAGE_NAME"] = ($arResult["VARIABLES"]["PAGE_NAME"] == "" ? "SECTIONS" : $arResult["VARIABLES"]["PAGE_NAME"]);
$arResult["VARIABLES"]["MODULE_ID"] = $ob->wfParams['DOCUMENT_TYPE'][0];
$arResult["VARIABLES"]["ENTITY"] = $ob->wfParams['DOCUMENT_TYPE'][1]; 
$arResult["VARIABLES"]["DOCUMENT_TYPE"] = $ob->wfParams['DOCUMENT_TYPE'][2];
$arResult["VARIABLES"]["BIZPROC"] = array(
	"MODULE_ID" => $ob->wfParams['DOCUMENT_TYPE'][0],
	"ENTITY" => $ob->wfParams['DOCUMENT_TYPE'][1],
	"DOCUMENT_TYPE" => $ob->wfParams['DOCUMENT_TYPE'][2]);
$arResult["VARIABLES"]["NOTE"] = str_replace(
		"#HREF#",
		($object == "user" ? $arResult["~PATH_TO_USER_FILES_HELP"] : $arResult["~PATH_TO_GROUP_FILES_HELP"]),
		GetMessage("WD_HOW_TO_INCREASE_QUOTA"));

//$arResult["~PATH_TO_USER_FILES_BIZPROC_WORKFLOW_ADMIN"] = "";
//$arResult["~PATH_TO_USER_FILES_BIZPROC_WORKFLOW_EDIT"] = "";
/********************************************************************
				/Path
********************************************************************/
/********************************************************************
				Activity before
********************************************************************/
if (($componentPage == "group_photo_element_upload" || $componentPage == "group_files_element_upload" || 
	$componentPage == "user_photo_element_upload" || $componentPage == "user_files_element_upload") &&
	$_REQUEST["save_upload"] == "Y" && !isset($_REQUEST['AJAX_CALL']))
{
	$_REQUEST["FORMAT_ANSWER"] = "return";
	$arParams["ANSWER_UPLOAD_PAGE"] = array();
}
/********************************************************************
				/Activity before
********************************************************************/

return 1;
?>
