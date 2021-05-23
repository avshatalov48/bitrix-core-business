<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);
define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

if (!function_exists("__SLGetSubscriptionData")) 
{
	function __SLGetSubscriptionData($event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll)
	{
		$transport_inherited = false;
		$transport_inherited_from = false;

		if (
			array_key_exists($event_id_tmp, $arSubscribesTmp) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmp[$event_id_tmp])
			&& $arSubscribesTmp[$event_id_tmp]["TRANSPORT"] != "I"
		)
			$transport = $arSubscribesTmp[$event_id_tmp]["TRANSPORT"];
		elseif (
			array_key_exists("all", $arSubscribesTmp) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmp["all"])
			&& $arSubscribesTmp["all"]["TRANSPORT"] != "I"
		)
		{
			$transport = $arSubscribesTmp["all"]["TRANSPORT"];
			$transport_inherited = true;
			
			if ($arSubscribesTmp["all"]["ENTITY_ID"] == 0 && $arSubscribesTmp["all"]["ENTITY_MY"] == "Y")
				$entity_id = "allmy";
			elseif ($arSubscribesTmp["all"]["ENTITY_ID"] == 0 && $arSubscribesTmp["all"]["ENTITY_MY"] != "Y")
				$entity_id = "all";
			else
				$entity_id = $arSubscribesTmp["all"]["ENTITY_ID"];

			$transport_inherited_from = $entity_id."_all";
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAllMy) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmpAllMy[$event_id_tmp])
			&& $arSubscribesTmpAllMy[$event_id_tmp]["TRANSPORT"] != "I"
		)
		{
			$transport = $arSubscribesTmpAllMy[$event_id_tmp]["TRANSPORT"];
			$transport_inherited = true;
			$transport_inherited_from = "allmy_event";
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAllMy) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmpAllMy["all"])
			&& $arSubscribesTmpAllMy["all"]["TRANSPORT"] != "I"
		)
		{
			$transport = $arSubscribesTmpAllMy["all"]["TRANSPORT"];
			$transport_inherited = true;
			$transport_inherited_from = "allmy_all";
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAll) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmpAll[$event_id_tmp])
			&& $arSubscribesTmpAll[$event_id_tmp]["TRANSPORT"] != "I"
		)
		{
			$transport = $arSubscribesTmpAll[$event_id_tmp]["TRANSPORT"];
			$transport_inherited = true;
			$transport_inherited_from = "all_event";
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAll) 
			&& array_key_exists("TRANSPORT", $arSubscribesTmpAll["all"])
			&& $arSubscribesTmpAll["all"]["TRANSPORT"] != "I"
		)
		{
			$transport = $arSubscribesTmpAll["all"]["TRANSPORT"];
			$transport_inherited = true;
			$transport_inherited_from = "all_all";
		}
		else
		{
			$transport = "N";
			$transport_inherited = true;
			$transport_inherited_from = "root_all";
		}

		$visible_inherited = false;
		$visible_inherited_from = false;
		if (
			array_key_exists($event_id_tmp, $arSubscribesTmp) 
			&& array_key_exists("VISIBLE", $arSubscribesTmp[$event_id_tmp])
			&& $arSubscribesTmp[$event_id_tmp]["VISIBLE"] != "I"
		)
			$visible = $arSubscribesTmp[$event_id_tmp]["VISIBLE"];
		elseif (
			array_key_exists("all", $arSubscribesTmp) 
			&& array_key_exists("VISIBLE", $arSubscribesTmp["all"])
			&& $arSubscribesTmp["all"]["VISIBLE"] != "I"
		)
		{
			$visible = $arSubscribesTmp["all"]["VISIBLE"];
			$visible_inherited = true;
			
			if ($arSubscribesTmp["all"]["ENTITY_ID"] == 0 && $arSubscribesTmp["all"]["ENTITY_MY"] == "Y")
				$entity_id = "allmy";
			elseif ($arSubscribesTmp["all"]["ENTITY_ID"] == 0 && $arSubscribesTmp["all"]["ENTITY_MY"] != "Y")
				$entity_id = "all";
			else
				$entity_id = $arSubscribesTmp["all"]["ENTITY_ID"];

			$visible_inherited_from = $entity_id."_all";
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAllMy) 
			&& array_key_exists("VISIBLE", $arSubscribesTmpAllMy[$event_id_tmp])
			&& $arSubscribesTmpAllMy[$event_id_tmp]["VISIBLE"] != "I"
		)
		{
			$visible = $arSubscribesTmpAllMy[$event_id_tmp]["VISIBLE"];
			$visible_inherited = true;
			$visible_inherited_from = "allmy_event";
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAllMy) 
			&& array_key_exists("VISIBLE", $arSubscribesTmpAllMy["all"])
			&& $arSubscribesTmpAllMy["all"]["VISIBLE"] != "I"
		)
		{
			$visible = $arSubscribesTmpAllMy["all"]["VISIBLE"];
			$visible_inherited = true;
			$visible_inherited_from = "allmy_all";
		}
		elseif (
			array_key_exists($event_id_tmp, $arSubscribesTmpAll) 
			&& array_key_exists("VISIBLE", $arSubscribesTmpAll[$event_id_tmp])
			&& $arSubscribesTmpAll[$event_id_tmp]["VISIBLE"] != "I"
		)
		{
			$visible = $arSubscribesTmpAll[$event_id_tmp]["VISIBLE"];
			$visible_inherited = true;
			$visible_inherited_from = "all_event";
		}
		elseif (
			array_key_exists("all", $arSubscribesTmpAll) 
			&& array_key_exists("VISIBLE", $arSubscribesTmpAll["all"])
			&& $arSubscribesTmpAll["all"]["VISIBLE"] != "I"
		)
		{
			$visible = $arSubscribesTmpAll["all"]["VISIBLE"];
			$visible_inherited = true;
			$visible_inherited_from = "all_all";
		}
		else
		{
			$visible = "Y";
			$visible_inherited = true;
			$visible_inherited_from = "root_all";
		}
	
		return array(
			"Transport" => $transport,
			"TransportInherited" => $transport_inherited,
			"TransportInheritedFrom" => $transport_inherited_from,
			"Visible" => $visible,
			"VisibleInherited" => $visible_inherited,
			"VisibleInheritedFrom" => $visible_inherited_from
		);
	
	}
}

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]) : "";
$entity_type = (isset($_REQUEST["et"]) && is_string($_REQUEST["et"])) ? trim($_REQUEST["et"]) : "";
$entity_id = (isset($_REQUEST["eid"]) && is_string($_REQUEST["eid"])) ? trim($_REQUEST["eid"]) : "";
$entity_cb = (array_key_exists("ecb", $_REQUEST) && is_string($_REQUEST["ecb"])) ? trim($_REQUEST["ecb"]) : "";
$event_id = (array_key_exists("evid", $_REQUEST) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]) : "";

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]) : "";
$lng = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Localization\Loc;

if (!$GLOBALS["USER"]->IsAuthorized())
	return false;

Loc::loadLanguageFile(__FILE__, $lng);

if(CModule::IncludeModule("socialnetwork"))
{
	$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
	$arSocNetLogEvents = CSocNetAllowed::GetAllowedLogEvents();
	$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

	$arResult = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
		$arResult[0] = "*";
	elseif (!check_bitrix_sessid())
		$arResult[0] = "*";
	elseif ($action == "get_data")
	{
		$arSubscribesTmp = array();
		$arSubscribesTmpCB = array();

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(), 
			"ENTITY_TYPE" => $entity_type,
			"SITE_ID" => array($site_id, false)
		);

		$arSubscribesTmpAllMy = array();
		$arSubscribesTmpAll = array();
		$arSubscribesTmpAllMyCB = array();
		$arSubscribesTmpAllCB = array();

		if ($entity_id == "all")
		{
			$arFilter["ENTITY_ID"] = 0;
			$arFilter["ENTITY_MY"] = "N";
		}
		else
		{
			// get upper level subscription
			$is_my = false;
			
			if (
				array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)			
				&& array_key_exists("CLASS_MY_BY_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
				&& array_key_exists("METHOD_MY_BY_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
			)
				$is_my = call_user_func(
					array(
						$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["CLASS_MY_BY_ID"],
						$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["METHOD_MY_BY_ID"]
					),
					$entity_id
				);

			if ($is_my)
			{
				$arSubscribesTmpAllMy = array();
				$dbResultTmp = CSocNetLogEvents::GetList(
					array(),
					array(
						"USER_ID" => $GLOBALS["USER"]->GetID(), 
						"ENTITY_TYPE" => $entity_type, 
						"ENTITY_ID" => 0, 
						"ENTITY_MY" => "Y"
					)
				);
				while ($arResultTmp = $dbResultTmp->Fetch())
				{
					if ($arResultTmp["ENTITY_CB"] == "Y")
						$arSubscribesTmpAllMyCB[$arResultTmp["EVENT_ID"]] = $arResultTmp;
					else
						$arSubscribesTmpAllMy[$arResultTmp["EVENT_ID"]] = $arResultTmp;
				}
			}

			$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				array(
					"USER_ID" => $GLOBALS["USER"]->GetID(), 
					"ENTITY_TYPE" => $entity_type, 
					"ENTITY_ID" => 0, 
					"ENTITY_MY" => "N"
				)
			);
			while ($arResultTmp = $dbResultTmp->Fetch())
			{
				if ($arResultTmp["ENTITY_CB"] == "Y")
					$arSubscribesTmpAllCB[$arResultTmp["EVENT_ID"]] = $arResultTmp;
				else
					$arSubscribesTmpAll[$arResultTmp["EVENT_ID"]] = $arResultTmp;
			}

			if ($entity_id == "allmy")
			{
				$arFilter["ENTITY_ID"] = 0;
				$arFilter["ENTITY_MY"] = "Y";
			}
			else
				$arFilter["ENTITY_ID"] = $entity_id;
		}

		$dbResultTmp = CSocNetLogEvents::GetList(
			array(),
			$arFilter
		);

		while ($arResultTmp = $dbResultTmp->Fetch())
		{
			if ($arResultTmp["ENTITY_CB"] == "Y")
				$arSubscribesTmpCB[$arResultTmp["EVENT_ID"]] = $arResultTmp;
			else
				$arSubscribesTmp[$arResultTmp["EVENT_ID"]] = $arResultTmp;
		}

		$arFeaturesTmp = array();
		$dbResultTmp = CSocNetFeatures::GetList(
			array(),
			array("ENTITY_TYPE" => $entity_type, "ENTITY_ID" => $entity_id)
		);
		while ($arResultTmp = $dbResultTmp->GetNext())
			$arFeaturesTmp[$arResultTmp["FEATURE"]] = $arResultTmp;

		$arResult["Subscription"] = array();

		foreach ($arSocNetLogEvents as $event_id_tmp => $arEventTmp)
		{
			if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
				continue;

			if (
				!array_key_exists("ENTITIES", $arEventTmp)
				|| !array_key_exists($entity_type, $arEventTmp["ENTITIES"])
			)
				continue;

			$arSubscriptionData = __SLGetSubscriptionData($event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll);

			$arResult["Subscription"][] = array(
				"Feature" => $event_id_tmp,
				"Name" => $arEventTmp["ENTITIES"][$entity_type]["TITLE"],
				"Transport" => $arSubscriptionData["Transport"],
				"TransportInherited" => $arSubscriptionData["TransportInherited"],
				"TransportInheritedFrom" => $arSubscriptionData["TransportInheritedFrom"],
				"Visible" => $arSubscriptionData["Visible"],
				"VisibleInherited" => $arSubscriptionData["VisibleInherited"],		
				"VisibleInheritedFrom" => $arSubscriptionData["VisibleInheritedFrom"],
			);
		}
	
		foreach ($arSocNetFeaturesSettings as $feature => $arFeature)
		{
			if ($feature == "files")
				continue;

			if (
				array_key_exists("allowed", $arFeature)
				&& is_array($arFeature["allowed"])
				&& !in_array($entity_type, $arFeature["allowed"])
			)
				continue;

			if (
				!array_key_exists("subscribe_events", $arFeature) 
				|| !$arFeature["subscribe_events"]
			)
				continue;

			if (
				in_array($entity_id, array("all", "allmy")) 
				|| CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $entity_type, $entity_id, $feature, $arFeature["minoperation"][0], CSocNetUser::IsCurrentUserModuleAdmin())
			)
			{
				foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
				{
					if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
						continue;

					$arSubscriptionData = __SLGetSubscriptionData($event_id_tmp, $arSubscribesTmp, $arSubscribesTmpAllMy, $arSubscribesTmpAll);

					$arSubscription = array(
						"Feature" => $event_id_tmp,
						"Name" => $arEventTmp["ENTITIES"][$entity_type]["TITLE"],
						"Transport" => $arSubscriptionData["Transport"],
						"TransportInherited" => $arSubscriptionData["TransportInherited"],
						"TransportInheritedFrom" => $arSubscriptionData["TransportInheritedFrom"],
						"Visible" => $arSubscriptionData["Visible"],
						"VisibleInherited" => $arSubscriptionData["VisibleInherited"],		
						"VisibleInheritedFrom" => $arSubscriptionData["VisibleInheritedFrom"],
					);
			
					if (
						array_key_exists("HAS_CB", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
						&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_CB"] == "Y"
						&& intval($entity_id) > 0
					)
					{
						$arSubscriptionData = __SLGetSubscriptionData($event_id_tmp, $arSubscribesTmpCB, $arSubscribesTmpAllMyCB, $arSubscribesTmpAllCB);

						$arSubscription["TransportCB"] = $arSubscriptionData["Transport"];
						$arSubscription["TransportInheritedCB"] = $arSubscriptionData["TransportInherited"];
						$arSubscription["TransportInheritedFromCB"] = $arSubscriptionData["TransportInheritedFrom"];
						$arSubscription["VisibleCB"] = $arSubscriptionData["Visible"];
						$arSubscription["VisibleInheritedCB"] = $arSubscriptionData["VisibleInherited"];
						$arSubscription["VisibleInheritedFromCB"] = $arSubscriptionData["VisibleInheritedFrom"];
					}
					
					$arResult["Subscription"][] = $arSubscription;				
				}
			}
		}

		$arResult["Transport"] = array(
			0 => array("Key" => "N", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_NONE", false, $lng)),
			1 => array("Key" => "M", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_MAIL", false, $lng)),
//			3 => array("Key" => "D", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_DIGEST", false, $lng)),
//			4 => array("Key" => "E", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_DIGEST_WEEK", false, $lng))
		);

		if (CBXFeatures::IsFeatureEnabled("WebMessenger"))
			$arResult["Transport"][] = array("Key" => "X", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_XMPP"));
		
		$arResult["Visible"] = array(
			0 => array("Key" => "Y", "Value" => Loc::getMessage("SUBSCRIBE_VISIBLE_VISIBLE", false, $lng)),
			1 => array("Key" => "N", "Value" => Loc::getMessage("SUBSCRIBE_VISIBLE_HIDDEN", false, $lng)),
		);		

	}
	elseif ($action == "delete")
	{
		if ($entity_cb == "Y")
			$entity_cb_val = "Y";
		else
			$entity_cb_val = "N";

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => $entity_id,
			"ENTITY_CB" => $entity_cb_val,
			"SITE_ID" => array($site_id, false)
		);
				
		if (
			$event_id <> '' 
			&& $event_id != 'all'
		)
		{
// FULL_SET !!!
			$bFound = false;
			if (array_key_exists($event_id, $arSocNetLogEvents))
			{
				if (array_key_exists("FULL_SET", $arSocNetLogEvents[$event_id]))
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
								if (array_key_exists("FULL_SET", $arEvent))
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

			$arFilter["EVENT_ID"] = $arEventID;
		}
		
		$dbResultTmp = CSocNetLogEvents::GetList(
			array("ID" => "DESC"),
			$arFilter,
			false,
			false,
			array("ID")
		);

		$bSuccess = true;
		while ($arResultTmp = $dbResultTmp->Fetch())	
			if (!CSocNetLogEvents::Delete($arResultTmp["ID"]))
				$bSuccess = false;
		
		if ($bSuccess)
			$arResult["ActionResult"] = "OK";
			
	}	

	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>