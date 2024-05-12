<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $GLOBALS["USER"]
 * @param array $arParams
 * @param array $arResult
 * @param string $componentName
 * @param string $action
 * @param string $s_action
 * @param CBitrixComponent $this
 */
if (!CModule::IncludeModule("forum"))
	return 0;
$this->IncludeComponentLang("action.php");
$post = $this->request->getPostList()->toArray();

if (($action <> '' || $s_action <> '') && (!isset($_REQUEST["MESSAGE_MODE"]) || $_REQUEST["MESSAGE_MODE"] != "VIEW") && check_bitrix_sessid())
{
	//*************************!Subscribe***************************************************
	if ($s_action == 'SUBSCRIBE' || $s_action == 'UNSUBSCRIBE')
	{
		if (isset($_REQUEST["TOPIC_UNSUBSCRIBE"]) && $_REQUEST["TOPIC_UNSUBSCRIBE"] == "Y")
			ForumUnsubscribeNewMessagesEx($arParams["FID"], $arParams["TID"], "N", $strErrorMessage, $strOKMessage);
		if (isset($_REQUEST["FORUM_UNSUBSCRIBE"]) && $_REQUEST["FORUM_UNSUBSCRIBE"] == "Y")
			ForumUnsubscribeNewMessagesEx($arParams["FID"], 0, "N", $strErrorMessage, $strOKMessage);
		if (isset($_REQUEST["TOPIC_SUBSCRIBE"]) && $_REQUEST["TOPIC_SUBSCRIBE"] == "Y")
			ForumSubscribeNewMessagesEx($arParams["FID"], $arParams["TID"], "N", $strErrorMessage, $strOKMessage);
		if (isset($_REQUEST["FORUM_SUBSCRIBE"]) && $_REQUEST["FORUM_SUBSCRIBE"] == "Y")
			ForumSubscribeNewMessagesEx($arParams["FID"], 0, "N", $strErrorMessage, $strOKMessage);

		if (empty($strErrorMessage) && ($_SERVER['REQUEST_METHOD']=='GET'))
			LocalRedirect($APPLICATION->GetCurPageParam("", array("TOPIC_UNSUBSCRIBE", "FORUM_UNSUBSCRIBE", "TOPIC_SUBSCRIBE", "FORUM_SUBSCRIBE", "sessid")));
	}
	$result = false;
	//*************************!Subscribe***************************************************
	if ($action <> '' && $action != "SUBSCRIBE" && $action != "UNSUBSCRIBE" )
	{
		$arFields = array();
		$url = false;
		$code = false;
		$message = array();
		if ($_SERVER['REQUEST_METHOD'] == "POST"):
			$message = (!empty($_POST["MID_ARRAY"]) ? $_POST["MID_ARRAY"] : ($_POST["MID"] ?? null));
			if ((empty($message) || $message == "s") && !empty($_POST["message_id"])):
				$message = $_POST["message_id"];
			endif;
		else:
			$message = (!empty($_REQUEST["MID_ARRAY"]) ? $_REQUEST["MID_ARRAY"] : ($_REQUEST["MID"] ?? null));
			if ((empty($message) || $message == "s") && !empty($_REQUEST["message_id"])):
				$message = $_REQUEST["message_id"];
			endif;
		endif;

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
						CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])),
						array("TID" => $arParams["TID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => $MID, "MESSAGE_TYPE" => "EDIT"), false, false);
					LocalRedirect($url);
				}
				break;
			case "REPLY":
				$arFields = array(
						"FID" => $arParams["FID"] ?? null,
						"TID" => $arParams["TID"] ?? null,
						"POST_MESSAGE" => $post["POST_MESSAGE"] ?? null,
						"AUTHOR_NAME" => $post["AUTHOR_NAME"] ?? null,
						"AUTHOR_EMAIL" => $post["AUTHOR_EMAIL"] ?? null,
						"USE_SMILES" => $post["USE_SMILES"] ?? null,
						"captcha_word" =>  $post["captcha_word"] ?? null,
						"captcha_code" => $post["captcha_code"] ?? null,
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
						);
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
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"] ?? null,
							array(
								"FID" => $arParams["FID"] ?? null,
								"TID" => $arParams["TID"] ?? null,
								"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"] ?? null,
								"MID"=>"#result#"));
				break;
			case "VOTE4USER":
				$arFields = array(
					"UID" => $_GET["UID"] ?? null,
					"VOTES" => $_GET["VOTES"] ?? null,
					"VOTE" => (($_GET["VOTES_TYPE"]=="U") ? True : False));
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"],
					array("FID" => $arParams["FID"] ?? null,
						"TID" => $arParams["TID"] ?? null,
						"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"] ?? null,
						"MID" => (intval($_REQUEST["MID"]) > 0 ? $_REQUEST["MID"] : "s")
					));
				break;
			case "HIDE":
			case "SHOW":
			case "FORUM_MESSAGE2SUPPORT":
				$arFields = array("MID" => $message);
				$mid = (is_array($message) ? $message[0] : $message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"],
						array(
							"FID" => $arParams["FID"] ?? null,
							"TID" => $arParams["TID"] ?? null,
							"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"],
							"MID" => (!empty($mid) ? $mid : "s")
						));
				if ($action == "FORUM_MESSAGE2SUPPORT")
				{
					$url = "/bitrix/admin/ticket_edit.php?ID=#result#&amp;lang=".LANGUAGE_ID;
				}
				break;
			case "DEL":
				$arFields = array("MID" => $message);
				$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"],  "MID" => "#MID#"));
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
					$arParams["~URL_TEMPLATES_MESSAGE"] ?? null,
					array("FID" => $arParams["FID"] ?? null,
						"TID" => $arParams["TID"] ?? null,
						"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"] ?? null,
						"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
				break;
			case "HIDE_TOPIC":
			case "SHOW_TOPIC":
				$arFields = array("TID" => $arParams["TID"]);
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"] ?? null,
					array("FID" => $arParams["FID"] ?? null,
						"TID" => $arParams["TID"] ?? null,
						"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"] ?? null,
						"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
				break;
			case "DEL_TOPIC":
					$arFields = array("TID" => $arParams["TID"] ?? null);
					$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_LIST"] ?? null,
						array("FID" => $arParams["FID"] ?? null));
				break;
			case "FORUM_UNSUBSCRIBE":
			case "TOPIC_UNSUBSCRIBE":
			case "FORUM_SUBSCRIBE":
			case "TOPIC_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				$arFields = array(
					"FID" => $arParams["FID"] ?? null,
					"TID" => (($action=="FORUM_SUBSCRIBE")?0:$arParams["TID"] ?? null),
					"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"] ?? null,
					"NEW_TOPIC_ONLY" => (($action=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"));
				$url = ForumAddPageParams(
						CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_SUBSCR_LIST"] ?? null,
							array()
						),
						array("FID" => $arParams["FID"] ?? null, "TID" => $arParams["TID"] ?? null));
				break;
			case "MOVE":
				$tmp_message = ForumDataToArray($message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE_MOVE"] ?? null,
						array("FID" => $arParams["FID"] ?? null, "TID" => $arParams["TID"] ?? null, "MID" => implode(",", $tmp_message)));
				break;
			case "MOVE_TOPIC":
				$url = CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_TOPIC_MOVE"] ?? null,
							array("FID" => $arParams["FID"] ?? null, "TID" => $arParams["TID"] ?? null));
				break;
		}

		if ($action != "MOVE" && $action != "MOVE_TOPIC")
		{
			$result = ForumActions($action, $arFields, $strErrorMessage, $strOKMessage);
			if (($action == "REPLY" || $action == "EDIT_TOPIC") && ($arParams["AUTOSAVE"]))
				$arParams["AUTOSAVE"]->Reset();

			if ($action == "DEL")
			{
				$arFields = CForumTopic::GetByID($arParams["TID"]);
				if (empty($arFields))
				{
					$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"],
						array("FID" => $arParams["FID"]));
					$action = "del_topic";
				}
				else
				{
					$mid = intval($message);
					if (is_array($message))
					{
						sort($message);
						$mid = array_pop($message);
					}
					$arFilter = array("TOPIC_ID"=>$arParams["TID"], ">ID" => $mid);
					if (isset($arResult["PERMISSION"]) && $arResult["PERMISSION"] < "Q")
						$arFilter["APPROVED"] = "Y";
					$db_res = CForumMessage::GetList(array("ID" => "ASC"), $arFilter, false, 1);
					if ($db_res && $res = $db_res->Fetch()):
						$mid = $res["ID"];
					else:
						unset($arFilter[">ID"]);
						$arFilter["<ID"] = $mid;
						$db_res = CForumMessage::GetList(array("ID" => "DESC"), $arFilter, false, 1);
						if ($db_res && $res = $db_res->Fetch())
							$mid = $res["ID"];
					endif;
					$mid = (intval($mid) > 0 ? $mid : "s");
					$url = str_replace("#MID#", $mid, $url);
				}
			}
			elseif ($action == "VOTE4USER")
			{
				$result = true;
			}
			elseif ($action == "REPLY")
			{
				$arParams["MID"] = intval($result);
			}

			$url = str_replace("#result#", $result, $url);
		}
		else
			$result = true;
		$action = mb_strtolower($action);
	}

	if (!$result)
	{
		$bVarsFromForm = true;
	}
	else
	{
		$arNote = array(
			"code" => $action,
			"title" => $strOKMessage,
			"link" => $url);
	}
	$arResult['RESULT'] = $result;
	if (isset($_REQUEST['AJAX_CALL']) && in_array($action, array('show', 'hide', 'del')))
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		$arRes = array('status' => (!$result === false), 'message' => ($result ? $strOKMessage : $strErrorMessage));
		echo CUtil::PhpToJSObject($arRes);
		die();
	}
}
elseif (($action <> '') && (isset($_REQUEST["MESSAGE_MODE"]) && $_REQUEST["MESSAGE_MODE"] != "VIEW") && !check_bitrix_sessid())
{
	$bVarsFromForm = true;
	$strErrorMessage = GetMessage("F_ERR_SESS_FINISH");
}
elseif(isset($post["MESSAGE_MODE"]) && $post["MESSAGE_MODE"] == "VIEW")
{
	$View = true;
	$bVarsFromForm = true;
	$arAllow = array(
		"HTML" => $arResult["FORUM"]["ALLOW_HTML"] ?? null,
		"ANCHOR" => $arResult["FORUM"]["ALLOW_ANCHOR"] ?? null,
		"BIU" => $arResult["FORUM"]["ALLOW_BIU"] ?? null,
		"IMG" => $arResult["FORUM"]["ALLOW_IMG"] ?? null,
		"VIDEO" => $arResult["FORUM"]["ALLOW_VIDEO"] ?? null,
		"LIST" => $arResult["FORUM"]["ALLOW_LIST"] ?? null,
		"QUOTE" => $arResult["FORUM"]["ALLOW_QUOTE"] ?? null,
		"CODE" => $arResult["FORUM"]["ALLOW_CODE"] ?? null,
		"FONT" => $arResult["FORUM"]["ALLOW_FONT"] ?? null,
		"SMILES" => $arResult["FORUM"]["ALLOW_SMILES"] ?? null,
		"UPLOAD" => $arResult["FORUM"]["ALLOW_UPLOAD"] ?? null,
		"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"] ?? null);
	$arAllow["SMILES"] = ($_POST["USE_SMILES"]!="Y" ? "N" : $arResult["FORUM"]["ALLOW_SMILES"]);
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
	$arResult["POST_MESSAGE_VIEW"] = $parser->convert($post["POST_MESSAGE"], $arAllow, "html", $arResult["MESSAGE_VIEW"]["FILES"]);
	$arResult["MESSAGE_VIEW"]["FILES_PARSED"] = $parser->arFilesIDParsed;
	$arResult["MESSAGE_VIEW"]["AUTHOR_NAME"] = ($USER->IsAuthorized() || empty($post["AUTHOR_NAME"]) ? $arResult["USER"]["SHOW_NAME"] : trim($post["AUTHOR_NAME"]));
	$arResult["MESSAGE_VIEW"]["TEXT"] = $arResult["POST_MESSAGE_VIEW"];
	$arResult["VIEW"] = "Y";
}
?>
