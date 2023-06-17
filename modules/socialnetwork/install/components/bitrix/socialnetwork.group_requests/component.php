<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");
$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] === "N" ? false : true);

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
$arParams["PATH_TO_GROUP_REQUESTS"] = trim($arParams["PATH_TO_GROUP_REQUESTS"]);
if($arParams["PATH_TO_GROUP_REQUESTS"] == '')
	$arParams["PATH_TO_GROUP_REQUESTS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_requests&".$arParams["GROUP_VAR"]."=#group_id#");
$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = trim($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]);
if ($arParams["PATH_TO_GROUP_REQUEST_SEARCH"] == '')
	$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_search&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["PATH_TO_SMILE"] = Trim($arParams["PATH_TO_SMILE"]);

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (!$GLOBALS["USER"]->IsAuthorized())
	$arResult["NEED_AUTH"] = "Y";
else
{
	$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

	if (
		!$arGroup 
		|| !is_array($arGroup) 
		|| $arGroup["ACTIVE"] !== "Y"
	)
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
	else
	{
		$arGroupSites = array();
		$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
		while ($arGroupSite = $rsGroupSite->Fetch())
			$arGroupSites[] = $arGroupSite["LID"];

		if (!in_array(SITE_ID, $arGroupSites))
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
		else
		{
			$arResult["Group"] = $arGroup;

			$arResult['CurrentUserPerms'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
				'groupId' => $arGroup['ID'],
			]);

			if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanViewGroup"])
				$arResult["FatalError"] = GetMessage("SONET_C12_NO_PERMS").". ";
			else
			{
				$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
				$arResult["Urls"]["RequestSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));

				if ($arParams["SET_TITLE"] === "Y")
					$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C12_TITLE"));

				if ($arParams["SET_NAV_CHAIN"] !== "N")
				{
					$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
					$APPLICATION->AddChainItem(GetMessage("SONET_C12_TITLE"));
				}

				if (!$arResult["CurrentUserPerms"]["UserCanInitiate"])
				{
					$arResult["FatalError"] = GetMessage("SONET_C12_CANT_INVITE").". ";
				}
				else
				{
					$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
					$arNavigation = CDBResult::GetNavParams($arNavParams);

					if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["save"] <> '' || $_POST["reject"] <> '') && check_bitrix_sessid())
					{
						$errorMessage = "";

						$arIDs = array();
						if ($errorMessage == '')
						{
							for ($i = 0; $i <= intval($_POST["max_count"]); $i++)
							{
								if ($_POST["checked_".$i] === "Y")
									$arIDs[] = intval($_POST["id_".$i]);
							}

							if (count($arIDs) <= 0)
								$errorMessage .= GetMessage("SONET_C12_NOT_SELECTED").". ";
						}

						if ($errorMessage == '')
						{
							if (!empty($_POST["save"]))
							{
								if (
									!CSocNetUserToGroup::ConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), $arResult["Group"]["ID"], $arIDs, $bAutoSubscribe)
									&& ($e = $APPLICATION->GetException())
								)
									$errorMessage .= $e->GetString();
							}
							elseif (!empty($_POST["reject"]))
							{
								if (
									!CSocNetUserToGroup::RejectRequestToBeMember($GLOBALS["USER"]->GetID(), $arResult["Group"]["ID"], $arIDs)
									&& ($e = $APPLICATION->GetException())
								)
									$errorMessage .= $e->GetString();
							}
						}

						if ($errorMessage <> '')
							$arResult["ErrorMessage"] = $errorMessage;
					}

					$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

					$arResult["Requests"] = false;
					$dbRequests = CSocNetUserToGroup::GetList(
						array("DATE_CREATE" => "ASC"),
						array(
							"GROUP_ID" => $arResult["Group"]["ID"],
							"ROLE" => SONET_ROLES_REQUEST,
							"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER
						),
						false,
						$arNavParams,
						array("ID", "USER_ID", "DATE_CREATE", "DATE_UPDATE", "MESSAGE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER")
					);
					if ($dbRequests)
					{
						$arResult["Requests"] = array();
						$arResult["Requests"]["List"] = false;
						while ($arRequests = $dbRequests->GetNext())
						{
							if ($arResult["Requests"]["List"] == false)
								$arResult["Requests"]["List"] = array();

							$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arRequests["USER_ID"]));
							$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arRequests["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
							
							if (intval($arRequests["USER_PERSONAL_PHOTO"]) <= 0)
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
							$arImage = CSocNetTools::InitImage($arRequests["USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

							$arResult["Requests"]["List"][] = array(
								"ID" => $arRequests["ID"],
								"USER_ID" => $arRequests["USER_ID"],
								"USER_NAME" => $arRequests["USER_NAME"],
								"USER_LAST_NAME" => $arRequests["USER_LAST_NAME"],
								"USER_SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
								"USER_LOGIN" => $arRequests["USER_LOGIN"],
								"USER_PERSONAL_PHOTO" => $arRequests["USER_PERSONAL_PHOTO"],
								"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
								"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
								"USER_PROFILE_URL" => $pu,
								"SHOW_PROFILE_LINK" => $canViewProfile,
								"DATE_CREATE" => $arRequests["DATE_CREATE"],
								"MESSAGE" => $parser->convert(
									$arRequests["~MESSAGE"],
									false,
									array(),
									array(
										"HTML" => "N",
										"ANCHOR" => "Y",
										"BIU" => "Y",
										"IMG" => "Y",
										"LIST" => "Y",
										"QUOTE" => "Y",
										"CODE" => "Y",
										"FONT" => "Y",
										"SMILES" => "Y",
										"UPLOAD" => "N",
										"NL2BR" => "N"
									)
								)
							);
						}
						$arResult["NAV_STRING"] = $dbRequests->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C12_NAV"), "", false);
					}
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();
