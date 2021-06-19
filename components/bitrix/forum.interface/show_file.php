<?
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("NO_KEEP_STATISTIC", true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true || !CModule::IncludeModule("forum"))die();

/**
 * @global CUser $USER
 */

$MESS = array();
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/show_file.php");
include_once($path);
$MESS1 =& $MESS;
$GLOBALS["MESS"] = $MESS1 + $GLOBALS["MESS"];

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams = array(
	"FILE_ID" => intval($_REQUEST["fid"]),
	"WIDTH" => intval($_REQUEST['width']),
	"HEIGHT" => intval($_REQUEST['height']),
	"ACTION" => ($_REQUEST["action"] == "download" ? "download" : "view"),
	"PERMISSION" => false
);
// *************************/Input params***************************************************************
// ************************* Default params*************************************************************
$arResult = array(
	"MESSAGE" => array(),
	"FILE" => array()
);
$arError = array();
if (intval($arParams["FILE_ID"]) > 0)
{
	$db_res = CForumFiles::GetList(array("ID" => "ASC"), array("FILE_ID" => $arParams["FILE_ID"]));
	if ($db_res && ($arResult["FILE"] = $db_res->GetNext()))
	{
		$res = CFile::GetFileArray($arParams["FILE_ID"]);
		if (!!$res)
			$arResult["FILE"] += $res;
	}
}

if (empty($arResult["FILE"]))
{
	$arError = array(
		"code" => "EMPTY FILE",
		"title" => GetMessage("F_EMPTY_FID"));
}
elseif (intval($arResult["FILE"]["MESSAGE_ID"]) > 0)
{
	$arResult["MESSAGE"] = CForumMessage::GetByIDEx($arResult["FILE"]["MESSAGE_ID"], array("GET_FORUM_INFO" => "Y", "GET_TOPIC_INFO" => "Y"));
	$arResult["TOPIC"] = $arResult["MESSAGE"]["TOPIC_INFO"];
	$arResult["FORUM"] = $arResult["MESSAGE"]["FORUM_INFO"];

	if (IsModuleInstalled('meeting'))
	{
		$forumId = COption::GetOptionInt('meeting', 'comments_forum_id', 0, SITE_ID);
		$forumId = ($forumId > 0 ? $forumId : COption::GetOptionInt('meeting', 'comments_forum_id', 0));
		if ($arResult['FORUM']['ID'] == $forumId && CModule::IncludeModule('meeting'))
		{
			$meetingID = false;
			$xmlID = $arResult['MESSAGE']['FT_XML_ID'];
			preg_match('/MEETING_ITEM_([0-9]+)/', $xmlID, $matches);
			if (sizeof($matches) > 0)
			{
				$meetingItemID = $matches[1];
				if (CMeetingItem::HasAccess($meetingItemID))
					$arParams['PERMISSION'] = 'M';
			}

			preg_match('/MEETING_([0-9]+)/', $xmlID, $matches);
			if (sizeof($matches) > 0)
			{
				$meetingID = $matches[1];
				if (CMeeting::GetUserRole($meetingID) !== false)
					$arParams['PERMISSION'] = 'M';
			}

		}
	}

	if (CModule::IncludeModule('tasks'))
	{
		$tasksIsTasksJurisdiction = false;

		// Insurance for cross-modules version compatibility
		if (method_exists('CTasksTools','ListTasksForumsAsArray'))
		{
			try
			{
				$arTasksForums = CTasksTools::ListTasksForumsAsArray();

				if (in_array((int) $arResult['FORUM']['ID'], $arTasksForums, true))
					$tasksIsTasksJurisdiction = true;
			}
			catch (TasksException $e)
			{
				// do nothing
			}
		}
		else
		{
			// TODO: this old code section to be removed in next versions.
			$forumId = COption::GetOptionString('tasks', 'task_forum_id', -1);
			if (
				($forumId !== (-1)) 
				&& ((int) $arResult['FORUM']['ID'] === (int) $forumId)
			)
			{
				$tasksIsTasksJurisdiction = true;
			}
		}

		if ($tasksIsTasksJurisdiction)
		{
			$arParams['PERMISSION'] = 'D';

			if (CTasks::CanCurrentUserViewTopic($arResult['TOPIC']['ID']))
				$arParams['PERMISSION'] = 'M';
		}
	}

	if (empty($arParams["PERMISSION"]))
	{
		$arParams["PERMISSION"] = CForumNew::GetUserPermission($arResult["MESSAGE"]["FORUM_ID"], $USER->GetUserGroupArray());

		if ($arParams["PERMISSION"] < "E" && (intval($arResult["TOPIC"]["SOCNET_GROUP_ID"]) > 0 ||
			intval($arResult["TOPIC"]["OWNER_ID"]) > 0) && CModule::IncludeModule("socialnetwork"))
		{
			$sPermission = $arParams["PERMISSION"];
			$user_id = $USER->GetID();
			$group_id = intval($arResult["TOPIC"]["SOCNET_GROUP_ID"]);
			$owner_id = intval($arResult["TOPIC"]["OWNER_ID"]);

			if ($group_id):

				$arSonetGroup = CSocNetGroup::GetByID($group_id);
				if ($arSonetGroup)
					$site_id_tmp = $arSonetGroup["SITE_ID"];
				else
					$site_id_tmp = false;

				$bIsCurrentUserModuleAdmin = CSocNetUser::IsCurrentUserModuleAdmin($site_id_tmp);

				if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $group_id, "forum", "full", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "Y";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $group_id, "forum", "newtopic", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "M";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $group_id, "forum", "answer", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "I";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $group_id, "forum", "view", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "E";
				endif;
			else:

				$arForumSites = CForumNew::GetSites($arResult["FORUM"]["ID"]);
				if (count($arForumSites) > 0)
				{
					$key = key($arForumSites);
					if ($key <> '')
						$site_id_tmp = $key;
					else
						$site_id_tmp = false;
				}
				else
					$site_id_tmp = false;

				$bIsCurrentUserModuleAdmin = CSocNetUser::IsCurrentUserModuleAdmin($site_id_tmp);

				if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $owner_id, "forum", "full", CForumUser::IsAdmin($user_id))):
					$arParams["PERMISSION"] = "Y";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $owner_id, "forum", "newtopic", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "M";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $owner_id, "forum", "answer", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "I";
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $owner_id, "forum", "view", $bIsCurrentUserModuleAdmin)):
					$arParams["PERMISSION"] = "E";
				endif;
			endif;

			$arParams["PERMISSION"] = ($arParams["PERMISSION"] < $sPermission ? $sPermission : $arParams["PERMISSION"]);
		}
	}

	if (empty($arResult["MESSAGE"]))
	{
		$arError = array(
			"code" => "EMPTY MESSAGE",
			"title" => GetMessage("F_EMPTY_MID"));
	}
	elseif ($arParams["PERMISSION"])
	{
		if ($arParams["PERMISSION"] < "E")
			$arError = array(
				"code" => "NOT RIGHT",
				"title" => GetMessage("F_NOT_RIGHT"));
	}
	elseif (ForumCurrUserPermissions($arResult["MESSAGE"]["FORUM_ID"]) < "E")
	{
		$arError = array(
			"code" => "NOT RIGHT",
			"title" => GetMessage("F_NOT_RIGHT"));
	}
}


if (!empty($arError))
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
	ShowError((!empty($arError["title"]) ? $arError["title"] : $arError["code"]));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
	die();
}
// *************************/Default params*************************************************************

set_time_limit(0);

$options = array();
if ($arParams["ACTION"] == "download")
{
	$options["force_download"] = true;
}

if (CFile::IsImage($arResult["FILE"]["ORIGINAL_NAME"], $arResult["FILE"]["CONTENT_TYPE"]))
{
	if ($arParams['WIDTH'] > 0 && $arParams['HEIGHT'] > 0)
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arResult['FILE'],
			array("width" => $arParams["WIDTH"], "height" => $arParams["HEIGHT"]),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);
		$arResult['FILE']["FILE_SIZE"] = $arFileTmp['size'];
		$arResult['FILE']["SRC"] = $arFileTmp['src'];
	}
}

CFile::ViewByUser($arResult["FILE"], $options);
