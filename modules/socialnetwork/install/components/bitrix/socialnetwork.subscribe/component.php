<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists('__SubscribeGetValue'))
{
	function __SubscribeGetValue($key, $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, $default_value)
	{
		$inherited = false;
		if (
			array_key_exists($event_id_tmp, $arSubscribesTmp) 
			&& array_key_exists($key, $arSubscribesTmp[$event_id_tmp])
			&& $arSubscribesTmp[$event_id_tmp][$key] != "I"
		)
			$value = $arSubscribesTmp[$event_id_tmp][$key];
		elseif (
			array_key_exists("all", $arSubscribesTmp) 
			&& array_key_exists($key, $arSubscribesTmp["all"])
			&& $arSubscribesTmp["all"][$key] != "I"
		)
		{
			$value = $arSubscribesTmp["all"][$key];
			$inherited = true;
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAllMy) 
			&& array_key_exists($key, $arSubscribesTmpAllMy[$event_id_tmp])
			&& $arSubscribesTmpAllMy[$event_id_tmp][$key] != "I"
		)
		{
			$value = $arSubscribesTmpAllMy[$event_id_tmp][$key];
			$inherited = true;
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAllMy) 
			&& array_key_exists($key, $arSubscribesTmpAllMy["all"])
			&& $arSubscribesTmpAllMy["all"][$key] != "I"
		)
		{
			$value = $arSubscribesTmpAllMy["all"][$key];
			$inherited = true;
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAll) 
			&& array_key_exists($key, $arSubscribesTmpAll[$event_id_tmp])
			&& $arSubscribesTmpAll[$event_id_tmp][$key] != "I"
		)
		{
			$value = $arSubscribesTmpAll[$event_id_tmp][$key];
			$inherited = true;
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAll) 
			&& array_key_exists($key, $arSubscribesTmpAll["all"])
			&& $arSubscribesTmpAll["all"][$key] != "I"
		)
		{
			$value = $arSubscribesTmpAll["all"][$key];
			$inherited = true;
		}
		else
		{
			$value = $default_value;
			$inherited = true;
		}

		return array(
			"value"		=> $value,
			"inherited"	=> $inherited
		);
	}
}

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);

if (StrLen($arParams["ENTITY_TYPE"]) <= 0)
{
	if ($arParams["PAGE_ID"] == "group_subscribe")
		$arParams["ENTITY_TYPE"] = SONET_SUBSCRIBE_ENTITY_GROUP;
	elseif ($arParams["PAGE_ID"] == "user_subscribe")
		$arParams["ENTITY_TYPE"] = SONET_SUBSCRIBE_ENTITY_USER;
}

if (intval($arParams["ENTITY_ID"]) <= 0)
{
	if ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
		$arParams["ENTITY_ID"] = $arParams["GROUP_ID"];
	elseif ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
		$arParams["ENTITY_ID"] = $arParams["USER_ID"];
}

if (StrLen($arParams["PAGE_ID"]) <= 0)
	$arParams["PAGE_ID"] = "user_subscribe";

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_SUBSCRIBE"] = trim($arParams["PATH_TO_SUBSCRIBE"]);
if (strlen($arParams["PATH_TO_SUBSCRIBE"]) <= 0)
	$arParams["PATH_TO_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=subscribe");

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
				
$arResult["FatalError"] = "";

$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
$arSocNetLogEvents = CSocNetAllowed::GetAllowedLogEvents();
$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

if (!$GLOBALS["USER"]->IsAuthorized())
	$arResult["NEED_AUTH"] = "Y";
else
{
	if ($arParams["ENTITY_ID"] <= 0)
		$arResult["FatalError"] = GetMessage("SONET_C3_NO_ENTITY_ID").".";

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		// get upper level subscription
		
		$is_my = false;
		if (
			array_key_exists($arParams["ENTITY_TYPE"], $arSocNetAllowedSubscribeEntityTypesDesc)
				&& array_key_exists("CLASS_MY_BY_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]])
				&& array_key_exists("METHOD_MY_BY_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]])				
			)
			$is_my = call_user_func(
				array(
					$arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]]["CLASS_MY_BY_ID"],
					$arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]]["METHOD_MY_BY_ID"]
				),
				$arParams["ENTITY_ID"]
			);

		$arSubscribesTmpAllMy = array();
		$arSubscribesTmpAll = array();
		if ($is_my)
		{
			$arSubscribesTmpAllMy = array();
			$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				array("USER_ID" => $GLOBALS["USER"]->GetID(), "ENTITY_TYPE" => $arParams["ENTITY_TYPE"], "ENTITY_ID" => 0, "ENTITY_MY" => "Y")
			);
			while ($arResultTmp = $dbResultTmp->GetNext())
				$arSubscribesTmpAllMy[$arResultTmp["EVENT_ID"]] = $arResultTmp;
		}

		$dbResultTmp = CSocNetLogEvents::GetList(
			array(),
			array(
				"USER_ID" 		=> $GLOBALS["USER"]->GetID(), 
				"ENTITY_TYPE" 	=> $arParams["ENTITY_TYPE"], 
				"ENTITY_ID" 	=> 0, 
				"ENTITY_MY" 	=> "N"
			)
		);
		while ($arResultTmp = $dbResultTmp->GetNext())
			$arSubscribesTmpAll[$arResultTmp["EVENT_ID"]] = $arResultTmp;

		$arSubscribesTmp = array();
		$arSubscribesTmpCB = array();

		$dbResultTmp = CSocNetLogEvents::GetList(
			array(),
			array(
				"USER_ID" 		=> $GLOBALS["USER"]->GetID(), 
				"ENTITY_TYPE" 	=> $arParams["ENTITY_TYPE"], 
				"ENTITY_ID" 	=> $arParams["ENTITY_ID"]
			)
		);
		while ($arResultTmp = $dbResultTmp->GetNext())
		{
			if ($arResultTmp["ENTITY_CB"] == "Y")
				$arSubscribesTmpCB[$arResultTmp["EVENT_ID"]] = $arResultTmp;
			else
				$arSubscribesTmp[$arResultTmp["EVENT_ID"]] = $arResultTmp;
		}
			

		if ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
		{
			$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
			if ($arGroup)
			{
				$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms(
					$GLOBALS["USER"]->GetID(),
					$arGroup,
					CSocNetUser::IsCurrentUserModuleAdmin()
				);

				$arResult["Group"] = $arGroup;
				$arResult["Subscribe"] = array();

				foreach ($arSocNetLogEvents as $event_id_tmp => $arEventTmp)
				{
					if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
						continue;
							
					if (
						!array_key_exists("ENTITIES", $arEventTmp)
						|| !array_key_exists(SONET_ENTITY_GROUP, $arEventTmp["ENTITIES"])
					)
						continue;
						
					if (
						!array_key_exists("Operations", $arResult["CurrentUserPerms"])
						|| !array_key_exists($arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["OPERATION"], $arResult["CurrentUserPerms"]["Operations"])
						|| !$arResult["CurrentUserPerms"]["Operations"][$arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["OPERATION"]]
					)
						continue;
					
					
					$arTmp = __SubscribeGetValue("TRANSPORT", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "N");
					$transport = $arTmp["value"];
					$transport_inherited = $arTmp["inherited"];
					
					$arTmp = __SubscribeGetValue("VISIBLE", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "Y");
					$visible = $arTmp["value"];
					$visible_inherited = $arTmp["inherited"];

					$arResult["Subscribe"][$event_id_tmp] = array(
						"SubscribeName" 		=> $arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["TITLE"],
						"Active" 				=> array_key_exists($event_id_tmp, $arSubscribesTmp),
						"SiteID" 				=> (array_key_exists($event_id_tmp, $arSubscribesTmp) ? $arSubscribesTmp[$event_id_tmp]["SITE_ID"] : ""),
						"MailEvent" 			=> ((array_key_exists($event_id_tmp, $arSubscribesTmp) && $arSubscribesTmp[$event_id_tmp]["MAIL_EVENT"] == "Y") ? "Y" : "N"),
						"Transport" 			=> $transport,
						"TransportInherited" 	=> $transport_inherited,
						"Visible" 				=> $visible,
						"VisibleInherited" 		=> $visible_inherited,
					);
				}

				foreach ($arSocNetFeaturesSettings as $feature => $arFeature)
				{
					if (!in_array($arParams["ENTITY_TYPE"], $arFeature["allowed"]))
						continue;

					if (
						!array_key_exists("subscribe_events", $arFeature) 
						|| !is_array($arFeature["subscribe_events"]) 
						|| count($arFeature["subscribe_events"]) <= 0
					)
						continue;
						
					if (!CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arParams["ENTITY_TYPE"], $arParams["ENTITY_ID"], $feature, $arFeature["minoperation"][0], CSocNetUser::IsCurrentUserModuleAdmin()))
						continue;

					foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
					{
						if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
							continue;

						$arTmp = __SubscribeGetValue("TRANSPORT", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "N");
						$transport = $arTmp["value"];
						$transport_inherited = $arTmp["inherited"];
						
						$arTmp = __SubscribeGetValue("VISIBLE", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "Y");
						$visible = $arTmp["value"];
						$visible_inherited = $arTmp["inherited"];

						$arResult["Subscribe"][$event_id_tmp] = array(
							"SubscribeName" 		=> $arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["TITLE"],
							"Active" 				=> array_key_exists($event_id_tmp, $arSubscribesTmp),
							"SiteID" 				=> (array_key_exists($event_id_tmp, $arSubscribesTmp) ? $arSubscribesTmp[$event_id_tmp]["SITE_ID"] : ""),
							"MailEvent" 			=> ((array_key_exists($event_id_tmp, $arSubscribesTmp) && $arSubscribesTmp[$event_id_tmp]["MAIL_EVENT"] == "Y") ? "Y" : "N"),
							"Transport" 			=> $transport,
							"TransportInherited" 	=> $transport_inherited,
							"Visible" 				=> $visible,
							"VisibleInherited" 		=> $visible_inherited,
						);
					}
				}
			}
			else
				$arResult["FatalError"] = GetMessage("SONET_C3_NO_GROUP").".";
		}
		elseif ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
		{
			$dbUser = CUser::GetByID($arParams["ENTITY_ID"]);
			$arResult["User"] = $dbUser->GetNext();

			if (is_array($arResult["User"]))
			{
				$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);
				$arResult["User"]["~NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin, false);
				if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
				{
					$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
						array("#NOBR#", "#/NOBR#"), 
						array("", ""), 
						$arParams["NAME_TEMPLATE"]
					);
					$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arResult["User"], $bUseLogin, false);	
				}
			
				$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms(
					$GLOBALS["USER"]->GetID(),
					$arResult["User"]["ID"],
					CSocNetUser::IsCurrentUserModuleAdmin()
				);

				$arResult["Subscribe"] = array();

				foreach ($arSocNetLogEvents as $event_id_tmp => $arEventTmp)
				{
					if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
						continue;

					if (
						!array_key_exists("ENTITIES", $arEventTmp)
						|| !array_key_exists($arParams["ENTITY_TYPE"], $arEventTmp["ENTITIES"])
					)
						continue;

					if (
						!array_key_exists("Operations", $arResult["CurrentUserPerms"])
						|| !array_key_exists($arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["OPERATION"], $arResult["CurrentUserPerms"]["Operations"])
						|| !$arResult["CurrentUserPerms"]["Operations"][$arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["OPERATION"]]
					)
						continue;

					$arTmp = __SubscribeGetValue("TRANSPORT", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "N");
					$transport = $arTmp["value"];
					$transport_inherited = $arTmp["inherited"];
					
					$arTmp = __SubscribeGetValue("VISIBLE", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "Y");
					$visible = $arTmp["value"];
					$visible_inherited = $arTmp["inherited"];

					$arResult["Subscribe"][$event_id_tmp] = array(
						"SubscribeName" 		=> $arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["TITLE"],
						"Active" 				=> array_key_exists($event_id_tmp, $arSubscribesTmp),
						"SiteID" 				=> (array_key_exists($event_id_tmp, $arSubscribesTmp) ? $arSubscribesTmp[$event_id_tmp]["SITE_ID"] : ""),
						"MailEvent" 			=> ((array_key_exists($event_id_tmp, $arSubscribesTmp) && $arSubscribesTmp[$event_id_tmp]["MAIL_EVENT"] == "Y") ? "Y" : "N"),
						"Transport" 			=> $transport,
						"TransportInherited" 	=> $transport_inherited,
						"Visible" 				=> $visible,
						"VisibleInherited" 		=> $visible_inherited,						
					);
				}

				foreach ($arSocNetFeaturesSettings as $feature => $arFeature)
				{
					if (!in_array($arParams["ENTITY_TYPE"], $arFeature["allowed"]))
						continue;

					if (!CSocNetFeaturesPerms::CanPerformOperation(
							$GLOBALS["USER"]->GetID(), 
							$arParams["ENTITY_TYPE"], 
							$arParams["ENTITY_ID"], 
							$feature, 
							$arFeature["minoperation"][0], 
							CSocNetUser::IsCurrentUserModuleAdmin())
						)
						continue;

					if (array_key_exists("subscribe_events", $arFeature))
					{
						foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
						{
							if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
								continue;						

							$arTmp = __SubscribeGetValue("TRANSPORT", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "N");
							$transport = $arTmp["value"];
							$transport_inherited = $arTmp["inherited"];
							
							$arTmp = __SubscribeGetValue("VISIBLE", $event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll, "Y");
							$visible = $arTmp["value"];
							$visible_inherited = $arTmp["inherited"];
							
							$arFeatureTmp = array(
								"SubscribeName" 		=> $arEventTmp["ENTITIES"][$arParams["ENTITY_TYPE"]]["TITLE"],
								"Active" 				=> array_key_exists($event_id_tmp, $arSubscribesTmp),
								"SiteID" 				=> (array_key_exists($event_id_tmp, $arSubscribesTmp) ? $arSubscribesTmp[$event_id_tmp]["SITE_ID"] : ""),
								"MailEvent" 			=> ((array_key_exists($event_id_tmp, $arSubscribesTmp) && $arSubscribesTmp[$event_id_tmp]["MAIL_EVENT"] == "Y") ? "Y" : "N"),
								"Transport" 			=> $transport,
								"TransportInherited" 	=> $transport_inherited,
								"Visible" 				=> $visible,
								"VisibleInherited" 		=> $visible_inherited,							
							);

							$transport_inheritedCB = false;
							if (
								array_key_exists($event_id_tmp, $arSubscribesTmpCB) 
								&& array_key_exists("TRANSPORT", $arSubscribesTmpCB[$event_id_tmp])
								&& $arSubscribesTmpCB[$event_id_tmp]["TRANSPORT"] != "I"
							)
								$transportCB = $arSubscribesTmpCB[$event_id_tmp]["TRANSPORT"];
							elseif (
								array_key_exists("all", $arSubscribesTmpCB) 
								&& array_key_exists("TRANSPORT", $arSubscribesTmpCB["all"])
								&& $arSubscribesTmpCB["all"]["TRANSPORT"] != "I"
							)
							{
								$transportCB = $arSubscribesTmpCB["all"]["TRANSPORT"];
								$transport_inheritedCB = true;
							}
							else
							{
								$transportCB = "N";
								$transport_inheritedCB = true;
							}
							
							$arFeatureTmp["TransportCB"] = $transportCB;
							$arFeatureTmp["TransportInheritedCB"] = $transport_inheritedCB;

							$visible_inheritedCB = false;
							if (
								array_key_exists($event_id_tmp, $arSubscribesTmpCB) 
								&& array_key_exists("VISIBLE", $arSubscribesTmpCB[$event_id_tmp])
								&& $arSubscribesTmpCB[$event_id_tmp]["VISIBLE"] != "I"
							)
								$visibleCB = $arSubscribesTmpCB[$event_id_tmp]["VISIBLE"];
							elseif (
								array_key_exists("all", $arSubscribesTmpCB) 
								&& array_key_exists("VISIBLE", $arSubscribesTmpCB["all"])
								&& $arSubscribesTmpCB["all"]["VISIBLE"] != "I"
							)
							{
								$visibleCB = $arSubscribesTmpCB["all"]["VISIBLE"];
								$visible_inheritedCB = true;
							}
							else
							{
								$visibleCB = "Y";
								$visible_inheritedCB = true;
							}
							
							$arFeatureTmp["VisibleCB"] = $visibleCB;
							$arFeatureTmp["VisibleInheritedCB"] = $visible_inheritedCB;
							
							$arResult["Subscribe"][$event_id_tmp] = $arFeatureTmp;
						}
					}
				}
			}
			else
				$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").".";
		}
	}

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		
		$arResult["Urls"]["Entity"] = CComponentEngine::MakePathFromTemplate(
				$arParams[$arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]]["URL_PARAM_KEY"]], 
				array(
					$arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]]["URL_PATTERN"] => $arParams["ENTITY_ID"]
				)
			);

		if ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
			$arResult["Urls"]["Group"] = $arResult["Urls"]["Entity"];
		elseif ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
			$arResult["Urls"]["User"] = $arResult["Urls"]["Entity"];
			
		$arResult["Urls"]["MySubscribe"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SUBSCRIBE"], array());

		if ($arParams["SET_TITLE"] == "Y")
		{
			if ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
				$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C3_GROUP_SETTINGS"));
			elseif ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C3_USER_SETTINGS"));
		}
		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			if ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
			{
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C3_GROUP_SETTINGS"));
			}
			elseif ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
			{
				$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C3_USER_SETTINGS"));
			}
		}

		if (!$arResult["Subscribe"] || count($arResult["Subscribe"]) <= 0)
			$arResult["FatalError"] = GetMessage("SONET_C3_NO_SUBSCRIPTIONS").".";
		
		$arResult["ShowForm"] = "Input";

		if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
		{
			$errorMessage = "";
			CSocNetLogEvents::DeleteByUserAndEntity($GLOBALS["USER"]->GetID(), $arParams["ENTITY_TYPE"], $arParams["ENTITY_ID"]);

			foreach ($arResult["Subscribe"] as $event_id_tmp => $arEventTmp)
			{
				if (
					!array_key_exists($event_id_tmp."_transport", $_REQUEST) 
					&& !array_key_exists("cb_".$event_id_tmp."_transport", $_REQUEST) 
					&& !array_key_exists($event_id_tmp."_visible", $_REQUEST) 
					&& !array_key_exists("cb_".$event_id_tmp."_visible", $_REQUEST)
					&& (
						!array_key_exists($event_id_tmp."_active", $_REQUEST) 
						|| (
							$_REQUEST[$event_id_tmp."_active"] != "S" 
							&& $_REQUEST[$event_id_tmp."_active"] != "M")
					)
				)
					continue;

				if (array_key_exists($event_id_tmp."_transport", $_REQUEST))
					$subscribe_transport = $_REQUEST[$event_id_tmp."_transport"];
				else
				{
					switch ($_REQUEST[$event_id_tmp."_active"])
					{
						case "M":
							$subscribe_transport = "M";
							break;
						case "N":
							$subscribe_transport = "N";
							break;
						default:
							$subscribe_transport = "N";
					}
				}
				
				if (array_key_exists($event_id_tmp."_visible", $_REQUEST))
					$subscribe_visible = $_REQUEST[$event_id_tmp."_visible"];
				else
				{
					switch ($_REQUEST[$event_id_tmp."_active"])
					{
						case "M":
							$subscribe_visible = "Y";
							break;
						case "N":
							$subscribe_visible = "N";
							break;
						default:
							$subscribe_visible = "Y";
					}
				}				
				
				if (array_key_exists("cb_".$event_id_tmp."_transport", $_REQUEST))
					$subscribe_transport_cb = $_REQUEST["cb_".$event_id_tmp."_transport"];
					
				if (array_key_exists("cb_".$event_id_tmp."_visible", $_REQUEST))
					$subscribe_visible_cb = $_REQUEST["cb_".$event_id_tmp."_visible"];
					
				$bFound = false;
				if (
					array_key_exists($event_id_tmp, $arSocNetLogEvents)
					&& array_key_exists("FULL_SET", $arSocNetLogEvents[$event_id_tmp])
				)
				{
					$arEventID = $arSocNetLogEvents[$event_id_tmp]["FULL_SET"];
					$bFound = true;
				}
				else
				{
					foreach($arSocNetFeaturesSettings as $arFeatureTmp)
					{
						if (array_key_exists("subscribe_events", $arFeatureTmp))
						{
							foreach($arFeatureTmp["subscribe_events"] as $event_id_settings => $arEventTmpSettings)
							{
								if ($event_id_settings == $event_id_tmp)
								{
									if (
										array_key_exists("ENTITIES", $arEventTmpSettings)
										&& array_key_exists($arParams["ENTITY_TYPE"], $arEventTmpSettings["ENTITIES"])
										&& array_key_exists("FULL_SET", $arEventTmpSettings)
									)
									{
										$arEventID = $arEventTmpSettings["FULL_SET"];
										$bFound = true;
									}
									break;
								}							
							}
							if ($bFound)
								break;
						}
					}
				}
					
				if (!$bFound)
					$arEventID = array($event_id_tmp);

				foreach($arEventID as $event_id)
				{
					if (
						$subscribe_transport != "I" 
						|| $subscribe_visible != "I"
					)
					{
						$idTmp = CSocNetLogEvents::Add(
							array(
								"USER_ID" 		=> $GLOBALS["USER"]->GetID(),
								"ENTITY_TYPE" 	=> $arParams["ENTITY_TYPE"],
								"ENTITY_ID" 	=> ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP) ? $arResult["Group"]["ID"] : $arResult["User"]["ID"],
								"ENTITY_CB" 	=> "N",
								"EVENT_ID" 		=> $event_id,
								"SITE_ID" 		=> (
													array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]])
													&& $arSocNetAllowedSubscribeEntityTypesDesc[$arParams["ENTITY_TYPE"]]["HAS_SITE_ID"] == "Y"
													&& defined("SITE_ID") 
													&& strlen(SITE_ID) > 0 
													? 
														SITE_ID 
													: 
														false
												),
								"MAIL_EVENT" 	=> ($_REQUEST[$event_id_tmp."_active"] == "M") ? "Y" : "N",
								"TRANSPORT" 	=> $subscribe_transport,
								"VISIBLE" 		=> $subscribe_visible,
							)
						);
						if (!$idTmp)
						{
							if ($e = $APPLICATION->GetException())
								$errorMessage .= $e->GetString();
							break;
						}
					}
					
					if (
						array_key_exists("TransportCB", $arEventTmp) 
						&& (
							$subscribe_transport_cb != "I" 
							|| $subscribe_visible_cb != "I"
						)
					)
					{
						$idTmp = CSocNetLogEvents::Add(
							array(
								"USER_ID" => $GLOBALS["USER"]->GetID(),
								"ENTITY_TYPE" 	=> $arParams["ENTITY_TYPE"],
								"ENTITY_ID" 	=> ($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP) ? $arResult["Group"]["ID"] : $arResult["User"]["ID"],
								"ENTITY_CB" 	=> "Y",
								"EVENT_ID" 		=> $event_id,
								"SITE_ID" 		=> (($arParams["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP && defined("SITE_ID") && strlen(SITE_ID) > 0) ? SITE_ID : false),
								"MAIL_EVENT" 	=> ($_REQUEST[$event_id_tmp."_active"] == "M") ? "Y" : "N",
								"TRANSPORT"		=> $subscribe_transport_cb,
								"VISIBLE"		=> $subscribe_visible_cb,								
							)
						);
						if (!$idTmp)
						{
							if ($e = $APPLICATION->GetException())
								$errorMessage .= $e->GetString();
							break;
						}
					}					
				}
				
				if (strlen($errorMessage) > 0)
					break;
			}

			if (strlen($errorMessage) > 0)
				$arResult["ErrorMessage"] = $errorMessage;
			else
				$arResult["ShowForm"] = "Confirm";
		}
	}
}

$arResult["FriendsAllowed"] = COption::GetOptionString("socialnetwork", "allow_frields", "Y");

$this->IncludeComponentTemplate();
?>