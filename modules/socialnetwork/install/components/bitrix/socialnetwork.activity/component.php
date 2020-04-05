<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
{
	ShowError(GetMessage("SONET_ACTIVITY_NO_USER"));
	return;
}

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
	);
$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;

$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

$arParams["LOG_DATE_DAYS"] = IntVal($arParams["LOG_DATE_DAYS"]);
if ($arParams["LOG_DATE_DAYS"] <= 0)
	$arParams["LOG_DATE_DAYS"] = 7;

$arParams["AUTH"] = ((StrToUpper($arParams["AUTH"]) == "Y") ? "Y" : "N");

$arParams["EVENT_ID"] = (array_key_exists("EVENT_ID", $arParams) && strlen($arParams["EVENT_ID"]) > 0 ? $arParams["EVENT_ID"] : false);

$arParams["LOG_CNT"] = (array_key_exists("LOG_CNT", $arParams) && intval($arParams["LOG_CNT"]) > 0 ? $arParams["LOG_CNT"] : 0);

if ($arParams["USER_ID"] <= 0)
{
	ShowError(GetMessage("SONET_ACTIVITY_NO_USER"));
	return;
}

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

$dbUser = CUser::GetByID($arParams["USER_ID"]);
if ($arUser = $dbUser->Fetch())
{
	$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "viewprofile", $bCurrentUserIsAdmin);
			
	if ($canViewProfile)
	{
	
		$arResult["Features"] = Array("all", "system", "system_groups"); 

		if (CSocnetUser::IsFriendsAllowed())
			$arResult["Features"][] = "system_friends";

		if (IsModuleInstalled("forum"))
			$arResult["Features"][] = "forum";
	
		if (IsModuleInstalled("blog"))
			$arResult["Features"][] = "blog";

		if (IsModuleInstalled("photogallery"))
			$arResult["Features"][] = "photo";

		if (IsModuleInstalled("intranet"))
		{
			$arResult["Features"][] = "calendar";
			$arResult["Features"][] = "tasks";
		}

		if (IsModuleInstalled("webdav") || IsModuleInstalled("disk"))
			$arResult["Features"][] = "files";
	
		if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
		{
			$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"), 
				array("", ""), 
				$arParams["NAME_TEMPLATE"]
			);
			$title_user = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);
			$title = Str_Replace("#TITLE#", $title_user, GetMessage("SONET_ACTIVITY_PAGE_TITLE"));

			if ($arParams["SET_TITLE"] == "Y")
				$APPLICATION->SetTitle($title);

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($title_user, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["USER_ID"])));
				$APPLICATION->AddChainItem(GetMessage("SONET_ACTIVITY_CHAIN_TITLE"));
			}
		}
		
		$arResult["CurrentUserPerms_UserID"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arParams["USER_ID"], $bCurrentUserIsAdmin);
	
		$arResult["Events"] = false;

		$arFilter = array("USER_ID" => $arParams["USER_ID"]);
				
		if ($arParams["LOG_DATE_DAYS"] > 0)
		{
			$arrAdd = array(
				"DD"	=> -($arParams["LOG_DATE_DAYS"]),
				"MM"	=> 0,
				"YYYY"	=> 0,
				"HH"	=> 0,
				"MI"	=> 0,
				"SS"	=> 0,
			);
			$stmp = AddToTimeStamp($arrAdd, time()+CTimeZone::GetOffset());				
			$arFilter[">=LOG_DATE"] = ConvertTimeStamp($stmp, "FULL");
		}
		
		if (strlen($arParams["EVENT_ID"]) > 0)
		{
			$arFilter["EVENT_ID"] = $arParams["EVENT_ID"];
			if ($arFilter["EVENT_ID"] == "blog")
				$arFilter["EVENT_ID"] = array("blog", "blog_post", "blog_comment", "blog_post_micro");
		}
		
		if (StrLen($_REQUEST["flt_event_id"]) > 0 && $_REQUEST["flt_event_id"] != "all")
		{
			$arFilter["EVENT_ID"] = $_REQUEST["flt_event_id"];
			if ($arFilter["EVENT_ID"] == "blog")
				$arFilter["EVENT_ID"] = array("blog", "blog_post", "blog_comment", "blog_post_micro");
		}

		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
			$arFilter["SITE_ID"] = SITE_ID;
		else
			$arFilter["SITE_ID"] = array(SITE_ID, false);

		$cnt = 0;
		$dbEvents = CSocNetLog::GetList(array("LOG_DATE"=>"DESC"), $arFilter, false, false, array("MIN_ID_JOIN" => true));
		while ($arEvents = $dbEvents->GetNext())
		{
			if (intval($arParams["LOG_CNT"]) > 0 && $cnt >= $arParams["LOG_CNT"])
				break;
				
			if ($arResult["Events"] == false)
				$arResult["Events"] = array();

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			{
				$arCurrentUserPerms = CSocNetUserToGroup::InitUserPerms(
					$GLOBALS["USER"]->GetID(),
					array(
						"ID" => $arEvents["ENTITY_ID"],
						"OWNER_ID" => $arEvents["GROUP_OWNER_ID"],
						"INITIATE_PERMS" => $arEvents["GROUP_INITIATE_PERMS"],
						"VISIBLE" => $arEvents["GROUP_VISIBLE"],
						"OPENED" => $arEvents["GROUP_OPENED"],
					),
					$bCurrentUserIsAdmin
				);

				if ($arEvents["EVENT_ID"] == "system")
				{
					if (!$arCurrentUserPerms["UserIsMember"])
						continue;
				}
				elseif ($arEvents["EVENT_ID"] == "blog_post")
				{
					if (
						!array_key_exists("blog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arEvents["ENTITY_ID"], "blog", "view_post", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}
				elseif ($arEvents["EVENT_ID"] == "blog_post_micro")
				{
					if (
						!array_key_exists("microblog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arEvents["ENTITY_ID"], "blog", "view_post", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}
				elseif ($arEvents["EVENT_ID"] == "blog_comment")
				{
					if (
						!array_key_exists("blog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arEvents["ENTITY_ID"], "blog", "view_comment", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}
				else
				{
					if (
						!array_key_exists($arEvents["EVENT_ID"], $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arEvents["ENTITY_ID"], $arEvents["EVENT_ID"], $arSocNetFeaturesSettings[$arEvents["EVENT_ID"]]["minoperation"][0], $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}

				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arEvents["ENTITY_ID"]));
			}
			else
			{
				$arCurrentUserPerms = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arEvents["ENTITY_ID"], $bCurrentUserIsAdmin);

				if ($arEvents["EVENT_ID"] == "system")
				{
					if (!$arCurrentUserPerms["Operations"]["viewprofile"])
						continue;
				}
				elseif ($arEvents["EVENT_ID"] == "system_friends")
				{
					if (!$arCurrentUserPerms["Operations"]["viewfriends"] || !$arResult["CurrentUserPerms_UserID"]["Operations"]["viewfriends"])
						continue;
				}
				elseif ($arEvents["EVENT_ID"] == "system_groups")
				{
					if (!$arCurrentUserPerms["Operations"]["viewgroups"])
						continue;
				}
				elseif ($arEvents["EVENT_ID"] == "blog_post")
				{
					if (
						!array_key_exists("blog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arEvents["ENTITY_ID"], "blog", "view_post", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}
				elseif ($arEvents["EVENT_ID"] == "blog_post_micro")
				{
					if (
						!array_key_exists("microblog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arEvents["ENTITY_ID"], "blog", "view_post", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}
				elseif ($arEvents["EVENT_ID"] == "blog_comment")
				{
					if (
						!array_key_exists("blog", $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arEvents["ENTITY_ID"], "blog", "view_comment", $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}				
				else
				{
					if (
						!array_key_exists($arEvents["EVENT_ID"], $arSocNetFeaturesSettings)
						|| !CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arEvents["ENTITY_ID"], $arEvents["EVENT_ID"], $arSocNetFeaturesSettings[$arEvents["EVENT_ID"]]["minoperation"][0], $bCurrentUserIsAdmin)
					)
					{
						continue;
					}
				}

				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arEvents["ENTITY_ID"]));
			}

			$arDateTmp = ParseDateTime($arEvents["LOG_DATE"], CSite::GetDateFormat('FULL'));
			$day = IntVal($arDateTmp["DD"]);
			$month = IntVal($arDateTmp["MM"]);
			$year = IntVal($arDateTmp["YYYY"]);
			$dateFormated = $day.' '.ToLower(GetMessage('MONTH_'.$month.'_S')).' '.$year;
			$timeFormated = $arDateTmp["HH"].':'.$arDateTmp["MI"].':'.$arDateTmp["SS"];

			$arEvents["MESSAGE_FORMAT"] = htmlspecialcharsback($arEvents["MESSAGE"]);
			if (StrLen($arEvents["CALLBACK_FUNC"]) > 0)
			{
				if (StrLen($arEvents["MODULE_ID"]) > 0)
					CModule::IncludeModule($arEvents["MODULE_ID"]);
					$arEvents["MESSAGE_FORMAT"] = call_user_func($arEvents["CALLBACK_FUNC"], $arEvents);
			}

			if ($arEvents["EVENT_ID"] == "system" || $arEvents["EVENT_ID"] == "system_friends" || $arEvents["EVENT_ID"] == "system_groups")
			{
				$arEvents["TITLE_TEMPLATE"] = "";
				$arEvents["URL"] = "";

				switch ($arEvents["TITLE"])
				{
					case "join":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_JOIN1"), GetMessage("SONET_ACTIVITY_TITLE_JOIN2"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unjoin":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_UNJOIN1"), GetMessage("SONET_ACTIVITY_TITLE_UNJOIN2"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "moderate":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_MODERATE1"), GetMessage("SONET_ACTIVITY_TITLE_MODERATE2"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unmoderate":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_UNMODERATE1"), GetMessage("SONET_ACTIVITY_TITLE_UNMODERATE2"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "owner":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_OWNER1"), GetMessage("SONET_ACTIVITY_TITLE_OWNER1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "friend":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_FRIEND1"), GetMessage("SONET_ACTIVITY_TITLE_FRIEND1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unfriend":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_UNFRIEND1"), GetMessage("SONET_ACTIVITY_TITLE_UNFRIEND1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "group":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_GROUP1"), GetMessage("SONET_ACTIVITY_TITLE_GROUP1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "ungroup":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_UNGROUP1"), GetMessage("SONET_ACTIVITY_TITLE_UNGROUP1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;
						
						break;
					case "exclude_user":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_EXCLUDE_USER1"), GetMessage("SONET_ACTIVITY_TITLE_EXCLUDE_USER1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "exclude_group":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_ACTIVITY_TITLE_EXCLUDE_GROUP1"), GetMessage("SONET_ACTIVITY_TITLE_EXCLUDE_GROUP1"), $arParams);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					default:
						continue;
						break;
				}
			}

			$arTmpUser = array(
				"NAME" => "",
				"LAST_NAME" => "",
				"SECOND_NAME" => "",
				"LOGIN" => ""
			);

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_USER && intval($arEvents["ENTITY_ID"]) > 0)
				$arTmpUser = array(
					"NAME" => $arEvents["~USER_NAME"],
					"LAST_NAME" => $arEvents["~USER_LAST_NAME"],
					"SECOND_NAME" => $arEvents["~USER_SECOND_NAME"],
					"LOGIN" => $arEvents["~USER_LOGIN"]
				);						
				
			$arTmpEvent = array(
				"ID" => $arEvents["ID"],
				"ENTITY_TYPE" => $arEvents["ENTITY_TYPE"],
				"ENTITY_ID" => $arEvents["ENTITY_ID"],
				"EVENT_ID" => $arEvents["EVENT_ID"],
				"LOG_DATE" => $arEvents["LOG_DATE"],
				"LOG_TIME_FORMAT" => $timeFormated,
				"TITLE_TEMPLATE" => $arEvents["TITLE_TEMPLATE"],
				"TITLE" => $arEvents["TITLE"],
				"TITLE_FORMAT" => CSocNetLog::MakeTitle($arEvents["TITLE_TEMPLATE"], $arEvents["TITLE"], $arEvents["URL"], true),
				"MESSAGE" => $arEvents["MESSAGE"],
				"MESSAGE_FORMAT" => $arEvents["MESSAGE_FORMAT"],
				"URL" => $arEvents["URL"],
				"MODULE_ID" => $arEvents["MODULE_ID"],
				"CALLBACK_FUNC" => $arEvents["CALLBACK_FUNC"],
				"ENTITY_NAME" => (($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP) ? $arEvents["GROUP_NAME"] : CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin)),
				"ENTITY_PATH" => $path2Entity,
			);

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_USER)
			{
				$arTmpEvent["USER_NAME"] 			= $arTmpUser["NAME"];
				$arTmpEvent["USER_LAST_NAME"] 		= $arTmpUser["LAST_NAME"];
				$arTmpEvent["USER_SECOND_NAME"] 	= $arTmpUser["SECOND_NAME"];
				$arTmpEvent["USER_LOGIN"] 			= $arTmpUser["LOGIN"];
			}

			if (preg_match("/#USER_NAME#/i".BX_UTF_PCRE_MODIFIER, $arTmpEvent["TITLE_FORMAT"], $res))
			{
				if (intval($arEvents["USER_ID"]) > 0)
				{
					$arTmpCreatedBy = array(
						"NAME" 			=> 	$arEvents["~CREATED_BY_NAME"],
						"LAST_NAME" 	=> 	$arEvents["~CREATED_BY_LAST_NAME"],
						"SECOND_NAME" 	=> 	$arEvents["~CREATED_BY_SECOND_NAME"],
						"LOGIN" 		=> 	$arEvents["~CREATED_BY_LOGIN"]
					);

					$name_formatted = CUser::FormatName(
						$arParams["NAME_TEMPLATE_WO_NOBR"], 
						$arTmpCreatedBy, 
						$bUseLogin
					);
				}
				else
					$name_formatted = GetMessage("SONET_C73_CREATED_BY_ANONYMOUS");

				$arTmpEvent["TITLE_FORMAT"] = str_replace("#USER_NAME#", $name_formatted, $arTmpEvent["TITLE_FORMAT"]);
			}
			
			$arResult["Events"][$dateFormated][] = $arTmpEvent;
			$cnt++;
		}
	}
	else
	{
		ShowError(GetMessage("SONET_ACTIVITY_NO_ACCESS"));
		return;
	}
}
else
{
	ShowError(GetMessage("SONET_ACTIVITY_NO_USER"));
	return;
}

$this->IncludeComponentTemplate();
?>