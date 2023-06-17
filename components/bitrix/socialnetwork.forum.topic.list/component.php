<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
}
elseif (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return false;
}
elseif (intval($arParams["FID"]) <= 0)
{
	ShowError(GetMessage("F_FID_IS_EMPTY"));
	return false;
}

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$GLOBALS["FID"] = $arParams["FID"] = intval($arParams["FID"]);
$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");

$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
$arParams["USER_ID"] = intval(!empty($arParams["USER_ID"]) ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"topic_list" => "PAGE_NAME=topic_list",
		"topic" => "PAGE_NAME=topic&TID=#TID#",
		"topic_edit" => "PAGE_NAME=topic_edit&TID=#TID#&MID=#MID#&MESSAGE_TYPE=#MESSAGE_TYPE#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
	$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
	$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
}
/***************** ADDITIONAL **************************************/
$arParams["PAGEN"] = (intval($arParams["PAGEN"]) <= 0 ? 1 : intval($arParams["PAGEN"]));
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_WINDOW"] = intval(intval($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);

$arParams["TOPICS_PER_PAGE"] = intval($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
$arParams["MESSAGES_PER_PAGE"] = intval($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat());

$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$isSlider = $request->get('IFRAME') === 'Y';

/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
if ($arParams["MODE"] == "GROUP")
{
	$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
		'filter' => array(
			'=ID' => $arParams["SOCNET_GROUP_ID"]
		),
		'select' => array('ID')
	));
	$entity = $res->fetch();
	if (!$entity)
	{
		ShowError(GetMessage("SFTL_ERROR_NO_GROUP"));
		return false;
	}
}
elseif ($arParams["MODE"] == "USER")
{
	$filter = array(
		'=ID' => $arParams["USER_ID"]
	);
	if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
	{
		$filter['=ACTIVE'] = 'Y';
	}
	$res = \Bitrix\Main\UserTable::getList(array(
		'filter' => $filter,
		'select' => array('ID')
	));
	$entity = $res->fetch();
	if (!$entity)
	{
		ShowError(GetMessage("SFTL_ERROR_NO_USER"));
		return false;
	}
}

//************** SocNet Activity ***********************************/
if (
	(
		$arParams["MODE"] == "GROUP"
		&& !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")
	)
	|| (
		$arParams["MODE"] != "GROUP"
		&& !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum")
	)
)
{
	ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
	return false;
}

//************** Forum *********************************************/
	$arResult["TOPICS"] = array();
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	$arParams["PERMISSION_ORIGINAL"] = ForumCurrUserPermissions($arParams["FID"]);
	$arParams['PERMISSION'] = \Bitrix\Forum\Permission::ACCESS_DENIED;
	$arResult["ERROR_MESSAGE"] = "";
	$arResult["OK_MESSAGE"] = "";

	$arError = array();
	$arNote = array();
//************** Permission ****************************************/

if (empty($arResult["FORUM"]))
{
	CHTTP::SetStatus("404 Not Found");
	$arError[] = array(
		"id" => "forum_is_lost", 
		"text" => GetMessage("F_FID_IS_LOST"));
}
else
{
	$arParams['PERMISSION'] = \Bitrix\Socialnetwork\Helper\Forum\ComponentHelper::getForumPermission([
		'ENTITY_TYPE' => ($arParams['MODE'] === 'GROUP' ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
		'ENTITY_ID' => ($arParams['MODE'] === 'GROUP' ? $arParams['SOCNET_GROUP_ID'] : $arParams['USER_ID']),
	]);
}

if (empty($arError) && !CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray(), $arParams["PERMISSION"])):
	$arError[] = array(
		"id" => "acces denied", 
		"text" => GetMessage("FORUM_SONET_NO_ACCESS"));
endif;
if (!empty($arError)):
	$e = new CAdminException($arError);
	$res = $e->GetString();
	ShowError($res);
	return false;
endif;
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Actions
********************************************************************/
$ACTION = mb_strtoupper(
	is_set($_REQUEST, "form_action")
		? $_REQUEST["form_action"]
		: ($_REQUEST["ACTION"] ?? '')
);
if (($_REQUEST["topic_edit"] ?? '') === "Y")
{
	$strErrorMessage = ""; $strOkMessage = ""; 
	$result = false; 
	$topics = (is_set($_REQUEST, "topic_id") ? $_REQUEST["topic_id"] : $_REQUEST["TID"]);
	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad_sessid", 
			"text" => GetMessage("F_ERR_SESS_FINISH"));
	}
	elseif (!in_array($ACTION, array("SET_TOP", "TOP", "SET_ORDINARY", "ORDINARY", 
		"DEL_TOPIC", "DELETE", "STATE_Y", "STATE_N", "CLOSE", "OPEN")))
	{
		$arError[] = array(
			"id" => "empty action", 
			"text" => GetMessage("F_ERR_EMPTY_ACTION"));
	}
	elseif (empty($topics))
	{
		$arError[] = array(
			"id" => "empty topics", 
			"text" => GetMessage("F_ERR_EMPTY_TOPICS"));
	}
	else
	{
		$arTopics = array(); 
		$arFilter = array(
			"FORUM_ID" => $arParams["FID"], 
			"SOCNET_GROUP_ID" => false, 
			"@ID" => $topics);
		if ($arParams["MODE"] == "GROUP")
			$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		else 
			$arFilter["OWNER_ID"] = $arParams["USER_ID"];
		$db_res = CForumTopic::GetListEx(array("SORT"=>"ASC"), $arFilter);
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				$arTopic[] = intval($res["ID"]);
			}while ($res = $db_res->Fetch());
			switch ($ACTION)
			{
				case "SET_TOP":
				case "SET_ORDINARY":
				case "TOP":
				case "ORDINARY":
					$ACTION = ($ACTION == "SET_ORDINARY" ? "ORDINARY" : ($ACTION == "SET_TOP" ? "TOP" : $ACTION));
					$sort = ($ACTION == "TOP" ? "150" : "100");
					$result = ForumTopOrdinaryTopic($arTopic, $ACTION, $strErrorMessage, $strOkMessage, array("PERMISSION" => $arParams["PERMISSION"]));
					break;
				case "DEL_TOPIC":
				case "DELETE":
					$arLogID = array();
					foreach($arTopic as $topic_id_tmp)
					{
						// delete message log records
						$dbForumMessage = CForumMessage::GetList(
										array("ID" => "ASC"),
										array("TOPIC_ID" => $topic_id_tmp)
									);
						while ($arForumMessage = $dbForumMessage->Fetch())
						{
							$dbRes = CSocNetLog::GetList(
								array("ID" => "DESC"),
								array(
									"EVENT_ID" => "forum",
									"SOURCE_ID" => $arForumMessage["ID"]
								),
								false,
								false,
								array("ID", "PARAMS")
							);
							while ($arRes = $dbRes->Fetch())
								$arLogID[] = $arRes["ID"];
						}
					}
					$result = ForumDeleteTopic($arTopic, $strErrorMessage, $strOkMessage, array("PERMISSION" => $arParams["PERMISSION"]));
					if ($result)
						foreach($arLogID as $log_id)
							CSocNetLog::Delete($log_id);
					break;
				case "STATE_Y":
				case "STATE_N":
				case "CLOSE":
				case "OPEN":
					$ACTION = ($ACTION == "STATE_Y" ? "OPEN" : ($ACTION == "STATE_N" ? "CLOSE" : $ACTION));
					$state = ($ACTION == "OPEN" ? "Y" : "N");
					$result = ForumOpenCloseTopic($arTopic, $ACTION, $strErrorMessage, $strOkMessage, array("PERMISSION" => $arParams["PERMISSION"]));
					break;
				default:
					$arError[] = array(
						"id" => "bad action", 
						"text" => $ACTION);
					break;
			}
			if (!empty($strErrorMessage))
			{
				$arError[] = array(
					"id" => "action error", 
					"text" => $strErrorMessage);
			}
		}
		$arTopic = array_diff($topics, $arTopic);
		
		if (!empty($arTopic))
		{
			$arError[] = array(
				"id" => "empty topics", 
				"text" => str_replace("#TOPICS#", implode(", ", $arTopic), GetMessage("F_ERR_TOPICS_NOT_MODERATION")));
		}
	}
	if (empty($arError))
	{
		$url = CComponentEngine::MakePathFromTemplate(
			$arParams['URL_TEMPLATES_TOPIC_LIST'],
			[
				'FID' => $arParams['FID'],
				'UID' => $arParams['USER_ID'],
				'GID' => $arParams['SOCNET_GROUP_ID'],
			],
		);

		if ($isSlider)
		{
			$uri = new \Bitrix\Main\Web\Uri($url);
			$uri->addParams([ 'IFRAME' => 'Y' ]);
			$url = $uri->getUri();
		}

		LocalRedirect($url);
	}
	else
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
		$arResult["OK_MESSAGE"] = $strOkMessage;
	}
}

if($arParams["SOCNET_GROUP_ID"]>0 && $USER->IsAuthorized() && check_bitrix_sessid())
{
	if($_REQUEST['SAVE_EMAIL_FORUM']=='Y' && $_SERVER['REQUEST_METHOD']=='POST' && $arParams["PERMISSION"] >= "Y" && CModule::IncludeModule("mail") && $APPLICATION->GetGroupRight("mail")>"R")
	{
		$arFields = Array();
		$arFields["FORUM_ID"] = $arParams["FID"];
		$arFields["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		if($_POST["EMAIL_FORUM_ACTIVE"] != "Y")
		{
			$arFields["EMAIL_FORUM_ACTIVE"] = "N";
			CForumEMail::Set($arFields);
		}
		else
		{
			$arFields["EMAIL_FORUM_ACTIVE"] = "Y";
			$arFields["EMAIL"] = $_POST["EMAIL"]; 
			$arFields["USE_EMAIL"] = $_POST["USE_EMAIL"]; 
			$arFields["EMAIL_GROUP"] = ($_POST["EMAIL_GROUP"]?$_POST["EMAIL_GROUP"]:""); 
			$arFields["SUBJECT_SUF"] = ($_POST["SUBJECT_SUF"]?$_POST["SUBJECT_SUF"]:""); 
			$arFields["USE_SUBJECT"] = $_POST["USE_SUBJECT"]; 
			$arFields["NOT_MEMBER_POST"] = $_POST["NOT_MEMBER_POST"];
			$arFields["URL_TEMPLATES_MESSAGE"] = $arParams["~URL_TEMPLATES_MESSAGE"];

			if($_POST["EMAIL_FORUM_MAILBOX"]>0)
			{
				$dbrMailF = CMailFilter::GetById($_POST["EMAIL_FORUM_MAILBOX"]);
				if($arMailF = $dbrMailF->GetNext())
				{
					if($arMailF['MAILBOX_TYPE']=='smtp')
					{
						$arFields["EMAIL_GROUP"] = ''; 
						$domains = preg_split("/[\r\n]+/", $arMailF['DOMAINS'], -1, PREG_SPLIT_NO_EMPTY);
						if(count($domains)>0)
							$arFields["EMAIL"] = $arFields["EMAIL"]."@".$_POST['EMAIL_DOMAIN']; 
					}

					$arFields["MAIL_FILTER_ID"] = $_POST["EMAIL_FORUM_MAILBOX"];
					if(CForumEMail::Set($arFields)>0)
					{
						LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"], 
							array("FID" => $arParams["FID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"]))
						);
					}
				}
			}
			else
			{
				if($_POST["EMAIL_FORUM_MAILBOX"]=="!") //new pop3
				{
					$arMailboxFields = Array(
						"LID" => SITE_ID,
						"SERVER_TYPE" => "pop3",
						"ACTIVE" => "Y",
						"NAME" => $_POST["EMAIL_FORUM_MAILBOX_NAME"], 
						"SERVER" => $_POST["EMAIL_FORUM_MAILBOX_SERVER"], 
						"PORT" => $_POST["EMAIL_FORUM_MAILBOX_PORT"], 
						"LOGIN" => $_POST["EMAIL_FORUM_MAILBOX_LOGIN"], 
						"PASSWORD" => $_POST["EMAIL_FORUM_MAILBOX_PASSWORD"],
						"USE_TLS" => $_POST["EMAIL_FORUM_MAILBOX_SSL"],
						"DELETE_MESSAGES" => $_POST["EMAIL_FORUM_MAILBOX_DELETE_MESSAGES"],
						"PERIOD_CHECK" => 5,
						);

					$MAILBOX_ID = CMailBox::Add($arMailboxFields);
				}
				elseif(mb_substr($_POST["EMAIL_FORUM_MAILBOX"], 0, 1) == 'M') //new smtp rule
				{
					$MAILBOX_ID = mb_substr($_POST["EMAIL_FORUM_MAILBOX"], 1);
					$dbrMailF = CMailBox::GetById($MAILBOX_ID);
					if(($arMailF = $dbrMailF->GetNext()) && $arMailF['SERVER_TYPE']=='smtp')
					{
						$arFields["EMAIL_GROUP"] = ''; 
						$domains = preg_split("/[\r\n]+/", $arMailF['DOMAINS'], -1, PREG_SPLIT_NO_EMPTY);
						if(count($domains)>0)
							$arFields["EMAIL"] = $arFields["EMAIL"]."@".$_POST['EMAIL_DOMAIN']; 
					}
					else
						$MAILBOX_ID = 0;					
				}

				if($MAILBOX_ID>0)
				{
					$arMailFilterFields = Array(
						"MAILBOX_ID" => $MAILBOX_ID,
						"NAME" => GetMessage("SOCNET_FORUM_TL_EMAIL_RULE"), 
						"ACTION_TYPE" => "forumsocnet",
						"ACTION_VARS" => "",
						"WHEN_MAIL_RECEIVED" => "Y",
						"WHEN_MANUALLY_RUN" => "Y",
						);
					
					$MAIL_FILTER_ID = CMailFilter::Add($arMailFilterFields); 

					$arFields["MAIL_FILTER_ID"] = $MAIL_FILTER_ID;

					if(CForumEMail::Set($arFields)>0)
					{
						LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"], 
							array("FID" => $arParams["FID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"]))
						);
					}
				}
			}
		}
	}
	elseif($ACTION=="FORUM_SUBSCRIBE")
	{	
		if (ForumSubscribeNewMessagesEx($arParams["FID"], 0, "N", $strErrorMessage, $strOkMessage, false, $arParams["SOCNET_GROUP_ID"])):

			$url = CComponentEngine::MakePathFromTemplate(
				$arParams['URL_TEMPLATES_TOPIC_LIST'],
				[
					'FID' => $arParams['FID'],
					'UID' => $arParams['USER_ID'],
					'GID' => $arParams['SOCNET_GROUP_ID'],
				],
			);

			if ($isSlider)
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams([ 'IFRAME' => 'Y' ]);
				$url = $uri->getUri();
			}

			LocalRedirect($url);
		else:
			$arResult["ERROR_MESSAGE"] = $strErrorMessage;
		endif;
	}
	elseif($ACTION=="FORUM_UNSUBSCRIBE")
	{
		$arFields = array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $arParams["FID"],
			"SITE_ID" => SITE_ID,
			"TOPIC_ID" => false,
			"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"]
			);

		$db_res = CForumSubscribe::GetListEx(array(), $arFields);
		if ($db_res && ($res = $db_res->Fetch()) && CForumSubscribe::Delete($res["ID"]))
		{
			$url = CComponentEngine::MakePathFromTemplate(
				$arParams['URL_TEMPLATES_TOPIC_LIST'],
				[
					'FID' => $arParams['FID'],
					'UID' => $arParams['USER_ID'],
					'GID' => $arParams['SOCNET_GROUP_ID'],
				],
			);
			if ($isSlider)
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams([ 'IFRAME' => 'Y' ]);
				$url = $uri->getUri();
			}
			LocalRedirect($url);
		}

	}
}
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Default params # 2
********************************************************************/
global $by, $order;
InitSorting();
if (!$by):
	ForumGetTopicSort($by, $order, $arResult["FORUM"]);
endif;
$by = ($by == "ABS_LAST_POST_DATE" ? "LAST_POST_DATE" : $by);
$arResult["SortingEx"]["TITLE"] = SortingEx("TITLE");
$arResult["SortingEx"]["USER_START_NAME"] = SortingEx("USER_START_NAME");
$arResult["SortingEx"]["POSTS"] = SortingEx("POSTS");
$arResult["SortingEx"]["VIEWS"] = SortingEx("VIEWS");
$arResult["SortingEx"]["LAST_POST_DATE"] = SortingEx("LAST_POST_DATE");
$by = ($by == "LAST_POST_DATE" && $arParams["PERMISSION"] >= "Q" ? "ABS_LAST_POST_DATE" : $by);

$parser = new forumTextParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];

$arResult["TOPICS"] = array();

if ($arParams["PERMISSION"] > "E")
	$arResult["CanUserAddTopic"] = CForumTopic::CanUserAddTopic(
		$arParams["FID"],
		$USER->GetUserGroupArray(),
		$USER->GetID(),
		$arResult["FORUM"],
		$arParams["PERMISSION"]);
else
	$arResult["CanUserAddTopic"] = false;

$urlTopicNew = CComponentEngine::MakePathFromTemplate(
	$arParams['URL_TEMPLATES_TOPIC_EDIT'],
	array(
		'FID' => $arParams['FID'],
		'TID' => 'new',
		'ACTION' => 'new',
		'MESSAGE_TYPE' => 'NEW',
		'UID' => $arParams['USER_ID'],
		'GID' => $arParams['SOCNET_GROUP_ID'],
		'MID' => 0,
	)
);

if ($isSlider)
{
	$uri = new \Bitrix\Main\Web\Uri($urlTopicNew);
	$uri->addParams([ 'IFRAME' => 'Y' ]);
	$urlTopicNew = $uri->getUri();
}

$arResult["URL"] = [
	"TOPIC_NEW" => $urlTopicNew,
];
/********************************************************************
				/Default params # 2
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arFilter = array(
//	"FORUM_ID" => $arParams["FID"],
	"SOCNET_GROUP_ID" => false);
if ($arParams["PERMISSION"] < "Q")
	$arFilter["APPROVED"] = "Y";
if ($USER->IsAuthorized())
	$arFilter["USER_ID"] = $USER->GetID();
if ($arParams["MODE"] == "GROUP")
	$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
else
{
	$arFilter["OWNER_ID"] = $arParams["USER_ID"];
	$arFilter["FORUM_ID"] = $arParams["FID"];
}

$db_res = CForumTopic::GetListEx(
	array("SORT"=>"ASC", $by=>$order),
	$arFilter,
	false,
	false,
	array(
		"bDescPageNumbering" => ($arParams["USE_DESC_PAGE"] == "Y" ? true : false),
		"nPageSize" => $arParams["TOPICS_PER_PAGE"],
		"bShowAll" => false,
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]
	)
);
$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
//******************************************************************/
$arResult["NAV_RESULT"] = $db_res;
$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
while ($res = $db_res->GetNext())
{
	$res["STATUS"] = "OLD";
	if ($res["APPROVED"] != "Y")
	{
		$res["STATUS"] = "NA";
	}
	elseif ($res["STATE"] == "L")
	{
		$res["STATUS"] = "MOVED";

		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_TOPIC"],
			[
				'FID' => $res['FORUM_ID'],
				'TID' => $res['TOPIC_ID'],
				'MID' => 's',
			],
		);
		if ($isSlider)
		{
			$uri = new \Bitrix\Main\Web\Uri($url);
			$uri->addParams([ 'IFRAME' => 'Y' ]);
			$url = $uri->getUri();
		}
		$res["URL"]["READ"] = $url;
	}
	elseif (NewMessageTopic($res["FORUM_ID"], $res["ID"], 
		($arParams["PERMISSION"] < "Q" ? $res["LAST_POST_DATE"] : $res["ABS_LAST_POST_DATE"]), $res["LAST_VISIT"]))
	{
		$res["STATUS"] = "NEW";
	}
	$res["TopicStatus"] = $res["STATUS"];

	$res["numMessages"] = $res["POSTS"];
	/*******************************************************************/
	if ($arParams["PERMISSION"] >= "Q")
	{
		$res["LAST_POSTER_ID"] = $res["ABS_LAST_POSTER_ID"];
		$res["LAST_POST_DATE"] = $res["ABS_LAST_POST_DATE"];
		$res["LAST_POSTER_NAME"] = $res["ABS_LAST_POSTER_NAME"];
		$res["LAST_MESSAGE_ID"] = $res["ABS_LAST_MESSAGE_ID"];
		$res["mCnt"] = intval($res["POSTS_UNAPPROVED"]);
		$res["numMessages"] += $res["mCnt"];
		$res["mCntURL"] = $res["URL"]["MODERATE_MESSAGE"] ?? '';
	}

	/*******************************************************************/
	$res["numMessages"]++;
	/*******************************************************************/
	/*******************************************************************/
	$res["pages"] = ForumShowTopicPages(
		$res["numMessages"],
		$res["URL"]["READ"] ?? '',
		"PAGEN_".$arParams["PAGEN"],
		intval($arParams["MESSAGES_PER_PAGE"])
	);
	$res["PAGES_COUNT"] = intval(ceil($res["numMessages"]/$arParams["MESSAGES_PER_PAGE"]));
/*******************************************************************/
	$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
	$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
	$res["USER_START_NAME"] = $parser->wrap_long_words($res["USER_START_NAME"]);
	$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
	$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
	$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));
/*******************************************************************/
	$res["URL"] = array(
		"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC"], 
			array("TID" => $res["ID"],  "MID" => "s", "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])),
		"READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC"], 
			array("TID" => $res["ID"],  "MID" => "s", "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])),
		"LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
			array("TID" => $res["ID"], "MID" => $res["LAST_MESSAGE_ID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])), 
		"UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC"], 
			array("TID" => $res["ID"],  "MID" => "unread_mid", "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])),
		"MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC"], 
			array("TID" => $res["ID"],  "MID" => "unread_mid", "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])),
		"USER_START" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["USER_START_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])), 
		"LAST_POSTER" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["LAST_POSTER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])));
	foreach ($res["URL"] as $key => $val):
		if ($isSlider)
		{
			$uri = new \Bitrix\Main\Web\Uri($val);
			$uri->addParams([ 'IFRAME' => 'Y' ]);
			$val = $uri->getUri();
		}
		$res["URL"]["~".$key] = $val;
		$res["URL"][$key] = htmlspecialcharsbx($val);
	endforeach;
/*******************************************************************/
	$arResult["TOPICS"][] = $res;
}

if($arParams["SOCNET_GROUP_ID"] > 0 && $USER->IsAuthorized() && CModule::IncludeModule("mail"))
{
	$arResult["EMAIL_INTEGRATION"] = CForumEMail::GetForumFilters($arParams["FID"], $arParams["SOCNET_GROUP_ID"]);
	if($arResult["EMAIL_INTEGRATION"])
	{
		$dbMBF = CMailFilter::GetById($arResult["EMAIL_INTEGRATION"]["MAIL_FILTER_ID"]);
		$arResult["EMAIL_INTEGRATION"]["MAIL_FILTER"] = $dbMBF->Fetch();

		$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => 0, "SITE_ID" => SITE_ID, "SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"]);
		$db_res = CForumSubscribe::GetList(array(), $arFields);
		if($db_res && $res = $db_res->Fetch())
		{
			$arResult["USER"]["SUBSCRIBE"] = "Y";
		}
	}
	

	// if user has mail module permissions:
	if($arParams["PERMISSION"] >= "Y" && $APPLICATION->GetGroupRight("mail")>"R")
	{
		$arResult["MAILBOXES"] = Array();

		$dbrMailF = CMailFilter::GetList(Array(), Array("SERVER_TYPE"=>"smtp", "EMPTY"=>"Y"));
		while($arMailF = $dbrMailF->GetNext())
			$arResult["MAILBOXES"][] = $arMailF;

		$dbrMailF = CMailFilter::GetList(Array(), Array("SERVER_TYPE"=>"pop3", "ACTION_TYPE"=>"forumsocnet"));
		while($arMailF = $dbrMailF->GetNext())
			$arResult["MAILBOXES"][] = $arMailF;
	}
}
/********************************************************************
				/Data
********************************************************************/

$this->IncludeComponentTemplate();

/********************************************************************
				Standart Action
 ********************************************************************/
if ($arParams["SET_TITLE"] != "N"):
	$APPLICATION->AddChainItem(GetMessage("FL_FORUM_CHAIN"));
	$APPLICATION->SetTitle(GetMessage("FL_FORUM_CHAIN"));
endif;
/********************************************************************
				/Standart Action
 ********************************************************************/
