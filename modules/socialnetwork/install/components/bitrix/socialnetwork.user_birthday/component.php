<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Socialnetwork\ComponentHelper;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = intval($USER->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 6;

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (CSocNetUser::IsFriendsAllowed())
{
	if ($arParams["USER_ID"] > 0)
	{
		$dbUser = CUser::GetByID($arParams["USER_ID"]);
		$arResult["User"] = $dbUser->GetNext();

		if (!is_array($arResult["User"]))
		{
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		}
		else
		{
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

			/*
			if ($arParams["SET_TITLE"] == "Y")
				$APPLICATION->SetTitle($arResult["User"]["NAME"]." ".$arResult["User"]["LAST_NAME"].": ".GetMessage("SONET_C33_PAGE_TITLE"));

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($arResult["User"]["NAME"]." ".$arResult["User"]["LAST_NAME"], CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
				$APPLICATION->AddChainItem(GetMessage("SONET_C33_PAGE_TITLE"));
			}
			*/

			if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewfriends"])
			{
				$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
				$arNavigation = CDBResult::GetNavParams($arNavParams);

				$nowDay = intval(Date("d"));
				$nowMonth = intval(Date("m"));

				$arResult["Users"] = false;
				$cnt = 0;
				$dbFriends = CSocNetUserRelations::GetListBirthday($arResult["User"]["ID"], 0);
				if ($dbFriends)
				{
					$arResult["Users"] = array();
					$arResult["Users"]["List"] = false;
					while ($arFriends = $dbFriends->GetNext())
					{
						if ($arFriends["PB"] == '')
							continue;

						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends["ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arFriends["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
						{
							if (intval($arFriends["PERSONAL_PHOTO"]) <= 0)
							{
								switch ($arFriends["PERSONAL_GENDER"])
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
								$arFriends["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
							}						
							$arImage = CSocNetTools::InitImage($arFriends["PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
						}
						else // old 
							$arImage = CSocNetTools::InitImage($arFriends["PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $pu, $canViewProfile);

						$arDateTmp = ParseDateTime($arFriends["PB"], "YYYY-MM-DD");

						$day = intval($arDateTmp["DD"]);
						$month = intval($arDateTmp["MM"]);
						$year = intval($arDateTmp["YYYY"]);
						if ($day <= 0)
							continue;

						if ($arResult["Users"]["List"] == false)
							$arResult["Users"]["List"] = array();

						$val = $day.' '.mb_strtolower(GetMessage('MONTH_'.$month.'_S'));

						$arResult["Users"]["List"][] = array(
							"ID" => $arFriends["ID"],
							"NAME" => $arFriends["NAME"],
							"LAST_NAME" => $arFriends["LAST_NAME"],
							"SECOND_NAME" => $arFriends["SECOND_NAME"],
							"LOGIN" => $arFriends["LOGIN"],
							"PERSONAL_PHOTO" => $arFriends["PERSONAL_PHOTO"],
							"PERSONAL_PHOTO_FILE" => $arImage["FILE"],
							"PERSONAL_PHOTO_IMG" => $arImage["IMG"],
							"PROFILE_URL" => $pu,
							"SHOW_PROFILE_LINK" => $canViewProfile,
							"BIRTHDAY" => $val,
							"NOW" => ($nowDay == $day && $nowMonth == $month),
							"IS_ONLINE" => ($arFriends["IS_ONLINE"] === "Y")
						);

						$cnt++;
						if ($cnt > $arParams["ITEMS_COUNT"])
							break;
					}

					$arResult["NAV_STRING"] = $dbFriends->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C33_NAV"), "", false);
				}
			}
		}
	}

	$this->IncludeComponentTemplate();
}
