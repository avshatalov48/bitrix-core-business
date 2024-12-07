<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
$arSocNetLogEvents = CSocNetAllowed::GetAllowedLogEvents();
$arResult["arSocNetAllowedSubscribeEntityTypesDesc"] = CSocNetAllowed::GetAllowedEntityTypesDesc();
$arResult["arSocNetAllowedSubscribeEntityTypes"] = CSocNetAllowed::GetAllowedEntityTypes();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.subscribe_list/include.php");

$arParams["USER_ID"] = intval($GLOBALS["USER"]->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_SUBSCRIBE"] = trim($arParams["PATH_TO_GROUP_SUBSCRIBE"]);
if ($arParams["PATH_TO_GROUP_SUBSCRIBE"] == '')
	$arParams["PATH_TO_GROUP_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_subscribe&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_USER_SUBSCRIBE"] = trim($arParams["PATH_TO_USER_SUBSCRIBE"]);
if ($arParams["PATH_TO_USER_SUBSCRIBE"] == '')
	$arParams["PATH_TO_USER_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_subscribe&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
	);
$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;

$arFilter["ENTITY_TYPE"] = Trim($arFilter["ENTITY_TYPE"]);
if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
	$arFilter["ENTITY_TYPE"] = "";
if ($arParams["ENTITY_TYPE"] == '')
	$arParams["ENTITY_TYPE"] = Trim($_REQUEST["flt_entity_type"]);
if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
	$arFilter["ENTITY_TYPE"] = "";

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["save"] <> '' && check_bitrix_sessid())
	{
		$errorMessage = "";

		foreach($_POST as $key => $value)
		{
			if ($value == "I")
				continue;

			if (
				mb_strpos($key, "t_bx_sl_") === 0
				|| mb_strpos($key, "t_cb_bx_sl_") === 0
				|| mb_strpos($key, "v_bx_sl_") === 0
				|| mb_strpos($key, "v_cb_bx_sl_") === 0
			)
			{
				if (preg_match("#(t_bx_sl|t_cb_bx_sl|v_bx_sl|v_cb_bx_sl)_([a-zA-Z0-9]+)_([0-9almy]+)_([a-zA-Z_]+)#iu", $key, $res) > 0)
				{
					$entity_type = $res[2];
					if ($res[3] == "all")
					{
						$entity_id = 0;
						$entity_my = "N";
					}
					elseif ($res[3] == "allmy")
					{
						$entity_id = 0;
						$entity_my = "Y";
					}
					else
					{
						$entity_id = intval($res[3]);
						$entity_my = "N";
					}
					
					if ($res[1] == "t_cb_bx_sl" || $res[1] == "v_cb_bx_sl")
						$entity_cb = "Y";
					else
						$entity_cb = "N";

					$event_id = $res[4];

					if ($event_id == "cb_all")
						$event_id = "all";

					$bFound = false;

					if (array_key_exists($event_id, $arSocNetLogEvents))
					{
						if (
							array_key_exists("ENTITIES", $arSocNetLogEvents[$event_id])
							&& array_key_exists($entity_type, $arSocNetLogEvents[$event_id]["ENTITIES"])
							&& array_key_exists("FULL_SET", $arSocNetLogEvents[$event_id])
						)
						{
							$arEventID = $arSocNetLogEvents[$event_id]["FULL_SET"];
							$bFound = true;
						}

					}
					else
					{
						foreach($arSocNetFeaturesSettings as $arFeature)
						{
							if (array_key_exists("subscribe_events", $arFeature))
							{
								foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEvent)
								{
									if ($event_id_tmp == $event_id)
									{
										if (
											array_key_exists("ENTITIES", $arEvent)
											&& array_key_exists($entity_type, $arEvent["ENTITIES"])
											&& array_key_exists("FULL_SET", $arEvent)
										)
										{
											$arEventID = $arEvent["FULL_SET"];
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
						$arEventID = array($event_id);
					
					foreach($arEventID as $event_id)
					{
						$dbRes = CSocNetLogEvents::GetList(
							array(),
							array(
								"USER_ID" => $GLOBALS["USER"]->GetID(),
								"ENTITY_TYPE" => $entity_type,
								"ENTITY_ID" => $entity_id,
								"ENTITY_CB" => $entity_cb,
								"ENTITY_MY" => $entity_my,
								"EVENT_ID" => $event_id,
								"SITE_ID" => (
									$entity_cb != "Y"
									&& array_key_exists("HAS_SITE_ID", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
									&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
									&& defined("SITE_ID") 
									&& SITE_ID <> ''
										? SITE_ID 
										: false
								)
							)
						);

						$arFields = array(
							"USER_ID" => $GLOBALS["USER"]->GetID(),
							"ENTITY_TYPE" => $entity_type,
							"ENTITY_ID" => $entity_id,
							"ENTITY_CB" => $entity_cb,
							"ENTITY_MY" => $entity_my,
							"EVENT_ID" 	=> $event_id,
							"SITE_ID" 	=> (
								$entity_cb != "Y"
								&& array_key_exists("HAS_SITE_ID", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
								&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
								&& defined("SITE_ID") 
								&& SITE_ID <> ''
									? SITE_ID 
									: false
							)
						);

						if (
							mb_strpos($key, "t_bx_sl_") === 0
							|| mb_strpos($key, "t_cb_bx_sl_") === 0
						)
							$arFields["TRANSPORT"] = $value;
						elseif (
							mb_strpos($key, "v_bx_sl_") === 0
							|| mb_strpos($key, "v_cb_bx_sl_") === 0
						)
							$arFields["VISIBLE"] = $value;
						
						if ($arRes = $dbRes->Fetch())
						{
							$idTmp = CSocNetLogEvents::Update(
								$arRes["ID"],
								$arFields
							);
						}
						else
						{
							if (isset($arFields["TRANSPORT"]))
								$arFields["VISIBLE"] = "I";
							elseif (isset($arFields["VISIBLE"]))
								$arFields["TRANSPORT"] = "I";

							$idTmp = CSocNetLogEvents::Add(
								$arFields
							);
						}

						if (!$idTmp)
						{
							if ($e = $APPLICATION->GetException())
								$errorMessage .= $e->GetString();
							break;
						}
					}
					
					if ($errorMessage <> '')
						break;
				}
			}
		}

		if ($errorMessage <> '')
		{
			$arResult["ErrorMessage"] = $errorMessage;
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage());
		}
	}

	// get my entities of each types
	$arEntities = array();
	foreach ($arResult["arSocNetAllowedSubscribeEntityTypes"] as $entity_type)
	{
		if (
			is_array($arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("TITLE_LIST", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST"] <> ''
		)
			$arEntities[$entity_type]["ALL"]["TITLE_LIST"] = $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST"];
	
		if (
			array_key_exists($entity_type, $arResult["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("HAS_MY", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_MY"] == "Y"
			&& array_key_exists("CLASS_MY", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("METHOD_MY", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"] <> ''
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"] <> ''
			&& method_exists($arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"], $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"])
		)
		{
			if (
				array_key_exists("TITLE_LIST_MY", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
				&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST_MY"] <> ''
			)
			{
				$arEntities[$entity_type]["ALL_MY"]["TITLE_LIST"] = $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST_MY"];
			}
			$arEntities[$entity_type]["ALL_MY"]["ITEMS"] = call_user_func(array($arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"], $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"]));
		}
	}

	$arResult["Urls"]["ViewAll"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("", array("flt_entity_type"))); 
	
	if (CBXFeatures::IsFeatureEnabled("Workgroups"))
		$arResult["Urls"]["ViewGroups"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_GROUP, array("flt_entity_type"))); 
	else
		$arResult["Urls"]["ViewGroups"] = "";

	$arResult["Urls"]["ViewUsers"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_USER, array("flt_entity_type")));

	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_C30_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] != "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_C30_PAGE_TITLE"));

	$arResult["Events"] = false;
	$arResult["EventsNew"] = array();

	$arFilter = array("USER_ID" => $GLOBALS["USER"]->GetID());
	if ($arParams["ENTITY_TYPE"] <> '')
		$arFilter["ENTITY_TYPE"] = $arParams["ENTITY_TYPE"];

	if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
		$arFilter["GROUP_SITE_ID"] = SITE_ID;

	if ($arParams["ENTITY_TYPE"] == '')
	{
		$arFilter["COMMON_GROUP_SITE_ID"] = SITE_ID;
		$arFilter["SITE_ID"] = array(SITE_ID, false);
	}

	$dbEvents = CSocNetLogEvents::GetList(
		array("ENTITY_TYPE" => "ASC", "ENTITY_ID" => "ASC"),
		$arFilter
	);
	
	$arEntityID = array();
	
	while ($arEvents = $dbEvents->GetNext())
	{
		if (
			$arEvents["EVENT_ID"] != "all"
			&& !array_key_exists($arEvents["EVENT_ID"], $arSocNetLogEvents) 
			&& (
				!array_key_exists($arEvents["EVENT_ID"], $arSocNetFeaturesSettings) 
				|| !array_key_exists("subscribe_events", $arSocNetFeaturesSettings[$arEvents["EVENT_ID"]]) 
				|| !is_array($arSocNetFeaturesSettings[$arEvents["EVENT_ID"]]["subscribe_events"]) 
				|| count($arSocNetFeaturesSettings[$arEvents["EVENT_ID"]]["subscribe_events"]) <= 0
			)
		)
			continue;

		if ($arResult["Events"] == false)
			$arResult["Events"] = array();

		if ($arResult["EventsNew"] == false)
			$arResult["EventsNew"] = array();

		$arrayKey = $arEvents["ENTITY_TYPE"]."_".$arEvents["ENTITY_ID"];
		$arrayKeyNew = $arEvents["ENTITY_ID"];

		
		if (in_array($arEvents["ENTITY_TYPE"], array(SONET_ENTITY_GROUP, SONET_ENTITY_USER)))
		{
			if ($arEvents["EVENT_ID"] != "all" && !array_key_exists($arrayKey, $arResult["Events"]))
			{
				$arResult["Events"][$arrayKey] = array(
					"ENTITY_TYPE" => $arEvents["ENTITY_TYPE"],
					"ENTITY_ID" => $arEvents["ENTITY_ID"],
				);

				if (
					$arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP
					&& intval($arEvents["ENTITY_ID"]) > 0
				)
				{
					$arGroup = CSocNetGroup::GetByID($arEvents["ENTITY_ID"]);
					$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arEvents["ENTITY_ID"]));

					$arResult["Events"][$arrayKey]["Group"] = $arGroup;
					$arResult["Events"][$arrayKey]["GroupUrl"] = $path2Entity;
					$arResult["Events"][$arrayKey]["EditUrl"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SUBSCRIBE"], array("group_id" => $arEvents["ENTITY_ID"]));
				}
				elseif (intval($arEvents["ENTITY_ID"]) > 0)
				{
					$dbUser = CUser::GetByID($arEvents["ENTITY_ID"]);
					$arUser = $dbUser->GetNext();
					$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arEvents["ENTITY_ID"]));

					$arResult["Events"][$arrayKey]["User"] = $arUser;
					$arResult["Events"][$arrayKey]["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);
					$arResult["Events"][$arrayKey]["UserUrl"] = $path2Entity;
					$arResult["Events"][$arrayKey]["EditUrl"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SUBSCRIBE"], array("user_id" => $arEvents["ENTITY_ID"]));
				}
				
				$arResult["Events"][$arrayKey]["EditUrl"] .= (mb_strpos($arResult["Events"][$arrayKey]["EditUrl"], "?") !== false ? "&" : "?")."backurl=".$APPLICATION->GetCurPage();
			}

			if ($arEvents["EVENT_ID"] != "all")
				$arResult["Events"][$arrayKey]["Items"][] = array(
					"EVENT_ID" 		=> $arEvents["EVENT_ID"],
					"SITE_ID" 		=> $arEvents["SITE_ID"],
					"MAIL_EVENT" 	=> $arEvents["MAIL_EVENT"],
					"TRANSPORT" 	=> $arEvents["TRANSPORT"],
					"VISIBLE" 		=> $arEvents["VISIBLE"],				
				);		
		}
			
		if (in_array($arEvents["ENTITY_TYPE"], $arResult["arSocNetAllowedSubscribeEntityTypes"]))
		{
			if ($arEvents["ENTITY_ID"] != 0)
			{
				if ($arEvents["ENTITY_CB"] == "Y")
					$key = $arEvents["ENTITY_TYPE"]."_CB";
				elseif (
					array_key_exists("ALL_MY", $arEntities[$arEvents["ENTITY_TYPE"]])
					&& array_key_exists("ITEMS", $arEntities[$arEvents["ENTITY_TYPE"]]["ALL_MY"])
					&& in_array($arEvents["ENTITY_ID"], $arEntities[$arEvents["ENTITY_TYPE"]]["ALL_MY"]["ITEMS"])
				)
					$key = $arEvents["ENTITY_TYPE"]."_My";
				else
					$key = $arEvents["ENTITY_TYPE"];
			}
			elseif($arEvents["ENTITY_MY"] == "Y")
				$key = $arEvents["ENTITY_TYPE"]."_MyCommon";
			else
				$key = $arEvents["ENTITY_TYPE"]."_Common";			
		}

		if (!array_key_exists($key, $arResult["EventsNew"]))
			$arResult["EventsNew"][$key] = array();

		// initialize entity description
		if (!array_key_exists($arrayKeyNew, $arResult["EventsNew"][$key]))
		{
			$arResult["EventsNew"][$key][$arrayKeyNew] = array(
				"ENTITY_TYPE" => $arEvents["ENTITY_TYPE"],
				"ENTITY_ID" => $arEvents["ENTITY_ID"],
			);

			$arEntityTmp = call_user_func(
				array(
					$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$arEvents["ENTITY_TYPE"]]["CLASS_DESC_GET"], 
					$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$arEvents["ENTITY_TYPE"]]["METHOD_DESC_GET"]
				),
				$arEvents["ENTITY_ID"]
			);

			$path2Entity = CComponentEngine::MakePathFromTemplate(
				$arParams[$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$arEvents["ENTITY_TYPE"]]["URL_PARAM_KEY"]], 
				array(
					$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$arEvents["ENTITY_TYPE"]]["URL_PATTERN"] => $arEvents["ENTITY_ID"]
				)
			);

			$arResult["EventsNew"][$key][$arrayKeyNew]["ENTITY_DESC"] = $arEntityTmp;
			$arResult["EventsNew"][$key][$arrayKeyNew]["ENTITY_URL"] = $path2Entity;
		}

		$items_key = "Items";

		$arResult["EventsNew"][$key][$arrayKeyNew][$items_key][] = array(
			"EVENT_ID" 		=> $arEvents["EVENT_ID"],
			"SITE_ID" 		=> $arEvents["SITE_ID"],
			"TRANSPORT" 	=> $arEvents["TRANSPORT"],
			"VISIBLE" 		=> $arEvents["VISIBLE"],			
		);

		if (!in_array(array("KEY" => $key, "ENTITY_ID" => $arEvents["ENTITY_ID"], "SITE_ID" => $arEvents["SITE_ID"]), $arEntityID))
			$arEntityID[] = array("KEY" => $key, "ENTITY_ID" => $arEvents["ENTITY_ID"], "SITE_ID" => $arEvents["SITE_ID"]);
	}

	$arResult["Transport"] = array(
		"N" => GetMessage("SONET_C30_TRANSPORT_NONE"),
		"M" => GetMessage("SONET_C30_TRANSPORT_MAIL"),
//		"D" => GetMessage("SONET_C30_TRANSPORT_DIGEST"),
//		"E" => GetMessage("SONET_C30_TRANSPORT_DIGEST_WEEK")
	);
	
	if (CBXFeatures::IsFeatureEnabled("WebMessenger"))	
		$arResult["Transport"]["X"] = GetMessage("SONET_C30_TRANSPORT_XMPP");

	$arResult["Visible"] = array(
		"Y" => GetMessage("SONET_C30_VISIBLE_VISUAL"),
		"N" => GetMessage("SONET_C30_VISIBLE_HIDDEN"),
	);

	if (!function_exists("__SSL_cmp"))
	{
		function __SSL_cmp($a, $b)
		{
			return ($a < $b ? -1 : ($a > $b ? 1 : 0));
		}
	}

	foreach ($arResult["arSocNetAllowedSubscribeEntityTypes"] as $entity_type)
	{
		if (
			array_key_exists($entity_type, $arResult["EventsNew"]) 
			&& !empty($arResult["EventsNew"][$entity_type])
		)
				uksort($arResult["EventsNew"][$entity_type], "__SSL_cmp");
		if (
			array_key_exists($entity_type."_My", $arResult["EventsNew"]) 		
			&& !empty($arResult["EventsNew"][$entity_type."_My"])
		)
			uksort($arResult["EventsNew"][$entity_type."_My"], "__SSL_cmp");	
	}
	
}

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE_WO_NOBR"];

$arResult["ENTITY_TYPES"] = array();

foreach ($arSocNetLogEvents as $event_id_tmp => $arEventTmp)
{
	if (
		array_key_exists("HIDDEN", $arEventTmp)
		&& $arEventTmp["HIDDEN"]
	)
		continue;

	if (
		array_key_exists("ENTITIES", $arEventTmp)
		&& is_array($arEventTmp["ENTITIES"])
	)
		foreach ($arEventTmp["ENTITIES"] as $event_type_tmp => $arEntityTypeTmp)
			$arResult["ENTITY_TYPES"][$event_type_tmp][] = $event_id_tmp;
}

foreach ($arSocNetFeaturesSettings as $feature_tmp => $arFeatureTmp)
{
	if (
		!array_key_exists("subscribe_events", $arFeatureTmp) 
		|| !$arFeatureTmp["subscribe_events"]
	)
		continue;

	if ($feature_tmp == "files")
		continue;

	foreach ($arFeatureTmp["subscribe_events"] as $event_id_tmp => $arEventTmp)
	{
		if (
			array_key_exists("HIDDEN", $arEventTmp)
			&& $arEventTmp["HIDDEN"]
		)
			continue;

		if (
			array_key_exists("ENTITIES", $arEventTmp)
			&& is_array($arEventTmp["ENTITIES"])
		)
			foreach ($arEventTmp["ENTITIES"] as $event_type_tmp => $arEntityTypeTmp)
				$arResult["ENTITY_TYPES"][$event_type_tmp][] = $event_id_tmp;
	}
}

$this->IncludeComponentTemplate();
?>