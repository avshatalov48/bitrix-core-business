<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if ($arParams["EVENTS_VAR"] == '')
	$arParams["EVENTS_VAR"] = "events";

$arParams["LOG_DATE_DAYS"] = intval($arParams["LOG_DATE_DAYS"]);
if ($arParams["LOG_DATE_DAYS"] <= 0)
	$arParams["LOG_DATE_DAYS"] = 7;

$arParams["EVENT_ID"] = false;
	
if (array_key_exists($arParams["EVENTS_VAR"], $_REQUEST) && trim($_REQUEST[$arParams["EVENTS_VAR"]]) <> '')
{
	$arParams["EVENT_ID"] = trim($_REQUEST[$arParams["EVENTS_VAR"]]);
	$arParams["EVENT_ID"] = explode("|", $arParams["EVENT_ID"]);
	foreach($arParams["EVENT_ID"] as $feature_id)
	{
		if ($feature_id == "blog")
		{
			$arParams["EVENT_ID"][] = "blog_post";
			$arParams["EVENT_ID"][] = "blog_comment";					
			$arParams["EVENT_ID"][] = "blog_post_micro";					
			break;					
		}
	}
	
}
else
	$arParams["EVENT_ID"] = false;

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat();
$arParams["NAME_TEMPLATE"] = str_replace(
		array("#NOBR#", "#/NOBR#"), 
		array("", ""), 
		$arParams["NAME_TEMPLATE"]
	);

$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

$arResult["RSS_TTL"] = (isset($arParams["RSS_TTL"]) && intval($arParams["RSS_TTL"]) > 0 ? $arParams["RSS_TTL"] : "60");

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

if (!function_exists("__RSSCheckServerName"))
{
	function __RSSCheckServerName($url, $server_name)
	{	
		$protocol = (CMain::IsHTTPS() ? "https://" : "http://");
		$result = $url <> '' && mb_strpos($url, $protocol) !== 0 ? $protocol.$server_name.$url : $url;
		return $result;
	}
}

if($arResult["SERVER_NAME"] == '' && defined("SITE_SERVER_NAME"))
	$arResult["SERVER_NAME"] = SITE_SERVER_NAME;

if($arResult["SERVER_NAME"] == '' && defined("SITE_SERVER_NAME"))
{
	$rsSite = CSite::GetList("sort", "asc", array("LID" => SITE_ID));
	if($arSite = $rsSite->Fetch())
		$arResult["SERVER_NAME"] = $arSite["SERVER_NAME"];
}
		
if($arResult["SERVER_NAME"] == '')
	$arResult["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
{
	$arGroup = CSocNetGroup::GetByID($arParams["ENTITY_ID"]);

	$arCurrentUserPerms = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
		'groupId' => $arGroup['ID'],
	]);

	if (!$arCurrentUserPerms || !$arCurrentUserPerms["UserCanViewGroup"])
	{
		$APPLICATION->RestartBuffer();
		$arResult["NAME"] = GetMessage("SONET_LOG_RSS_ERROR");
		$arResult["Events"] = array(
						array(
							"LOG_DATE" => date("r"),
							"TITLE_FORMAT" => htmlspecialcharsbx(GetMessage("SONET_LOG_RSS_GROUP_NO_PERMS_TITLE")),
							"MESSAGE_FORMAT" => htmlspecialcharsbx(GetMessage("SONET_LOG_RSS_GROUP_NO_PERMS_MESSAGE")),
							"URL" => "",
						)
					);
		$this->IncludeComponentTemplate();
		$r = $APPLICATION->EndBufferContentMan();
		echo $r;
		die();	
	}

	if (intval($arGroup["IMAGE_ID"]) <= 0)
		$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

	$arResult["PICTURE"] = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 100, "/bitrix/images/socialnetwork/nopic_group_100.gif", 100, "", false);
	$arResult["PICTURE"]["FILE"]["SRC"] = __RSSCheckServerName($arResult["PICTURE"]["FILE"]["SRC"], $arResult["SERVER_NAME"]);
	$arResult["NAME"] = $arGroup["NAME"];
	$arResult["DESCRIPTION"] = $arGroup["DESCRIPTION"];
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["ENTITY_ID"]));
	$arResult["URL"] = htmlspecialcharsbx(__RSSCheckServerName($arResult["URL"], $arResult["SERVER_NAME"]));
}
else
{
	$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arParams["ENTITY_ID"], "viewprofile", $bCurrentUserIsAdmin);

	if (!$canViewProfile)
	{
		$APPLICATION->RestartBuffer();
		$arResult["NAME"] = GetMessage("SONET_LOG_RSS_ERROR");
		$arResult["Events"] = array(
						array(
							"LOG_DATE" => date("r"),
							"TITLE_FORMAT" => htmlspecialcharsbx(GetMessage("SONET_LOG_RSS_USER_NO_PERMS_TITLE")),
							"MESSAGE_FORMAT" => htmlspecialcharsbx(GetMessage("SONET_LOG_RSS_USER_NO_PERMS_MESSAGE")),
							"URL" => "",
						)
					);
		$this->IncludeComponentTemplate();
		$r = $APPLICATION->EndBufferContentMan();
		echo $r;
		die();	
	}

	$rsUser = CUser::GetByID($arParams["ENTITY_ID"]);
	if ($arUser = $rsUser->GetNext())
	{
		if (intval($arUser["PERSONAL_PHOTO"]) <= 0)
		{
			switch ($arUser["PERSONAL_GENDER"])
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
			$arUser["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
		}
		$arResult["PICTURE"] = CSocNetTools::InitImage($arUser["PERSONAL_PHOTO"], 100, "/bitrix/images/socialnetwork/nopic_user_100.gif", 100, "", false);
		$arResult["PICTURE"]["FILE"]["SRC"] = __RSSCheckServerName($arResult["PICTURE"]["FILE"]["SRC"], $arResult["SERVER_NAME"]);		
		$arResult["NAME"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);
		$arResult["DESCRIPTION"] = "";
		$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ENTITY_ID"]));
		$arResult["URL"] = __RSSCheckServerName($arResult["URL"], $arResult["SERVER_NAME"]);
	}
}

if ($arResult["NAME"])
{
		$APPLICATION->RestartBuffer();
		header("Content-Type: text/xml; charset=".LANG_CHARSET);
		header("Pragma: no-cache");

		$arFilter["ENTITY_TYPE"] = Trim($arParams["ENTITY_TYPE"]);
		$arFilter["ENTITY_ID"] = intval($arParams["ENTITY_ID"]);

		$arFilter["EVENT_ID"] = array();
		
		if ($arParams["EVENT_ID"])
			$arFilter["EVENT_ID"] = $arParams["EVENT_ID"];
			
		if (!is_array($arFilter["EVENT_ID"]) && trim($arFilter["EVENT_ID"]) <> '')
			$arFilter["EVENT_ID"] = array($arFilter["EVENT_ID"]);
			
		foreach($arFilter["EVENT_ID"] as $i => $feature)
		{
			if ($feature == "all")
			{
				unset($arFilter["EVENT_ID"]);
				break;
			}
		}
		
		
		
		if (
			array_key_exists("ENTITY_TYPE", $arFilter) && $arFilter["ENTITY_TYPE"] <> ''
			&& array_key_exists("ENTITY_ID", $arFilter) && intval($arFilter["ENTITY_ID"]) > 0
		)
		{
			$arFeatures = CSocNetFeatures::GetActiveFeatures($arFilter["ENTITY_TYPE"], $arFilter["ENTITY_ID"]);
			$arFeatures[] = "system";
			if (in_array("blog", $arFeatures))
			{
				$arFeatures[] = "blog_post";
				$arFeatures[] = "blog_comment";
				$arFeatures[] = "blog_post_micro";
			}
				
			if (!array_key_exists("EVENT_ID", $arFilter) || empty($arFilter["EVENT_ID"]))
			{
				$arFilter["EVENT_ID"] = array("system");
				foreach($arFeatures as $feature_id)
				{
					if ($feature_id == "blog")
					{
						$arFilter["EVENT_ID"][] = "blog_post";
						$arFilter["EVENT_ID"][] = "blog_comment";
						$arFilter["EVENT_ID"][] = "blog_post_micro";
					}
					$arFilter["EVENT_ID"][] = $feature_id;
				}
			}
			else
			{
				foreach($arFilter["EVENT_ID"] as $key => $feature_id)
				{
					if (!in_array($feature_id, array_merge($arFeatures, array("system"))))
						unset($arFilter["EVENT_ID"][$key]);
				}				
			}
		}	
		
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
			$stmp = AddToTimeStamp($arrAdd, time());				
			$arFilter[">=LOG_DATE"] = ConvertTimeStamp($stmp, "FULL");			
		}

		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
			$arFilter["SITE_ID"] = SITE_ID;
		else
			$arFilter["SITE_ID"] = array(SITE_ID, false);

		$arResult["Events"] = array();

		CTimeZone::Disable();
		$dbEvents = CSocNetLog::GetList(array("LOG_DATE"=>"DESC"), $arFilter, false, false, array(), array("USER_ID" => ($bCurrentUserIsAdmin ? "A" : $GLOBALS["USER"]->GetID())));
		CTimeZone::Enable();

		while ($arEvents = $dbEvents->GetNext())
		{
	
			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arEvents["ENTITY_ID"]));
			else
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arEvents["ENTITY_ID"]));
			
			$arDateTmp = ParseDateTime($arEvents["LOG_DATE"], CSite::GetDateFormat('FULL'));
			$day = intval($arDateTmp["DD"]);
			$month = intval($arDateTmp["MM"]);
			$year = intval($arDateTmp["YYYY"]);
			$dateFormated = $day.' '.ToLower(GetMessage('MONTH_'.$month.'_S')).' '.$year;
			$timeFormated = $arDateTmp["HH"].':'.$arDateTmp["MI"].':'.$arDateTmp["SS"];

			$arEvents["MESSAGE_FORMAT"] = htmlspecialcharsback($arEvents["MESSAGE"]);
			if ($arEvents["CALLBACK_FUNC"] <> '')
			{
				if ($arEvents["MODULE_ID"] <> '')
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
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_JOIN1"), GetMessage("SONET_C73_TITLE_JOIN2"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unjoin":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_UNJOIN1"), GetMessage("SONET_C73_TITLE_UNJOIN2"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "moderate":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_MODERATE1"), GetMessage("SONET_C73_TITLE_MODERATE2"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unmoderate":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_UNMODERATE1"), GetMessage("SONET_C73_TITLE_UNMODERATE2"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "owner":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_OWNER1"), GetMessage("SONET_C73_TITLE_OWNER1"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "friend":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_FRIEND1"), GetMessage("SONET_C73_TITLE_FRIEND1"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "unfriend":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_UNFRIEND1"), GetMessage("SONET_C73_TITLE_UNFRIEND1"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "group":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_GROUP1"), GetMessage("SONET_C73_TITLE_GROUP1"), $arParams, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "ungroup":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_UNGROUP1"), GetMessage("SONET_C73_TITLE_UNGROUP1"), $arParams, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "exclude_user":
						list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_EXCLUDE_USER1"), GetMessage("SONET_C73_TITLE_EXCLUDE_USER1"), $arParams, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					case "exclude_group":
						list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvents["MESSAGE"], GetMessage("SONET_C73_TITLE_EXCLUDE_GROUP1"), GetMessage("SONET_C73_TITLE_EXCLUDE_GROUP1"), $arParams, $bCurrentUserIsAdmin, true);

						$arEvents["TITLE"] = $titleTmp;
						$arEvents["MESSAGE_FORMAT"] = $messageTmp;

						break;
					default:
						continue;
						break;
				}
			}			

			$protocol = (CMain::IsHTTPS() ? "https://" : "http://");			
			$arEvents["MESSAGE_FORMAT"] = preg_replace("#(<a\\s[^>/]*?href\\s*=\\s*)(['\"])(.+?)(\\2)#i", "\\1\\2".$protocol.$arResult["SERVER_NAME"]."\\3\\4",$arEvents["MESSAGE_FORMAT"]);
			$arEvents["MESSAGE_FORMAT"] = preg_replace("#(<img\\s[^>/]*?src\\s*=\\s*)(['\"])(.+?)(\\2)#i", "\\1\\2".$protocol.$arResult["SERVER_NAME"]."\\3\\4",$arEvents["MESSAGE_FORMAT"]);
			
			$arTmpUser = array(
				"NAME" => $arEvents["~USER_NAME"],
				"LAST_NAME" => $arEvents["~USER_LAST_NAME"],
				"SECOND_NAME" => $arEvents["~USER_SECOND_NAME"],
				"LOGIN" => $arEvents["~USER_LOGIN"]
			);
	
			$arEvents["LOG_DATE"] = date("r", MkDateTime($GLOBALS["DB"]->FormatDate($arEvents["LOG_DATE"], CLang::GetDateFormat("FULL"), "DD.MM.YYYY H:I:S"), "d.m.Y H:i:s"));	

			$arEvents["URL"] = __RSSCheckServerName($arEvents["URL"], $arResult["SERVER_NAME"]);

			$arTmpEvent = array(
				"ID" => $arEvents["ID"],
				"LOG_DATE" => $arEvents["LOG_DATE"],
				"TITLE_FORMAT" => CSocNetLog::MakeTitle($arEvents["TITLE_TEMPLATE"], $arEvents["TITLE"], "", true),
				"MESSAGE_FORMAT" => htmlspecialcharsbx($arEvents["MESSAGE_FORMAT"]),
				"URL" => htmlspecialcharsbx($arEvents["URL"]),
			);
			
			$arResult["Events"][] = $arTmpEvent;

		}

		$this->IncludeComponentTemplate();
		$r = $APPLICATION->EndBufferContentMan();
		echo $r;
		die();
}
else
{
	ShowError(GetMessage("SONET_LOG_RSS_NO_ENTITY"));
	CHTTP::SetStatus("404 Not Found");
}
?>