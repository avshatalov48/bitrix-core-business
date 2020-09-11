<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams, $arResult
 * @var CBitrixComponent $this
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
$arParams["ELEMENT_ID"] = $arParams["ELEMENT_ID"] <= 0 ? $GLOBALS["ID"] : $arParams["ELEMENT_ID"];
$arParams["ELEMENT_ID"] = $arParams["ELEMENT_ID"] <= 0 ? $_REQUEST["ELEMENT_ID"] : $arParams["ELEMENT_ID"];
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams["FORUM_ID"] = intval($arParams["FORUM_ID"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("F_NO_MODULE_IBLOCK"));
	return 0;
elseif ($arParams["FORUM_ID"] <= 0):
	ShowError(GetMessage("F_ERR_FID_EMPTY"));
	return 0;
elseif ($arParams["ELEMENT_ID"] <= 0):
	ShowError(GetMessage("F_ERR_EID_EMPTY"));
	return 0;
endif;
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = ($_REQUEST["result"] == "reply" ?
	GetMessage("COMM_COMMENT_OK") : ($_REQUEST["result"] == "not_approved" ?
		GetMessage("COMM_COMMENT_OK_AND_NOT_APPROVED") : ""));
$arResult["~OK_MESSAGE"] = $arResult["~ERROR_MESSAGE"] = array();

unset($_GET["result"]); unset($GLOBALS["HTTP_GET_VARS"]["result"]);
DeleteParam(array("result"));
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
//	$arParams["FORUM_ID"] = intval($arParams["FORUM_ID"]);
//	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
//	$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (empty($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]))
			continue;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : (empty($arParams["USER_FIELDS"]) ? array() : array($arParams["USER_FIELDS"])));
if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
	$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";
$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
$arParams["PATH_TO_SMILE"] = "";
$arParams["POST_FIRST_MESSAGE"] = "Y";
$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = trim($arParams["POST_FIRST_MESSAGE_TEMPLATE"]);
if (empty($arParams["POST_FIRST_MESSAGE_TEMPLATE"]))
	$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = "#IMAGE# \n [url=#LINK#]#TITLE#[/url]\n\n#BODY#";
$arParams["ENABLE_HIDDEN"] = ($arParams["ENABLE_HIDDEN"] == "Y" ? "Y" : "N");
$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");

$arParams["SUBSCRIBE_AUTHOR_ELEMENT"] = ($arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" ? "Y" : "N");

$arParams["MESSAGES_PER_PAGE"] = intval($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = (!empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? $arParams["PAGE_NAVIGATION_TEMPLATE"] : "modern");

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")):$arParams["DATE_TIME_FORMAT"]);
$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
$arParams["SHOW_AVATAR"] = ($arParams["SHOW_AVATAR"] == "N" || $arParams["SHOW_AVATAR"] == "PHOTO" ? $arParams["SHOW_AVATAR"] : "Y");

$arParams["USE_CAPTCHA"] = ($arParams["USE_CAPTCHA"] == "Y" ? "Y" : "N");

$arParams["PREORDER"] = ($arParams["PREORDER"] == "N" ? "N" : "Y");

$arParams["AUTOSAVE"] = (!isset($arParams["AUTOSAVE"]) ? CForumAutosave::GetInstance() : $arParams["AUTOSAVE"]);

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] == "N" ? "N" : "Y");

if ($arParams['AJAX_POST'] == 'Y' &&
	isset($this->__parent) &&
	isset($this->__parent->arParams) &&
	isset($this->__parent->arParams['AJAX_MODE']) &&
	$this->__parent->arParams['AJAX_MODE'] == 'Y')
		$arParams['AJAX_POST'] = 'N';

$arParams['AJAX_TYPE'] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
if ($arParams['AJAX_POST'] == 'Y') $arParams['NO_REDIRECT_AFTER_SUBMIT'] = 'Y';

$arParams["SHOW_SUBSCRIBE"] = ($arParams["SHOW_SUBSCRIBE"] == "N" ? "N" : "Y");
$arParams["SHOW_MINIMIZED"] = ($arParams["SHOW_MINIMIZED"] == "Y" ? "Y" : "N");

$arMessages = array(
	"MINIMIZED_EXPAND_TEXT" => GetMessage('F_EXPAND_TEXT'),
	"MINIMIZED_MINIMIZE_TEXT" => GetMessage('F_MINIMIZE_TEXT'),
	"MESSAGE_TITLE" => GetMessage('F_MESSAGE_TEXT')
);
foreach($arMessages as $paramName => $paramValue)
	$arParams[$paramName] = (($arParams[$paramName]) ? $arParams[$paramName] : $paramValue);
/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName);
$cache_path = $cache_path_main;
/********************************************************************
				Main Params
********************************************************************/
// ELEMENT
$arIblock = array();
$cache_id = "forum_iblock_".$arParams["ELEMENT_ID"].(CTimeZone::GetOffset() <> 0 ? "_".$tzOffset : "");
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path_main))
{
	$res = $cache->GetVars();
	if (is_array($res["arIblock"]) && $res["arIblock"]["ID"] == $arParams["ELEMENT_ID"])
		$arIblock = $res["arIblock"];
}
if (!is_array($arIblock) || ($arIblock["ID"] != $arParams["ELEMENT_ID"]))
{
	$arFilter = array("ID" => $arParams["ELEMENT_ID"]);
	if ($arParams["ENABLE_HIDDEN"] == "Y")
		$arFilter["SHOW_HISTORY"] = "Y";
	if ($arParams["IBLOCK_ID"] > 0)
		$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	$db_res = CIBlockElement::GetList(
		array(),
		$arFilter,
		false,
		false,
		array(
			"IBLOCK_ID",
			"ID",
			"NAME",
			"TAGS",
			"CODE",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"CREATED_BY",
			"PREVIEW_PICTURE",
			"PREVIEW_TEXT",
			"PROPERTY_FORUM_TOPIC_ID",
			"PROPERTY_FORUM_MESSAGE_CNT"
		)
	);
	if ($db_res && $res = $db_res->GetNext())
	{
		$arIblock = $res;
	}
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache();
		CForumCacheManager::SetTag($cache_path_main, "forum_".$arParams["FORUM_ID"]);
		$cache->EndDataCache(array("arIblock" => $arIblock));
	}
}
$arResult["ELEMENT"] = $arIblock;
// FORUM
$arResult["FORUM"] = CForumNew::GetByIDEx($arParams["FORUM_ID"], SITE_ID);
// USER PERMISSION
$arResult["USER"] = array(
	"ID" => $USER->GetID(),
	"GROUPS" => $USER->GetUserGroupArray(),
	"PERMISSION" => ForumCurrUserPermissions($arParams["FORUM_ID"]),
	"SHOWED_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
	"SUBSCRIBE" => array(),
	"FORUM_SUBSCRIBE" => "N",
	"TOPIC_SUBSCRIBE" => "N");
/********************************************************************
				/Main Params
 ********************************************************************/

if (empty($arResult["FORUM"])):
	ShowError(str_replace("#FORUM_ID#", $arParams["FORUM_ID"], GetMessage("F_ERR_FID_IS_NOT_EXIST")));
	return false;
elseif (empty($arResult["ELEMENT"])):
	ShowError(str_replace("#ELEMENT_ID#", $arParams["ELEMENT_ID"], GetMessage("F_ERR_EID_IS_NOT_EXIST")));
	return false;
elseif ($arResult["USER"]["PERMISSION"] <= "A"):
	return false;
endif;

/********************************************************************
				Default values
 ********************************************************************/
$arResult["FORUM_TOPIC_ID"] = intval($arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);

$arResult["USER"]["RIGHTS"] = array(
	"ADD_TOPIC" => CForumTopic::CanUserAddTopic($arParams["FORUM_ID"], $arResult["USER"]["GROUPS"], $arResult["USER"]["ID"], $arResult["FORUM"] ) ? "Y" : "N",
	"MODERATE" => CForumNew::CanUserModerateForum($arParams["FORUM_ID"], $arResult["USER"]["GROUPS"], $arResult["USER"]["ID"]) == true ? "Y" : "N",
	"EDIT" => CForumNew::CanUserEditForum($arParams["FORUM_ID"], $arResult["USER"]["GROUPS"], $arResult["USER"]["ID"]) ? "Y" : "N",
	"ADD_MESSAGE" => CForumMessage::CanUserAddMessage($arResult["FORUM_TOPIC_ID"], $arResult["USER"]["GROUPS"], $arResult["USER"]["ID"]) ? "Y" : "N");

if ($USER->IsAuthorized())
{
	$arResult["USER"]["SHOWED_NAME"] = $USER->GetLogin();
	if ($_SESSION["FORUM"]["SHOW_NAME"] == "Y")
	{
		$arResult["USER"]["SHOWED_NAME"] = CUser::FormatName(
			$arParams["NAME_TEMPLATE"],
			array(
				"NAME" => $USER->GetFirstName(),
				"LAST_NAME"	=> $USER->GetLastName(),
				"SECOND_NAME"	=> $USER->GetSecondName(),
				"LOGIN" 		=> $USER->GetLogin()),
			true,
			false
		);
	}
}

$arResult["PANELS"] = array(
	"MODERATE" => $arResult["USER"]["RIGHTS"]["MODERATE"],
	"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
);
$arResult["SHOW_PANEL"] = in_array("Y", $arResult["PANELS"]) ? "Y" : "N";

// PARSER
$parser = new forumTextParser(LANGUAGE_ID);
$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
$arResult["PARSER"] = $parser;

// FORUM
$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);

$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$arResult["MESSAGES"] = array();
$arResult["MESSAGE_VIEW"] = array();
$arResult["MESSAGE"] = array();
$arResult["FILES"] = array();
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Actions
********************************************************************/
ForumSetLastVisit($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"]);
$arError = array(); $arNote = array();
include(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/action.php"));
foreach ($arError as $res):
	$arResult["ERROR_MESSAGE"] .= (empty($res["title"]) ? $res["code"] : $res["title"]);
	$arResult["~ERROR_MESSAGE"][] = array("id" => $res["code"], "text" => $res["title"]);
endforeach;
foreach ($arNote as $res):
	$arResult["OK_MESSAGE"] .= (empty($res["text"]) ? $res["id"] : $res["text"]);
	$arResult["~OK_MESSAGE"][] = $res;
endforeach;
if (!!$strOKMessage)
{
	$arResult["OK_MESSAGE"] .= $strOKMessage;
	$arResult["~OK_MESSAGE"][] = array("id" => "good", "text" => $strOKMessage);
}

if (!empty($arResult["ERROR_MESSAGE"]))
	$arParams["SHOW_MINIMIZED"] = "N";
/********************************************************************
				/Actions
********************************************************************/
/********************************************************************
				Input params II
********************************************************************/
/************** URL ************************************************/
if (empty($arParams["~URL_TEMPLATES_READ"]) && !empty($arResult["FORUM"]["PATH2FORUM_MESSAGE"]))
	$arParams["~URL_TEMPLATES_READ"] = $arResult["FORUM"]["PATH2FORUM_MESSAGE"];
elseif (empty($arParams["~URL_TEMPLATES_READ"]))
	$arParams["~URL_TEMPLATES_READ"] = $APPLICATION->GetCurPage()."?PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#";
$arParams["~URL_TEMPLATES_READ"] = str_replace(array("#FORUM_ID#", "#TOPIC_ID#", "#MESSAGE_ID#"),
		array("#FID#", "#TID#", "#MID#"), $arParams["~URL_TEMPLATES_READ"]);
$arParams["URL_TEMPLATES_READ"] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_READ"]);
/************** ADDITIONAL *****************************************/
$arParams["USE_CAPTCHA"] = $arResult["FORUM"]["USE_CAPTCHA"] == "Y" ? "Y" : $arParams["USE_CAPTCHA"];
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** 3. Get inormation about USER ***********************/
if ($arParams["SHOW_SUBSCRIBE"] == "Y" && $USER->IsAuthorized() && $arResult["USER"]["PERMISSION"] > "E")
{
	// USER subscribes
	$db_res = CForumSubscribe::GetList(
		array(),
		array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $arParams["FORUM_ID"]
		)
	);
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arResult["USER"]["SUBSCRIBE"][] = $res;
			if (intval($res["TOPIC_ID"]) <= 0)
				$arResult["USER"]["FORUM_SUBSCRIBE"] = "Y";
			elseif(intval($res["TOPIC_ID"]) == intval($arResult["FORUM_TOPIC_ID"]))
				$arResult["USER"]["TOPIC_SUBSCRIBE"] = "Y";
		} while ($res = $db_res->Fetch());
	}
}
/************** 4. Get message list ********************************/
if ($arResult["FORUM_TOPIC_ID"] > 0)
{
	ForumSetReadTopic($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"]);

	$page_number = $GLOBALS["NavNum"] + 1;
	$arMessages = array();
	$pageNo = $_GET["PAGEN_".$page_number];
	if ($pageNo > 200) $pageNo = 0;
	if (isset($arResult['RESULT']) && intval($arResult['RESULT']) > 0) $pageNo = $arResult['RESULT'];
	$ar_cache_id = array(
		$arParams["FORUM_ID"],
		$arParams["ELEMENT_ID"],
		$arResult["FORUM_TOPIC_ID"],
		$arResult["USER"]["GROUPS"],
		$arResult["PANELS"],
		$arParams['SHOW_AVATAR'],
		$arParams['SHOW_RATING'],
		$arParams["MESSAGES_PER_PAGE"],
		$arParams["DATE_TIME_FORMAT"],
		$arParams["PREORDER"],
		$pageNo);
	$cache_id = "forum_message_".serialize($ar_cache_id);
	//$cache_path = $cache_path_main."forum".$arParams["FORUM_ID"]."/topic".$arResult["FORUM_TOPIC_ID"];
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arMessages"]))
		{
			$arMessages = $res["arMessages"];
			$arResult["NAV_RESULT"] = $db_res;
			if (is_array($res["Nav"]))
			{
				$arResult["NAV_RESULT"] = $res["Nav"]["NAV_RESULT"];
				$arResult["NAV_STRING"] = $res["Nav"]["NAV_STRING"];
				$APPLICATION->SetAdditionalCSS($res["Nav"]["NAV_STYLE"]);
			}
		}
	}

	if (empty($arMessages))
	{
		$arOrder = array("ID" => ($arParams["PREORDER"] == "N" ? "DESC" : "ASC"));
		$arFields = array(
			"bDescPageNumbering" => false,
			"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
			"bShowAll" => false,
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]);
		$MID = intval($_REQUEST["MID"]);
		unset($_GET["MID"]); unset($GLOBALS["MID"]);
		if (isset($arResult['RESULT']) && intval($arResult['RESULT']) > 0)
			$MID = $arResult['RESULT'];
		if (intval($MID) > 0)
		{
			$page_number = CForumMessage::GetMessagePage(
				$MID,
				$arParams["MESSAGES_PER_PAGE"],
				$USER->GetUserGroupArray(),
				$arResult["FORUM_TOPIC_ID"],
				array(
					"ORDER_DIRECTION" => ($arParams["PREORDER"] == "N" ? "DESC" : "ASC"),
					"PERMISSION_EXTERNAL" => $arResult["USER"]["PERMISSION"],
					"FILTER" => array("!PARAM1" => "IB")
				)
			);
			if ($page_number > 0)
				$arFields["iNumPage"] = intval($page_number);
		}

		$arFilter = array("FORUM_ID"=>$arParams["FORUM_ID"], "TOPIC_ID"=>$arResult["FORUM_TOPIC_ID"], "!PARAM1" => "IB");
		if ($arResult["USER"]["RIGHTS"]["MODERATE"] != "Y")
			$arFilter["APPROVED"] = "Y";
		$db_res = CForumMessage::GetListEx($arOrder, $arFilter, false, 0, $arFields);
		$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, ($arFields["iNumPage"] > 0 ? $arFields["iNumPage"] : false));
		$arResult["NAV_RESULT"] = $db_res;
		if ($db_res)
		{
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("NAV_OPINIONS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			$arResult["NAV_STYLE"] = $APPLICATION->GetAdditionalCSS();
			$arResult["PAGE_COUNT"] = $db_res->NavPageCount;
			$arResult['PAGE_NUMBER'] = $db_res->NavPageNomer;
			$number = intval($db_res->NavPageNomer-1)*$arParams["MESSAGES_PER_PAGE"] + 1;
			while ($res = $db_res->GetNext())
			{
/************** Message info ***************************************/
	// number in topic
	$res["NUMBER"] = $number++;
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N")));
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
	// links
	if ($arResult["SHOW_PANEL"] == "Y")
	{
		$res["URL"]["REVIEWS"] = $APPLICATION->GetCurPageParam();
		$res["URL"]["~MODERATE"] = ForumAddPageParams($res["URL"]["REVIEWS"],
				array("MID" => $res["ID"], "REVIEW_ACTION" => $res["APPROVED"]=="Y" ? "HIDE" : "SHOW"), true, false);
		$res["URL"]["MODERATE"] = htmlspecialcharsbx($res["URL"]["~MODERATE"])."&amp;".bitrix_sessid_get();
		$res["URL"]["~DELETE"] = ForumAddPageParams($res["URL"]["REVIEWS"],
			array("MID" => $res["ID"], "REVIEW_ACTION" => "DEL"), true, false);
		$res["URL"]["DELETE"] = htmlspecialcharsbx($res["URL"]["~DELETE"])."&amp;".bitrix_sessid_get();
	}
/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
	$res["AUTHOR_URL"] = "";
	if ($res["AUTHOR_ID"] > 0)
	{
		if (!empty($arParams["URL_TEMPLATES_PROFILE_VIEW"]))
		{
			$res["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array(
				"UID" => $res["AUTHOR_ID"],
				"USER_ID" => $res["AUTHOR_ID"],
				"ID" => $res["AUTHOR_ID"]
			));
		}
		// avatar
		$id = ($arParams['SHOW_AVATAR'] == 'Y' ?
			($res["AVATAR"] > 0 ? $res["AVATAR"] : $res["PERSONAL_PHOTO"]) :
			($arParams['SHOW_AVATAR'] == 'PHOTO' ? $res["PERSONAL_PHOTO"] : 0)
		);
		if ($id > 0)
		{
			$res["AVATAR"] = array(
				"ID" => $id,
				"FILE" => CFile::ResizeImageGet(
						$id,
						array("width" => 30, "height" => 30),
						BX_RESIZE_IMAGE_EXACT,
						false
					)
			);
			if (!empty($res["AVATAR"]["FILE"]))
				$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"]['src'], 30, 30, "border=0 align='right'");
		}
	}

/************** Author info/****************************************/
	// For quote JS
	$res["FOR_JS"] = array(
		"AUTHOR_NAME" => Cutil::JSEscape($res["AUTHOR_NAME"]),
		"POST_MESSAGE_TEXT" => Cutil::JSEscape(htmlspecialcharsbx($res["POST_MESSAGE_TEXT"]))
	);
	$arMessages[$res["ID"]] = $res;
			}
		}
/************** Attach files ***************************************/
		if (!empty($arMessages))
		{
			$res = array_keys($arMessages);

			$arFilter = array("FORUM_ID" => $arParams["FORUM_ID"], "TOPIC_ID" => $arResult["FORUM_TOPIC_ID"]);
			if ($arResult["USER"]["RIGHTS"]["MODERATE"] != "Y")
				$arFilter["APPROVED"] = "Y";
			$arFilterProps = $arFilter;
			if (min($res) > 1)
				$arFilterProps[">ID"] = $arFilter[">MESSAGE_ID"] = intval(min($res) - 1);
			$arFilterProps["<ID"] = $arFilter["<MESSAGE_ID"] = intval(max($res) + 1);
			$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
			if ($db_files && $res = $db_files->Fetch())
			{
				do
				{
					$res["SRC"] = CFile::GetFileSRC($res);
					if ($arMessages[$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
					{
					// attach for custom
						$arMessages[$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
							$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"];
					}
					$arMessages[$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
					$arResult["FILES"][$res["FILE_ID"]] = $res;
				}while ($res = $db_files->Fetch());
			}
			if (!empty($arParams["USER_FIELDS"]))
			{
				$db_props = CForumMessage::GetList(array("ID" => "ASC"), $arFilterProps, false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
				while ($db_props && ($res = $db_props->Fetch()))
				{
					$arMessages[$res["ID"]]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
				}
			}
			/************** Message info ***************************************/
			$parser->arFiles = $arResult["FILES"];
			foreach ($arMessages as $iID => $res)
				$arMessages[$iID]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], array_merge($res["ALLOW"], array("USERFIELDS" => $res["PROPS"])));
		}

/************** Message List/***************************************/
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache();
			CForumCacheManager::SetTag($cache_path, "forum_topic_".$arResult["FORUM_TOPIC_ID"]);
			$cache->EndDataCache(array(
				"arMessages" => $arMessages,
				"Nav" => array(
					"NAV_RESULT" => $arResult["NAV_RESULT"],
					"NAV_STYLE"  => $arResult["NAV_STYLE"],
					"NAV_STRING" => $arResult["NAV_STRING"])));
		}
	}
	else
	{
		$GLOBALS["NavNum"]++;
		if ($arAllow["VIDEO"] == "Y")
		{
			foreach ($arMessages as $key => $res):
				$arAllow["SMILES"] = ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N");
				$arMessages[$key]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $arAllow);
			endforeach;
		}
	}
	/************** Rating ****************************************/
	if ($arParams["SHOW_RATING"] == "Y")
	{
		$arMessageIDs = array_keys($arMessages);
		$arRatings = CRatings::GetRatingVoteResult('FORUM_POST', $arMessageIDs);
		if ($arRatings)
		foreach($arRatings as $messageID => $arRating)
			$arMessages[$messageID]['RATING'] = $arRating;
	}
	$arResult["MESSAGES"] = $arMessages;
	// Link to forum
	$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
		array("FID" => $arParams["FORUM_ID"], "TID" => $arResult["FORUM_TOPIC_ID"], "TITLE_SEO" => $arResult["FORUM_TOPIC_ID"], "MID" => "s",
			"PARAM1" => "IB", "PARAM2" => $arParams["ELEMENT_ID"]));
}
/************** 5. Show post form **********************************/
$arResult["SHOW_POST_FORM"] = (($arResult["USER"]["PERMISSION"] >= "M" || ($arResult["USER"]["PERMISSION"] >= "I" && !empty($arResult["MESSAGES"]))) ? "Y" : "N");

if ($arResult["SHOW_POST_FORM"] == "Y")
{
	// Author name
	$arResult["~REVIEW_AUTHOR"] = $arResult["USER"]["SHOWED_NAME"];
	$arResult["~REVIEW_USE_SMILES"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? "Y" : "N");

	if (!empty($arError) || !empty($arResult["MESSAGE_VIEW"]))
	{
		if (!empty($_POST["REVIEW_AUTHOR"]))
			$arResult["~REVIEW_AUTHOR"] = $_POST["REVIEW_AUTHOR"];
		$arResult["~REVIEW_EMAIL"] = $_POST["REVIEW_EMAIL"];
		$arResult["~REVIEW_TEXT"] = $_POST["REVIEW_TEXT"];
		$arResult["~REVIEW_USE_SMILES"] = ($_POST["REVIEW_USE_SMILES"] == "Y" ? "Y" : "N");
	}
	$arResult["REVIEW_AUTHOR"] = htmlspecialcharsbx($arResult["~REVIEW_AUTHOR"]);
	$arResult["REVIEW_EMAIL"] = htmlspecialcharsbx($arResult["~REVIEW_EMAIL"]);
	$arResult["REVIEW_TEXT"] = htmlspecialcharsbx($arResult["~REVIEW_TEXT"]);
	$arResult["REVIEW_USE_SMILES"] = $arResult["~REVIEW_USE_SMILES"];
	$arResult["REVIEW_FILES"] = array();
	foreach ($_REQUEST["FILES"] as $key => $val):
		$val = intval($val);
		if ($val <= 0)
			continue;
		if (($file = CFile::GetFileArray($val)) && is_array($file))
			$arResult["REVIEW_FILES"][$val] = htmlspecialcharsEx($file);
	endforeach;

	// Form Info
	$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("A", "F", "Y")) ? "Y" : "N");
	$arResult["TRANSLIT"] = (LANGUAGE_ID=="ru" ? "Y" : " N");
	if ($arResult["FORUM"]["ALLOW_SMILES"] == "Y"):
		$arResult["ForumPrintSmilesList"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? ForumPrintSmilesList(3, LANGUAGE_ID) : "");
		$arResult["SMILES"] = CForumSmile::getSmiles("S", LANGUAGE_ID);
	endif;

	$arResult["CAPTCHA_CODE"] = "";
	if ($arParams["USE_CAPTCHA"] == "Y" && !$USER->IsAuthorized())
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
		$cpt = new CCaptcha();
		$captchaPass = COption::GetOptionString("main", "captcha_password", "");
		if ($captchaPass == '')
		{
			$captchaPass = randString(10);
			COption::SetOptionString("main", "captcha_password", $captchaPass);
		}
		$cpt->SetCodeCrypt($captchaPass);
		$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
	}
}

/* For custom template */
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["IS_AUTHORIZED"] = $USER->IsAuthorized();
$arResult["PERMISSION"] = $arResult["USER"]["PERMISSION"];
$arResult["SHOW_NAME"] = $arResult["USER"]["SHOWED_NAME"];
$arResult["sessid"] = bitrix_sessid_post();
$arResult["SHOW_SUBSCRIBE"] = ((($arParams["SHOW_SUBSCRIBE"] == "Y") && ($arResult["USER"]["ID"] > 0 && $arResult["USER"]["PERMISSION"] > "E")) ? "Y" : "N");
$arResult["TOPIC_SUBSCRIBE"] = $arResult["USER"]["TOPIC_SUBSCRIBE"];
$arResult["FORUM_SUBSCRIBE"] = $arResult["USER"]["FORUM_SUBSCRIBE"];
$arResult["SHOW_LINK"] = (empty($arResult["read"]) ? "N" : "Y");
$arResult["SHOW_POSTS"]	= (empty($arResult["MESSAGES"]) ? "N" : "Y");
$arResult["PARSER"] = $parser;
$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPageParam();

$arResult["ELEMENT_REAL"] = $arResult["ELEMENT"];
$arResult["ELEMENT"] = array(
	"PRODUCT" => $arResult["ELEMENT"],
	"PRODUCT_PROPS" => array());
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_TOPIC_ID_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_TOPIC_ID_VALUE"]);
}
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_MESSAGE_CNT_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
}
/* For custom template */
// *****************************************************************************************
$this->IncludeComponentTemplate();
// *****************************************************************************************
if ($arResult["FORUM_TOPIC_ID"] > 0)
	return CForumTopic::GetMessageCount($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"], (($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y")?null:true));
else
	return 0;
?>