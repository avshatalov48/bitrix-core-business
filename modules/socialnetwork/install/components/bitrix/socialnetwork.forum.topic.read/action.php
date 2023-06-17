<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Socialnetwork\LogCommentTable;

global $USER_FIELD_MANAGER;

if (!CModule::IncludeModule("forum"))
	return 0;

$this->IncludeComponentLang("action.php");
$action = mb_strtoupper($arParams["ACTION"]);
$action = ($action == "SUPPORT" ? "FORUM_MESSAGE2SUPPORT" : $action);

$post = $this->request->getPostList()->toArray();
if (($post["AJAX_POST"] ?? null) == "Y")
{
	CUtil::decodeURIComponent($post);
}

if ($action == '')
{
}
elseif (!check_bitrix_sessid())
{
	$arError[] = array(
		"id" => "bad_sessid",
		"text" => GetMessage("F_ERR_SESS_FINISH")
	);
}
elseif (isset($_REQUEST["MESSAGE_MODE"]) && $_REQUEST["MESSAGE_MODE"] == "VIEW")
{
	$arResult["VIEW"] = "Y";
	$bVarsFromForm = true;
/************** Preview message ************************************/
	$arAllow["SMILES"] = ($post["USE_SMILES"]!="Y" ? "N" : "Y" );

	$arResult["POST_MESSAGE_VIEW"] = $post["POST_MESSAGE"];
	$arResult["MESSAGE_VIEW"]["AUTHOR_NAME"] = ($USER->IsAuthorized() || empty($post["AUTHOR_NAME"]) ? $arResult["USER"]["SHOW_NAME"] : trim($post["AUTHOR_NAME"]));
	$arResult["MESSAGE_VIEW"]["TEXT"] = $arResult["POST_MESSAGE_VIEW"];
	$arFields = array(
		"FORUM_ID" => intval($arParams["FID"]),
		"TOPIC_ID" => intval($arParams["TID"]),
		"MESSAGE_ID" => intval($arParams["MID"]),
		"USER_ID" => intval($GLOBALS["USER"]->GetID()));
	$arFiles = array();
	$arFilesExists = array();
	$res = array();

	foreach ($_FILES as $key => $val):
		if (mb_substr($key, 0, mb_strlen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
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
			$strErrorMessage .= $res1->GetString();
		endif;
	}
	$res = is_array($res) ? $res : array();
	foreach ($res as $key => $val)
		$arFilesExists[$key] = $val;
	$arFilesExists = array_keys($arFilesExists);
	sort($arFilesExists);
	$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILES"] = $arFilesExists;
	$arResult["MESSAGE_VIEW"]["TEXT"] = $arResult["POST_MESSAGE_VIEW"] = $parser->convert($post["POST_MESSAGE"], $arAllow, "html", $arResult["MESSAGE_VIEW"]["FILES"]);
	$arResult["MESSAGE_VIEW"]["FILES_PARSED"] = $parser->arFilesIDParsed;
}
else
{
	$arFields = array(
		"PERMISSION_EXTERNAL" => $arParams["PERMISSION"],
		"PERMISSION" => $arParams["PERMISSION"]);

	$url = false; $code = false;
	$message = (!empty($_REQUEST["MID_ARRAY"]) ? $_REQUEST["MID_ARRAY"] : $_REQUEST["MID"]);
	if ((empty($message) || $message == "s") && !empty($_REQUEST["message_id"]))
		$message = $_REQUEST["message_id"];
	if ((empty($message) || $message == "s") && !empty($arParams["MID"]))
		$message = $arParams["MID"];

	switch ($action)
	{
		case "EDIT_TOPIC":
			$MID = 0;
			$db_res = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID"=>$arParams["TID"]), false, 1);
			if (($db_res) && ($res = $db_res->Fetch()))
				$MID = intval($res["ID"]);
			if ($MID > 0)
			{
				$url = ForumAddPageParams(
					CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_EDIT"],
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => $MID, "MESSAGE_TYPE" => "EDIT")),
					array("TID" => $arParams["TID"], "MID" => $MID, "MESSAGE_TYPE" => "EDIT", "sessid" => bitrix_sessid()), false, false);
				LocalRedirect($url);
			}
			break;
		case "REPLY":
			$arFields = array(
				"FID" => $arParams["FID"],
				"TID" => $arParams["TID"],
				"POST_MESSAGE" => $post["POST_MESSAGE"],
				"AUTHOR_NAME" => $post["AUTHOR_NAME"] ?? null,
				"AUTHOR_EMAIL" => $post["AUTHOR_EMAIL"] ?? null,
				"USE_SMILES" => $post["USE_SMILES"] ?? null,
				"ATTACH_IMG" => $_FILES["ATTACH_IMG"] ?? null,
				"captcha_word" =>  $post["captcha_word"] ?? null,
				"captcha_code" => $post["captcha_code"] ?? null,
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]);
				if (!empty($_FILES["ATTACH_IMG"]))
				{
					$arFields["ATTACH_IMG"] = $_FILES["ATTACH_IMG"];
				}
				else
				{
					$arFiles = array();
					if (!empty($_REQUEST["FILES"]))
					{
						foreach ($_REQUEST["FILES"] as $key):
							$arFiles[$key] = array("FILE_ID" => $key);
							if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
								$arFiles[$key]["del"] = "Y";
						endforeach;
					}
					if (!empty($_FILES))
					{
						$res = array();
						foreach ($_FILES as $key => $val):
							if (mb_substr($key, 0, mb_strlen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
								$arFiles[] = $_FILES[$key];
							endif;
						endforeach;
					}
					if (!empty($arFiles))
						$arFields["FILES"] = $arFiles;
				}
				$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
					array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID"=>"#result#"));
			break;
		case "VOTE4USER":
			return false;
			$arFields = array(
				"UID" => $_GET["UID"],
				"VOTES" => $_GET["VOTES"],
				"VOTE" => (($_GET["VOTES_TYPE"]=="U") ? True : False));
			$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arParams["TID"],
					"MID" => (intval($_REQUEST["MID"]) > 0 ? $_REQUEST["MID"] : "s")));
			break;
		case "HIDE":
		case "SHOW":
		case "FORUM_MESSAGE2SUPPORT":
			$arFields = array("MID" => $message);
			$mid = (is_array($message) ? $message[0] : $message);
			$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
					array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => (!empty($mid) ? $mid : "s")));
			if ($action == "FORUM_MESSAGE2SUPPORT")
			{
				$url = "/bitrix/admin/ticket_edit.php?ID=#result#&amp;lang=".LANGUAGE_ID;
			}
			break;
		case "DEL":
		case "SPAM":
			$arFields = array("MID" => $message, "PERMISSION" => $arParams["PERMISSION"]);
			$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
					array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "#MID#"));
			break;
		case "SET_ORDINARY":
		case "SET_TOP":
		case "STATE_Y":
		case "STATE_N":
			if ($action == "STATE_Y")
				$action = "OPEN";
			elseif ($action == "STATE_N")
				$action = "CLOSE";
			elseif ($action == "SET_ORDINARY")
				$action = "ORDINARY";
			else
				$action = "TOP";

			$arFields = array("TID" => $arParams["TID"]);
			$url = CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"],
					"TID" => $arParams["TID"],
					"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
			break;
		case "HIDE_TOPIC":
		case "SHOW_TOPIC":
			$arFields = array("TID" => $arParams["TID"]);
			$url = CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"],
					"TID" => $arParams["TID"],
					"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
			break;
		case "SPAM_TOPIC":
		case "DEL_TOPIC":
			$arFields = array("TID" => $arParams["TID"]);
			$url = CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_TOPIC_LIST"],
				array("FID" => $arParams["FID"]));
			break;
	}
	$strErrorMessage = ""; $strOKMessage = ""; $res = false;
	$arFields["PERMISSION_EXTERNAL"] = $arParams["PERMISSION"];
	$arFields["PERMISSION"] = $arParams["PERMISSION"];

	$arLogID_Del = array();
	$arLogCommentID_Del = array();
	switch ($action)
	{
		case "DEL":
		case "HIDE":
			// delete message log record
			$dbRes = CSocNetLogComments::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "forum",
					"SOURCE_ID" => $arFields["MID"]
				),
				false,
				false,
				array("ID")
			);
			while ($arRes = $dbRes->Fetch())
				$arLogCommentID_Del[] = $arRes["ID"];
			break;
		case "DEL_TOPIC":
		case "HIDE_TOPIC":
			if (!is_array($arFields["TID"]))
				$arTID = array($arFields["TID"]);
			else
				$arTID = $arFields["TID"];

			$arLogID_Del = array();
			foreach($arTID as $topic_id_tmp)
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
						array("ID")
					);
					while ($arRes = $dbRes->Fetch())
						$arLogID_Del[] = $arRes["ID"];
				}
			}
			break;
	}

	$actionResult = $res = ForumActions($action, $arFields, $strErrorMessage, $strOKMessage);

	if ($res)
	{
		// check out not hidden topic messages
		$iApprovedMessagesCnt = CForumMessage::GetList(array(), array("TOPIC_ID"=>$arParams["TID"], "APPROVED"=>"Y"), true);
		if ($iApprovedMessagesCnt <= 0)
		{
			$rsForumMessage = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID"=>$arParams["TID"]), false, 1);
			if ($arForumMessage = $rsForumMessage->Fetch())
			{
				$dbLogRes = CSocNetLog::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID" => "forum",
						"SOURCE_ID" => $arForumMessage["ID"]
					),
					false,
					false,
					array("ID")
				);
				if ($arLogRes = $dbLogRes->Fetch())
					$arLogID_Del[] = $arLogRes["ID"];
			}
		}

		foreach($arLogID_Del as $log_id)
			CSocNetLog::Delete($log_id);
		foreach($arLogCommentID_Del as $log_comment_id)
			CSocNetLogComments::Delete($log_comment_id);
	}

	if (!empty($strErrorMessage))
	{
		$arError[] = array(
			"id" => $action,
			"text" => $strErrorMessage
		);
	}
	elseif ($action == "DEL" || $action == "SPAM")
	{
		$arFields = CForumTopic::GetByID($arParams["TID"]);
		if (empty($arFields))
		{
			$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_LIST"], array("FID" => $arParams["FID"]));
			$action = "del_topic";
		}
		else
		{
			$res = intval($message); $mid = "s";
			if (is_array($message)):
				sort($message);
				$res = array_pop($message);
			endif;
			$arFilter = array("TOPIC_ID" => $arParams["TID"], ">ID" => $res);
			if ($arParams["PERMISSION"] < "Q"):
				$arFilter["APPROVED"] = "Y";
			endif;

			$db_res = CForumMessage::GetList(array("ID" => "ASC"), $arFilter);
			if ($db_res && $res = $db_res->Fetch())
				$mid = $res["ID"];
			$url = str_replace("#MID#", $mid, $url);
		}
	}
	elseif ($action == "REPLY" || $action == "SHOW")
	{
		if ($action == "REPLY")
			$arParams["MID"] = intval($res);

		$result = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y"));

		$arResult["MESSAGE"] = $result;
		if (is_array($result) && !empty($result))
		{
			$arParams["TID"] = intval($result["TOPIC_ID"]);
			if ($arParams["AUTOSAVE"])
				$arParams["AUTOSAVE"]->Reset();
			$sText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $result["POST_MESSAGE_FILTER"] : $result["POST_MESSAGE"]);
			if ($arParams["MODE"] == "GROUP")
				CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);

			// calculate root MID
			$dbFirstMessage = CForumMessage::GetList(
				array("ID" => "ASC"),
				array("TOPIC_ID" => $arParams["TID"]),
				false,
				1
			);
			if ($arFirstMessage = $dbFirstMessage->Fetch())
			{
				$bSocNetLogRecordExists = false;
				$dbRes = CSocNetLog::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID" => "forum",
						"SOURCE_ID" => $arFirstMessage["ID"]
					),
					false,
					false,
					array("ID", "TMP_ID", "USER_ID")
				);

				if ($arRes = $dbRes->Fetch())
				{
					$log_id = $arRes["TMP_ID"];
					$log_user_id = $arRes["USER_ID"];
					$bSocNetLogRecordExists = true;
				}
				else
				{
					// get root message
					$arFirstMessage = CForumMessage::GetByIDEx($arFirstMessage["ID"], array("GET_TOPIC_INFO" => "Y", "getFiles" => "Y"));
					$arTopic = $arFirstMessage["TOPIC_INFO"];
					$sFirstMessageText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ?
						$arFirstMessage["POST_MESSAGE_FILTER"] : $arFirstMessage["POST_MESSAGE"]);

					$sFirstMessageURL = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"],
						array(
							"UID" => $arFirstMessage["AUTHOR_ID"],
							"FID" => $arFirstMessage["FORUM_ID"],
							"TID" => $arFirstMessage["TOPIC_ID"],
							"MID" => $arFirstMessage["ID"]
						)
					);

					$arFieldsForSocnet = array(
						"ENTITY_TYPE" => ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
						"ENTITY_ID" => ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]),
						"EVENT_ID" => "forum",
						"LOG_DATE" => $arFirstMessage["POST_DATE"],
						"LOG_UPDATE" => $arFirstMessage["POST_DATE"],
						"TITLE_TEMPLATE" => str_replace("#AUTHOR_NAME#", $arFirstMessage["AUTHOR_NAME"], GetMessage("SONET_FORUM_LOG_TOPIC_TEMPLATE")),
						"TITLE" => $arTopic["TITLE"],
						"MESSAGE" => $sFirstMessageText,
						"TEXT_MESSAGE" => $parser->convert4mail($sFirstMessageText),
						"URL" => $sFirstMessageURL,
						"PARAMS" => serialize(array(
							"PATH_TO_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], array("TID" => $arParams["TID"])),
							"VOTE_ID" => ($arFirstMessage["PARAM1"] == "VT" ? $arFirstMessage["PARAM2"] : 0))),
						"MODULE_ID" => false,
						"CALLBACK_FUNC" => false,
						"SOURCE_ID" => $arFirstMessage["ID"],
						"RATING_TYPE_ID" => "FORUM_TOPIC",
						"RATING_ENTITY_ID" => intval($arParams["TID"])
					);

					if (intval($arFirstMessage["AUTHOR_ID"]) > 0)
						$arFieldsForSocnet["USER_ID"] = $arFirstMessage["AUTHOR_ID"];
					$log_id = CSocNetLog::Add($arFieldsForSocnet, false);
					if (intval($log_id) > 0)
					{
						$log_user_id = $arFieldsForSocnet["USER_ID"];
						CSocNetLog::Update($log_id, array("TMP_ID" => $log_id));
						CSocNetLogRights::SetForSonet($log_id, ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]), "forum", "view");
					}
				}

				if (intval($log_id) > 0)
				{
					$arFieldsForSocnet = array(
						"ENTITY_TYPE" => ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
						"ENTITY_ID" => ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]),
						"EVENT_ID" => "forum",
						"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"MESSAGE" => $sText,
						"TEXT_MESSAGE" => $parser->convert4mail($sText),
						"URL" => str_replace("#result#", $arParams["MID"], $url),
						"MODULE_ID" => false,
						"SOURCE_ID" => $arParams["MID"],
						"LOG_ID" => $log_id,
						"RATING_TYPE_ID" => "FORUM_POST",
						"RATING_ENTITY_ID" => intval($arParams["MID"])
					);

					$userFieldsList = $USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT", 0, LANGUAGE_ID);
					$controlId = false;
					if (
						!empty($userFieldsList['UF_SONET_COM_FILE'])
						&& !empty($userFieldsList['UF_SONET_COM_FILE']['ID'])
					)
					{
						$controlId = LogCommentTable::getUfId().'-'.$userFieldsList['UF_SONET_COM_FILE']['ID'].'-UF_SONET_COM_FILE';
						FileInputUtility::instance()->registerControl($controlId, $controlId);
					}

					$ufFileID = array();
					$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $arParams["MID"]));
					while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
					{
						$ufFileID[] = $arAddedMessageFiles["FILE_ID"];
						if ($controlId)
						{
							FileInputUtility::instance()->registerFile($controlId, $arAddedMessageFiles["FILE_ID"]);
						}
					}

					if (count($ufFileID) > 0)
					{
						$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;
					}

					$ufDocID = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $arParams["MID"], LANGUAGE_ID);
					if ($ufDocID)
						$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;

					if ($bSocNetLogRecordExists)
					{
						if (intval($arResult["MESSAGE"]["AUTHOR_ID"]) > 0)
							$arFieldsForSocnet["USER_ID"] = $arResult["MESSAGE"]["AUTHOR_ID"];
						$log_comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
						CSocNetLog::CounterIncrement($log_comment_id, false, false, "LC");

						if (
							CModule::IncludeModule("im")
							&& intval($arFieldsForSocnet["USER_ID"]) > 0
							&& $arFieldsForSocnet["USER_ID"] != $log_user_id
						)
						{
							$rsUnFollower = CSocNetLogFollow::GetList(
								array(
									"USER_ID" => $log_user_id,
									"CODE" => "L".$log_id,
									"TYPE" => "N"
								),
								array("USER_ID")
							);

							$arUnFollower = $rsUnFollower->Fetch();
							if (!$arUnFollower)
							{
								$arMessageFields = array(
									"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
									"TO_USER_ID" => $log_user_id,
									"FROM_USER_ID" => $arFieldsForSocnet["USER_ID"],
									"NOTIFY_TYPE" => IM_NOTIFY_FROM,
									"NOTIFY_MODULE" => "forum",
									"NOTIFY_EVENT" => "comment",
								);

								$arParams["TITLE"] = str_replace(Array("\r\n", "\n"), " ", $arResult["MESSAGE"]["TOPIC_INFO"]["TITLE"]);
								$arParams["TITLE"] = TruncateText($arParams["TITLE"], 100);
								$arParams["TITLE_OUT"] = TruncateText($arParams["TITLE"], 255);

								$arTmp = CSocNetLogTools::ProcessPath(array("MESSAGE_URL" => $arFieldsForSocnet["URL"]), $log_user_id);
								$serverName = $arTmp["SERVER_NAME"];
								$url = $arTmp["URLS"]["MESSAGE_URL"];

								$arMessageFields["NOTIFY_TAG"] = "FORUM|COMMENT|".$arParams["MID"];
								$arMessageFields["NOTIFY_MESSAGE"] = GetMessage("SONET_FORUM_ACTION_IM_COMMENT", Array(
									"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
								));
								$arMessageFields["NOTIFY_MESSAGE_OUT"] = GetMessage("SONET_FORUM_ACTION_IM_COMMENT", Array(
									"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
								))." (".$serverName.$url.")#BR##BR#".$sText;

								CIMNotify::Add($arMessageFields);
							}
						}
					}
					else //socnetlog record didn't exist - adding all comments
					{
						$dbComments = CForumMessage::GetListEx(
							array("ID" => "ASC"),
							array('TOPIC_ID' => $arParams["TID"], "NEW_TOPIC" => "N")
						);
						if ($dbComments && ($arComment = $dbComments->Fetch()))
						{
							do {
								$ufFileID = array();
								$dbAddedMessageFiles = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $arComment["ID"]));
								while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
									$ufFileID[] = $arAddedMessageFiles["FILE_ID"];

								if (count($ufFileID) > 0)
									$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;
								else
									unset($arFieldsForSocnet["UF_SONET_COM_FILE"]);

								$ufDocID = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $arComment["ID"], LANGUAGE_ID);
								if ($ufDocID)
									$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;
								else
									unset($arFieldsForSocnet["UF_SONET_COM_DOC"]);

								$arSocLog = array(
									"=LOG_DATE" => $DB->CharToDateFunction($arComment['POST_DATE'], "FULL", SITE_ID),
									"MESSAGE" => $arComment['POST_MESSAGE'],
									"TEXT_MESSAGE" => $parser->convert4mail($arComment['POST_MESSAGE']),
									"SOURCE_ID" => intval($arComment["ID"]),
									"RATING_ENTITY_ID" => intval($arComment["ID"])
								) + (!!$arComment['AUTHOR_ID'] ? array("USER_ID" => $arComment["AUTHOR_ID"]) : array());
								$log_comment_id = CSocNetLogComments::Add(array_merge($arFieldsForSocnet, $arSocLog), false, false);
								CSocNetLog::CounterIncrement($log_comment_id, false, false, "LC");
							} while ($arComment = $dbComments->Fetch());
						}
					}
				}
			}
		}
		$res = $arParams["MID"];
	}
	if (!$res)
		$bVarsFromForm = true;
	else
	{
		$arNote = array(
			"code" => $action,
			"title" => $strOKMessage,
			"link" => $url);
	}
	$arResult['RESULT'] = $res;
	if (isset($_REQUEST['AJAX_CALL']) && in_array($action, array('SHOW', 'HIDE', 'DEL')))
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		$arRes = array('status' => (!($actionResult === false)), 'message' => ( (!($actionResult===false)) ? $strOKMessage : $strErrorMessage));
		echo CUtil::PhpToJSObject($arRes);
		die();
	}
	if (empty($arError) && !($arParams['AJAX_POST'] == 'Y' && $action == 'REPLY'))
	{
		$url = str_replace("#result#", $res, $url);
		LocalRedirect(ForumAddPageParams($url, array("result" => mb_strtolower($action)), true, false).(!empty($arParams["MID"]) ? "#message".$arParams["MID"] : ""));
	}
}
if (!empty($arError))
{
	$bVarsFromForm = true;
}

?>
