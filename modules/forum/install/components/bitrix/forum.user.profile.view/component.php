<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if (!function_exists("ForumUrlExtractTmp"))
{
	function ForumUrlExtractTmp($s)
	{
		$x = 0;
		while (mb_strpos(",}])>.", mb_substr($s, -1, 1)) !== false)
		{
			$s2 = mb_substr($s, -1, 1);
			$s = mb_substr($s, 0, mb_strlen($s) - 1);
		}
		return "<a href=\"".$s."\" target=\"_blank\">".$s."</a>".$s2;
	}
}
if (!function_exists("ForumNumberRusEnding"))
{
	function ForumNumberRusEnding($num)
	{
		if (LANGUAGE_ID == "ru")
		{
			if (mb_strlen($num) > 1 && mb_substr($num, mb_strlen($num) - 2, 1) == "1")
			{
				return GetMessage("F_ENDING_OV");
			}
			else
			{
				$c = intval(mb_substr($num, mb_strlen($num) - 1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return GetMessage("F_ENDING_OV");
				elseif ($c==1)
					return "";
				else
					return GetMessage("F_ENDING_A");
			}
		}
		else
		{
			if (intval($num)>1)
				return "s";
			return "";
		}
	}
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["UID"] = trim($arParams["UID"] == '' ? $_REQUEST["UID"] : $arParams["UID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"profile" => "PAGE_NAME=profile&UID=#UID#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"message_send" => "PAGE_NAME=message_send&TYPE=#TYPE#&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#");
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
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = (isset($arParams["SHOW_FORUM_ANOTHER_SITE"]) && $arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	$arParams["DATE_FORMAT"] = trim($arParams["DATE_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : '#NAME# #LAST_NAME#');
	if($arParams["DATE_TIME_FORMAT"] == '')
		$arParams["DATE_TIME_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	if($arParams["DATE_FORMAT"] == '')
		$arParams["DATE_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT"));
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/
$parser = new forumTextParser();
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arResult["USER"] = array();
$arResult["FORUM_USER"] = array();
$bUserFound = $ar_res = false;
if (!empty($arParams["UID"]))
{
	false;
	$db_res = CUser::GetByID(intval($arParams["UID"]));
	if (!($ar_res = $db_res->Fetch())):
		$db_res = CUser::GetByLogin($arParams["UID"]);
		$ar_res = $db_res->Fetch();
		$arParams["UID"] = $ar_res["ID"];
	endif;
	$bUserFound = !empty($ar_res) && is_array($ar_res);
}
if (!$bUserFound):
	CHTTP::SetStatus("404 Not Found");
	ShowError(empty($arParams["UID"]) ? GetMessage("F_NO_UID") : GetMessage("F_NO_DUSER", array("#UID#" => htmlspecialcharsbx($arParams["UID"]))));
	return false;
endif;

foreach ($ar_res as $key => $val):
	$arResult["USER"]["~".$key] = $val;
	$arResult["USER"][$key] = (is_string($val) ? $parser->wrap_long_words(htmlspecialcharsex(trim($val))) : $val);
endforeach;

$arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"] = CForumFormat::FormatDate($arResult["USER"]["~PERSONAL_BIRTHDAY"],
	CLang::GetDateFormat("SHORT"), $arParams["DATE_FORMAT"]);
$arResult["FORUM_USER"] = CForumUser::GetByUSER_ID($arParams["UID"]);
$arResult["FORUM_USER"] = (empty($arResult["FORUM_USER"]) ? array() : $arResult["FORUM_USER"]);
foreach ($arResult["FORUM_USER"] as $key => $val):
	$arResult["FORUM_USER"]["~".$key] = $val;
	$arResult["FORUM_USER"][$key] = (is_string($val) ? $parser->wrap_long_words(htmlspecialcharsbx($val)) : $val);
endforeach;
/********************************************************************
				Default values
********************************************************************/
$strErrorMessage = "";
$strOKMessage = "";

$arParams["UID"] = isset($arParams["UID"]) ? intval($arParams["UID"]) : null;
$arResult["FID"] = isset($arParams["FID"]) ? intval($_REQUEST["FID"]) : null;
$arResult["TID"] = isset($arParams["TID"]) ? intval($_REQUEST["TID"]) : null;
$arResult["TITLE_SEO"] = isset($_REQUEST["TITLE_SEO"]) ? trim($_REQUEST["TITLE_SEO"]) : null;
$arResult["MID"] = isset($_REQUEST["MID"]) ? intval($_REQUEST["MID"]) : null;
$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
$arResult["IsAdmin"] = CForumUser::IsAdmin() ? "Y" : "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = (isset($_REQUEST["result"]) && $_REQUEST["result"] == "message_send" ? GetMessage("F_OK_MESSAGE_SEND") : "");
$arResult["FORUMS"] = array();

$arResult["SHOW_BACK_URL"] = (($arResult["FID"] > 0 || $arResult["TID"] > 0 || $arResult["MID"] > 0) ? "Y" : "N");
$arResult["SHOW_USER_INFO"] = "Y"; // out of date params
$arResult["SHOW_EDIT_PROFILE"] = ($USER->IsAuthorized() && ((intval($USER->GetID()) == $arParams["UID"] && $USER->CanDoOperation('edit_own_profile')) ||
	$USER->IsAdmin()) ? "Y" : "N");
$arResult["SHOW_VOTES"] = ((COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $USER->IsAuthorized()
	&& (CForumUser::IsAdmin() || intval($USER->GetParam("USER_ID"))!=$arParams["UID"])) ? "Y" : "N");
$arResult["SHOW_RANK"] = 'Y';//(COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" ? "Y" : "N");
/******************************************************************/
$arResult["SHOW_ICQ"] = ((COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : (($arParams["SEND_ICQ"] <= "A" || ($arParams["SEND_ICQ"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y"));
$arResult["SHOW_MAIL"] = $arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");;
/******************************************************************/
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"]));
$arResult["URL"] = array(
	"PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE"], array("UID" => $arParams["UID"])),
	"PROFILE_VIEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"~PROFILE_VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"USER_EMAIL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $arParams["UID"], "TYPE"=>"mail")),
	"USER_ICQ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $arParams["UID"], "TYPE"=>"icq")),
	"USER_PM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"],
		array("FID" => 1, "MID" => 0, "UID" => $arParams["UID"], "mode"=>"new")),
	"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
		array("FID" => $arResult["FID"], "TID" => $arResult["TID"], "TITLE_SEO" => $arResult["TITLE_SEO"], "MID" => $arResult["MID"])),
	"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
		array("FID" => $arResult["FID"], "TID" => $arResult["TID"], "TITLE_SEO" => $arResult["TITLE_SEO"], "MID" => $arResult["MID"])),
	"USER_POSTS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"all")),
	"USER_POSTS_MEMBER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"lt")),
	"USER_POSTS_AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"lta")),
	"SUBSCRIBE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("UID" => $arParams["UID"])),
	"~SUBSCRIBE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("UID" => $arParams["UID"])));
$arResult["URL"]["VOTE"] = $arResult["URL"]["PROFILE_VIEW"];
/************** For custom ****************************************/
$arResult["profile"] = $arResult["URL"]["PROFILE"];
$arResult["profile_view"] = $arResult["URL"]["PROFILE_VIEW"];
$arResult["message_mail"] = $arResult["URL"]["USER_EMAIL"];
$arResult["message_icq"] = $arResult["URL"]["USER_ICQ"];
$arResult["read"] = $arResult["URL"]["TOPIC"];
$arResult["message"] = $arResult["URL"]["MESSAGE"];
$arResult["pm_edit"] = $arResult["URL"]["USER_PM"];
$arResult["user_post_lta"] = $arResult["URL"]["USER_POSTS_AUTHOR"];
$arResult["user_post_lt"] = $arResult["URL"]["USER_POSTS_MEMBER"];
$arResult["user_post_all"] = $arResult["URL"]["USER_POSTS"];
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Votings ********************************************/
if ($arResult["SHOW_VOTES"] == "Y"):
	if ($_GET["VOTE_USER"] == "Y" && $USER->IsAuthorized() && check_bitrix_sessid()):
		ForumVote4User($arParams["UID"], $_GET["VOTES"], ($_GET["CANCEL_VOTE"] <> '' ? True : False), $strErrorMessage, $strOKMessage);
		if (empty($strErrorMessage)):
			LocalRedirect($arResult["URL"]["~PROFILE_VIEW"]);
		endif;
	endif;

	$strNotesText = "";
	$bCanVote = CForumUser::IsAdmin();
	$bCanUnVote = False;
	$arUserRank = CForumUser::GetUserRank(intval($USER->GetParam("USER_ID")));
	$arUserPoints = CForumUserPoints::GetByID(intval($USER->GetParam("USER_ID")), $arParams["UID"]);
	if ($arUserPoints)
	{
		$bCanUnVote = True;
		$strNotesText .= str_replace("#POINTS#", $arUserPoints["POINTS"], str_replace("#END#",
			ForumNumberRusEnding($arUserPoints["POINTS"]), GetMessage("F_ALREADY_VOTED1"))).". \n";
		if (CForumUser::IsAdmin())
		{
			$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
		}
		elseif (intval($arUserPoints["POINTS"]) < intval($arUserRank["VOTES"]))
		{
			$bCanVote = True;
			$strNotesText .= str_replace("#POINTS#", (intval($arUserRank["VOTES"])-intval($arUserPoints["POINTS"])), str_replace("#END#",
				ForumNumberRusEnding((intval($arUserRank["VOTES"])-intval($arUserPoints["POINTS"]))), GetMessage("F_ALREADY_VOTED3")));
		}
	}
	elseif (intval($arUserRank["VOTES"]) > 0 || CForumUser::IsAdmin())
	{

		$bCanVote = True;
		$strNotesText .= GetMessage("F_NOT_VOTED");
		if (!CForumUser::IsAdmin())
		{
			$strNotesText .= str_replace("#POINTS#", $arUserRank["VOTES"], str_replace("#END#",
				ForumNumberRusEnding($arUserRank["VOTES"]), GetMessage("F_NOT_VOTED1"))).". \n";
		}
		else
		{
			$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
		}
	}

	$arResult["bCanVote"] = $bCanVote;
	$arResult["bCanUnVote"] = $bCanUnVote;
	$arResult["titleVote"] = $strNotesText;
	$arResult["SHOW_VOTES"] = ($strNotesText <> '' || $bCanVote || $bCanUnVote ? "Y" : "N");
	if (CForumUser::IsAdmin() && $bCanVote)
		$arResult["VOTES"] = intval($arUserRank["VOTES"]);
	if ($bCanUnVote):
		$arResult["VOTE_ACTION"] = "UNVOTE";
		$arResult["URL"]["~VOTE"] = $APPLICATION->GetCurPageParam("CANCEL_VOTE=Y&VOTE_USER=Y", array("sessid", "VOTE_USER", "VOTES", "CANCEL_VOTE"));
	else:
		$arResult["VOTE_ACTION"] = "VOTE";
		$arResult["URL"]["~VOTE"] = $APPLICATION->GetCurPageParam("VOTE_USER=Y", array("sessid", "VOTE_USER", "VOTES", "CANCEL_VOTE"));
	endif;
	$arResult["URL"]["VOTE"] = $arResult["URL"]["~VOTE"]."&".bitrix_sessid_get();
endif;

/*******************************************************************/
if (!empty($arResult["FORUM_USER"]["DATE_REG"]))
	$arResult["FORUM_USER"]["DATE_REG_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"],
		MakeTimeStamp($arResult["FORUM_USER"]["DATE_REG"], CSite::GetDateFormat()));
if (!empty($arResult["FORUM_USER"]["LAST_VISIT"]))
	$arResult["FORUM_USER"]["LAST_VISIT_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"],
		MakeTimeStamp($arResult["FORUM_USER"]["LAST_VISIT"], CSite::GetDateFormat()));
$arResult["~SHOW_NAME"] = CForumUser::GetFormattedNameByUserID(
	$arResult["USER"]["ID"],
	$arParams["NAME_TEMPLATE"],
	array(
		"SHOW_NAME" => $arResult["FORUM_USER"]["SHOW_NAME"],
		"LOGIN" => $arResult["USER"]["~LOGIN"],
		"NAME" => $arResult["USER"]["~NAME"],
		"SECOND_NAME" => $arResult["USER"]["~SECOND_NAME"],
		"LAST_NAME" => $arResult["USER"]["~LAST_NAME"]
	));
$arResult["SHOW_NAME"] = htmlspecialcharsbx($arResult["~SHOW_NAME"]);

$arResult["SHOW_EDIT_PROFILE_TITLE"] = (intval($USER->GetID())!=$arParams["UID"]) ? GetMessage("F_EDIT_THIS_PROFILE") : GetMessage("F_EDIT_YOUR_PROFILE");
$arResult["SHOW_EDIT_PROFILE_TITLE_BOTTOM"] = ((intval($USER->GetID())!=$arParams["UID"]) ? GetMessage("F_TO_CHANGE2") : GetMessage("F_TO_CHANGE3"))." ".GetMessage("F_TO_CHANGE4");
if ($arResult["USER"]["PERSONAL_WWW"] <> '')
{
	$arResult["USER"]["PERSONAL_WWW_FORMATED"] = $arResult["USER"]["PERSONAL_WWW"];
	$strBValueTmp = mb_substr($arResult["USER"]["PERSONAL_WWW_FORMATED"], 0, 6);
	if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
		$arResult["USER"]["PERSONAL_WWW_FORMATED"] = "http://".$arResult["USER"]["PERSONAL_WWW_FORMATED"];
	$arResult["USER"]["PERSONAL_WWW"] = "<noindex><a rel=\"nofollow\" href=\"".$arResult["USER"]["PERSONAL_WWW_FORMATED"]."\" target=\"_blank\">".$arResult["USER"]["PERSONAL_WWW_FORMATED"]."</a></noindex>";
}

if ($arResult["USER"]["WORK_WWW"] <> '')
{
	$arResult["USER"]["WORK_WWW_FORMATED"] = $arResult["USER"]["WORK_WWW"];
	$strBValueTmp = mb_substr($arResult["USER"]["WORK_WWW_FORMATED"], 0, 6);
	if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
		$arResult["USER"]["WORK_WWW_FORMATED"] = "http://".$arResult["USER"]["WORK_WWW_FORMATED"];

	$arResult["USER"]["WORK_WWW"] = "<noindex><a rel=\"nofollow\" href=\"".$arResult["USER"]["WORK_WWW_FORMATED"]."\" target=\"_blank\">".$arResult["USER"]["WORK_WWW_FORMATED"]."</a></noindex>";
}

if ($arResult["USER"]["PERSONAL_GENDER"]=="M")
	$arResult["USER"]["PERSONAL_GENDER"] = GetMessage("F_SEX_MALE");
elseif ($arResult["USER"]["PERSONAL_GENDER"]=="F")
	$arResult["USER"]["PERSONAL_GENDER"] = GetMessage("F_SEX_FEMALE");

$arResult["USER"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["USER"]["PERSONAL_COUNTRY"]);
if (!empty($arResult["USER"]["PERSONAL_LOCATION"]) && !empty($arResult["USER"]["PERSONAL_CITY"]))
	$arResult["USER"]["PERSONAL_LOCATION"] .= ", ";
$arResult["USER"]["PERSONAL_LOCATION"] .= $arResult["USER"]["PERSONAL_CITY"];

$arResult["USER"]["WORK_LOCATION"] = GetCountryByID($arResult["USER"]["WORK_COUNTRY"]);
if ($arResult["USER"]["WORK_LOCATION"] <> '' && $arResult["USER"]["WORK_CITY"] <> '')
	$arResult["USER"]["WORK_LOCATION"] .= ", ";
$arResult["USER"]["WORK_LOCATION"] .= $arResult["USER"]["WORK_CITY"];


$arResult["FORUM_USER"]["INTERESTS"] = $parser->convert(
	$arResult["FORUM_USER"]["INTERESTS"],
	array(
		"HTML" => "N",
		"ANCHOR" => "Y",
		"BIU" => "Y",
		"IMG" => "Y",
		"VIDEO" => "Y",
		"LIST" => "Y",
		"QUOTE" => "Y",
		"CODE" => "Y",
		"FONT" => "Y",
		"SMILES" => "N",
		"NL2BR" => "Y",
		"TABLE" => "N",
		"ALIGN" => "N",
	));

$arResult["FORUM_USER"]["AVATAR"] = "";
if (!empty($arResult["FORUM_USER"]["~AVATAR"])):
	$arResult["FORUM_USER"]["AVATAR_FILE"] = CFile::GetFileArray($arResult["FORUM_USER"]["~AVATAR"]);
	if ($arResult["FORUM_USER"]["AVATAR_FILE"] !== false)
		$arResult["FORUM_USER"]["AVATAR"] = CFile::ShowImage($arResult["FORUM_USER"]["AVATAR_FILE"],
			COption::GetOptionString("forum", "avatar_max_width", 100),
			COption::GetOptionString("forum", "avatar_max_height", 100), "border=0", "", true);
endif;
$arResult["USER"]["PERSONAL_PHOTO"] = "";
if (!empty($arResult["USER"]["~PERSONAL_PHOTO"])):
	$arResult["USER"]["PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($arResult["USER"]["~PERSONAL_PHOTO"]);
	if ($arResult["USER"]["PERSONAL_PHOTO_FILE"] !== false)
		$arResult["USER"]["PERSONAL_PHOTO"] = CFile::ShowImage($arResult["USER"]["PERSONAL_PHOTO_FILE"], 200, 200, "border=0 alt=\"\"", "", true);
endif;
/************** Getting User rank **********************************/
$arResult["USER_RANK"] = ""; $arResult["USER_RANK_CODE"] = "";
$arFilter = array();
if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin())
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID_RANGE"]))
	$arFilter["@ID"] = $arParams["FID_RANGE"];
if (!CForumUser::IsAdmin()):
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
	$arFilter["ACTIVE"] = "Y";
endif;
$arUserPerm = array();
$db_res = CForumNew::GetList(array(), $arFilter);
if ($db_res && ($res = $db_res->GetNext())):
	$arUserGroup = CUser::GetUserGroup($arParams["UID"]);
	do
	{
		$arResult["FORUMS"][$res["ID"]] = $res;
		$arUserPerm[] = CForumNew::GetUserPermission($res["ID"], $arUserGroup);
	}while ($res = $db_res->GetNext());
endif;

rsort($arUserPerm);
$arRank = CForumUser::GetUserRank($arParams["UID"], LANGUAGE_ID);
list($arResult["USER_RANK_CODE"], $arResult["USER_RANK"]) = ForumGetUserForumStatus($arParams["UID"], $arUserPerm[0], array("Rank" => $arRank));
$arResult["SHOW_POINTS"] = "N";
$arResult["arRank"] = array_merge((is_array($arRank) ? $arRank : array()), array("NAME" => $arResult["USER_RANK"]));
if ($USER->IsAuthorized() && (CForumUser::IsAdmin() || intval($USER->GetID()) == $arParams["UID"]))
{
	$arResult["SHOW_POINTS"] = "Y";
	$arResult["USER_POINTS"] = (!empty($arRank["VOTES"]) ? intval($arRank["VOTES"]) : GetMessage("F_NO_VOTES"));
}
/*******************************************************************/
$arResult["arTopic"] = "N";
if (!empty($arResult["FORUMS"]))
{
	$db_res = CForumUser::UserAddInfo(
		array("LAST_POST"=>"DESC"),
		array("AUTHOR_ID" => $arParams["UID"], "@FORUM_ID" => array_keys($arResult["FORUMS"])),
		"topics");
	if ($db_res && $res = $db_res->GetNext())
	{
		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
		$arResult["arTopic"] = array_merge(
			$res,
			array(
				"read" => CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_MESSAGE"],
					array(
						"FID" => $res["FORUM_ID"],
						"TID" => $res["TOPIC_ID"],
						"TITLE_SEO" => $res["TITLE_SEO"],
						"MID" => intval($res["LAST_POST"])
					)
				)."#message".intval($res["LAST_POST"])));
	}
}
/************** User properties ************************************/
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
if (!empty($arParams["USER_PROPERTY"]))
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $arParams["UID"], LANGUAGE_ID);
	if (count($arParams["USER_PROPERTY"]) > 0)
	{
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
				continue;
			$arUserField["~EDIT_FORM_LABEL"] = (!empty($arUserField["EDIT_FORM_LABEL"]) ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]);
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsbx($arUserField["~EDIT_FORM_LABEL"]);
			$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
		}
	}
	if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
		$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
}
/*******************************************************************/
$arResult["ERROR_MESSAGE"] .= $strErrorMessage;
$arResult["OK_MESSAGE"] .= $strOKMessage;
/*******************************************************************/
foreach ($arResult["USER"] as $key => $val):
	if (mb_substr($key, 0, 1) == "~")
		$arResult["~f_".mb_substr($key, 1)] = $val;
	else
		$arResult["f_".$key] = $val;
endforeach;
foreach ($arResult["FORUM_USER"] as $key => $val):
	if (mb_substr($key, 0, 1) == "~")
		$arResult["~fu_".mb_substr($key, 1)] = $val;
	else
		$arResult["fu_".$key] = $val;
endforeach;
/********************************************************************
				Data
********************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem($arResult["~SHOW_NAME"]);
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arResult["SHOW_NAME"]);
?>
