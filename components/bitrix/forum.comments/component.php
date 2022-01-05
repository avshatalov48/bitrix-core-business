<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Forum;
use Bitrix\Main;

global $USER;
global $APPLICATION;

/**
 * @var ForumCommentsComponent $this
 * @var $USER CUser
 * @var $DB CDataBase
 * @var $arParams array
 * @var $arResult array
 * @var $this->capcha CCaptcha
 * @var $this->feed \Bitrix\Forum\Comments\Feed
 */
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/

$arParams["FORUM_ID"] = intval($arParams["FORUM_ID"]);
$arParams["~URL_TEMPLATES_PROFILE_VIEW"] = str_replace(
	["#USER_ID#", "#author_id#", "#AUTHOR_ID#", "#UID#", "#ID#"],
	"#user_id#",
	trim($arParams["URL_TEMPLATES_PROFILE_VIEW"] ?: "PAGE_NAME=profile_view&UID=#UID#"));
$arParams["URL_TEMPLATES_PROFILE_VIEW"] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_PROFILE_VIEW"]);
/***************** ADDITIONAL **************************************/
$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] === "Y" ? "Y" : "N");
$arParams["SHOW_MINIMIZED"] = ($arParams["SHOW_MINIMIZED"] === "Y" ? "Y" : "N");
$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 600);
$arParams["IMAGE_HTML_SIZE"] = intval($arParams["IMAGE_HTML_SIZE"]);
$arParams["IMAGE_HTML_SIZE"] = ($arParams["IMAGE_SIZE"] > $arParams["IMAGE_HTML_SIZE"] && $arParams["IMAGE_HTML_SIZE"] > 0 ? $arParams["IMAGE_HTML_SIZE"] : 0);
$arParams["MESSAGES_PER_PAGE"] = intval($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["NAME_TEMPLATE"] = empty($arParams["NAME_TEMPLATE"]) ? "" : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
$arParams["PREORDER"] = ($arParams["PREORDER"] == "Y" ? "Y" : "N");
$arParams["SET_LAST_VISIT"] = $arParams["SET_LAST_VISIT"] == "Y" ? "Y" : "N";
$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["PAGE_NAVIGATION_TEMPLATE"] = $arParams["PAGE_NAVIGATION_TEMPLATE"] <> "" ? $arParams["PAGE_NAVIGATION_TEMPLATE"] : "modern";
$arParams["ALLOW"] = array_flip(array(
	"ALLOW_HTML",
	"ALLOW_ANCHOR",
	"ALLOW_BIU",
	"ALLOW_IMG",
	"ALLOW_VIDEO",
	"ALLOW_LIST",
	"ALLOW_QUOTE",
	"ALLOW_CODE",
	"ALLOW_FONT",
	"ALLOW_SMILES",
	"ALLOW_NL2BR",
	"ALLOW_TABLE",
	"ALLOW_MENTION",
	"ALLOW_ALIGN",
	"ALLOW_MENTION"));
/***************** URL *********************************************/
foreach ($arParams["ALLOW"] as $sName => $default)
{
	$sVal = array_key_exists($sName, $arParams) ? $arParams[$sName] : $arResult["FORUM"][$sName];
	$arParams["ALLOW"][$sName] = ($sName == "ALLOW_HTML" ? ($sVal === "Y" ? "Y" : "N") : ($sVal === "N" ? "N" : "Y"));
}
$arParams["ALLOW"]["ALLOW_UPLOAD"] = $arResult["FORUM"]["ALLOW_UPLOAD"];
$arParams["ALLOW"]["ALLOW_UPLOAD_EXT"] = trim($arResult["FORUM"]["ALLOW_UPLOAD_EXT"]);
if (in_array($arParams["ALLOW_UPLOAD"], array("A", "Y", "F", "N", "I")))
{
	$arParams["ALLOW"]["ALLOW_UPLOAD"] = ($arParams["ALLOW_UPLOAD"] == "I" ? "Y" : $arParams["ALLOW_UPLOAD"]);
	$arParams["ALLOW"]["ALLOW_UPLOAD_EXT"] = trim($arParams["ALLOW_UPLOAD_EXT"]);
}
$arParams = array_merge($arParams, $arParams["ALLOW"]);

foreach(["MINIMIZED_EXPAND_TEXT" => GetMessage("F_EXPAND_TEXT"),
		"MINIMIZED_MINIMIZE_TEXT" => GetMessage("F_MINIMIZE_TEXT"),
		"MESSAGE_TITLE" => GetMessage("F_MESSAGE_TEXT")] as $paramName => $paramValue)
	$arParams[$paramName] = (($arParams[$paramName]) ? $arParams[$paramName] : $paramValue);
/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
$arResult["FORUM"] = $this->feed->getForum();
$arResult["TOPIC"] = $this->feed->getTopic();
$arResult["MESSAGES"] = array();
$arResult["FORUM_TOPIC_ID"] = $arResult["TOPIC"]["ID"];

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

$arResult["USER"] = array(
	"PERMISSION" => $this->feed->getPermission(),
	"SHOWED_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
	"SUBSCRIBE" => array(),
	"FORUM_SUBSCRIBE" => "N",
	"TOPIC_SUBSCRIBE" => "N",
	"RIGHTS" => array(
		"ADD_TOPIC" => $this->feed->canAdd() ? "Y" : "N",
		"MODERATE" => $this->feed->canModerate() ? "Y" : "N",
		"EDIT" => $this->feed->canEdit() ? "Y" : "N",
		"ADD_MESSAGE" => ($this->feed->canAdd() ? "Y" : "N")
));
if ($USER->IsAuthorized())
{
	$arResult["USER"]["ID"] = $USER->getID();
	$tmpName = empty($arParams["NAME_TEMPLATE"]) ? $USER->getFormattedName(false) : CUser::FormatName($arParams["NAME_TEMPLATE"], array(
		"NAME"			=>	$USER->GetFirstName(),
		"LAST_NAME"		=>	$USER->GetLastName(),
		"SECOND_NAME"	=>	$USER->GetSecondName(),
		"LOGIN"			=>	$USER->GetLogin()
	));

	$arResult["USER"]["SHOWED_NAME"] = trim($this->feed->getUser()->getParam("SHOW_NAME") == "Y" ? $tmpName : $USER->getLogin());
	$arResult["USER"]["SHOWED_NAME"] = trim(!empty($arResult["USER"]["SHOWED_NAME"]) ? $arResult["USER"]["SHOWED_NAME"] : $USER->getLogin());
}

$arResult["DO_NOT_CACHE"] = true;

// PARSER
$parser = new forumTextParser(LANGUAGE_ID);
$parser->imageWidth = $arParams["IMAGE_SIZE"];
$parser->imageHeight = $arParams["IMAGE_SIZE"];
$parser->imageHtmlWidth = $arParams["IMAGE_HTML_SIZE"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arResult["PARSER"] = $parser;
$arAllow = [
	"HTML" => $arParams["ALLOW_HTML"],
	"ANCHOR" => $arParams["ALLOW_ANCHOR"],
	"BIU" => $arParams["ALLOW_BIU"],
	"IMG" => $arParams["ALLOW_IMG"],
	"VIDEO" => $arParams["ALLOW_VIDEO"],
	"LIST" => $arParams["ALLOW_LIST"],
	"QUOTE" => $arParams["ALLOW_QUOTE"],
	"CODE" => $arParams["ALLOW_CODE"],
	"FONT" => $arParams["ALLOW_FONT"],
	"SMILES" => $arParams["ALLOW_SMILES"],
	"NL2BR" => $arParams["ALLOW_NL2BR"],
	"TABLE" => $arParams["ALLOW_TABLE"],
	"UPLOAD" => $arParams["ALLOW_UPLOAD"],
	"ALIGN" => $arParams["ALLOW_ALIGN"],
	"MENTION" => $arParams["ALLOW_MENTION"]];
/********************************************************************
				/Default values
********************************************************************/

$arResult["PANELS"] = array(
	"MODERATE" => $arResult["USER"]["RIGHTS"]["MODERATE"],
	"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"],
	"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"]
);

/************** Show post form **********************************/
$arResult["SHOW_POST_FORM"] = array_key_exists("SHOW_POST_FORM", $arParams) && $arParams["SHOW_POST_FORM"] === "N" ?
	"N" : $arResult["USER"]["RIGHTS"]["ADD_MESSAGE"];

if ($arResult["SHOW_POST_FORM"] == "Y")
{
	// Author name
	$arResult["~REVIEW_AUTHOR"] = $arResult["USER"]["SHOWED_NAME"];
	$arResult["~REVIEW_USE_SMILES"] = ($arParams["ALLOW_SMILES"] == "Y" ? "Y" : "N");

	if (array_key_exists("MESSAGE_VIEW", $arResult))
	{
		$arParams["SHOW_MINIMIZED"] = "N";
		$arResult["MESSAGE_VIEW"] = array(
			"POST_MESSAGE_TEXT" => $parser->convert($arResult["MESSAGE_VIEW"]["POST_MESSAGE"], array_merge(
					$arAllow, array("SMILES" => $arAllow["ALLOW_SMILES"] == "Y" && $arResult["MESSAGE_VIEW"]["USE_SMILES"] == "Y" ? "Y" : "N"))),
			"AUTHOR_NAME" => htmlspecialcharsbx($arResult["USER"]["SHOWED_NAME"]),
			"AUTHOR_ID" => intval($arResult["USER"]["ID"]),
			"AUTHOR_URL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arResult["MESSAGE_VIEW"]["AUTHOR_ID"])),
			"POST_DATE" => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], time()+CTimeZone::GetOffset()),
			"FILES" => array());
	}

	if (!empty($_POST["REVIEW_AUTHOR"]))
		$arResult["~REVIEW_AUTHOR"] = $_POST["REVIEW_AUTHOR"];
	$arResult["~REVIEW_EMAIL"] = $_POST["REVIEW_EMAIL"];
	$arResult["~REVIEW_TEXT"] = $_POST["REVIEW_TEXT"];
	$arResult["~REVIEW_USE_SMILES"] = ($_POST["REVIEW_USE_SMILES"] == "Y" ? "Y" : "N");

	$arResult["REVIEW_AUTHOR"] = htmlspecialcharsbx($arResult["~REVIEW_AUTHOR"]);
	$arResult["REVIEW_EMAIL"] = htmlspecialcharsbx($arResult["~REVIEW_EMAIL"]);
	$arResult["REVIEW_TEXT"] = htmlspecialcharsbx($arResult["~REVIEW_TEXT"]);
	$arResult["REVIEW_USE_SMILES"] = $arResult["~REVIEW_USE_SMILES"];

	// Form Info
	$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arParams["ALLOW_UPLOAD"], array("A", "F", "Y")) ? "Y" : "N");
	$arResult["TRANSLIT"] = (LANGUAGE_ID=="ru" ? "Y" : " N");
	if ($arParams["ALLOW_SMILES"] == "Y"):
		/* @deprecated */
		$arResult["ForumPrintSmilesList"] = ForumPrintSmilesList(3, LANGUAGE_ID);
		/* @deprecated */
		$arResult["SMILES"] = CForumSmile::getSmiles("S", LANGUAGE_ID);
	endif;
	$arResult["CAPTCHA_CODE"] = "";
	if (is_object($this->captcha))
	{
		$this->captcha->SetCodeCrypt(COption::GetOptionString("main", "captcha_password", ""));
		$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($this->captcha->getCodeCrypt());
	}
}
/********************************************************************
				Data
********************************************************************/
$request = $this->request;
$firstMID = 0;
$navParams = \CDBResult::GetNavParams($arParams["MESSAGES_PER_PAGE"]);
$hideServiceComments = !($arParams["COMPONENT_AJAX"] == "Y" && $arResult["RESULT"] > 0)
	&& Main\Config\Option::get("forum", "LivefeedConvertServiceMessageStepper") !== "inProgress";

if ($arResult["FORUM_TOPIC_ID"] > 0)
{
	$firstMID = intval($request->getQuery("MID"));
	// RESULT - new message ID was created on this hit
	if (array_key_exists("RESULT", $arResult) && $arResult["RESULT"] > 0)
	{
		$firstMID = intval($arResult["RESULT"]);
		if ($arResult["UNREAD_MID"] === $firstMID)
		{
			$arResult["UNREAD_MID"]++;
		}
	}
	elseif ($arResult["UNREAD_MID"] > 0 && ($firstMID <= 0 || $arResult["UNREAD_MID"] < $firstMID))
	{
		$firstMID = $arResult["UNREAD_MID"];
	}
	$arResult["MID"] = $firstMID;
}
$ar_cache_id = array(
	$arParams["FORUM_ID"],
	$arParams["ENTITY_XML_ID"],
	$arResult["FORUM_TOPIC_ID"],
	$arResult["USER"]["RIGHTS"],
	$arResult["USER"]["PERMISSION"],
	$arResult["PANELS"],
	$arParams["SHOW_RATING"],
	$arParams["MESSAGES_PER_PAGE"],
	$arParams["DATE_TIME_FORMAT"],
	$arParams["PREORDER"],
	$navParams["PAGEN"],
	$this->isWeb()
);
$cache_id = "forum_comment_".serialize($ar_cache_id);
if ($arResult["DO_NOT_CACHE"] || $this->StartResultCache($arParams["CACHE_TIME"], $cache_id))
{
	$auxSuffix = false;
	switch(mb_strtolower($arParams["ENTITY_TYPE"]))
	{
		case Forum\Comments\TaskEntity::ENTITY_TYPE:
			$auxSuffix = "TASK";
			break;
		case Forum\Comments\CalendarEntity::ENTITY_TYPE:
			$auxSuffix = "CALENDAR";
			break;
		case Forum\Comments\WorkflowEntity::ENTITY_TYPE:
			$auxSuffix = "WF";
			break;
		case \Bitrix\Forum\Comments\TimemanEntryEntity::ENTITY_TYPE:
			$auxSuffix = "TIMEMAN_ENTRY";
			break;
		default:
			$auxSuffix = false;
	}

	if ($arResult["FORUM_TOPIC_ID"] > 0)
	{
		//region Make a filter
		$filter = [
			"FORUM_ID" => $arParams["FORUM_ID"],
			"TOPIC_ID" => $arResult["FORUM_TOPIC_ID"],
			[
				"LOGIC" => "OR",
				"PARAM1" => null,
				"!=PARAM1" => $arParams["ENTITY_TYPE"]
			]
		];
		if ($hideServiceComments)
		{
			$filter["SERVICE_TYPE"] = 0;
		}
		if ($arResult["USER"]["RIGHTS"]["MODERATE"] !== "Y")
		{
			if ($USER->GetId() > 0)
			{
				$filter[] = [
					"LOGIC" => "OR",
					"=APPROVED" => "Y",
					"AUTHOR_ID" => $USER->GetId()
				];
			}
			else
			{
				$filter["=APPROVED"] = "Y";
			}
		}

		$initialOffset = 0;
		if (is_array($request->get("FILTER")))
		{
			$filter += $request->get("FILTER");
		}
		elseif ($arResult["MODE"] === "PULL_MESSAGE" && $arResult["RESULT"] === $firstMID)
		{
			$filter["ID"] = $firstMID;
			$navParams["SHOW_ALL"] = true;
		}
		elseif ($navParams["PAGEN"] <= 1 && $firstMID > 0)
		{
			$res = Forum\MessageTable::getList([
				"select" => ["CNT"],
				"filter" =>
					$filter + ($arParams["PREORDER"] === "N" ?
						[">=ID" => $firstMID] :
						["<=ID" => $firstMID]),
				"runtime" => [
					new Main\Entity\ExpressionField("CNT", "COUNT(*)")
				]
			])->fetch();
			if ($res["CNT"] > $navParams["SIZEN"])
			{
				$navParams["SHOW_ALL"] = true;
				$navParams["PAGEN"] = ceil($res["CNT"] / $navParams["SIZEN"]);
				$initialOffset = (int) $res["CNT"]; //TODO Use this param to filter instead of SHOW_ALL but remember about custom templates
			}
			unset($res);
		}
		//endregion

		//region Get total count
		$res = Forum\MessageTable::getList([
			"select" => ["CNT"],
			"filter" => $filter,
			"runtime" => [
				new Main\Entity\ExpressionField("CNT", "COUNT(*)")
			]
		])->fetch();
		$totalCount = (int) $res["CNT"];
		$totalPages = ceil($totalCount / $navParams["SIZEN"]);
		unset($res);
		//endregion

		//region Make an iterator
		if ($hideServiceComments && $navParams["SHOW_ALL"] !== true)
		{
			$getListParams = [
					"select" => ["ID"],
					"filter" => $filter,
					"order" => ["ID" => ($arParams["PREORDER"] === "N" ? "DESC" : "ASC")],
				];
			$finalFilter = array_diff_key($filter, ["SERVICE_TYPE" => "Does not matter"]);

			if ($navParams["PAGEN"] > 1)
			{
				$getListParams["limit"] = $navParams["SIZEN"] + 2;
				$getListParams["offset"] = $navParams["SIZEN"] * ($navParams["PAGEN"] - 1) - 1;
				$rawMessages = Forum\MessageTable::getList($getListParams)->fetchAll();
				$first = reset($rawMessages);
				$last = false;
				if (count($rawMessages) > ($navParams["SIZEN"] + 1))
				{
					end($rawMessages);
					$last = prev($rawMessages);
				}
			}
			else
			{
				$getListParams["limit"] = $navParams["SIZEN"] + 1;
				$getListParams["offset"] = 0;
				$rawMessages = Forum\MessageTable::getList($getListParams)->fetchAll();
				$first = false;
				$last = false;
				if (count($rawMessages) > $navParams["SIZEN"])
				{
					end($rawMessages);
					$last = prev($rawMessages);
				}
			}
			unset($rawMessages);

			if ($arParams["PREORDER"] === "N")
			{
				if ($first)
				{
					$finalFilter["<ID"] = $first["ID"];
				}
				if ($last && ($first != $last))
				{
					$finalFilter[">=ID"] = $last["ID"];
				}
			}
			else
			{
				if ($first)
				{
					$finalFilter[">ID"] = $first["ID"];
				}
				if ($last && ($first != $last))
				{
					$finalFilter["<=ID"] = $last["ID"];
				}
			}
			$getListParams = [
				"select" => ["*"],
				"filter" => $finalFilter,
				"order" => ["ID" => ($arParams["PREORDER"] === "N" ? "DESC" : "ASC")],
			];
		}
		else
		{
			$getListParams = [
					"select" => ["*"],
					"filter" => array_diff_key($filter, ["SERVICE_TYPE" => "Does not matter"]),
					"order" => ["ID" => ($arParams["PREORDER"] === "N" ? "DESC" : "ASC")],
				] + ($navParams["SHOW_ALL"] !== true ? [
					"limit" => $navParams["SIZEN"],
					"offset" => $navParams["SIZEN"] * ($navParams["PAGEN"] - 1)
				] : []);
		}

		$getListParams["select"] = [
			"*",
			"SHOW_NAME" => "FORUM_USER.SHOW_NAME",
			"AVATAR" => "FORUM_USER.AVATAR",
			"LOGIN" => "USER.LOGIN",
			"NAME" => "USER.NAME",
			"SECOND_NAME" => "USER.SECOND_NAME",
			"LAST_NAME" => "USER.LAST_NAME",
			"PERSONAL_PHOTO" => "USER.PERSONAL_PHOTO",
			"PERSONAL_GENDER" => "USER.PERSONAL_GENDER",
		];
		//endregion

		$dbMessageIterator = new CDBResult(Forum\MessageTable::getList($getListParams)->fetchAll());

		$dbMessageIterator->NavRecordCount = $totalCount;
		$dbMessageIterator->NavStart(
			$navParams["SIZEN"],
			$navParams["SHOW_ALL"],
			($hideServiceComments && $navParams["SHOW_ALL"] !== true ? 1 : $navParams["PAGEN"])
		);
		if (!$navParams["SHOW_ALL"])
		{
			$dbMessageIterator->NavPageCount = $totalPages;
			$dbMessageIterator->NavPageNomer = $navParams["PAGEN"];
		}
		$arResult["NAV_RESULT"] = $dbMessageIterator;
		$arResult["NAV_STRING"] = $dbMessageIterator->GetPageNavStringEx($navComponentObject, GetMessage("NAV_OPINIONS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$arResult["NAV_STYLE"] = $APPLICATION->GetAdditionalCSS();
		$arResult["PAGE_NUMBER"] = $dbMessageIterator->NavPageNomer;
		$number = intval($dbMessageIterator->NavPageNomer-1)*$arParams["MESSAGES_PER_PAGE"] + 1;
		$GLOBALS["forumComponent"] = &$this;
		$FormatDate = (mb_strpos($arParams["DATE_TIME_FORMAT"], "a") !== false ? "g:i a" :
			(mb_strpos($arParams["DATE_TIME_FORMAT"], "A") !== false ? "g:i A" : "G:i"));
		$url = (new Main\Web\Uri($arParams["URL"]))
			->deleteParams(["MID", "ID", "sessid", "AJAX_POST", "ENTITY_XML_ID", "ENTITY_TYPE", "ENTITY_ID", "REVIEW_ACTION", "MODE", "FILTER", "result", "ACTION"]);
		$messages = [];
		while ($res = $dbMessageIterator->GetNext())
		{
			/************** Message info ***************************************/
			$url->addParams(array("MID" => $res["ID"]));
			$postDate = new Main\Type\DateTime($res["POST_DATE"]);
			$editDate = new Main\Type\DateTime($res["EDIT_DATE"]);
			$message = [
				"ID" => $res["ID"],
				"NUMBER" => $number++, // number in topic
				// data
				"POST_TIMESTAMP" => $postDate->getTimestamp(),
				"POST_TIME" => $postDate->format($FormatDate),
				"POST_DATE" => $postDate->format($arParams["DATE_TIME_FORMAT"]),
				"EDIT_DATE" => $editDate->format($arParams["DATE_TIME_FORMAT"]),
				//
				"AUTHOR_ID" => intval($res["AUTHOR_ID"]),
				"AUTHOR_NAME" => $res["AUTHOR_NAME"],
				"AUTHOR_EMAIL" => $res["AUTHOR_EMAIL"],
				"LOGIN" => $res["LOGIN"],
				"~LOGIN" => $res["~LOGIN"],
				"NAME" => $res["NAME"],
				"~NAME" => $res["~NAME"],
				"SECOND_NAME" => $res["SECOND_NAME"],
				"~SECOND_NAME" => $res["~SECOND_NAME"],
				"LAST_NAME" => $res["LAST_NAME"],
				"~LAST_NAME" => $res["~LAST_NAME"],
				"PERSONAL_GENDER" => $res["PERSONAL_GENDER"],
				"~PERSONAL_GENDER" => $res["~PERSONAL_GENDER"],
				"AUTHOR_URL" => (empty($arParams["URL_TEMPLATES_PROFILE_VIEW"])
					? CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("user_id" => $res["AUTHOR_ID"])) : ""),
				"AVATAR" => null,
				// text
				"APPROVED" => $res["APPROVED"],
				"USE_SMILES" => $res["USE_SMILES"],
				"ALLOW" => array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arParams["ALLOW_SMILES"] : "N"))),
				"POST_MESSAGE" => $res["POST_MESSAGE"],
				"~POST_MESSAGE" => $res["~POST_MESSAGE"],
				"~POST_MESSAGE_TEXT" => (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]),
				// links
				"PANELS" => $arResult["PANELS"],
				"URL" => [
					"LINK" => $url->getPathQuery(),
					"MODERATE" => $url->addParams(array("ACTION" => ($res["APPROVED"]=="Y" ? "HIDE" : "SHOW")))->getPathQuery(),
					"EDIT" => $url->addParams(array("ACTION" => "GET"))->getPathQuery(),
					"DELETE" => $url->addParams(array("ACTION" => "DEL"))->getPathQuery(),
				],
				"~SERVICE_TYPE" => ($hideServiceComments ? $res["~SERVICE_TYPE"] : 0),
				"SERVICE_TYPE" => $res["SERVICE_TYPE"],
			];

			if ($res["PANELS"]["EDIT"] == "Y" || (
					$arParams["ALLOW_EDIT_OWN_MESSAGE"] === "LAST" &&
					$res["ID"] == $arResult["TOPIC"]["ABS_LAST_MESSAGE_ID"] &&
					$res["AUTHOR_ID"] > 0 &&
					$res["AUTHOR_ID"] == $USER->GetId()) ||
				($arParams["ALLOW_EDIT_OWN_MESSAGE"] === "ALL" &&
					$res["AUTHOR_ID"] > 0 &&
					$res["AUTHOR_ID"] == $USER->GetId())
			)
			{
				$message["PANELS"]["EDIT"] = "Y";
				$message["PANELS"]["DELETE"] = "Y";
			}
			/************** Message info/***************************************/
			/************** Author info ****************************************/
			if (empty($res["NAME"]) && !empty($res["AUTHOR_NAME"]))
			{
				$message["NAME"] = $res["AUTHOR_NAME"];
				$message["~NAME"] = $res["~AUTHOR_NAME"];
			}
			if (!empty($arParams["NAME_TEMPLATE"]) && $res["SHOW_NAME"] != "Y")
			{
				$name = CUser::FormatName(
					$arParams["NAME_TEMPLATE"],
					array(
						"NAME"			=> $res["NAME"],
						"LAST_NAME"		=> $res["LAST_NAME"],
						"SECOND_NAME"	=> $res["SECOND_NAME"],
						"LOGIN"			=> $res["LOGIN"]
					),
					true,
					false
				);
				if (!!$name)
				{
					$message["~AUTHOR_NAME"] = $name;
					$message["AUTHOR_NAME"] = htmlspecialcharsbx($name);
				}
			}

			$avatarId = (int)$res["PERSONAL_PHOTO"];
			if ($avatarId > 0 && ($avatar = CFile::ResizeImageGet(
					$avatarId,
					array("width" => 42, "height" => 42),
					BX_RESIZE_IMAGE_EXACT,
					false)
				))
			{
				$message["AVATAR"] = [
					"ID" => $avatarId,
					"FILE" => $avatar,
					"HTML" => CFile::ShowImage($avatar["src"], 30, 30, "border=0 align=\"right\"")
				];
			}

			// For quote JS
			$message["FOR_JS"] = array(
				"AUTHOR_NAME" => CUtil::JSEscape($message["AUTHOR_NAME"]),
				"POST_MESSAGE_TEXT" => CUtil::JSEscape(htmlspecialcharsbx($res["POST_MESSAGE_TEXT"]))
			);

			$message["NEW"] = ($arResult["UNREAD_MID"] > 0 && $message["ID"] >= $arResult["UNREAD_MID"] ? "Y" : "N");

			if ($auxSuffix)
			{
				if ((int)($message["SERVICE_TYPE"]) > 0)
				{
					if ($serviceProvider = Forum\Comments\Service\Manager::find([
						"SERVICE_TYPE" => (int)$message["SERVICE_TYPE"]
					]))
					{
						$message["~POST_MESSAGE_TEXT"] = $serviceProvider->getText(
							($res["~SERVICE_DATA"] ?? $res["~POST_MESSAGE"]),
							[
								'mobile' => !$this->isWeb(),
								'suffix' => $auxSuffix,
								'entityType' => $arParams['ENTITY_TYPE'],
								'entityId' => $arParams['ENTITY_ID'],
							]
						);
						$message["AUX"] = $serviceProvider->getType();
						$message['AUX_LIVE_PARAMS'] = (is_array($arParams['~AUX_LIVE_PARAMS']) ? $arParams['~AUX_LIVE_PARAMS'] : []);
						$message["CAN_DELETE"] = ($serviceProvider->canDelete() ? "Y" : "N");
					}
				}
				elseif (
					Main\Loader::includeModule("socialnetwork")
					&& ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
						[
							"POST_TEXT" => $res["~SERVICE_DATA"] ?? $res["~POST_MESSAGE"],
						],
						[
							"needSetParams" => false
						]
					))
				)
				{
					$forumPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
					$dbres = \Bitrix\Socialnetwork\LogCommentTable::getList([
						"filter" => [
							"SOURCE_ID" => $message["ID"],
							"EVENT_ID" => $forumPostLivefeedProvider->getEventId()
						],
						"select" => [ "EVENT_ID", "SHARE_DEST", "LOG_ID" ]
					]);
					if ($sonetCommentFields = $dbres->fetch())
					{
						$auxParams = $commentAuxProvider->getParamsFromFields($sonetCommentFields);
						if (!empty($auxParams))
						{
							$commentAuxProvider->setParams($auxParams);
							$commentAuxProvider->setOptions([
								"eventId" => $sonetCommentFields["EVENT_ID"],
								"suffix" => $auxSuffix,
								"logId" => $sonetCommentFields["LOG_ID"],
								"cache" => !$arResult["DO_NOT_CACHE"]
							]);
							$message["~POST_MESSAGE_TEXT"] = $commentAuxProvider->getText();
							$message["AUX"] = $commentAuxProvider->getType();
							$message["AUX_LIVE_PARAMS"] = [];
							$message["CAN_DELETE"] = ($commentAuxProvider->canDelete() ? "Y" : "N");
							$message["SERVICE_TYPE"] = Forum\Comments\Service\Manager::TYPE_FORUM_DEFAULT;
						}
					}
				}
			}
			$messages[$message["ID"]] = $message;
		}
		$arResult["MESSAGES"] = $messages;
		unset($messages);

		foreach (GetModuleEvents("forum", "OnPrepareComments", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, [$this]);

		$parser->arFiles = $arResult["FILES"];
		foreach ($arResult["MESSAGES"] as $iID => $res):
			$parser->arUserfields = $arResult["MESSAGES"][$iID]["PROPS"] = (array_key_exists($res["ID"], $arResult["UFS"]) ?
				$arResult["UFS"][$res["ID"]] : array());
			$arResult["MESSAGES"][$iID]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);
			$arResult["MESSAGES"][$iID]["FILES_PARSED"] = $parser->arFilesIDParsed;
		endforeach;

		if (
			!empty($arParams['ENTITY_TYPE'])
			&& Main\Loader::includeModule('socialnetwork')
		)
		{
			$contentTypeMap = \Bitrix\Socialnetwork\Livefeed\ForumPost::getForumTypeMap();
			$arResult['POST_CONTENT_TYPE_ID'] = ($contentTypeMap[$arParams['ENTITY_TYPE']] ?? '');
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			CForumCacheManager::SetTag($this->GetCachePath(), "forum_topic_".$arResult["FORUM_TOPIC_ID"]);
		}
	}
	$this->IncludeComponentTemplate();
}
