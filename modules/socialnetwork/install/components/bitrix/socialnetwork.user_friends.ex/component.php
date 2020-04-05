<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = IntVal($GLOBALS["USER"]->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if (strlen($arParams["PATH_TO_SEARCH"]) <= 0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;

$arParams["THUMBNAIL_LIST_SIZE"] = IntVal($arParams["THUMBNAIL_LIST_SIZE"]);
if ($arParams["THUMBNAIL_LIST_SIZE"] <= 0)
	$arParams["THUMBNAIL_LIST_SIZE"] = 42;

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : GetMessage("SONET_UFE_NAME_TEMPLATE_DEFAULT");
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
	array("#NOBR#", "#/NOBR#"), 
	array("", ""), 
	$arParams["NAME_TEMPLATE"]
);
$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
//by default in the public section show only active users in the friends list
$bActiveOnly = "Y";

if (!CSocNetUser::IsFriendsAllowed())
{
	$arResult["FatalError"] = GetMessage("SONET_UFE_NO_FR_FUNC").". ";
}
else
{
	if ($arParams["ID"] <= 0)
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
	}
	else
	{
		$dbUser = CUser::GetByID($arParams["ID"]);
		$arResult["User"] = $dbUser->GetNext();

		if (!is_array($arResult["User"]))
		{
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		}
		else
		{
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());
			$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false, "bShowAll"=>false);
			$arResult["Urls"]["Search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH"], array());

			if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
			{
				if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
				{
					$arParams["NAME_TEMPLATE"] = GetMessage("SONET_UFE_NAME_TEMPLATE_DEFAULT");
				}

				$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
					array("#NOBR#", "#/NOBR#"), 
					array("", ""), 
					$arParams["NAME_TEMPLATE"]
				);

				$arTmpUser = array(
					"NAME" => $arResult["User"]["~NAME"],
					"LAST_NAME" => $arResult["User"]["~LAST_NAME"],
					"SECOND_NAME" => $arResult["User"]["~SECOND_NAME"],
					"LOGIN" => $arResult["User"]["~LOGIN"],
				);
				$strTitleFormatted = CUser::FormatName($arParams["TITLE_NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
			}

			if ($arParams["SET_TITLE"] == "Y")
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_UFE_PAGE_TITLE"));
			}
	
			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
				$APPLICATION->AddChainItem(GetMessage("SONET_UFE_PAGE_TITLE"));
			}

			if ($arResult["CurrentUserPerms"]["Operations"]["viewfriends"])
			{
				$arResult["Friends"] = false;
				$dbFriends = CSocNetUserRelations::GetRelatedUsers(
					$arResult["User"]["ID"], 
					SONET_RELATIONS_FRIEND,
					$arNavParams,
					$bActiveOnly
				);
				if ($dbFriends)
				{
					$arResult["Friends"] = array();
					$arResult["Friends"]["List"] = false;
					while ($arFriends = $dbFriends->GetNext())
					{
						if ($arResult["Friends"]["List"] == false)
							$arResult["Friends"]["List"] = array();

						$pref = ((IntVal($arResult["User"]["ID"]) == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends[$pref."_USER_ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arFriends[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (!$arResult["CurrentUserPerms"]["IsCurrentUser"])
							$rel = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arFriends[$pref."_USER_ID"]);
						else
							$rel = SONET_RELATIONS_FRIEND;

						if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
						{
							if (intval($arFriends[$pref."_USER_PERSONAL_PHOTO"]) <= 0)
							{
								switch ($arFriends[$pref."_USER_PERSONAL_GENDER"])
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
								$arFriends[$pref."_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
							}

							$arImage = CFile::ResizeImageGet(
								$arFriends[$pref."_USER_PERSONAL_PHOTO"],
								array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
								BX_RESIZE_IMAGE_EXACT,
								false
							);
						}

						$arResult["Friends"]["List"][] = array(
							"ID" => $arFriends["ID"],
							"USER_ID" => $arFriends[$pref."_USER_ID"],
							"USER_NAME" => $arFriends[$pref."_USER_NAME"],
							"USER_LAST_NAME" => $arFriends[$pref."_USER_LAST_NAME"],
							"USER_SECOND_NAME" => $arFriends[$pref."_USER_SECOND_NAME"],
							"USER_LOGIN" => $arFriends[$pref."_USER_LOGIN"],
							"USER_PERSONAL_PHOTO" => $arFriends[$pref."_USER_PERSONAL_PHOTO"],
							"USER_PERSONAL_PHOTO" => $arFriends[$pref."_USER_PERSONAL_PHOTO"],
							"USER_PERSONAL_PHOTO_IMG" => $arImage,
							"USER_PROFILE_URL" => $pu,
							"SHOW_PROFILE_LINK" => $canViewProfile,
							"USER_NAME_FORMATTED" => $NameFormatted,
							"USER_WORK_POSITION" => $arFriends[$pref."_USER_WORK_POSITION"]
						);
					}
					$arResult["Friends"]["NAV_STRING"] = $dbFriends->GetPageNavStringEx($navComponentObject, GetMessage("SONET_UFE_FRIENDS_NAV"), "", false);
				}

				if (!IsModuleInstalled("im"))
				{
					$arResult["Banned"] = false;
					$dbBan = CSocNetUserRelations::GetRelatedUsers(
						$arResult["User"]["ID"], 
						SONET_RELATIONS_BAN, 
						$arNavParams,
						$bActiveOnly
					);
					if ($dbBan)
					{
						$arResult["Banned"] = array();
						$arResult["Banned"]["List"] = false;
						while ($arBan = $dbBan->GetNext())
						{
							if ($arResult["Banned"]["List"] == false)
								$arResult["Banned"]["List"] = array();

							$pref = ((IntVal($arResult["User"]["ID"]) == $arBan["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

							$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBan[$pref."_USER_ID"]));
							$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arBan[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
							$bInitiated = (
								(
									($GLOBALS["USER"]->GetID() == $arBan["FIRST_USER_ID"]) 
									&& ($arBan["INITIATED_BY"] == "F")
								)
								|| (
									($GLOBALS["USER"]->GetID() == $arBan["SECOND_USER_ID"]) 
									&& ($arBan["INITIATED_BY"] == "S")
								)
							);
						
							if (!$arResult["CurrentUserPerms"]["IsCurrentUser"])
								$rel = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arBan[$pref."_USER_ID"]);
							else
								$rel = SONET_RELATIONS_FRIEND;

							if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
							{
								if (intval($arBan[$pref."_USER_PERSONAL_PHOTO"]) <= 0)
								{
									switch ($arBan[$pref."_USER_PERSONAL_GENDER"])
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
									$arBan[$pref."_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
								}

								$arImage = CFile::ResizeImageGet(
									$arBan[$pref."_USER_PERSONAL_PHOTO"],
									array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
									BX_RESIZE_IMAGE_EXACT,
									false
								);
							}

							$arResult["Banned"]["List"][] = array(
								"ID" => $arBan["ID"],
								"USER_ID" => $arBan[$pref."_USER_ID"],
								"USER_NAME" => $arBan[$pref."_USER_NAME"],
								"USER_LAST_NAME" => $arBan[$pref."_USER_LAST_NAME"],
								"USER_SECOND_NAME" => $arBan[$pref."_USER_SECOND_NAME"],
								"USER_LOGIN" => $arBan[$pref."_USER_LOGIN"],
								"USER_PERSONAL_PHOTO" => $arBan[$pref."_USER_PERSONAL_PHOTO"],
								"USER_PERSONAL_PHOTO" => $arBan[$pref."_USER_PERSONAL_PHOTO"],
								"USER_PERSONAL_PHOTO_IMG" => $arImage,
								"USER_PROFILE_URL" => $pu,
								"SHOW_PROFILE_LINK" => $canViewProfile,
								"USER_NAME_FORMATTED" => $NameFormatted,
								"USER_WORK_POSITION" => $arBan[$pref."_USER_WORK_POSITION"],
								"CAN_UNBAN" => $bInitiated
							);
						}
						$arResult["Banned"]["NAV_STRING"] = $dbBan->GetPageNavStringEx($navComponentObject, GetMessage("SONET_UFE_BAN_NAV"), "", false);
					}
				}
			}
			else
			{
				$arResult["FatalError"] = GetMessage("SONET_UFE_ACCESS_DENIED");
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>