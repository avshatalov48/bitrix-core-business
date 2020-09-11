<?
global $APPLICATION, $DBType;
IncludeModuleLangFile(__FILE__);

if (file_exists(__DIR__."/deprecated.php"))
{
	include("deprecated.php");
}

$arNameStatuses = @unserialize(COption::GetOptionString("forum", "statuses_name"));
$arNameStatuses = is_array($arNameStatuses) ? $arNameStatuses : array();
$arNameStatuses[LANGUAGE_ID] = is_array($arNameStatuses[LANGUAGE_ID]) ? $arNameStatuses[LANGUAGE_ID] : array();
$name = array("guest" => "Guest", "user" => "User", "moderator" => "Moderator", "editor" => "Editor", "administrator" => "Administrator");
foreach ($name as $k => $v):
	$name[$k] = trim(!empty($arMess["F_".mb_strtoupper($k)]) ? $arMess["F_".mb_strtoupper($k)] : $name[$k]);
	$arNameStatuses[LANGUAGE_ID][$k] = htmlspecialcharsbx(empty($arNameStatuses[LANGUAGE_ID][$k]) ? $name[$k] : $arNameStatuses[LANGUAGE_ID][$k]);
endforeach;

$GLOBALS["FORUM_STATUS_NAME"] = $arNameStatuses[LANGUAGE_ID];
$GLOBALS["SHOW_FORUM_DEBUG_INFO"] = false;
$GLOBALS["FORUM_CACHE"] = array(
	"FORUM" => array(),
	"MESSAGE" => array(),
	"USER" => array(),
	"TOPIC" => array(),
	"TOPIC_INFO" => array());
/* cache structure:
	[forum] [forum_id]
			main - main info about forum
			ex - extra (additional) info
			ex_site - extra info with site path
			permission - array permission for group on forum array(implode("-", group_id_array) => permission)
			permissions - array permission with permissions for all groups on forum array(group_id => permission)
			sites - site for forum array(site_id => path)
	message - i do not know
	topic - i do not know
	topic_filter - i do not know
	user - i do not know
	path to clear cache
*/
if(!defined("CACHED_b_forum_group"))
	define("CACHED_b_forum_group", 3600);
if(!defined("CACHED_b_forum"))
	define("CACHED_b_forum", 3600);
if(!defined("CACHED_b_forum_perms"))
	define("CACHED_b_forum_perms", 3600);
if(!defined("CACHED_b_forum2site"))
	define("CACHED_b_forum2site", 3600);
if(!defined("CACHED_b_forum_filter"))
	define("CACHED_b_forum_filter", 3600);
if(!defined("CACHED_b_forum_user"))
	define("CACHED_b_forum_user", 3600);
\Bitrix\Main\Loader::registerAutoLoadClasses(
	"forum",
	array(
		"bitrix\\forum\\internals\\basetable" => "lib/internals/basetable.php",
		"bitrix\\forum\\comments\\comment" => "lib/comments/comment.php",
		"bitrix\\forum\\comments\\entity" => "lib/comments/entity.php",
		"bitrix\\forum\\comments\\eventmanager" => "lib/comments/eventmanager.php",
		"bitrix\\forum\\comments\\feed" => "lib/comments/feed.php",
		"bitrix\\forum\\comments\\taskentity" => "lib/comments/taskentity.php",
		"bitrix\\forum\\comments\\user" => "lib/comments/user.php",
		"bitrix\\forum\\forum" => "lib/forum.php",
		"bitrix\\forum\\badwords\\dictionary" => "lib/badwords/dictionary.php",
		"bitrix\\forum\\badwords\\filter" => "lib/badwords/filter.php",
		"bitrix\\forum\\badwords\\letter" => "lib/badwords/letter.php",

		"textParser" => "classes/general/functions.php",
		"forumTextParser" => "classes/general/functions.php",

		"CForumNew" =>   "classes/".$DBType."/forum_new.php",
		"CForumGroup" => "classes/".$DBType."/forum_new.php",
		"CForumSmile" => "classes/general/forum_new.php",
		"_CForumDBResult"=>"classes/general/forum_new.php",

		"CForumTopic" => "classes/".$DBType."/topic.php",
		"_CTopicDBResult" => "classes/general/topic.php",

		"CForumMessage" => "classes/".$DBType."/message.php",
		"CForumFiles" => "classes/".$DBType."/message.php",
		"_CMessageDBResult" => "classes/general/message.php",

		"CForumEventLog" => "classes/general/event_log.php",

		"CFilterDictionary" => "classes/".$DBType."/filter_dictionary.php",
		"CFilterLetter" => "classes/".$DBType."/filter_dictionary.php",
		"CFilterUnquotableWords" => "classes/".$DBType."/filter_dictionary.php",

		"CForumPMFolder" => "classes/".$DBType."/private_message.php",
		"CForumPrivateMessage" => "classes/".$DBType."/private_message.php",

		"CForumPoints" => "classes/".$DBType."/points.php",
		"CForumPoints2Post" => "classes/".$DBType."/points.php",
		"CForumUserPoints" => "classes/".$DBType."/points.php",

		"CForumRank" => "classes/".$DBType."/user.php",
		"CForumStat" => "classes/".$DBType."/user.php",
		"CForumSubscribe" => "classes/".$DBType."/user.php",
		"CForumUser" => "classes/".$DBType."/user.php",

		"CForumParameters" => "tools/components_lib.php",
		"CForumEMail" => "mail/mail.php",
		"CForumFormat" => "tools/components_lib.php",
		"CRatingsComponentsForum" => "classes/".$DBType."/ratings_components.php",
		"CEventForum" => "classes/general/event_log.php",

		"CForumCacheManager" => "classes/general/functions.php",
		"CForumAutosave" => "classes/general/functions.php",
		"CForumDBTools" => "tools/dbtools.php",
		"CForumNotifySchema" => "classes/general/forum_notify_schema.php",

		"CForumRestService" => "classes/general/rest.php",
	));

new CForumCacheManager();
\Bitrix\Forum\Comments\EventManager::init();

function ForumCurrUserPermissions($FID, $arAddParams = array())
{
	static $arCache = array();
	$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
	$arAddParams["PERMISSION"] = (!!$arAddParams["PERMISSION"] ? $arAddParams["PERMISSION"] : '');
	if (! isset($arCache[$FID.$arAddParams["PERMISSION"]]))
	{
		if (CForumUser::IsAdmin())
		{
			$result = "Y";
		}
		else
		{
			$strPerms = (!!$arAddParams["PERMISSION"] ? $arAddParams["PERMISSION"] : CForumNew::GetUserPermission($FID, $GLOBALS["USER"]->GetUserGroupArray()));
			if ($strPerms <= "E")
			{
				$result = $strPerms;
			}
			elseif (CForumUser::IsLocked($GLOBALS["USER"]->GetID()))
			{
				$strPerms = CForumNew::GetPermissionUserDefault($FID);
				$result = ($strPerms >= "E" ? $strPerms : "E");
			}
			else
			{
				$result = $strPerms;
			}
		}
		$arCache[$FID.$arAddParams["PERMISSION"]] = $result;
	}

	return $arCache[$FID.$arAddParams["PERMISSION"]];
}

function ForumSubscribeNewMessagesEx($FID, $TID, $NEW_TOPIC_ONLY, &$strErrorMessage, &$strOKMessage, $strSite = false, $SOCNET_GROUP_ID = false)
{
	if ($strSite===false)
		$strSite = SITE_ID;

	return ForumSubscribeNewMessages($FID, $TID, $strErrorMessage, $strOKMessage, $NEW_TOPIC_ONLY, $strSite, $SOCNET_GROUP_ID);
}

function ForumUnsubscribeNewMessagesEx($FID, $TID, $NEW_TOPIC_ONLY, &$strErrorMessage, &$strOKMessage, $strSite = false, $SOCNET_GROUP_ID = false)
{
	if ($strSite===false)
		$strSite = SITE_ID;

	return ForumUnsubscribeNewMessages($FID, $TID, $strErrorMessage, $strOKMessage, $NEW_TOPIC_ONLY, $strSite, $SOCNET_GROUP_ID);
}

function ForumUnsubscribeNewMessages($FID, $TID, &$strErrorMessage, &$strOKMessage, $NEW_TOPIC_ONLY = "N", $strSite = false, $SOCNET_GROUP_ID = false)
{
	global $USER;

	$strSite = ($strSite===false ? SITE_ID : $strSite);
	$FID = intval($FID);
	$TID = intval($TID);
	$arError = array();
	$arNote = array();

	if (!$USER->IsAuthorized())
	{
		$arError[] = GetMessage("FORUM_SUB_ERR_AUTH");
	}
	else
	{
		$arFields = array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $FID,
			"SITE_ID" => $strSite,
			"TOPIC_ID" => ($TID>0) ? $TID : false);
		if($SOCNET_GROUP_ID>0)
			$arFields['SOCNET_GROUP_ID'] = $SOCNET_GROUP_ID;
		$db_res = CForumSubscribe::GetListEx(array(), $arFields);
		if ($db_res && ($res = $db_res->Fetch()))
		{
			if (!CForumSubscribe::CanUserDeleteSubscribe($res['ID'], $USER->GetUserGroupArray(), $USER->GetID()))
				$arError[] = GetMessage("FORUM_SUB_ERR_PERMS");
			else
				CForumSubscribe::Delete($res["ID"]);
		}
		else
			$arError[] = GetMessage("FORUM_SUB_ERR_UNSUBSCR");
	}

	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError);
	if (!empty($arNote))
		$strOKMessage .= implode(".\n", $arNote);
	if (empty($arError))
		return True;
	else
		return False;
}

function ForumSubscribeNewMessages($FID, $TID, &$strErrorMessage, &$strOKMessage, $NEW_TOPIC_ONLY = "N", $strSite = false, $SOCNET_GROUP_ID = false)
{
	global $USER;

	$strSite = ($strSite===false ? SITE_ID : $strSite);
	$FID = intval($FID);
	$TID = intval($TID);
	$arError = array();
	$arNote = array();

	if (!$USER->IsAuthorized())
	{
		$arError[] = GetMessage("FORUM_SUB_ERR_AUTH");
	}
	elseif ($SOCNET_GROUP_ID==false && !CForumSubscribe::CanUserAddSubscribe($FID, $USER->GetUserGroupArray()))
	{
		$arError[] = GetMessage("FORUM_SUB_ERR_PERMS");
	}
	else
	{
		$arFields = array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $FID,
			"SITE_ID" => $strSite,
			"TOPIC_ID" => ($TID>0) ? $TID : false);
		if($SOCNET_GROUP_ID>0)
			$arFields['SOCNET_GROUP_ID'] = $SOCNET_GROUP_ID;
		$db_res = CForumSubscribe::GetListEx(array(), $arFields);
		if ($db_res && ($res = $db_res->Fetch()))
		{
			$sError = GetMessage("FORUM_SUB_ERR_ALREADY_TOPIC");
			if ($TID <= 0)
			{
				if ($res["NEW_TOPIC_ONLY"] == "Y")
				{
					$sError = GetMessage("FORUM_SUB_ERR_ALREADY_NEW");
					if ($NEW_TOPIC_ONLY != $res["NEW_TOPIC_ONLY"])
						$sError = str_replace("#FORUM_NAME#", htmlspecialcharsbx($res["FORUM_NAME"]),
							GetMessage("FORUM_SUB_ERR_ALREADY_ALL_HELP"));
				}
				else
				{
					$sError = GetMessage("FORUM_SUB_ERR_ALREADY_ALL");
					if ($NEW_TOPIC_ONLY != $res["NEW_TOPIC_ONLY"])
						$sError = str_replace("#FORUM_NAME#", htmlspecialcharsbx($res["FORUM_NAME"]),
							GetMessage("FORUM_SUB_ERR_ALREADY_NEW_HELP"));
				}
			}
			$arError[] = $sError;
		}
		else
		{
			$arFields["NEW_TOPIC_ONLY"] = (($arFields["TOPIC_ID"]!==false) ? "N" : $NEW_TOPIC_ONLY );

			$subid = CForumSubscribe::Add($arFields);
			if (intval($subid)>0)
			{
				if ($TID>0)
					$arNote[] = GetMessage("FORUM_SUB_OK_MESSAGE_TOPIC");
				else
					$arNote[] = GetMessage("FORUM_SUB_OK_MESSAGE");
			}
			else
			{
				$arError[] = GetMessage("FORUM_SUB_ERR_UNKNOWN");
			}
		}
	}

	if (!empty($arError))
		$strErrorMessage .= implode(".\n",$arError);
	if (!empty($arError))
		$strOKMessage .= implode(".\n",$arNote);

	if (empty($arError))
		return True;
	else
		return False;
}

function ForumGetRealIP()
{
	$ip = false;
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		foreach ($ips as $ipst)
		{
			// Skip RFC 1918 IP's 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16
			if (!preg_match("/^(10|172\.16|192\.168)\./", $ipst) && preg_match("/^[^.]+\.[^.]+\.[^.]+\.[^.]+/", $ipst))
			{
				$ip = $ipst;
				break;
			}
		}
	}
	// Return with the found IP or the remote address
	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

function ForumAddMessage(
	$MESSAGE_TYPE, $FID, $TID, $MID, $arFieldsG,
	&$strErrorMessage, &$strOKMessage,
	$iFileSize = false,
	$captcha_word = "", $captcha_sid = 0, $captcha_code = "")
{
	try
	{
		global $USER;
		$forum = \Bitrix\Forum\Forum::getById($FID);
		$usr = \Bitrix\Forum\User::getById($USER->GetID());
		//region 0. CAPTCHA
		if (!$USER->IsAuthorized() && $forum["USE_CAPTCHA"]=="Y")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");

			$cpt = new CCaptcha();
			if ($captcha_code <> '')
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code))
				{
					throw new \Bitrix\Main\AccessDeniedException(GetMessage("FORUM_POSTM_CAPTCHA"));
				}
			}
			else if (!$cpt->CheckCode($captcha_word, $captcha_sid))
			{
				throw new \Bitrix\Main\AccessDeniedException(GetMessage("FORUM_POSTM_CAPTCHA"));
			}
		}
		//endregion
		//region 1. Set permission
		if ($usr->getPermissionOnForum($FID) < \Bitrix\Forum\Permission::CAN_MODERATE)
		{
			if (!empty($arFieldsG["PERMISSION_EXTERNAL"]))
			{
				$usr->setPermissionOnForum($FID, $arFieldsG["PERMISSION_EXTERNAL"]);
			}
			elseif (!empty($arFieldsG["SONET_PERMS"]))
			{
				$externalPermission = "A";
				if ($arFieldsG["SONET_PERMS"]["bCanFull"] === true)
					$externalPermission = "Y";
				elseif ($arFieldsG["SONET_PERMS"]["bCanNew"] === true)
					$externalPermission = "M";
				elseif ($arFieldsG["SONET_PERMS"]["bCanWrite"] === true)
					$externalPermission = "I";
				$usr->setPermissionOnForum($FID, $externalPermission);
			}
		}
		//endregion
		//region 2. Collect data
		$arFieldsG["POST_MESSAGE"] = trim($arFieldsG["POST_MESSAGE"]);
		$arFieldsG["USE_SMILES"] = ($arFieldsG["USE_SMILES"] == "Y" ? "Y" : "N");
		if (array_key_exists("ATTACH_IMG", $arFieldsG))
		{
			unset($arFieldsG["ATTACH_IMG"]);
			$arFieldsG["FILES"] = [$arFieldsG["ATTACH_IMG"]];
		}
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("FORUM_MESSAGE", $arFieldsG);
		//endregion
		//region 3. Check permission & action
		if ($MESSAGE_TYPE == "EDIT")
		{
			if (!$usr->canModerate($forum) || $arFieldsG["EDIT_ADD_REASON"] === "Y")
			{
				$arFieldsG["EDITOR_ID"] = $usr->getId();
				$arFieldsG["EDITOR_NAME"] = $usr->getName();
				$arFieldsG["EDITOR_EMAIL"] = trim($arFieldsG["EDITOR_EMAIL"]);
				$arFieldsG["EDIT_REASON"] = trim($arFieldsG["EDIT_REASON"]);
				$arFieldsG["EDIT_DATE"] = new \Bitrix\Main\Type\DateTime();
			}
			else
			{
				$arFieldsG["EDITOR_ID"] = 0;
				$arFieldsG["EDITOR_NAME"] = "";
				$arFieldsG["EDITOR_EMAIL"] = "";
				$arFieldsG["EDIT_REASON"] = "";
				$arFieldsG["EDIT_DATE"] = "";
			}

			if (array_key_exists("TITLE", $arFieldsG))
			{
				$topic = \Bitrix\Forum\Topic::getById($TID);

				if (!$usr->canEditTopic($topic))
				{
					throw new \Bitrix\Main\AccessDeniedException(GetMessage("ADDMESS_NO_PERMS2EDIT"));
				}
				$result = $topic->edit($arFieldsG);
			}
			else
			{
				$message = \Bitrix\Forum\Message::getById($MID);
				if (!$usr->canEditMessage($message))
				{
					throw new \Bitrix\Main\AccessDeniedException(GetMessage("ADDMESS_NO_PERMS2EDIT"));
				}
				$result = $message->edit($arFieldsG);
			}
		}
		else
		{
			$arFieldsG["AUTHOR_ID"] = $usr->getId();
			$arFieldsG["AUTHOR_EMAIL"] = trim($arFieldsG["AUTHOR_EMAIL"]);
			$arFieldsG["AUTHOR_NAME"] = trim($arFieldsG["AUTHOR_NAME"]);
			if ($arFieldsG["AUTHOR_NAME"] == '' && $usr->getId() > 0)
			{
				$arFieldsG["AUTHOR_NAME"] = $usr->getName();
			}
			$arFieldsG["APPROVED"] = $forum["MODERATION"] != "Y" || $usr->canModerate($forum) ? "Y" : "N";
			$arFieldsG["POST_DATA"] = new \Bitrix\Main\Type\DateTime();

			if ($MESSAGE_TYPE == "NEW") // New Topic
			{
				if (!$usr->canAddTopic($forum))
				{
					throw new \Bitrix\Main\AccessDeniedException(GetMessage("ADDMESS_NO_PERMS2NEW"));
				}
				$result = \Bitrix\Forum\Topic::create($forum, $arFieldsG);
			}
			else
			{
				$topic = \Bitrix\Forum\Topic::getById($TID);
				if (!$usr->canAddMessage($topic))
				{
					throw new \Bitrix\Main\AccessDeniedException(GetMessage("ADDMESS_NO_PERMS2REPLY"));
				}

				$result = \Bitrix\Forum\Message::create($topic, $arFieldsG);
			}
		}
		//endregion
		//region 5.Send mail
		if ($result->isSuccess())
		{
			$MID = $result->getId();
			if ($MESSAGE_TYPE == "NEW" || $MESSAGE_TYPE == "REPLY")
			{
				CForumMessage::SendMailMessage($MID, array(), false, "NEW_FORUM_MESSAGE");
				if ($arFieldsG["APPROVED"] != "Y")
				{
					$strOKMessage = GetMessage("ADDMESS_AFTER_MODERATE").". \n";
				}
				else
				{
					$strOKMessage = GetMessage("ADDMESS_SUCCESS_ADD").". \n";
				}
			}
			else
			{
				CForumMessage::SendMailMessage($MID, array(), false, "EDIT_FORUM_MESSAGE");
				$strOKMessage = GetMessage("ADDMESS_SUCCESS_EDIT").". \n";
			}
			return $MID;
		}
		else
		{
			$strErrorMessage = implode("\n", $result->getErrorMessages());
			return false;
		}

	}
	catch(Exception $e)
	{
		$strErrorMessage = $e->getMessage();
		return false;
	}
}

function ForumModerateMessage($message, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("DELMES_NO_MESS").". \n";
	}
	else
	{
		$db_res = CForumMessage::GetList(array(), array("@ID" => implode(",", $message)));
		if ($db_res)
		{
			while ($arMessage = $db_res->Fetch())
			{
				if (!(ForumCurrUserPermissions($arMessage["FORUM_ID"], $arAddParams) >= "Q" ||
					CForumMessage::CanUserUpdateMessage($arMessage["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"])))
					$arError[] = GetMessage("MODMESS_NO_PERMS")." (MID=".$arMessage["ID"]."). \n";
				else
				{
					$arFields = array("APPROVED" => ($TYPE == "SHOW" ? "Y" : "N"));
					$ID = CForumMessage::Update($arMessage["ID"], $arFields);
					if ($ID > 0)
					{
						$TID = $arMessage["TOPIC_ID"];
						$arTopic = CForumTopic::GetByID($TID);
						/***************** Events onMessageModerate ************************/
						foreach (GetModuleEvents("forum", "onMessageModerate", true) as $arEvent)
							ExecuteModuleEventEx($arEvent, array($ID, $TYPE, $arMessage, $arTopic));
						/***************** /Events *****************************************/
						$res =  array(
								"ID" => $arMessage["ID"],
								"AUTHOR_NAME" => $arMessage["AUTHOR_NAME"],
								"POST_MESSAGE" => $arMessage["POST_MESSAGE"],
								"TITLE" => $arTopic["TITLE"],
								"TOPIC_ID" => $TID,
								"FORUM_ID" => $arMessage["FORUM_ID"]
						);
						$res = serialize($res);
						if ($TYPE == "SHOW")
						{
							$arOK[] = GetMessage("MODMESS_SUCCESS_SHOW")." (MID=".$arMessage["ID"]."). \n";
							CForumMessage::SendMailMessage($arMessage["ID"], array(), false, "NEW_FORUM_MESSAGE");
							CForumEventLog::Log("message", "approve", $arMessage["ID"], $res);
						}
						else
						{
							$arOK[] = GetMessage("MODMESS_SUCCESS_HIDE")." (MID=".$arMessage["ID"]."). \n";
							CForumMessage::SendMailMessage($arMessage["ID"], array(), false, "EDIT_FORUM_MESSAGE");
							CForumEventLog::Log("message", "unapprove", $arMessage["ID"], $res);
						}
					}
					else
					{
						$arError[] = GetMessage("MODMESS_ERROR_MODER")." (MID=".$arMessage["ID"]."). \n";
					}
				}
			}
		}
		else
			$arError[] = GetMessage("DELMES_NO_MESS").". \n";
	}
	$strErrorMessage .= implode("", $arError);
	$strOKMessage .= implode("", $arOK);

	if (count($arError) <= 0)
		return true;
	else
		return false;
}

function ForumOpenCloseTopic($topicIds, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	$topicIds = is_array($topicIds) ? $topicIds : [$topicIds];
	$arError = array();
	$arOk = array();
	$arAddParams = (is_array($arAddParams) ? $arAddParams : []);
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);


	global $USER;
	$usr = \Bitrix\Forum\User::getById($USER->GetID());
	foreach ($topicIds as $topicId)
	{
		$topic = \Bitrix\Forum\Topic::getById($topicId);
		$forum = \Bitrix\Forum\Forum::getById($topic->getForumId());
		if (is_string($arAddParams["PERMISSION"]))
		{
			$usr->setPermissionOnForum($forum, $arAddParams["PERMISSION"]);
		}
		if (!$usr->canModerate($forum))
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT") . " (TID={$topic->getId()})";
		}
		else
		{
			$result = ($TYPE === "OPEN" ? $topic->open() : $topic->close());

			if (!$result->isSuccess())
			{
				$arError[] = ($TYPE === "CLOSE" ? GetMessage("OCTOP_ERROR_CLOSE") : GetMessage("OCTOP_ERROR_OPEN")) . " (TID={$topic->getId()})";
			}
			else if (!empty($result->getData()))
			{
				$arOk[] = ($TYPE === "CLOSE" ? GetMessage("OCTOP_SUCCESS_CLOSE") : GetMessage("OCTOP_SUCCESS_OPEN")) . " (TID={$topic->getId()})";
			}
		}
	}

	$strOKMessage .= implode(".\n", $arOk);

	if (count($arError) > 0)
	{
		$strErrorMessage .= implode(".\n", $arError).".\n";
		return false;
	}

	return true;
}

function ForumTopOrdinaryTopic($topic, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOk = array();
	$arFields = array("SORT" => ($TYPE == "TOP" ? 100 : 150));
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);

	$topic = ForumDataToArray($topic);
	$forumID = 0;
	if (empty($topic))
	{
		$arError[] = GetMessage("TOTOP_NO_TOPIC");
	}
	else
	{
		if (!CForumUser::IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				if ($arAddParams["PERMISSION"] && !CForumTopic::CanUserUpdateTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					$arError[] = GetMessage("FMT_NO_PERMS_MODERATE")." (TID=".intval($res["ID"]).")";
					continue;
				}
				$ID = CForumTopic::Update($res["ID"], $arFields, True);
				if (intval($ID)<=0)
				{
					if ($TYPE=="TOP")
						$arError[] = GetMessage("TOTOP_ERROR_TOP")." (TID=".intval($res["ID"]).")";
					else
						$arError[] = GetMessage("TOTOP_ERROR_TOP1")." (TID=".intval($res["ID"]).")";
				}
				else
				{
					$forumID = $res['FORUM_ID'];
					$arTopic["SORT"] = $arFields["SORT"];
					$log = serialize($res);
					if ($TYPE=="TOP"):
						$arOk[] = GetMessage("TOTOP_SUCCESS_TOP")." (TID=".intval($res["ID"]).")";
						CForumEventLog::Log("topic", "stick", $ID, $log);
					else:
						$arOk[] = GetMessage("TOTOP_SUCCESS_TOP1")." (TID=".intval($res["ID"]).")";
						CForumEventLog::Log("topic", "unstick", $ID, $log);
					endif;
				}
			}while ($res = $db_res->Fetch());
			if (intval($forumID) > 0)
				CForumCacheManager::ClearTag("F", $forumID);
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";

	if (empty($arError))
		return true;
	else
		return false;
}

function ForumDeleteTopic($topic, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;

	$arError = array();
	$arOk = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams);
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);

	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("DELTOP_NO_TOPIC");
	}
	else
	{
		if (!CForumUser::IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				if (CForumTopic::CanUserDeleteTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					if (CForumTopic::Delete($res["ID"]))
					{
						$arOk[] = GetMessage("DELTOP_OK")." (TID=".intval($res["ID"]).")";
						CForumCacheManager::ClearTag("F", $res['FORUM_ID']);
						CForumCacheManager::ClearTag("T", $res["ID"]);
						CForumEventLog::Log("topic", "delete", $res["ID"], serialize($res));
					}
					else
					{
						$arError[] = GetMessage("DELTOP_NO")." (TID=".intval($res["ID"]).")";
					}
				}
				else
				{
					$arError[] = GetMessage("DELTOP_NO_PERMS")." (TID=".intval($res["ID"]).")";
				}

			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";

	if (count($arError) > 0)
		return false;
	else
		return true;
}

function ForumDeleteMessage($message, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);

	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("DELMES_NO_MESS");
	}
	else
	{
		foreach ($message as $MID)
		{
			if (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				$arError[] = GetMessage("DELMES_NO_PERMS")."(MID=".$MID.")";
			else
			{
				$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
				if (CForumMessage::Delete($MID)):
					$arOK[] = GetMessage("DELMES_OK")."(MID=".$MID.")";
					$TID = $arMessage["TOPIC_ID"];
					$arTopic = CForumTopic::GetByID($TID);
					$arMessage["TITLE"] = $arTopic["TITLE"];
					CForumEventLog::Log("message", "delete", $MID, serialize($arMessage));
				else:
					$arError[] = GetMessage("DELMES_NO")."(MID=".$MID.")";
				endif;
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arOK))
		$strOKMessage .= implode(".\n", $arOK).".\n";
	return (empty($arError) ? true : false);
}

function ForumSpamTopic($topic, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;

	$arError = array();
	$arOk = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams);
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);

	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("SPAMTOP_NO_TOPIC");
	}
	else
	{
		if (!CForumUser::IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				if (CForumTopic::CanUserDeleteTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					$db_mes = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID" => $res["ID"]));
					if ($db_mes && $mes = $db_mes->Fetch() && CModule::IncludeModule("mail"))
					{
						CMailMessage::MarkAsSpam($mes["XML_ID"], "Y");
					}

					if (CForumTopic::Delete($res["ID"]))
					{
						$arOk[] = GetMessage("SPAMTOP_OK")." (TID=".intval($res["ID"]).")";
						CForumEventLog::Log("topic", "spam", $res["ID"], print_r($res, true).print_r($mes, true));
					}
					else
					{
						$arError[] = GetMessage("SPAMTOP_NO")." (TID=".intval($res["ID"]).")";
					}
				}
				else
				{
					$arError[] = GetMessage("SPAMTOP_NO_PERMS")." (TID=".intval($res["ID"]).")";
				}
			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";

	if (count($arError) > 0)
		return false;
	else
		return true;
}

function ForumSpamMessage($message, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("SPAM_NO_MESS");
	}
	else
	{
		foreach ($message as $MID)
		{
			if (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				$arError[] = GetMessage("SPAM_NO_PERMS")."(MID=".$MID.")";
			else
			{
				$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
				if (CModule::IncludeModule("mail"))
				{
					CMailMessage::MarkAsSpam($arMessage["XML_ID"], "Y");
				}
				if (CForumMessage::Delete($MID)):
					$arOK[] = GetMessage("SPAM_OK")."(MID=".$MID.")";
					CForumEventLog::Log("message", "spam", $MID, print_r($arMessage, true));
				else:
					$arError[] = GetMessage("SPAM_NO")."(MID=".$MID.")";
				endif;
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arOK))
		$strOKMessage .= implode(".\n", $arOK).".\n";
	return (empty($arError) ? true : false);
}
function ForumMessageExistInArray($message = array())
{
	$message_exist = false;
	$result = array();
	if (!is_array($message))
		$message = explode(",", $message);

	foreach ($message as $message_id)
	{
		if (intval(trim($message_id)) > 0)
		{
			$result[] = intval(trim($message_id));
			$message_exist = true;
		}
	}

	if ($message_exist)
		return $result;
	else
		return false;
}

function ForumDeleteMessageArray($message, &$strErrorMessage, &$strOKMessage)
{
	return ForumDeleteMessage($message, $strErrorMessage, $strOKMessage);
}

function ForumModerateMessageArray($message, $TYPE, &$strErrorMessage, &$strOKMessage)
{
	return ForumModerateMessage($message, $TYPE, $strErrorMessage, $strOKMessage);
}


function ForumShowTopicPages($nMessages, $strUrl, $pagen_var = "PAGEN_1", $PAGE_ELEMENTS = false)
{
	global $FORUM_MESSAGES_PER_PAGE;
	$res_str = "";

	if ((!$PAGE_ELEMENTS) && (intval($PAGE_ELEMENTS) <= 0))
		$PAGE_ELEMENTS = $FORUM_MESSAGES_PER_PAGE;

	if (mb_strpos($strUrl, "?") === false)
		$strUrl = $strUrl."?";
	else
		$strUrl = $strUrl."&amp;";

	if ($nMessages > $PAGE_ELEMENTS)
	{
		$res_str .= "<small>(".GetMessage("FSTP_PAGES").": ";

		$nPages = intval(ceil($nMessages / $PAGE_ELEMENTS));
		$typeDots = true;
		for ($i = 1; $i <= $nPages; $i++)
		{
			if ($i<=3 || $i>=$nPages-2 || ($nPages == 7 && $i == 3))
			{
				$res_str .= "<a href=\"".$strUrl.$pagen_var."=".$i."\">".$i."</a> ";
			}
			elseif ($typeDots)
			{
				$res_str .= "... ";
				$typeDots = false;
			}
		}
		$res_str .= ")</small>";
	}
	return $res_str;
}

function ForumMoveMessage($FID, $TID, $Message, $NewTID = 0, $arFields, &$strErrorMessage, &$strOKMessage, $iFileSize = false)
{
	global $USER, $DB;
	$arError = array();
	$arOK = array();
	$NewFID = 0;
	$arForum = array();
	$arTopic = array();
	$arNewForum = array();
	$arNewTopic = array();
	$arCurrUser = array();
	$SendSubscribe = false;

//************************* Input params **************************************************************************
	$TID = intval($TID);
	$FID = intval($FID);
	$NewTID = intval($NewTID);
	$Message = ForumDataToArray($Message);
	if (empty($Message))
		$arError[] = GetMessage("FMM_NO_MESSAGE");
	if ($TID <= 0)
		$arError[] = GetMessage("FMM_NO_TOPIC_SOURCE0");
	else
	{
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			$FID = intval($arTopic["FORUM_ID"]);
			$arForum = CForumNew::GetByID($FID);
		}
		else
			$arError[] = GetMessage("FMM_NO_TOPIC_SOURCE1");
	}

	if (($NewTID <= 0) && (trim($arFields["TITLE"]) == ''))
		$arError[] = GetMessage("FMM_NO_TOPIC_RECIPIENT0");
	elseif($NewTID > 0)
	{
		if ($NewTID == $TID)
			$arError[] = GetMessage("FMM_NO_TOPIC_EQUAL");
		$arNewTopic = CForumTopic::GetByID($NewTID);

		if (!$arNewTopic)
			$arError[] = GetMessage("FMM_NO_TOPIC_RECIPIENT1");
		elseif ($arNewTopic["STATE"] == "L")
			$arError[] = GetMessage("FMM_TOPIC_IS_LINK");
		else
		{
			$NewFID =  $arNewTopic["FORUM_ID"];
			$arNewForum = CForumNew::GetByID($NewFID);
		}
	}
//*************************/Input params **************************************************************************
//*************************!Proverka prav pol'zovatelya na forume-istochnike i forume-poluchatele*********************
// Tak kak realizovan mehanizm peremeweniya tem s forumov, gde tekuwij pol'zovatel' yavlyaetsya moderatorom na forumy,
// gde on moderatorov ne yavlyaetsya, to v dannom sluchae budet ispol'zovan tot zhe samyj shablon dejstvij. Isklyucheniem
// yavlyaetsya to, chto esli pol'zovatel' na forume-poluchatele ne obladaet pravami moderirovaniya, tema budet neaktivna.
//*************************!Proverka prav pol'zovatelya*************************************************************
	$arCurrUser["Perms"]["FID"] = ForumCurrUserPermissions($FID);
	$arCurrUser["Perms"]["NewFID"] = ForumCurrUserPermissions($NewFID);
	if ($arCurrUser["Perms"]["FID"] < "Q")
		$arError[] = GetMessage("FMM_NO_MODERATE");
//************************* Actions *******************************************************************************
	$DB->StartTransaction();
	if (count($arError) <= 0)
	{
		// Create topic
		if ($NewTID <= 0)
		{
			$arFields["APPROVED"] = ($arNewForum["MODERATION"]=="Y") ? "N" : "Y";
			if ($arCurrUser["Perms"]["NewFID"] >= "Q")
				$arFields["APPROVED"] = "Y";

			$arRes = array("NAME" => GetMessage("FR_GUEST"));
			$ShowName = GetMessage("FR_GUEST");
			$db_res = CForumMessage::GetList(array("ID" => "ASC"), array("@ID" => implode(",", $Message), "TOPIC_ID" => $TID));
			if ($db_res && $res = $db_res->Fetch())
			{
				$arRes["NAME"] = $res["AUTHOR_NAME"];
				$arRes["ID"] = $res["AUTHOR_ID"];
			}
			$arFieldsTopic = array(
				"TITLE"			=> $arFields["TITLE"],
				"TITLE_SEO"			=> $arFields["TITLE_SEO"],
				"DESCRIPTION"	=> $arFields["DESCRIPTION"],
				"ICON"		=> $arFields["ICON"],
				"TAGS"		=> $arFields["TAGS"],
				"FORUM_ID"		=> $FID,
				"USER_START_ID" => $arRes["ID"],
				"USER_START_NAME" => $arRes["NAME"],
				"LAST_POSTER_NAME" => $arRes["NAME"],
				"LAST_POSTER_ID" => $arRes["ID"],
				"APPROVED" => $arFields["APPROVED"],
			);
			$NewTID = CForumTopic::Add($arFieldsTopic);
			if (intval($NewTID)<=0)
				$arError[] = GetMessage("FMM_NO_TOPIC_NOT_CREATED");
			else
			{
				$arNewTopic = CForumTopic::GetByID($NewTID);
				if ($arNewTopic)
				{
					$NewFID = $FID;
					$arNewForum = $arForum;
					$SendSubscribe = true;
				}
				else
					$arError[] = GetMessage("FMM_NO_TOPIC_NOT_CREATED");
			}
		}
	}

	if (count($arError) <= 0)
	{
		// Move message
		$db_res = CForumMessage::GetList(array(), array("@ID" => implode(",", $Message), "TOPIC_ID" => $TID));
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
//				echo "NewFID: ".$NewFID." -- FID:".$FID."<br/>";
				$arMessage = array();
				if ($NewFID != $FID)
				{
					$arMessage["APPROVED"] = ($arNewForum["MODERATION"] == "Y" ? "N" : "Y");
					if ($arCurrUser["Perms"]["NewFID"] >= "Q")
						$arMessage["APPROVED"] = "Y";

					$arMessage["FORUM_ID"] = $NewFID;
					$arMessage["POST_MESSAGE_HTML"] = "";
				}

				if ($NewTID != $TID)
				{
					$arMessage["NEW_TOPIC"] = "N";
					$arMessage["TOPIC_ID"] = $NewTID;
				}

				if (count($arMessage) > 0)
				{
					$MID = CForumMessage::Update($res["ID"], $arMessage, true);
					$res_log = ($SendSubscribe == true ? GetMessage("F_MESSAGE_WAS_MOVED_TO_NEW") : GetMessage("F_MESSAGE_WAS_MOVED"));
					$res_log = str_replace(array("#ID#", "#TOPIC_TITLE#", "#TOPIC_ID#", "#NEW_TOPIC_TITLE#", "#NEW_TOPIC_ID#"),
						array($MID, $arTopic["TITLE"], $arTopic["ID"], $arNewTopic['TITLE'], $arNewTopic['ID']), $res_log);
					$res["TITLE"] = $arNewTopic['TITLE'];
					$res["TOPIC_ID"] = $arNewTopic['ID'];
					$res["beforeTITLE"] = $arTopic["TITLE"];
					$res["DESCRIPTION"] = $res_log;
					CForumEventLog::Log("message", "move", $MID, serialize($res));
					$db_res2 = CForumFiles::GetList(array(), array("FILE_MESSAGE_ID" => $res["ID"]));
					if ($db_res2 && $res2 = $db_res2->Fetch())
					{
						$arFiles = array();
						do
						{
							$arFiles[] = $res2["FILE_ID"];
						} while ($res2 = $db_res2->Fetch());
						CForumFiles::UpdateByID($arFiles, $arMessage);
					}
					if (intval($MID) <= 0)
					{
						$arError[] = str_replace("##", $res["ID"], GetMessage("FMM_NO_MESSAGE_MOVE"));
						break;
					}
				}
			}while ($res = $db_res->Fetch());
		}
	}

	if (count($arError) <= 0)
	{
		$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $TID), false, 1);
		if (!($db_res && $res = $db_res->Fetch())):
			CForumTopic::Delete($TID);
		else:
			CForumTopic::SetStat($TID);
		endif;

		$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $NewTID), false, 1);
		if (!($db_res && $res = $db_res->Fetch())):
			CForumTopic::Delete($NewTID);
		else:
			CForumTopic::SetStat($NewTID);
		endif;

		CForumNew::SetStat($FID);
		if ($NewFID != $FID)
			CForumNew::SetStat($NewFID);
	}
	if (count($arError) <= 0)
		$DB->Commit();
	else
		$DB->Rollback();

	if (count($arError) > 0)
		$strErrorMessage .= implode(". \n", $arError).". \n";
	else
	{
		$strOKMessage .= GetMessage("FMM_YES_MESSAGE_MOVE");
		if ($SendSubscribe)
		{
			foreach ($Message as $MID)
				CForumMessage::SendMailMessage($MID, array(), false, "NEW_FORUM_MESSAGE");
		}
		return true;
	}
	return false;
}

/**
 * @param $num_cols
 * @deprecated
 * @return string
 */
function ForumPrintIconsList($num_cols, $value = "")
{
	$arSmile = CForumSmile::getByType("I", LANGUAGE_ID);
	$arSmile[] = array('TYPING' => '', 'IMAGE' => '/bitrix/images/1.gif', 'NAME' => '', 'CLASS' => 'forum-icon-empty');
	$strPath2Icons = "/bitrix/images/forum/icon/";
	$num_cols = ($num_cols > 0 ? $num_cols : 7);
	$ind = $num_cols;
	$res_str = '<table border="0" class="forum-icons"><tr>';

	foreach ($arSmile as $res)
	{
		$width = ($res["IMAGE_WIDTH"] > 0 ? 'width="{$res["IMAGE_WIDTH"]}"' : '');
		$height = ($res["IMAGE_HEIGHT"] > 0 ? 'width="{$res["IMAGE_HEIGHT"]}"' : '');
		$checked = '';
		if (trim($res['TYPING']) == trim($value))
		{
			$checked = 'checked="checked"';
		}

		$res_str .= <<<HTML
		<td>
			<img src="{$strPath2Icons}{$res["IMAGE"]}" alt="{$res["NAME"]}" border="0" class="icons {$res["CLASS"]}" $width $height />
			<input type="radio" name="ICON" value="{$res["TYPING"]}" $checked />
		</td>
HTML;

		if (--$ind <= 0)
		{
			$ind = $num_cols;
			$res_str .= "</tr><tr>";
		}
	}
	$res_str .= '</tr></table>';
	return $res_str;
}

/**
 * @deprecated
 * @param $num_cols
 * @param bool $strLang
 * @return string
 */
function ForumPrintSmilesList($num_cols, $strLang = false)
{
	$num_cols = intval($num_cols);
	$num_cols = $num_cols > 0 ? $num_cols : 3;
	$strLang = ($strLang === false ? LANGUAGE_ID : $strLang);
	$strPath2Icons = "/bitrix/images/forum/smile/";
	$arSmile = CForumSmile::getByType("S", $strLang);

	$res_str = "";
	$ind = 0;
	foreach ($arSmile as $res)
	{
		if ($ind == 0) $res_str .= "<tr align=\"center\">";
		$res_str .= "<td width=\"".intval(100/$num_cols)."%\">";
		$strTYPING = strtok($res['TYPING'], " ");
		$res_str .= "<img src=\"".$strPath2Icons.$res['IMAGE']."\" alt=\"".$res['NAME']."\" title=\"".$res['NAME']."\" border=\"0\"";
		if (intval($res['IMAGE_WIDTH'])>0) $res_str .= " width=\"".$res['IMAGE_WIDTH']."\"";
		if (intval($res['IMAGE_HEIGHT'])>0) $res_str .= " height=\"".$res['IMAGE_HEIGHT']."\"";
		$res_str .= " class=\"smiles-list\" alt=\"smile".$strTYPING."\" onclick=\"if(emoticon){emoticon('".$strTYPING."');}\" name=\"smile\"  id='".$strTYPING."' ";
		$res_str .= "/>&nbsp;</td>\n";
		$ind++;
		if ($ind >= $num_cols)
		{
			$ind = 0;
			$res_str .= "</tr>";
		}
	}
	if ($ind < $num_cols)
	{
		for ($i=0; $i<$num_cols-$ind; $i++)
		{
			$res_str .= "<td> </td>";
		}
	}

	return $res_str;
}

function ForumMoveMessage2Support($MID, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$MID = intval($MID);
	$sError = array();
	$sNote = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	if ($MID<=0)
		$arError[] = GetMessage("MOVEMES_NO_MESS_EX");

	if (!CModule::IncludeModule("support"))
		$arError[] = GetMessage("MOVEMES_NO_SUPPORT");

	if (empty($arError))
	{
		$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
		if (!$arMessage)
		{
			$arError[] = GetMessage("MOVEMES_NO_MESS_EX");
		}
		elseif (intval($arMessage["AUTHOR_ID"])<=0)
		{
			$arError[] = GetMessage("MOVEMES_NO_ANONYM");
		}
		elseif (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
		{
			$arError[] = GetMessage("MOVEMES_NO_PERMS2MOVE");
		}
		else
		{
			$arTopic = CForumTopic::GetByID($arMessage["TOPIC_ID"]);
			$arFieldsSu = array(
				"CLOSE"			=> "N",
				"TITLE"			=> $arTopic["TITLE"],
				"MESSAGE"		=> $arMessage["POST_MESSAGE"],
				"OWNER_USER_ID"	=> $arMessage["AUTHOR_ID"],
				"OWNER_SID"		=> $arMessage["AUTHOR_NAME"],
				"SOURCE_SID"	=> "forum",
				);

			$arIMAGE = CFile::MakeFileArray($arMessage["ATTACH_IMG"]);
			if(is_array($arIMAGE))
			{
				$arIMAGE["MODULE_ID"] = "support";
				$arFieldsSu["FILES"] = array($arIMAGE);
			}

			$SuID = CTicket::SetTicket($arFieldsSu);
			$SuID = intval($SuID);

			if ($SuID>0)
			{
				$sNote[] = GetMessage("MOVEMES_SUCCESS_SMOVE");
			}
			else
			{
				$arError[] = GetMessage("MOVEMES_ERROR_SMOVE");
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n",$arError).".\n";
	if (!empty($arNote))
		$strOKMessage .= implode(".\n",$arNote).".\n";

	if (empty($arError))
		return $SuID;
	else
		return False;
}

function ForumVote4User($UID, $VOTES, $bDelVote, &$strErrorMessage, &$strOKMessage)
{
	global $USER;
	$arError = array();
	$arNote = array();

	$UID = intval($UID);
	$VOTES = intval($VOTES);
	$bDelVote = ($bDelVote ? true : false);
	$CurrUserID = 0;

	if ($UID <= 0)
	{
		$arError[] = GetMessage("F_NO_VPERS");
	}
	else
	{
		if (!$USER->IsAuthorized())
		{
			$arError[] = GetMessage("FORUM_GV_ERROR_AUTH");
		}
		else
		{
			$CurrUserID = intval($USER->GetParam("USER_ID"));
			if ($CurrUserID == $UID && !CForumUser::IsAdmin())
			{
				$arError[] = GetMessage("FORUM_GV_OTHER");
			}
			else
			{
				$arUserRank = CForumUser::GetUserRank($CurrUserID);

				if (intval($arUserRank["VOTES"])<=0 && !$bDelVote && !CForumUser::IsAdmin())
				{
					$arError[] = GetMessage("FORUM_GV_ERROR_NO_VOTE");
				}
				else
				{
					if (!CForumUser::IsAdmin() || $VOTES<=0)
						$VOTES = intval($arUserRank["VOTES"]);

					if ($VOTES == 0) $VOTES = 1; // no ranks configured

					$arFields = array(
						"POINTS" => $VOTES
						);

					$arUserPoints = CForumUserPoints::GetByID($CurrUserID, $UID);
					if ($arUserPoints)
					{
						if ($bDelVote || $VOTES<=0)
						{
							if (CForumUserPoints::Delete($CurrUserID, $UID))
								$arNote[] = GetMessage("FORUM_GV_SUCCESS_UNVOTE");
							else
								$arError[] = GetMessage("FORUM_GV_ERROR_VOTE");
						}
						else
						{
							if (intval($arUserPoints["POINTS"])<intval($arUserRank["VOTES"])
								|| CForumUser::IsAdmin())
							{
								if (CForumUserPoints::Update(intval($USER->GetParam("USER_ID")), $UID, $arFields))
									$arNote[] = GetMessage("FORUM_GV_SUCCESS_VOTE_UPD");
								else
									$arError[] = GetMessage("FORUM_GV_ERROR_VOTE_UPD");
							}
							else
							{
								$arError[] = GetMessage("FORUM_GV_ALREADY_VOTE");
							}
						}
					}
					else
					{
						if (!$bDelVote && $VOTES>0)
						{
							$arFields["FROM_USER_ID"] = $USER->GetParam("USER_ID");
							$arFields["TO_USER_ID"] = $UID;

							if (CForumUserPoints::Add($arFields))
								$arNote[] = GetMessage("FORUM_GV_SUCCESS_VOTE_ADD");
							else
								$arError[] = GetMessage("FORUM_GV_ERROR_VOTE_ADD");
						}
						else
						{
							$arError[] = GetMessage("FORUM_GV_ERROR_A");
						}
					}
				}
			}
		}
	}

	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arNote))
		$strOKMessage .= implode(".\n", $arNote).".\n";

	if (empty($arError))
		return True;
	else
		return False;
}

function ShowActiveUser($arFields = array())
{
	$period = intval($arFields["PERIOD"]);
	if ($period <= 0)
		$period = 600;

	$date = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), time() - $period + CTimeZone::GetOffset());
	$arField = array(">=LAST_VISIT" => $date, "COUNT_GUEST"=>true);
	if (intval($arFields["FORUM_ID"]) > 0 )
		$arField["FORUM_ID"] = $arFields["FORUM_ID"];
	if (intval($arFields["TOPIC_ID"]) > 0 )
		$arField["TOPIC_ID"] = $arFields["TOPIC_ID"];

	$db_res = CForumStat::GetListEx(array("USER_ID" => "DESC"), $arField);
	$OnLineUser = array();
	$arOnLineUser = array();
	$OnLineUserStr = "";
	$UserHideOnLine = 0;
	$UserOnLine = 0;
	$result = array();
	$result["NONE"]	= "N";
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$OnLineUser["USER"] = array();
		do
		{
			if (($res["USER_ID"] > 0) && ($res["HIDE_FROM_ONLINE"] != "Y"))
			{
				$OnLineUser["USER"][] = "<a href=\"view_profile.php?UID=".$res["USER_ID"]."\" title='".GetMessage("FORUM_USER_PROFILE")."'>".$res["SHOW_NAME"]."</a>";
				$arOnLineUser[] = array_merge($res, array("UID"=>$res["USER_ID"], "title" => GetMessage("FORUM_USER_PROFILE"), "text" => $res["SHOW_NAME"]));
			}
			elseif(($res["USER_ID"] > 0) && ($res["HIDE_FROM_ONLINE"] == "Y"))
				$UserHideOnLine++;
			else
				$OnLineUser["GUEST"] = intval($res["COUNT_USER"]);
		}while ($res = $db_res->GetNext());

		$CountAllUsers = count($OnLineUser["USER"]) + $UserHideOnLine + $OnLineUser["GUEST"];
		$result["GUEST"] = $OnLineUser["GUEST"];
		$result["HIDE"] = $UserHideOnLine;
		$result["REGISTER"] = intval(count($OnLineUser["USER"])+$UserHideOnLine);
		$result["ALL"] = $CountAllUsers;

		if ($CountAllUsers > 0)
		{
			if (intval($arFields["TOPIC_ID"]) <= 0)
			{
				$result["PERIOD"] = round($period/60);
				$result["HEAD"] = str_replace("##", "<b>".round($period/60)."</b>", GetMessage("FORUM_AT_LAST_PERIOD"))." ".
				GetMessage("FORUM_COUNT_ALL_USER").": <b>".$CountAllUsers."</b><br/>";
			}
			$OnLineUserStr = GetMessage("FORUM_COUNT_GUEST").": <b>".intval($OnLineUser["GUEST"])."</b>, ".
				GetMessage("FORUM_COUNT_USER").": <b>".intval(count($OnLineUser["USER"])+$UserHideOnLine)."</b>,
				".GetMessage("FORUM_FROM_THIS")." ".GetMessage("FORUM_COUNT_USER_HIDEFROMONLINE").": <b>".$UserHideOnLine."</b>";

			if (count($OnLineUser["USER"]) > 0)
			{
				$OnLineUserStr .= "<br/>".implode(", ", $OnLineUser["USER"])."<br/>";
				$result["USER"] = $arOnLineUser;
			}
		}
		else
		{
			$OnLineUserStr = GetMessage("FORUM_NONE");
			$result["NONE"] = "Y";
		}
	}
	else
	{
		$OnLineUserStr = GetMessage("FORUM_NONE");
		$result["NONE"] = "Y";
	}
	$result["BODY"] = $OnLineUserStr;
	return $result;
}

function ForumGetUserForumStatus($userID = false, $perm = false, $arAdditionalParams = array())
{
	$arStatuses = array(
		"guest" => array("guest", $GLOBALS["FORUM_STATUS_NAME"]["guest"]),
		"user" => array("user", $GLOBALS["FORUM_STATUS_NAME"]["user"]),
		"Q" => array("moderator", $GLOBALS["FORUM_STATUS_NAME"]["moderator"]),
		"U" => array("editor", $GLOBALS["FORUM_STATUS_NAME"]["editor"]),
		"Y" => array("administrator", $GLOBALS["FORUM_STATUS_NAME"]["administrator"])
	);
	$res = ($userID === false ? $arStatuses : $arStatuses["guest"]);
	if (!empty($userID))
	{
		$res = $arStatuses["user"];
		if ($arStatuses[$perm])
			$res = $arStatuses[$perm];
		else
		{
			$arRank = (is_set($arAdditionalParams, "Rank") ?
				$arAdditionalParams["Rank"] : CForumUser::GetUserRank($userID, LANGUAGE_ID));
			if (is_array($arRank) && $arRank["NAME"])
				$res = array($arRank["CODE"], $arRank["NAME"]);
		}
	}
	return $res;
}

function ForumAddPageParams($page_url="", $params=array(), $addIfNull = false, $htmlSpecialChars = true)
{
	$strUrl = "";
	$strParams = "";
	$arParams = array();
	$param = "";
	// Attention: $page_url already is safe.
	if (is_array($params) && (count($params) > 0))
	{
		foreach ($params as $key => $val)
		{
			if ((is_array($val) && (count($val) > 0)) || (($val <> '') && ($val!="0")) || (intval($val) > 0) || $addIfNull)
			{
				if (is_array($val))
					$param = implode(",", $val);
				else
					$param = $val;
				if (($param <> '') || ($addIfNull))
				{
					if (mb_strpos($page_url, $key) !== false)
					{
						$page_url = preg_replace("/".$key."\=[^\&]*((\&amp\;)|(\&)*)/", "", $page_url);
					}
					$arParams[] = $key."=".$param;
				}
			}
		}

		if (count($arParams) > 0)
		{
			if (mb_strpos($page_url, "?") === false)
				$strParams = "?";
			elseif ((mb_substr($page_url, -5, 5) != "&amp;") && (mb_substr($page_url, -1, 1) != "&") && (mb_substr($page_url, -1, 1) != "?"))
			{
				$strParams = "&";
			}
			$strParams .= implode("&", $arParams);
			if ($htmlSpecialChars)
				$page_url .= htmlspecialcharsbx($strParams);
			else
				$page_url .= $strParams;
		}
	}
	return $page_url;
}

function ForumActions($action, $arFields, &$strErrorMessage, &$strOKMessage)
{
	global $USER;
	$result = false;
	$sError = "";
	$sNote = "";
	if (empty($action))
	{
		$sError = GetMessage("FORUM_NO_ACTION");
	}
	else
	{
		switch ($action)
		{
			case "REPLY":
				$result = ForumAddMessage("REPLY", $arFields["FID"], $arFields["TID"], 0, $arFields, $sError, $sNote, false, $arFields["captcha_word"], 0, $arFields["captcha_code"], $arFields["NAME_TEMPLATE"]);
				break;
			case "DEL":
				$result = ForumDeleteMessage($arFields["MID"], $sError, $sNote, $arFields);
			break;
			case "SHOW":
			case "HIDE":
				$result = ForumModerateMessage($arFields["MID"], $action, $sError, $sNote, $arFields);
				break;
			case "VOTE4USER":
				$result = ForumVote4User($arFields["UID"], $arFields["VOTES"], $arFields["VOTE"], $sError, $sNote, $arFields);
			break;
			case "FORUM_MESSAGE2SUPPORT":
				$result = ForumMoveMessage2Support($arFields["MID"], $sError, $sNote, $arFields);
			break;
			case "FORUM_SUBSCRIBE":
			case "TOPIC_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				$result = ForumSubscribeNewMessagesEx($arFields["FID"], $arFields["TID"], $arFields["NEW_TOPIC_ONLY"], $sError, $sNote);
			break;
			case "SET_ORDINARY":
			case "SET_TOP":
			case "ORDINARY":
			case "TOP":
				if ($action == "SET_ORDINARY")
					$action = "ORDINARY";
				elseif ($action == "SET_TOP")
					$action = "TOP";

				$result = ForumTopOrdinaryTopic($arFields["TID"], $action, $sError, $sNote, $arFields);
			break;
			case "DEL_TOPIC":
				$result =  ForumDeleteTopic($arFields["TID"], $sError, $sNote, $arFields);
			break;
			case "OPEN":
			case "CLOSE":
			case "STATE_Y":
			case "STATE_N":
				if ($action == "STATE_Y")
					$action = "OPEN";
				elseif ($action == "STATE_N")
					$action = "CLOSE";
				$result = ForumOpenCloseTopic($arFields["TID"], $action, $sError, $sNote, $arFields);
			break;
			case "SHOW_TOPIC":
			case "HIDE_TOPIC":
				$topicIds = is_array($arFields["TID"]) ? $arFields["TID"] : [$arFields["TID"]];
				$result = new \Bitrix\Main\Result();
				$usr = \Bitrix\Forum\User::getById($USER->GetID());
				foreach ($topicIds as $topicId)
				{
					$topic = \Bitrix\Forum\Topic::getById($topicId);
					$forum = \Bitrix\Forum\Forum::getById($topic->getForumId());
					if (is_string($arFields["PERMISSION"]))
					{
						$usr->setPermissionOnForum($forum, $arFields["PERMISSION"]);
					}
					if (!$usr->canModerate($forum))
					{
						$result->addError(new \Bitrix\Main\Error(GetMessage("MODMESS_NO_PERMS"). "(TID={$topic->getId()})"));
					}
					else
					{
						$res = ($action == "HIDE_TOPIC" ? $topic->disapprove() : $topic->approve());
						if (!$res->isSuccess())
						{
							$result->addErrors($res->getErrors());
						}
					}
				}
				if (!$result->isSuccess())
				{
					$sError = implode("", $result->getErrorMessages());
				}
				$result = $result->isSuccess();
			break;
			case "SPAM_TOPIC":
				$result =  ForumSpamTopic($arFields["TID"], $sError, $sNote, $arFields);
			break;
			case "SPAM":
				$result = ForumSpamMessage($arFields["MID"], $sError, $sNote, $arFields);
			break;
			default:
				$sError = GetMessage("FORUM_NO_ACTION")." (".htmlspecialcharsbx($action).")";
			break;
		}
	}
	$strErrorMessage = $sError;
	$strOKMessage = $sNote;
	return $result;
}

function ForumDataToArray(&$message)
{
	if (!is_array($message))
		$message = explode(",", $message);

	foreach ($message as $key => $val)
	{
		$message[$key] = intval(trim($val));
	}

	if (array_sum($message) > 0)
		return $message;
	else
		return false;
}

function ForumGetTopicSort(&$field_name, &$direction, $arForumInfo = array())
{
	$aSortOrder = array(
		"P" => "LAST_POST_DATE",
		"T" => "TITLE",
		"N" => "POSTS",
		"V" => "VIEWS",
		"D" => "START_DATE",
		"A" => "USER_START_NAME");
	if (empty($field_name) && !empty($arForumInfo))
	{
		$field_name = trim($arForumInfo["ORDER_BY"]);
		$direction = trim($arForumInfo["ORDER_DIRECTION"]);
	}

	$field_name = mb_strtoupper($field_name);
	$direction = mb_strtoupper($direction);

	$field_name = (!empty($aSortOrder[$field_name]) ? $aSortOrder[$field_name] : (in_array($field_name, $aSortOrder) ? $field_name : "LAST_POST_DATE"));
	$direction = ($direction == "ASC" ? "ASC" : "DESC");
	return array($field_name => $direction);
}

function ForumShowError($arError, $bShowErrorCode = false)
{
	$bShowErrorCode = ($bShowErrorCode === true ? true : false);
	$sReturn = "";
	$tmp = false;
	$arRes = array();
	if (empty($arError))
		return $sReturn;
	elseif (!is_array($arError))
		return $arError;

	if (!empty($arError["title"]) || !empty($arError["code"]))
	{
		$res = $arError;
		$sReturn .= (!empty($res["title"]) ? $res["title"] : $res["code"]).
			($bShowErrorCode ? "[CODE: ".$res["code"]."]" : "");
		unset($arError["code"]); unset($arError["title"]);
	}
	foreach ($arError as $res):
		$sReturn .= (!empty($res["title"]) ? $res["title"] : $res["code"]).
			($bShowErrorCode ? "[CODE: ".$res["code"]."]" : "")." ";
	endforeach;
	return $sReturn;
}

function ForumClearComponentCache($components)
{
	if (empty($components))
		return false;
	$aComponents = (is_array($components) ? $components : explode(",", $components));

	foreach($aComponents as $component_name)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($component_name);
		if ($componentRelativePath <> '')
		{
			$arComponentDescription = CComponentUtil::GetComponentDescr($component_name);
			if (is_array($arComponentDescription) && array_key_exists("CACHE_PATH", $arComponentDescription))
			{
				if($arComponentDescription["CACHE_PATH"] == "Y")
					$arComponentDescription["CACHE_PATH"] = "/".SITE_ID.$componentRelativePath;
				if($arComponentDescription["CACHE_PATH"] <> '')
					BXClearCache(true, $arComponentDescription["CACHE_PATH"]);
			}
		}
	}
}

function InitSortingEx($Path=false, $sByVar="by", $sOrderVar="order")
{
	static $ii = -1;
	$ii++;
	global $APPLICATION, ${$sByVar}, ${$sOrderVar};
	$sByVarE = $sByVar . $ii;
	$sOrderVarE = $sOrderVar . $ii;
	global ${$sByVarE}, ${$sOrderVarE};

	if($Path===false)
		$Path = $APPLICATION->GetCurPage();

	$md5Path = md5($Path);
	if (!empty(${$sByVarE}))
		$_SESSION["SESS_SORT_BY_EX"][$md5Path][$sByVarE] = ${$sByVarE};
	else
		${$sByVarE} = $_SESSION["SESS_SORT_BY_EX"][$md5Path][$sByVarE];

	if(!empty(${$sOrderVarE}))
		$_SESSION["SESS_SORT_ORDER_EX"][$md5Path][$sOrderVarE] = ${$sOrderVarE};
	else
		${$sOrderVarE} = $_SESSION["SESS_SORT_ORDER_EX"][$md5Path][$sOrderVarE];

	mb_strtolower(${$sByVarE});
	mb_strtolower(${$sOrderVarE});
	${$sByVar} = ${$sByVarE};
	${$sOrderVar} = ${$sOrderVarE};
	return $ii;
}
function ForumGetEntity($entityId, $value = true)
{
	static $arForumGetEntity = array();
	if (array_key_exists($entityId, $arForumGetEntity))
		return $arForumGetEntity[$entityId];
	$arForumGetEntity[$entityId] = $value;
	return false;
}
?>
