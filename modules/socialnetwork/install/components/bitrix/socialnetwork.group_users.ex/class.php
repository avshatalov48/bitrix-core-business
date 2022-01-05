<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

final class SocialnetworkGroupUsersEx extends CBitrixComponent
{
	protected function getUserList($key, $arParams, $arResult, $arNavParams)
	{
		global $USER;

		$userList = false;
		$bUseLogin = $arParams['SHOW_LOGIN'] !== "N" ? true : false;

		if (
			(
				$key === 'UsersAuto'
				&& (
					$arParams["USE_AUTO_MEMBERS"] !== "Y"
					|| !$arResult["bIntranetInstalled"]
				)
			)
			|| (
				$key === 'Ban'
				&& !(
					$arParams["GROUP_USE_BAN"] === "Y"
					&& $arResult["CurrentUserPerms"]
					&& $arResult["CurrentUserPerms"]["UserCanModerateGroup"]
				)
			)
		)
		{
			return $userList;
		}

		$arSelect = array("ID", "USER_ID", "USER_ACTIVE", "ROLE", "DATE_CREATE", "DATE_UPDATE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_IS_ONLINE", "USER_WORK_POSITION");

		$arFilter = array(
			"GROUP_ID" => $arResult["Group"]["ID"],
		);

		switch($key)
		{
			case "Users":
			case "UsersAuto":
				$arFilter["<=ROLE"] = \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER;
				break;
			case "Moderators":
				$arFilter["<=ROLE"] = \Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR;
				break;
			case "Ban":
				$arFilter["=ROLE"] = \Bitrix\Socialnetwork\UserToGroupTable::ROLE_BAN;
				break;
			default:
				$arFilter["<=ROLE"] = \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER;
		}

		$arFilter["USER_ACTIVE"] = "Y";

		if ($arResult["bIntranetInstalled"])
		{
			if (
				$key === 'Users'
				&& $arParams["USE_AUTO_MEMBERS"] === "Y"
			)
			{
				$arFilter["!=AUTO_MEMBER"] = "Y";
			}
			elseif ($key === 'UsersAuto')
			{
				$arFilter["=AUTO_MEMBER"] = "Y";
			}
		}

		$dbRequests = CSocNetUserToGroup::GetList(
			array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
			$arFilter,
			false,
			$arNavParams,
			$arSelect
		);

		if ($dbRequests)
		{
			$userList = array();
			$userList["List"] = false;

			while ($arRequests = $dbRequests->GetNext())
			{
				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arRequests["USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arRequests["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				$arImage = false;
				if ((int)$arParams["THUMBNAIL_LIST_SIZE"] > 0)
				{
					if ((int)$arRequests["USER_PERSONAL_PHOTO"] <= 0)
					{
						switch ($arRequests["USER_PERSONAL_GENDER"])
						{
							case "M":
								$suffix = "male";
								break;
							case "F":
								$suffix = "female";
								break;
							default:
								$suffix = "unknown";
						}
						$arRequests["USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
					}

					$arImage = CFile::ResizeImageGet(
						$arRequests["USER_PERSONAL_PHOTO"],
						array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
				}

				$arTmpUser = array(
					"NAME" => $arRequests["USER_NAME"],
					"LAST_NAME" => $arRequests["USER_LAST_NAME"],
					"SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
					"LOGIN" => $arRequests["USER_LOGIN"],
				);
				$NameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE_WO_NOBR'], $arTmpUser, $bUseLogin);

				if ($userList["List"] == false)
				{
					$userList["List"] = array();
				}

				$record = array(
					"ID" => $arRequests["ID"],
					"USER_ID" => $arRequests["USER_ID"],
					"USER_ACTIVE" => $arRequests["USER_ACTIVE"],
					"USER_NAME" => $arRequests["USER_NAME"],
					"USER_LAST_NAME" => $arRequests["USER_LAST_NAME"],
					"USER_SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
					"USER_LOGIN" => $arRequests["USER_LOGIN"],
					"USER_NAME_FORMATTED" => $NameFormatted,
					"USER_PERSONAL_PHOTO" => $arRequests["USER_PERSONAL_PHOTO"],
					"USER_PERSONAL_PHOTO_IMG" => $arImage,
					"USER_PERSONAL_GENDER" => $arRequests["USER_PERSONAL_GENDER"],
					"USER_WORK_POSITION" => $arRequests["USER_WORK_POSITION"],
					"USER_PROFILE_URL" => $pu,
					"SHOW_PROFILE_LINK" => $canViewProfile,
					"IS_ONLINE" => ($arRequests["USER_IS_ONLINE"] === "Y"),
					"USER_IS_EXTRANET" => (isset($GLOBALS["arExtranetUserID"]) && is_array($GLOBALS["arExtranetUserID"]) && in_array($arRequests["USER_ID"], $GLOBALS["arExtranetUserID"]) ? "Y" : "N")
				);

				if (in_array($key, array("Moderators", "Users")))
				{
					$record["IS_OWNER"] = ($arRequests["ROLE"] === \Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER);
					$record["IS_SCRUM_MASTER"] = ((int)$arRequests["USER_ID"] === (int)$arResult['Group']['SCRUM_MASTER_ID']);
				}
				$userList["List"][] = $record;
			}

			switch($key)
			{
				case "Users":
					$navTitle = GetMessage("SONET_GUE_USERS_NAV");
					break;
				case "UsersAuto":
					$navTitle = GetMessage("SONET_GUE_USERS_AUTO_NAV");
					break;
				case "Moderators":
					$navTitle = GetMessage("SONET_GUE_MODS_NAV");
					break;
				case "Ban":
					$navTitle = GetMessage("SONET_GUE_BAN_NAV");
					break;
				default:
					$navTitle = '';
			}

			$userList["NAV_STRING"] = $dbRequests->GetPageNavStringEx($navComponentObject, $navTitle, "", false);
		}

		return $userList;
	}
}