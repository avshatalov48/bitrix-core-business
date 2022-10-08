<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/***************** For custom component only ***********************/
	if (!empty($arParams["arFormParams"]) && is_array($arParams["arFormParams"]))
	{
		$arParams["FID"] = $arParams["arFormParams"]["FID"];
		$arParams["TID"] = $arParams["arFormParams"]["TID"];
		$arParams["MID"] = $arParams["arFormParams"]["MID"];

		$arParams["URL_TEMPLATES_LIST"] = $arParams["arFormParams"]["URL_TEMPLATES_LIST"];
		$arParams["URL_TEMPLATES_READ"] = $arParams["arFormParams"]["URL_TEMPLATES_READ"];

		$arParams["PAGE_NAME"] = $arParams["arFormParams"]["PAGE_NAME"];
		$arParams["MESSAGE_TYPE"] = $arParams["arFormParams"]["MESSAGE_TYPE"];
		$arParams["FORUM"] = $arParams["arFormParams"]["arForum"];
		$arParams["bVarsFromForm"] = $arParams["arFormParams"]["bVarsFromForm"];

		$arParams["CACHE_TIME"] = $arParams["arFormParams"]["CACHE_TIME"];
	}
/***************** BASE ********************************************/
	$arParams["MESSAGE_TYPE"] = (in_array(mb_strtoupper($arParams["MESSAGE_TYPE"]), array("REPLY", "EDIT", "NEW"))? mb_strtoupper($arParams["MESSAGE_TYPE"]) : "NEW");
	$arParams["FID"] = intval(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intval(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = intval(empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MID"] = ($arParams["MESSAGE_TYPE"] == "EDIT" ? $arParams["MID"] : 0);

	$arParams["PAGE_NAME"] = htmlspecialcharsbx((empty($arParams["PAGE_NAME"]) ? $_REQUEST["PAGE_NAME"] : $arParams["PAGE_NAME"]));
	$arParams["FORUM"] = (!empty($arParams["arForum"]) ? $arParams["arForum"] : (!empty($arParams["FORUM"]) ? $arParams["FORUM"] : array()));
	$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"] == "Y" || $arParams["bVarsFromForm"] === true ? "Y" : "N");
/***************** URL *********************************************/
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"help" =>"PAGE_NAME=help",
			"rules" =>"PAGE_NAME=rules");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : (empty($arParams["USER_FIELDS"]) ? array() : array($arParams["USER_FIELDS"])));
	$arResult["~USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", $arParams["MID"], LANGUAGE_ID);
	foreach ($arResult["~USER_FIELDS"] as $key => $val)
	{
		if ($val["MANDATORY"] == "Y")
		{
			$arParams["USER_FIELDS"][] = $key;
		}
	}

	$arParams["PATH_TO_SMILE"] = "";
	$arParams["PATH_TO_ICON"] = "";
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");
	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($_REQUEST["AJAX_CALL"] == "Y" && $arParams["AJAX_TYPE"] == "Y") ? "Y" : "N");
	$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] == "Y" ? "Y" : "N");
	$arParams["SMILE_TABLE_COLS"] = (intval($arParams["SMILE_TABLE_COLS"]) > 0 ? intval($arParams["SMILE_TABLE_COLS"]) : 3);
	$arParams["VOTE_CHANNEL_ID"] = intval($arParams["VOTE_CHANNEL_ID"]);
	$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && $arParams["VOTE_CHANNEL_ID"] > 0 && IsModuleInstalled("vote") ? "Y" : "N");
	$arParams["VOTE_GROUP_ID"] = (!is_array($arParams["VOTE_GROUP_ID"]) || empty($arParams["VOTE_GROUP_ID"]) ? array() : $arParams["VOTE_GROUP_ID"]);
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$arResult["USER_FIELDS"] = array_intersect_key($arResult["~USER_FIELDS"], array_flip($arParams["USER_FIELDS"]));
$arResult["SHOW_SEARCH"] = (IsModuleInstalled("search") ? "Y" : "N");
$arResult["IsAuthorized"] = ($USER->IsAuthorized() ? "Y" : "N");
$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arParams["FORUM"] = CForumNew::GetByID($arParams["FID"]);
$arParams["AUTOSAVE"] = CForumAutosave::GetInstance();

$arResult["URL"] = array(
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"],
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"])),
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"],
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"])),
	"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arParams["TID"], "MID"=>((intval($arParams["MID"]) > 0) ? intval($arParams["MID"]) : "s"))),
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arParams["TID"], "MID"=>((intval($arParams["MID"]) > 0) ? intval($arParams["MID"]) : "s"))),
	"RULES" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RULES"], array()),
	"~RULES" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RULES"], array()),
	"HELP" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_HELP"], array()),
	"~HELP" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_HELP"], array()));
$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
$arResult["SHOW_POST_FORM"] = "Y";
$arResult["SHOW_PANEL_GUEST"] = "N";
$arResult["SHOW_PANEL_VOTE"] = "N";
$arResult["SHOW_PANEL_NEW_TOPIC"] = "N";
$arResult["SHOW_PANEL_EDIT"] = ($arParams["MESSAGE_TYPE"] == "EDIT" ? "Y" : "N");
$arResult["SHOW_PANEL_EDIT_PANEL_GUEST"] = ($USER->IsAuthorized() ? "N" : "Y");
$arResult["SHOW_PANEL_EDIT_ASK"] = ($arParams["PERMISSION"] > "Q" ? "Y" : "N");
$arResult["SHOW_SUBSCRIBE"] = ($USER->IsAuthorized() && $arParams["PERMISSION"] > "E" ? "Y" : "N");
$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arParams["FORUM"]["ALLOW_UPLOAD"], array("Y", "F", "A")) ? "Y" : "N");
$arResult["SHOW_PANEL_TRANSLIT"] = (LANGUAGE_ID == "ru" ? "Y" : "N");
$arResult["TRANSLIT"] = (LANGUAGE_ID == "ru" ? "Y" : "N");
$arResult["ForumPrintIconsList"] = "";
$arResult["ForumPrintSmilesList"] = "";
$arResult["TOPIC_FILTER"] = array();
$arResult["TOPIC"] = array();
$arResult["MESSAGE"] = array(
	"AUTHOR_ID" => $USER->GetParam("USER_ID"),
	"USE_SMILES" => "Y",
	"AUTHOR_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
	"AUTHOR_EMAIL" => "",
	"POST_MESSAGE" => "",
	"EDITOR_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
	"EDITOR_EMAIL" => "quest@guest.com",
	"EDIT_REASON" => "",
	"FILES" => array());
$arResult["TOPIC"] = array(
	"TITLE" => "",
	"TITLE_SEO" => "",
	"TAGS" => "",
	"DESCRIPTION" => "",
	"ICON" => "");
$arResult["QUESTIONS"] = array();
$arResult["~QUESTIONS"] = array();
$arResult['DATE_END'] = GetTime((time() + 30*86400));
/********************************************************************
				/Default params
********************************************************************/
$bShowForm = false;
if ($arParams["MESSAGE_TYPE"] == "REPLY" && $arParams["TID"] > 0)
	$bShowForm = CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID());
elseif ($arParams["MESSAGE_TYPE"] == "EDIT" && $arParams["MID"] > 0)
	$bShowForm = CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), intval($USER->GetID()));
elseif ($arParams["MESSAGE_TYPE"] == "NEW" && $arParams["FID"] > 0)
	$bShowForm = CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID());

if (!$bShowForm):
	return 0;
endif;
if ($arParams["SHOW_VOTE"] == "Y" && CModule::IncludeModule("vote"))
{
	$permission = ((isset($arParams['PERMISSION']) &&
		(intval($arParams['PERMISSION'] > 0 || $arParams['PERMISSION'] === 0)))
		? intval($arParams['PERMISSION'])
		: CVoteChannel::GetGroupPermission($arParams["VOTE_CHANNEL_ID"]));

	if ($permission < 2)
	{
		$arParams["SHOW_VOTE"] = "N";
	}
	else if (!empty($arParams["VOTE_GROUP_ID"]))
	{
		$res = array_intersect($USER->GetUserGroupArray(), $arParams["VOTE_GROUP_ID"]);
		$arParams["SHOW_VOTE"] = (empty($res) ? "N" : $arParams["SHOW_VOTE"]);
	}
	else if ($permission < 4)
	{
		$arParams["SHOW_VOTE"] = "N";
	}
}
/********************************************************************
				Data
********************************************************************/
if ($arParams["MESSAGE_TYPE"] == "EDIT")
{
	$arMessage = CForumMessage::GetByIDEx($arParams["MID"], array("GET_FORUM_INFO" => "N", "GET_TOPIC_INFO" => "Y", "FILTER" => "N"));
	if (empty($arMessage)):
		ShowError(GetMessage("F_ERROR_MESSAGE_NOT_FOUND"));
		return 0;
	endif;

	$arResult["TOPIC"] = $arMessage["TOPIC_INFO"];
	$arResult["TOPIC_FILTER"] = CForumTopic::GetByID($arMessage["TOPIC_ID"]);

	$arResult["MESSAGE"] = $arMessage;
	$arResult["MESSAGE"]["FILES"] = array();
	$db_res = CForumFiles::GetList(array(), array("MESSAGE_ID" => $arParams["MID"]));
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arResult["MESSAGE"]["FILES"][$res["FILE_ID"]] = $res;
		} while ($res = $db_res->Fetch());
	}

	if ($arParams["SHOW_VOTE"] == "Y" && $arMessage["PARAM1"] == "VT" && intval($arMessage["PARAM2"]) > 0)
	{
		$db_vote = CVote::GetByID(intval($arMessage["PARAM2"]));
		if ($db_vote && $arVote = $db_vote->GetNext())
		{
			$arResult['DATE_END'] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arVote['DATE_END'], CSite::GetDateFormat()));
		}

		$db_res = CVoteQuestion::GetListEx(array("ID" => "ASC"), array("CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], "VOTE_ID" => $arMessage["PARAM2"]));
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				$arResult["~QUESTIONS"][$res["ID"]] = $res;
				$arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"] = array();
			} while ($res = $db_res->Fetch());
		}
		if (!empty($arResult["~QUESTIONS"]))
		{
			$db_res = CVoteAnswer::GetListEx(array("ID" => "ASC"), array("VOTE_ID" => $arMessage["PARAM2"]));
			if ($db_res && $res = $db_res->Fetch())
			{
				do
				{
					if (is_set($arResult["~QUESTIONS"], $res["QUESTION_ID"])):
						$arResult["~QUESTIONS"][$res["QUESTION_ID"]]["ANSWERS"][$res["ID"]] = $res;
						if (intval($res["FIELD_TYPE"]) == 1):
							$arResult["~QUESTIONS"][$res["QUESTION_ID"]]["MULTI"] = "Y";
						endif;
					endif;
				} while ($res = $db_res->Fetch());
			}
		}
		$arResult["QUESTIONS"] = $arResult["~QUESTIONS"];
	}
	$arResult["URL"] = array_merge($arResult["URL"], array(
		"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID"=>$arParams["MID"])),
		"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
			array("FID" => $arParams["FID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID"=>$arParams["MID"]))));
}

if ($arParams["bVarsFromForm"] == "Y")
{
	$arResult["MESSAGE"]["AUTHOR_NAME"] = $_REQUEST["AUTHOR_NAME"];
	$arResult["MESSAGE"]["AUTHOR_EMAIL"] = $_REQUEST["AUTHOR_EMAIL"];
	$arResult["MESSAGE"]["POST_MESSAGE"] = $_REQUEST["POST_MESSAGE"];
	$arResult["MESSAGE"]["USE_SMILES"] = ($_REQUEST["USE_SMILES"] == "Y" ? "Y" : "N");
	$arResult["MESSAGE"]["EDITOR_NAME"] = $_REQUEST["EDITOR_NAME"];
	$arResult["MESSAGE"]["EDITOR_EMAIL"] = $_REQUEST["EDITOR_EMAIL"];
	$arResult["MESSAGE"]["EDIT_REASON"] = $_REQUEST["EDIT_REASON"];
	$arResult["TOPIC"]["TITLE"] = $_REQUEST["TITLE"];
	$arResult["TOPIC"]["TITLE_SEO"] = $_REQUEST["TITLE_SEO"];
	$arResult["TOPIC"]["TAGS"] = $_REQUEST["TAGS"];
	$arResult["TOPIC"]["DESCRIPTION"] = $_REQUEST["DESCRIPTION"];
	$arResult["TOPIC"]["ICON"] = $_REQUEST["ICON"];
	foreach ($_REQUEST["FILES"] as $key => $val):
		if (intval($val) <= 0)
			return false;
		$arResult["MESSAGE"]["FILES"][$val] = $val;
	endforeach;
	$arResult["QUESTIONS"] = array();
	if (!empty($_REQUEST["QUESTION"]))
	{
		foreach ($_REQUEST["QUESTION"] as $key => $val)
		{
			$res = array(
				"QUESTION" => trim($val),
				"MULTI" => ($_REQUEST["MULTI"][$key] == "Y" ? "Y" : "N"),
				"ANSWERS" => array());
			if (is_set($arResult["~QUESTIONS"], $_REQUEST["QUESTION_ID"][$key]))
			{
				$res["ID"] = intval($_REQUEST["QUESTION_ID"][$key]);
				if ($_REQUEST["QUESTION_DEL"][$key] == "Y")
					$res["DEL"] = "Y";
			}
			elseif ($_REQUEST["QUESTION_DEL"][$key] == "Y")
			{
				continue;
			}

			if (is_array($_REQUEST["ANSWER"]) && !empty($_REQUEST["ANSWER"]))
			{
				foreach ($_REQUEST["ANSWER"][$key] as $keya => $vala)
				{
					$resa = array("MESSAGE" => trim($vala));
					if ($res["ID"] > 0 &&
						is_set($arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"], $_REQUEST["ANSWER_ID"][$key][$keya])
					)
					{
						$resa["ID"] = intval($_REQUEST["ANSWER_ID"][$key][$keya]);
						if ($_REQUEST["ANSWER_DEL"][$key][$keya] == "Y")
							$resa["DEL"] = "Y";
					}
					elseif ($_REQUEST["ANSWER_DEL"][$key][$keya] == "Y" || empty($resa["MESSAGE"]))
					{
						continue;
					}
					$res["ANSWERS"][] = $resa;
				}
			}
			if (empty($res["ANSWERS"]) && empty($res["QUESTION"]) && empty($res["ID"]))
				continue;
			$arResult["QUESTIONS"][] = $res;
		}
	}
}
/*******************************************************************/
if (($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="REPLY") && $arResult["IsAuthorized"] == "N" ||
	$arParams["MESSAGE_TYPE"]=="EDIT" && intval($arResult["MESSAGE"]["AUTHOR_ID"]) <= 0)
{
	$arResult["SHOW_PANEL_GUEST"] = "Y";
}

if ($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="EDIT" &&
	CForumTopic::CanUserUpdateTopic($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID()) &&
	$arResult["MESSAGE"]["NEW_TOPIC"] == "Y")
{
	$arResult["SHOW_PANEL_NEW_TOPIC"] = "Y";
	$arResult["ForumPrintIconsList"] = ForumPrintIconsList(7, $arResult["TOPIC"]["ICON"]);
	if ($arParams["SHOW_VOTE"] == "Y")
	{
		$arResult["SHOW_PANEL_VOTE"] = "Y";
	}
}

if ($arParams["FORUM"]["ALLOW_SMILES"]=="Y")
{
	$arResult["ForumPrintSmilesList"] = ForumPrintSmilesList($arParams["SMILE_TABLE_COLS"], LANGUAGE_ID);
	$arResult["SMILES"] = CForumSmile::getSmiles("S", LANGUAGE_ID);
}

if ($arResult["SHOW_SUBSCRIBE"] == "Y")
{
	$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "SITE_ID" => SITE_ID);
	$db_res = CForumSubscribe::GetList(array(), $arFields);
	$arResult["TOPIC_SUBSCRIBE"] = "N";
	$arResult["FORUM_SUBSCRIBE"] = "N";
	if ($db_res)
	{
		while ($res = $db_res->Fetch())
		{
			if (intval($res["TOPIC_ID"]) <= 0):
				$arResult["FORUM_SUBSCRIBE"] = "Y";
			elseif($res["TOPIC_ID"] == $arParams["TID"]):
				$arResult["TOPIC_SUBSCRIBE"] = "Y";
			endif;
		}
	}
}

if ($arResult["SHOW_PANEL_ATTACH_IMG"] == "Y")
{
	$fileIds = array_keys($arResult["MESSAGE"]["FILES"]);
	$arResult["MESSAGE"]["FILES"] = [];
	foreach ($fileIds as $key)
	{
		if ($file = CFile::GetFileArray($key))
		{
			$arResult["MESSAGE"]["FILES"][$key] = $file;
		}
	}
/************** For custom component *******************************/
	$arResult["MESSAGE"]["ATTACH_IMG_FILE"] = false;
	if ($arResult["MESSAGE"]["ATTACH_IMG"] <> '')
	{
		$arResult["MESSAGE"]["ATTACH_IMG_FILE"] = $arResult["MESSAGE"]["FILES"][$arResult["MESSAGE"]["ATTACH_IMG"]];
		if ($arResult["MESSAGE"]["ATTACH_IMG_FILE"])
			$arResult["MESSAGE"]["ATTACH_IMG"] = CFile::ShowImage($arResult["MESSAGE"]["ATTACH_IMG_FILE"], 200, 200, "border=0");
	}
/************** For custom component/*******************************/
}

$arResult["MESSAGE"]["CAPTCHA_CODE"] = "";
if (!$USER->IsAuthorized() && $arParams["FORUM"]["USE_CAPTCHA"]=="Y")
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
/*******************************************************************/
$arResult["SUBMIT"] = GetMessage("FPF_EDIT");
$arResult["str_HEADER"] = GetMessage("FPF_EDIT_FORM");
if ($arParams["MESSAGE_TYPE"]=="NEW"):
	$arResult["SUBMIT"] = GetMessage("FPF_SEND");
	$arResult["str_HEADER"] = GetMessage("FPF_CREATE_IN_FORUM")." ".$arParams["FORUM"]["NAME"];
elseif ($arParams["MESSAGE_TYPE"]=="REPLY"):
	$arResult["SUBMIT"] = GetMessage("FPF_REPLY");
	$arResult["str_HEADER"] = GetMessage("FPF_REPLY_FORM");
endif;
/************** For custom component *******************************/
foreach ($arResult["MESSAGE"] as $key => $val):
	$arResult["MESSAGE"][$key] = htmlspecialcharsEx($val);
	$arResult["MESSAGE"]["~".$key] = $val;
	$arResult["str_".$key] = htmlspecialcharsEx($val);
	$arResult["~str_".$key] = $val;
endforeach;
foreach ($arResult["TOPIC"] as $key => $val):
	$arResult["TOPIC"][$key] = htmlspecialcharsbx($val);
	$arResult["TOPIC"]["~".$key] = $val;
	$arResult["str_".$key] = htmlspecialcharsbx($val);
	$arResult["~str_".$key] = $val;
endforeach;
if (!empty($arResult["MESSAGE"]["FILES"]))
{
	foreach ($arResult["MESSAGE"]["FILES"] as &$file)
	{
		foreach ($file as $k => $val)
		{
			if (is_string($val))
			{
				$file[$k] = htmlspecialcharsbx($val);
			}
		}
	}
}
foreach ($arResult["QUESTIONS"] as $key => $arQuestion):
	foreach ($arQuestion as $keyq => $valq):
		if (is_string($valq)):
			$arResult["QUESTIONS"][$key][$keyq] = htmlspecialcharsbx($valq);
			$arResult["QUESTIONS"][$key]["~".$keyq] = $valq;
		elseif (is_array($valq) && $keyq == "ANSWERS"):
			foreach ($valq as $keyAnswer => $valAnswer):
				foreach ($valAnswer as $k => $v):
					$arResult["QUESTIONS"][$key]["ANSWERS"][$keyAnswer][$k] = htmlspecialcharsbx($v);
					$arResult["QUESTIONS"][$key]["~ANSWERS"][$keyAnswer][$k] = $v;
				endforeach;
			endforeach;
		endif;
	endforeach;
endforeach;
$arResult["list"] = $arResult["URL"]["LIST"];
$arResult["read"] = $arResult["URL"]["READ"];
$arResult["UserPermission"] = $arResult["PERMISSION"];
$arResult["FID"] = $arParams["FID"];
$arResult["TID"] = $arParams["TID"];
$arResult["MID"] = $arParams["MID"];
$arResult["FORUM"] = $arParams["FORUM"];
$arResult["MESSAGE_TYPE"] = $arParams["MESSAGE_TYPE"];
$arResult["PAGE_NAME"] = $arParams["PAGE_NAME"];
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["VIEW"] = ($arParams["VIEW"] != "Y" ? "N" : "Y");
$arResult["SHOW_CLOSE_ALL"] = "N";
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || $arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y")
	$arResult["SHOW_CLOSE_ALL"] = "Y";
$arResult["sessid"] = bitrix_sessid_post();
/********************************************************************
				Data
********************************************************************/
	$this->IncludeComponentTemplate();
?>