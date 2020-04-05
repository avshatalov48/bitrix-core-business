<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!CModule::IncludeModule("socialnetwork")):
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return false;
elseif (intVal($arParams["FID"]) <= 0):
	ShowError(GetMessage("F_FID_IS_EMPTY"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal($arParams["FID"]);
	$arParams["TID"] = 0;
	$arParams["MID"] = (intVal($arParams["MID"]) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MESSAGE_TYPE"] = (empty($arParams["MESSAGE_TYPE"]) ? $_REQUEST["MESSAGE_TYPE"] : $arParams["MESSAGE_TYPE"]);
	$arParams["MESSAGE_TYPE"] = ($arParams["MESSAGE_TYPE"] != "EDIT" ? "NEW" : "EDIT");
	
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"topic_list" => "PAGE_NAME=topic_list",
			"message" => "PAGE_NAME=message&TID=#TID#&MID=#MID#", 
			"profile_view" => "PAGE_NAME=user&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat() : $arParams["NAME_TEMPLATE"];
	$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icons/" : $arParams["PATH_TO_ICON"]);
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_CALL"] == "Y") ? "Y" : "N");
	$arParams["VOTE_CHANNEL_ID"] = intVal($arParams["VOTE_CHANNEL_ID"]);
	$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && $arParams["VOTE_CHANNEL_ID"] > 0 && IsModuleInstalled("vote") ? "Y" : "N");
	$arParams["VOTE_GROUP_ID"] = (!is_array($arParams["VOTE_GROUP_ID"]) || empty($arParams["VOTE_GROUP_ID"]) ? array() : $arParams["VOTE_GROUP_ID"]);
	if (!is_array($arParams['VOTE_UNIQUE'])) $arParams['VOTE_UNIQUE'] = array();
	if (!(isset($arParams['VOTE_UNIQUE_IP_DELAY']) && strlen(trim($arParams['VOTE_UNIQUE_IP_DELAY'])) > 0 && strpos($arParams['VOTE_UNIQUE_IP_DELAY'], " ") !== false))
		$arParams['VOTE_UNIQUE_IP_DELAY'] = "10 D";
	$arParams["AUTOSAVE"] = CForumAutosave::GetInstance();
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
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
//************** SocNet Activity ***********************************/
if (($arParams["MODE"] == "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")) ||
	($arParams["MODE"] != "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))):
	ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
	return false;
endif;

//************** Forum *********************************************/
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	$arResult["TOPIC"] = array();
	$arResult["MESSAGE"] = array();
	$arParams["PERMISSION_ORIGINAL"] = ForumCurrUserPermissions($arParams["FID"]);
	$arParams["PERMISSION"] = "A";
	$arResult["ERROR_MESSAGE"] = "";
	$arResult["OK_MESSAGE"] = "";

	$arError = array();
	$arNote = array();
	$user_id = $USER->GetID();
//************** Permission ****************************************/

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

if (empty($arResult["FORUM"]))
{
	CHTTP::SetStatus("404 Not Found");
	$arError[] = array(
		"id" => "forum_is_lost", 
		"text" => GetMessage("F_FID_IS_LOST"));
}
elseif ($arParams["MODE"] == "GROUP")
{
	if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "full", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "Y";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "M";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "answer", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "I";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "view", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "E";
}
else
{
	if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "full", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "Y";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "M";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "answer", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "I";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "view", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "E";
}

if ($arParams["SHOW_VOTE"] == "Y")
{
	CModule::IncludeModule("vote");
	$arParams["SHOW_VOTE"] = ( ($arParams["PERMISSION"] == "A") ? "N" : $arParams["SHOW_VOTE"]);
}
/************** Message ********************************************/
if ($arParams["MESSAGE_TYPE"] == "EDIT")
{
	$res = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y"));
	if (!is_array($res) || empty($res))
		$arError[] = array(
			"id" => "mid_is_lost",
			"text" => GetMessage("F_MID_IS_LOST")
		);
	elseif ($arParams["MODE"] != "GROUP" && $res["FORUM_ID"] != $arParams["FID"])
		$arError[] = array(
			"id" => "mid_is_lost",
			"text" => GetMessage("F_MID_IS_LOST_IN_FORUM")
		);
	elseif (($arParams["MODE"] == "GROUP" && $res["TOPIC_INFO"]["SOCNET_GROUP_ID"] == $arParams["SOCNET_GROUP_ID"]) ||
		($arParams["MODE"] != "GROUP" && $res["TOPIC_INFO"]["OWNER_ID"] == $arParams["USER_ID"]))
	{
		$arResult["MESSAGE"] = $res;
		$arParams["TID"] = $res["TOPIC_INFO"]["ID"];
		$arResult["TOPIC"] = $res["TOPIC_INFO"];
		$arResult["TOPIC_FILTER"] = CForumTopic::GetByID($arParams["TID"]);

		if ($arParams["SHOW_VOTE"] == "Y" && $arResult["MESSAGE"]["PARAM1"] == "VT" && intVal($arResult["MESSAGE"]["PARAM2"]) > 0)
		{
			$db_res = CVoteQuestion::GetListEx(
				array("ID" => "ASC"),
				array("CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], "VOTE_ID" => $arResult["MESSAGE"]["PARAM2"]));
			if ($db_res && $res = $db_res->Fetch())
			{
				do {
					$arResult["~QUESTIONS"][$res["ID"]] = $res + array("ANSWERS" => array());
				} while ($res = $db_res->Fetch());
			}
			if (!empty($arResult["~QUESTIONS"]))
			{
				$db_res = CVoteAnswer::GetListEx(array("ID" => "ASC"),
					array("VOTE_ID" => $arResult["MESSAGE"]["PARAM2"]));
				if ($db_res && $res = $db_res->Fetch())
				{
					do
					{
						if (is_set($arResult["~QUESTIONS"], $res["QUESTION_ID"]))
							$arResult["~QUESTIONS"][$res["QUESTION_ID"]]["ANSWERS"][$res["ID"]] = $res;
					}
					while ($res = $db_res->Fetch());
				}
			}
			$arResult["QUESTIONS"] = $arResult["~QUESTIONS"];
		}
	}
	else
		$arError[] = array(
			"id" => "mid_is_lost",
			"text" => GetMessage("F_MID_IS_LOST")
		);
}
/************** Permission *****************************************/
	if ($arParams["MESSAGE_TYPE"]=="NEW" && !CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), false, $arParams["PERMISSION"])):
		$arError[] = array(
			"id" => "acces denied", 
			"text" => GetMessage("F_NO_NPERMS"));
	elseif ($arParams["MESSAGE_TYPE"]=="EDIT" && !CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"])):
		$arError[] = array(
			"id" => "acces denied", 
			"text" => GetMessage("F_NO_EPERMS"));
	endif;
/************** Fatal Errors ***************************************/
if (!empty($arError))
{
	$e = new CAdminException($arError);
	$res = $e->GetString();
	ShowError($res);
	return false;
}
/*******************************************************************/
$strErrorMessage = ""; $strOKMessage = "";
$bVarsFromForm = false;
$arResult["VIEW"] = ((strToUpper($_REQUEST["MESSAGE_MODE"]) == "VIEW" && $_SERVER["REQUEST_METHOD"] == "POST") ? "Y" : "N");
$_REQUEST["FILES"] = (is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array());
$_REQUEST["FILES_TO_UPLOAD"] = (is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array());

$arResult["MESSAGE_VIEW"] = array();
$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);
$arAllow["SMILES"] = ($_POST["USE_SMILES"] == "Y" ? $arAllow["SMILES"] : "N");
/*******************************************************************/
$arResult["URL"] = array(
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "UID" => $arParams["USER_ID"])), 
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "UID" => $arParams["USER_ID"])), 
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
		array("UID" => $arParams["USER_ID"], "FID" => $arParams["FID"], "TID" => $arParams["TID"], 
			"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))), 
	"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
		array("UID" => $arParams["USER_ID"], "FID" => $arParams["FID"], "TID" => $arParams["TID"], 
			"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))));
/*******************************************************************/
$parser = new forumTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
/********************************************************************
				/Default params
********************************************************************/

ForumSetLastVisit($arParams["FID"], $arParams["TID"]);

/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$TID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["TID"]);
	$MID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["MID"]);

	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad sessid", 
			"text" => GetMessage("F_ERR_SESS_FINISH"));
	}
	elseif (!in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("Y", "A", "F")) && (!empty($_FILES) || !empty($_REQUEST["FILES"]))) 
	{
		$error = false;
		if (!empty($_FILES))
		{
			foreach($_FILES as $name => $file)
			{
				if (strpos($name, "FILE_NEW_") === 0 && empty($file["error"]) && !empty($file["name"]))
				{
					$error = true;
					break;
				}
			}
		}
		if ($error || !empty($_REQUEST["FILES"]))
		{
			$arError[] = array(
				"id" => "bad files",
				"text" => GetMessage("F_ERRRO_FILE_NOT_UPLOAD"));
			unset($_REQUEST["FILES"]);
		}
	}

	if (!empty($arError)) {}
	elseif ($arResult["VIEW"] == "N")
	{
		$arFieldsG = array(
			"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
			"USE_SMILES" => $_REQUEST["USE_SMILES"],
			"OWNER_ID" => $arParams["USER_ID"],
			"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"], 
			"PERMISSION_EXTERNAL" => $arParams["PERMISSION"]);

		if ($arParams["SHOW_VOTE"] == "Y" && (!empty($_REQUEST["QUESTION"]) || !empty($_REQUEST["QUESTION_ID"])))
		{
			$VOTE_ID = ($arResult["MESSAGE"]["PARAM1"] == 'VT' ? intVal($arResult["MESSAGE"]["PARAM2"]) : 0);
			$arVote = array(
				"CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"],
				"TITLE" => $_REQUEST["TITLE"],
				"QUESTIONS" => array());
			if ($VOTE_ID <= 0):
				$arVote["DATE_START"] = GetTime(CForumNew::GetNowTime(), "FULL");
				$arVote["DATE_END"] = GetTime(MakeTimeStamp($_REQUEST['DATE_END']), "FULL");
			else:
				$arVote["DATE_END"] = $_REQUEST['DATE_END'];
			endif;

			$arQuestions = $arResult["~QUESTIONS"];
			$_REQUEST["QUESTION"] = (is_array($_REQUEST["QUESTION"]) ? $_REQUEST["QUESTION"] : array());
			foreach ($_REQUEST["QUESTION"] as $key => $val)
			{
				$res = array(
					"QUESTION" => trim($val),
					"MULTI" => ($_REQUEST["MULTI"][$key] == "Y" ? "Y" : "N"),
					"DEL" => ($_REQUEST["QUESTION_DEL"][$key] == "Y" ? "Y" : "N"),
					"ANSWERS" => array());
				$id = intval($_REQUEST["QUESTION_ID"][$key]);
				if ($id > 0 && is_set($arQuestions, $id))
					$res["ID"] = $id;
				elseif ($res["DEL"] == "Y")
					continue;

				$arAnswers = (is_array($arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"]) ?
					$arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"] : array());
				foreach ($_REQUEST["ANSWER"][$key] as $keya => $vala)
				{
					$id = intval($_REQUEST["ANSWER_ID"][$key][$keya]);
					$resa = array(
						"ID" => ($id > 0 && is_set($arAnswers, $id) ? $id : false),
						"DEL" => ($_REQUEST["ANSWER_DEL"][$key][$keya] == "Y" ? "Y" : "N"),
						"MESSAGE" => trim($vala),
						"FIELD_TYPE" => ($res["MULTI"] == "Y" ? 1 : 0));
					if (!$resa["ID"] && ($resa["DEL"] == "Y" || empty($resa["MESSAGE"])))
						continue;
					unset($arAnswers[$resa["ID"]]);
					$res["ANSWERS"][] = $resa;
				}

				foreach ($arAnswers as $keya => $vala)
					$res["ANSWERS"][] = array_merge($vala, array("DEL" => "Y"));

				if (empty($res["ANSWERS"]) && empty($res["QUESTION"]) && intVal($res["ID"]) <= 0)
					continue;

				unset($arQuestions[$res["ID"]]);
				$arVote["QUESTIONS"][] = $res;
			}
			if (!empty($arQuestions))
				foreach ($arQuestions as $key => $val)
					$arVote["QUESTIONS"][] = array_merge($val, array("DEL" => "Y"));

			if (!empty($arVote["QUESTIONS"]))
			{
				$uniqType = 0;
				foreach ($arParams['VOTE_UNIQUE'] as $k => $v)
					$uniqType |= intval($v);
				$uniqType += 5;

				list($uniqDelay, $uniqDelayType) = explode(" ", $arParams['VOTE_UNIQUE_IP_DELAY']);
				$uniqDelay = intVal(trim($uniqDelay));
				$uniqDelayType = trim($uniqDelayType);
				if (!in_array($uniqDelayType, array("S", "M", "H", "D")))
					$uniqDelayType = "D";

				$arVoteParams = array(
					"UNIQUE_TYPE" => $uniqType,
					"DELAY" => $uniqDelay,
					"DELAY_TYPE" => $uniqDelayType);
				$VOTE_ID = VoteVoteEditFromArray($arParams["VOTE_CHANNEL_ID"], ($VOTE_ID > 0 ? $VOTE_ID : false), $arVote, $arVoteParams);
				if (intVal($VOTE_ID) > 0)
				{
					$arFieldsG["PARAM1"] = "VT";
					$arFieldsG["PARAM2"] = $VOTE_ID;
				}
				else
				{
					$e = $GLOBALS['APPLICATION']->GetException();
					if ($e)
					{
						$err = reset($e->messages);
						if ($err["id"] == "questions") {
							CVote::Delete($VOTE_ID);
							$arFieldsG["PARAM1"] = "";
							$arFieldsG["PARAM2"] = false; }
						else {
							$strErrorMessage .= $e->GetString(); }
					}
					$VOTE_ID = false;
				}
			}
		}

		if (empty($strErrorMessage))
		{
			foreach (array("AUTHOR_NAME", "AUTHOR_EMAIL", "TITLE", "TAGS", "DESCRIPTION", "ICON_ID") as $res)
			{
				if (is_set($_REQUEST, $res))
					$arFieldsG[$res] = $_REQUEST[$res];
			}
			if (!empty($_FILES["ATTACH_IMG"]))
			{
				$arFieldsG["ATTACH_IMG"] = $_FILES["ATTACH_IMG"];
				if ($arParams["MESSAGE_TYPE"] == "EDIT" && $_REQUEST["ATTACH_IMG_del"] == "Y")
					$arFieldsG["ATTACH_IMG"]["del"] = "Y";
			}
			else
			{
				$arFiles = array();
				if (!empty($_REQUEST["FILES"]))
				{
					foreach ($_REQUEST["FILES"] as $key)
					{
						$arFiles[$key] = array("FILE_ID" => $key);
						if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
							$arFiles[$key]["del"] = "Y";
					}
				}
				if (!empty($_FILES))
				{
					$res = array();
					foreach ($_FILES as $key => $val)
					{
						if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"]))
						{
							$arFiles[] = $_FILES[$key];
						}
					}
				}
				if (!empty($arFiles))
					$arFieldsG["FILES"] = $arFiles;
			}

			if ($arParams["MESSAGE_TYPE"] == "EDIT")
			{
				$arFieldsG["EDIT_ADD_REASON"] = $_REQUEST["EDIT_ADD_REASON"];
				$arFieldsG["EDITOR_NAME"] = $_REQUEST["EDITOR_NAME"];
				$arFieldsG["EDITOR_EMAIL"] = $_REQUEST["EDITOR_EMAIL"];
				$arFieldsG["EDIT_REASON"] = $_REQUEST["EDIT_REASON"];
			}
			$MID1 = intVal(ForumAddMessage($arParams["MESSAGE_TYPE"], $arParams["FID"], $TID1, $MID1, $arFieldsG,
				$strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"], $arParams["NAME_TEMPLATE"]));

			if ($MID1 > 0)
			{
				$arResult["MESSAGE"] = CForumMessage::GetByID($MID1);
				$arParams["TID"] = $arResult["MESSAGE"]["TOPIC_ID"];
				$arParams["MID"] = $arResult["MESSAGE"]["ID"];

				$sText = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $arResult["MESSAGE"]["POST_MESSAGE_FILTER"] : $arResult["MESSAGE"]["POST_MESSAGE"]);

				$sURL = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"],
					array(
						"UID" => $arParams["USER_ID"],
						"FID" => $arParams["FID"],
						"TID" => $arParams["TID"],
						"MID" => $arParams["MID"])
				);
				if ($arParams['AUTOSAVE'])
					$arParams['AUTOSAVE']->Reset();
				/************** Socialnetwork notification *************************/

				$workgroups_path = "";
				if ($arParams["MODE"] == "GROUP" && IsModuleInstalled("extranet")) {
					$workgroups_path = COption::GetOptionString("socialnetwork", "workgroups_page", false, SITE_ID);
					$workgroups_path = "#GROUPS_PATH#".substr(
						$arParams["~URL_TEMPLATES_MESSAGE"],
						strlen($workgroups_path),
						strlen($arParams["~URL_TEMPLATES_MESSAGE"]) - strlen($workgroups_path));
				}

				$arSonetFields = array(
					"ENTITY_TYPE" => ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
					"ENTITY_ID" => ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]),
					"EVENT_ID" => "forum",
					"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"TITLE_TEMPLATE" => str_replace("#AUTHOR_NAME#", $arResult["MESSAGE"]["AUTHOR_NAME"], GetMessage("SONET_FORUM_LOG_TEMPLATE")),
					"TITLE" => $arFieldsG["TITLE"],
					"MESSAGE" => $sText,
					"TEXT_MESSAGE" => $parser->convert4mail($sText),
					"URL" => $sURL,
					"PARAMS" => serialize(array(
						"PATH_TO_MESSAGE" => CComponentEngine::MakePathFromTemplate(
							(!empty($workgroups_path) ? $workgroups_path : $arParams["~URL_TEMPLATES_MESSAGE"]),
							array("TID" => $arParams["TID"])),
						"VOTE_ID" => ($arFieldsG["PARAM1"] == "VT" ? $arFieldsG["PARAM2"] : 0),
						"PARSED" => "N"
						)),
					"MODULE_ID" => false,
					"CALLBACK_FUNC" => false,
					"SOURCE_ID" => $MID1,
					"RATING_TYPE_ID" => "FORUM_TOPIC",
					"RATING_ENTITY_ID" => intval($arParams["TID"])
				);
				if (intVal($arResult["MESSAGE"]["AUTHOR_ID"]) > 0)
					$arSonetFields["USER_ID"] = $arResult["MESSAGE"]["AUTHOR_ID"];

				$ufFileID = array();
				$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $MID1));

				if (count($ufFileID) > 0)
					$arSonetFields["UF_SONET_LOG_FILE"] = $ufFileID;
				else
					unset($arSonetFields["UF_SONET_LOG_FILE"]);

				$ufDocID = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $MID1, LANGUAGE_ID);
				if ($ufDocID)
					$arSonetFields["UF_SONET_LOG_DOC"] = $ufDocID;
				else
					unset($arSonetFields["UF_SONET_LOG_DOC"]);

				if ($arParams["MESSAGE_TYPE"] == "NEW")
				{
					if ($arParams["MODE"] == "GROUP")
						CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);

					$logID = CSocNetLog::Add($arSonetFields, false);
					if (intval($logID) > 0)
					{
						CSocNetLog::Update($logID, array("TMP_ID" => $logID));
						CSocNetLogRights::SetForSonet($logID, $arSonetFields["ENTITY_TYPE"], $arSonetFields["ENTITY_ID"], "forum", "view", true);
						CSocNetLog::CounterIncrement($logID);

						if ($arParams["MODE"] == "GROUP")
						{
							$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $logID));
							while ($arRight = $dbRight->Fetch())
							{
								if ($arRight["GROUP_CODE"] == "SG".$arParams["SOCNET_GROUP_ID"]."_".SONET_ROLES_USER)
								{
									$title_tmp = str_replace(Array("\r\n", "\n"), " ", $arFieldsG["TITLE"]);
									$title = TruncateText($title_tmp, 100);
									$title_out = TruncateText($title_tmp, 255);

									$arNotifyParams = array(
										"LOG_ID" => $logID,
										"GROUP_ID" => array($arParams["SOCNET_GROUP_ID"]),
										"NOTIFY_MESSAGE" => "",
										"FROM_USER_ID" => $arSonetFields["USER_ID"],
										"URL" => $sURL,
										"MESSAGE" => GetMessage("SONET_IM_NEW_TOPIC", Array(
											"#title#" => "<a href=\"#URL#\" class=\"bx-notifier-item-action\">".$title."</a>",
										)),
										"MESSAGE_OUT" => GetMessage("SONET_IM_NEW_TOPIC", Array(
											"#title#" => $title_out
										))." (#URL#)",
										"EXCLUDE_USERS" => array($arSonetFields["USER_ID"])
									);

									CSocNetSubscription::NotifyGroup($arNotifyParams);
									break;
								}
							}
						}
					}
				}
				elseif ($arParams["MESSAGE_TYPE"] == "EDIT")
				{
					$dbRes = CSocNetLog::GetList(
						array(),
						array(
							"EVENT_ID" => "forum",
							"SOURCE_ID" => $MID1
						),
						false,
						false,
						array("ID")
					);
					if ($arRes = $dbRes->Fetch())
					{
						// topic
						$arSonetFields = array_intersect_key($arSonetFields,
							array_flip(array("TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "PARAMS", "UF_SONET_LOG_DOC")));

						CSocNetLog::Update($arRes["ID"], $arSonetFields);
						CSocNetLogRights::SetForSonet($arRes["ID"], ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]), "forum", "view");
					}
					else
					{
						$dbRes = CSocNetLogComments::GetList(
							array(),
							array(
								"EVENT_ID" => "forum",
								"SOURCE_ID" => $MID1
							),
							false,
							false,
							array("ID")
						);
						if ($arRes = $dbRes->Fetch())
						{
							// message/comment
							$arSonetFields = array_intersect_key($arSonetFields,
								array_flip(array("MESSAGE", "TEXT_MESSAGE", "PARAMS")));

							CSocNetLogComments::Update($arRes["ID"], $arSonetFields);
						}
					}
				}

				$url = ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
					array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => intVal($arParams["MID"]),
					"UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])),
				array("result" => $arNote["code"]));
				LocalRedirect($url);
			}
			elseif (intVal($arFieldsG["PARAM2"]) > 0 && $arFieldsG["PARAM1"] == "VT")
			{
				CVote::Delete($arFieldsG["PARAM2"]);
			}
		}
		if (!empty($strErrorMessage))
		{
			$arError[] = array(
				"id" => $arParams["MESSAGE_TYPE"], 
				"text" => $strErrorMessage
			);
		}
	}
	elseif ($arResult["VIEW"] == "Y")
	{
		$bVarsFromForm = true;
		$arFields = array(
			"FORUM_ID" => intVal($arParams["FID"]),
			"TOPIC_ID" => intVal($arParams["TID"]),
			"MESSAGE_ID" => intVal($arParams["MID"]),
			"USER_ID" => intVal($GLOBALS["USER"]->GetID()));
		$arFiles = array();
		$arFilesExists = array();
		$res = array();

		foreach ($_FILES as $key => $val):
			if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
				$arFiles[] = $_FILES[$key];
			endif;
		endforeach;
		foreach ($_REQUEST["FILES"] as $key => $val)
		{
			if (!in_array($val, $_REQUEST["FILES_TO_UPLOAD"]))
			{
				$arFiles[$val] = array("FILE_ID" => $val, "del" => "Y");
				unset($_REQUEST["FILES"][$key]);
				unset($_REQUEST["FILES_TO_UPLOAD"][$key]);
			}
			else
			{
				$arFilesExists[$val] = array("FILE_ID" => $val);
			}
		}
		if (!empty($arFiles))
		{
			$res = CForumFiles::Save($arFiles, $arFields);
			$res1 = $GLOBALS['APPLICATION']->GetException();
			if ($res1):
				$arError[] = array(
					"id" => "bad files", 
					"text" => $res1->GetString());
			endif;
		}
		$res = is_array($res) ? $res : array();
		foreach ($res as $key => $val)
			$arFilesExists[$key] = $val;
		$arFilesExists = array_keys($arFilesExists);
		sort($arFilesExists);
		$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILES"] = $arFilesExists;
		$arResult["MESSAGE_VIEW"]["TEXT"] = $arResult["POST_MESSAGE_VIEW"] =
			$parser->convert($_POST["POST_MESSAGE"], $arAllow, "html", $arResult["MESSAGE_VIEW"]["FILES"]);
		$arResult["MESSAGE_VIEW"]["FILES_PARSED"] = $parser->arFilesIDParsed;

		if ($arParams['AUTOSAVE'])
			$arParams['AUTOSAVE']->Reset();
	}
	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
		$bVarsFromForm = true;
	}
}
/********************************************************************
				/Action
********************************************************************/

$this->IncludeComponentTemplate();

/********************************************************************
				Standart Action
********************************************************************/
/*
$APPLICATION->AddChainItem(GetMessage("FL_FORUM_CHAIN"), $arResult["URL"]["LIST"]);
*/
if ($arParams["MESSAGE_TYPE"] == "EDIT"):
	$APPLICATION->AddChainItem($arResult["TOPIC_FILTER"]["TITLE"], $arResult["URL"]["~READ"]);
endif;
$APPLICATION->AddChainItem(($arParams["MESSAGE_TYPE"]=="NEW" ? GetMessage("F_NTITLE") : GetMessage("F_ETITLE")));
if ($arParams["SET_TITLE"] != "N"):
	$APPLICATION->SetTitle(($arParams["MESSAGE_TYPE"]=="NEW" ? GetMessage("F_NTITLE") : GetMessage("F_ETITLE")));
endif;
/********************************************************************
				Standart Action
********************************************************************/
return array(
	"PERMISSION" => $arParams["PERMISSION"], 
	"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
	"FORUM" => $arResult["FORUM"],
	"TOPIC" => $arResult["TOPIC"],
	"MESSAGE" => $arResult["MESSAGE_VIEW"],
	"bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
	"OK_MESSAGE" => $strOKMessage);
?>