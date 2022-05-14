<?php

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_POST["site"]) && is_string($_POST["site"])) ? trim($_POST["site"]) : "";
$site_id = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);
$group_id = (isset($_POST["GROUP_ID"]) ? intval($_POST["GROUP_ID"]) : 0);
$arUserID = (isset($_POST["USER_ID"]) ? $_POST["USER_ID"] : false);
$arDepartmentID = (isset($_POST["DEPARTMENT_ID"]) ? $_POST["DEPARTMENT_ID"] : false);
$action = (isset($_POST["ACTION"]) ? $_POST["ACTION"] : false);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Main\Localization\Loc;

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
{
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
}
else
{
	define("LANGUAGE_ID", "en");
}

if (!CModule::IncludeModule("socialnetwork"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_MODULE_NOT_INSTALLED'));
	die();
}

if (!$USER->IsAuthorized())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'CURRENT_USER_NOT_AUTH'));
	die();
}

if ($group_id <= 0)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'GROUP_ID_NOT_DEFINED'));
	die();
}

$arGroup = CSocNetGroup::GetByID($group_id);
if (!$arGroup)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'GROUP_ID_NOT_DEFINED'));
	die();
}

if (
	$action === 'UNCONNECT_DEPT'
	&& (!is_array($arDepartmentID) || empty($arDepartmentID))
)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'DEPARTMENT_ID_NOT_DEFINED'));
	die();
}

if (
	$action !== 'UNCONNECT_DEPT'
	&& (!is_array($arUserID) || empty($arUserID))
)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ID_NOT_DEFINED'));
	die();
}

if (check_bitrix_sessid())
{
	$arCurrentUserPerms = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
		'groupId' => $arGroup['ID'],
	]);
	if (!$arCurrentUserPerms || !$arCurrentUserPerms["UserCanViewGroup"] || !$arCurrentUserPerms["UserCanModifyGroup"])
	{
		echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_GROUP_NO_PERMS'));
		die();
	}

	if ($action === 'UNCONNECT_DEPT')
	{
		if (
			isset($arDepartmentID)
			&& is_array($arDepartmentID)
			&& !empty($arDepartmentID)
			&& is_array($arGroup["UF_SG_DEPT"])
			&& !empty($arGroup["UF_SG_DEPT"])
		)
		{
			$arGroup["UF_SG_DEPT"] = array_map('intval', array_unique($arGroup["UF_SG_DEPT"]));
			$arDepartmentIDs = array_map('intval', array_unique($arDepartmentID));
			if (!CSocNetGroup::Update($arGroup["ID"], array(
				'UF_SG_DEPT' => array_diff($arGroup["UF_SG_DEPT"], $arDepartmentID)
			)))
			{
				echo CUtil::PhpToJsObject(Array('ERROR' => 'DEPARTMENT_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
				die();
			}
		}
	}
	else
	{
		$arRelationID = array();
		$arRelalationData = array();
		$rsRelation = CSocNetUserToGroup::GetList(
			array("ID" => "DESC"),
			array(
				"USER_ID" => $arUserID,
				"GROUP_ID" => $arGroup["ID"]
			),
			false,
			false,
			array("ID", "USER_ID", "GROUP_ID", "ROLE", "AUTO_MEMBER")
		);
		while($arRelation = $rsRelation->Fetch())
		{
			$arRelationID[] = $arRelation["ID"];
			$arRelalationData[] = $arRelation;
		}

		if ($action === 'U2M' && !CSocNetUserToGroup::TransferMember2Moderator($USER->GetID(), $arGroup["ID"], $arRelationID))
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
			die();
		}

		if ($action === 'M2U' && !CSocNetUserToGroup::TransferModerator2Member($USER->GetID(), $arGroup["ID"], $arRelationID))
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
			die();
		}

		if ($action === 'SETOWNER')
		{
			if (!CSocNetUserToGroup::SetOwner($arUserID[0], $arGroup["ID"], $arGroup))
			{
				echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
				die();
			}
		}
		elseif ($action === 'ADDMODERATOR')
		{
			$error = false;
			$arUserPerms = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
				'groupId' => $arGroup['ID'],
			]);

			if (!$arUserPerms["UserCanModifyGroup"])
			{
				$error = true;
				$APPLICATION->throwException(CSocNetUserToGroup::getMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			}

			if (!$error)
			{
				if (UserToGroup::addModerators(array(
					'group_id' => $arGroup["ID"],
					'user_id' => $arUserID[0],
				)))
				{
					UserToGroup::addInfoToChat(array(
						'group_id' => $arGroup["ID"],
						'user_id' => $arUserID[0],
						'action' => UserToGroup::CHAT_ACTION_IN,
						'sendMessage' => false,
						'role' => \Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR
					));
				}
				else
				{
					$error = true;
				}
			}

			if ($error)
			{
				echo CUtil::phpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->getException()) ? $e->GetString() : "")));
				die();
			}
		}
		elseif ($action === 'BAN' && !CSocNetUserToGroup::BanMember($USER->GetID(), $arGroup["ID"], $arRelationID, CSocNetUser::IsCurrentUserModuleAdmin()))
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
			die();
		}
		elseif ($action === 'UNBAN' && !CSocNetUserToGroup::UnBanMember($USER->GetID(), $arGroup["ID"], $arRelationID, CSocNetUser::IsCurrentUserModuleAdmin()))
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
			die();
		}
		elseif ($action === 'EX')
		{
			foreach ($arRelalationData as $relationData)
			{
				//group owner can't exclude himself from the group
				if ($relationData["ROLE"] === SONET_ROLES_OWNER)
				{
					echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF'));
					die();
				}

				if ($relationData["AUTO_MEMBER"] === 'Y')
				{
					echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER'));
					die();
				}

				if ((int)$relationData['USER_ID'] === $arGroup['SCRUM_MASTER_ID'])
				{
					echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_GUE_T_CANT_EXCLUDE_SCRUM_MASTER'));
					die();
				}

				if (!CSocNetUserToGroup::Delete($relationData["ID"], true))
				{
					echo CUtil::PhpToJsObject(Array('ERROR' => 'USER_ACTION_FAILED: '.(($e = $APPLICATION->GetException()) ? $e->GetString() : "")));
					die();
				}

				CSocNetSubscription::DeleteEx($relationData["USER_ID"], "SG".$relationData["GROUP_ID"]);
			}
		}
	}

	echo CUtil::PhpToJsObject(Array('SUCCESS' => 'Y'));
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
