<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
$entity_type = (isset($_REQUEST["et"]) && is_string($_REQUEST["et"])) ? trim($_REQUEST["et"]): "";
$entity_id = isset($_REQUEST["eid"])? $_REQUEST["eid"]: "";
$cb_id = isset($_REQUEST["cb_id"])? $_REQUEST["cb_id"]: "";
$event_id = (isset($_REQUEST["evid"]) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]): "";
$transport = (isset($_REQUEST["transport"]) && is_string($_REQUEST["transport"])) ? trim($_REQUEST["transport"]): "";

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && !is_array($_REQUEST["ls"])? trim($_REQUEST["ls"]): "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Livefeed;

global $USER;

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
{
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
}
else
{
	define("LANGUAGE_ID", "en");
}

if (empty($lng))
{
	$lng = LANGUAGE_ID;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.log.ex/include.php");

Loc::loadLanguageFile(__FILE__, $lng);

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
	$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (in_array($action, array("get_comment", "get_comments")))
	{
		CSocNetTools::InitGlobalExtranetArrays();
	}

	if (!$USER->IsAuthorized())
	{
		$arResult[0] = "*";
	}
	elseif (!check_bitrix_sessid())
	{
		$arResult[0] = "*";
	}
	elseif ($action == "get_raw_data")
	{
		$provider = \Bitrix\Socialnetwork\Livefeed\Provider::init(array(
			'ENTITY_TYPE' => (isset($_REQUEST['ENTITY_TYPE']) ? preg_replace("/[^a-z0-9_]/i", "", $_REQUEST['ENTITY_TYPE']) : false),
			'ENTITY_ID' => (isset($_REQUEST['ENTITY_ID']) ? intval($_REQUEST['ENTITY_ID']) : false),
			'CLONE_DISK_OBJECTS' => true
		));

		if ($provider)
		{
			$arResult = array(
				'TITLE' => $provider->getSourceTitle(),
				'DESCRIPTION' => $provider->getSourceDescription(),
				'DISK_OBJECTS' => $provider->getSourceDiskObjects()
			);

			if (isset($_REQUEST["params"]))
			{
				if (
					isset($_REQUEST["params"]["getSonetGroupAvailableList"])
					&& !!$_REQUEST["params"]["getSonetGroupAvailableList"]
				)
				{
					$feature = $operation = false;
					if (
						isset($_REQUEST["params"]["checkParams"])
						&& isset($_REQUEST["params"]["checkParams"]["feature"])
						&& isset($_REQUEST["params"]["checkParams"]["operation"])
					)
					{
						$feature = $_REQUEST["params"]["checkParams"]["feature"];
						$operation = $_REQUEST["params"]["checkParams"]["operation"];
					}
					$arResult['GROUPS_AVAILABLE'] = $provider->getSonetGroupsAvailable($feature, $operation);
				}

				if (
					isset($_REQUEST["params"]["getLivefeedUrl"])
					&& !!$_REQUEST["params"]["getLivefeedUrl"]
				)
				{
					$arResult['LIVEFEED_URL'] = $provider->getLiveFeedUrl();
				}
			}
		}
	}
	elseif ($action == "create_task_comment")
	{
		\Bitrix\Socialnetwork\ComponentHelper::processBlogCreateTask(array(
			'TASK_ID' => (isset($_REQUEST['TASK_ID']) ? intval($_REQUEST['TASK_ID']) : false),
			'SOURCE_ENTITY_TYPE' => (isset($_REQUEST['ENTITY_TYPE']) ? preg_replace("/[^a-z0-9_]/i", "", $_REQUEST['ENTITY_TYPE']) : false),
			'SOURCE_ENTITY_ID' => (isset($_REQUEST['ENTITY_ID']) ? intval($_REQUEST['ENTITY_ID']) : false),
			'LIVE' => 'Y'
		));
	}
	elseif ($action == "get_data")
	{
		if
		(
			intval($entity_id) > 0
			&& array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
			&& array_key_exists("CLASS_DESC_GET", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
			&& array_key_exists("METHOD_DESC_GET", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
		)
			$arEntityTmp = call_user_func(
				array(
					$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["CLASS_DESC_GET"],
					$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["METHOD_DESC_GET"]
				),
				$entity_id
			);
		else
			$arEntityTmp = array();

		if (intval($cb_id) > 0)
			$arCreatedByTmp = call_user_func(
				array(
					$arSocNetAllowedSubscribeEntityTypesDesc[SONET_SUBSCRIBE_ENTITY_USER]["CLASS_DESC_GET"],
					$arSocNetAllowedSubscribeEntityTypesDesc[SONET_SUBSCRIBE_ENTITY_USER]["METHOD_DESC_GET"]
				),
				$cb_id
			);
		else
			$arCreatedByTmp = array();

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

		$arSubscribe = array();

		$arFilter = array(
			"USER_ID" => $USER->GetID(),
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => $entity_id,
			"ENTITY_CB" => "N"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["EVENT"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"TRANSPORT_INHERITED" => false
				);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
				$arSubscribe["ALL"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"TRANSPORT_INHERITED" => false
				);
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" => $USER->getID(),
			"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
			"ENTITY_ID" => $cb_id,
			"ENTITY_CB" => "Y"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["CB_EVENT"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"TRANSPORT_INHERITED" => false
				);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["CB_ALL"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"TRANSPORT_INHERITED" => false
					);
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" => $USER->getId(),
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => 0
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($is_my && $arSubscribesTmp["ENTITY_MY"] == "Y")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT_MY"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"TRANSPORT_INHERITED" => false
					);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL_MY"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"TRANSPORT_INHERITED" => false
					);
				else
					continue;
			}
			elseif ($arSubscribesTmp["ENTITY_MY"] == "N")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"TRANSPORT_INHERITED" => false
					);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"TRANSPORT_INHERITED" => false
					);
				else
					continue;
			}
		}

		$value_default = "N";
		$strTmp = "TRANSPORT";

		if (
			!array_key_exists("EVENT", $arSubscribe)
			|| !array_key_exists($strTmp, $arSubscribe["EVENT"])
			|| $arSubscribe["EVENT"][$strTmp] == "I"
		)
		{
			if (
				array_key_exists("ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["ALL"])
				&& $arSubscribe["ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["EVENT"][$strTmp] = $arSubscribe["ALL"][$strTmp];
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				$is_my
				&& array_key_exists("COMMON_EVENT_MY", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
				&& $arSubscribe["COMMON_EVENT_MY"][$strTmp] != "I"
			)
			{
				$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT_MY"][$strTmp];
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				$is_my
				&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
				&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
			)
			{
				$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				array_key_exists("COMMON_EVENT", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
				&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
			)
			{
				$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				array_key_exists("COMMON_ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
			else
			{
				$arSubscribe["EVENT"][$strTmp] = $value_default;
				$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
			}
		}

		if (
			!array_key_exists("ALL", $arSubscribe)
			|| !array_key_exists($strTmp, $arSubscribe["ALL"])
			|| $arSubscribe["ALL"][$strTmp] == "I"
		)
		{
			if (
				$is_my
				&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
				&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
			)
			{
				$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
				$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				array_key_exists("COMMON_ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
				$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
			}
			else
			{
				$arSubscribe["ALL"][$strTmp] = $value_default;
				$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
			}
		}

		if (
			$is_my
			&&
			(
				!array_key_exists("COMMON_EVENT_MY", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
				|| $arSubscribe["COMMON_EVENT_MY"][$strTmp] == "I"
			)
		)
		{
			if (
				array_key_exists("COMMON_ALL_MY", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
				&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
			)
			{
				$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
				$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				array_key_exists("COMMON_EVENT", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
				&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
			)
			{
				$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
				$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
			}
			elseif (
				array_key_exists("COMMON_ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
				$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
			}
			else
			{
				$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $value_default;
				$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
			}
		}

		if (
			$is_my
			&&
			(
				!array_key_exists("COMMON_ALL_MY", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
				|| $arSubscribe["COMMON_ALL_MY"][$strTmp] == "I"
			)
		)
		{
			if (
				array_key_exists("COMMON_ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["COMMON_ALL_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
				$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
			}
			else
			{
				$arSubscribe["COMMON_ALL_MY"][$strTmp] = $value_default;
				$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
			}
		}

		if (
			!array_key_exists("COMMON_EVENT", $arSubscribe)
			|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
			|| $arSubscribe["COMMON_EVENT"][$strTmp] == "I"
		)
		{
			if (
				array_key_exists("COMMON_ALL", $arSubscribe)
				&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
			)
			{
				$arSubscribe["COMMON_EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
				$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
			}
			else
			{
				$arSubscribe["COMMON_EVENT"][$strTmp] = $value_default;
				$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
			}
		}

		if (
			!array_key_exists("COMMON_ALL", $arSubscribe)
			|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
			|| $arSubscribe["COMMON_ALL"][$strTmp] == "I"
		)
		{
			$arSubscribe["COMMON_ALL"][$strTmp] = $value_default;
			$arSubscribe["COMMON_ALL"][$strTmp."_INHERITED"] = true;
		}


		$fullset_event_id = CSocNetLogTools::FindFullSetEventIDByEventID($event_id);
		if ($fullset_event_id)
			$arEvent = CSocNetLogTools::FindLogEventByID($fullset_event_id, $entity_type);
		else
			$arEvent = CSocNetLogTools::FindLogEventByID($event_id, $entity_type);

		if (!$arEvent)
		{
			$arEvent = CSocNetLogTools::FindLogEventByCommentID($event_id);
			if ($arEvent)
			{
				$fullset_event_id = CSocNetLogTools::FindFullSetEventIDByEventID($arEvent["EVENT_ID"]);
				if ($fullset_event_id)
					$arEvent = CSocNetLogTools::FindLogEventByID($fullset_event_id, $entity_type);
			}
		}

		if ($arEvent)
		{
			$arSubscribe["EVENT"]["TITLE"] = $arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS"];

			if (
				array_key_exists("NAME_FORMATTED", $arEntityTmp)
				&& strlen($arEntityTmp["NAME_FORMATTED"]) > 0
			)
			{
				$arSubscribe["EVENT"]["TITLE_1"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
					$arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS_1"]
				);
				$arSubscribe["EVENT"]["TITLE_2"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
					$arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS_2"]
				);
			}
		}

		if (
			array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
			&& array_key_exists("TITLE_SETTINGS_ALL", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
			&& strlen($arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["TITLE_SETTINGS_ALL"]) > 0
		)
		{
			$arSubscribe["ALL"]["TITLE"] = $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["TITLE_SETTINGS_ALL"];
		}

		if (
			array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
			&& array_key_exists("TITLE_SETTINGS_ALL_1", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
			&& strlen($arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["TITLE_SETTINGS_ALL_1"]) > 0
			&& array_key_exists("NAME_FORMATTED", $arEntityTmp)
			&& strlen($arEntityTmp["NAME_FORMATTED"]) > 0
		)
		{
			$arSubscribe["ALL"]["TITLE_1"] = str_replace(
				array("#TITLE#"),
				array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
				$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["TITLE_SETTINGS_ALL_1"]
			);
			$arSubscribe["ALL"]["TITLE_2"] = str_replace(
				array("#TITLE#"),
				array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
				$arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["TITLE_SETTINGS_ALL_2"]
			);
		}

		if (CSocNetLogTools::HasLogEventCreatedBy($event_id))
		{
			$value_default = "N";
			$strTmp = "TRANSPORT";

			if (
				!array_key_exists("CB_EVENT", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["CB_EVENT"])
				|| $arSubscribe["CB_EVENT"][$strTmp] == "I"
			)
			{
				if (
					array_key_exists("CB_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["CB_ALL"])
					&& $arSubscribe["CB_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["CB_EVENT"][$strTmp] = $arSubscribe["CB_ALL"][$strTmp];
					$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["CB_EVENT"][$strTmp] = $value_default;
					$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				!array_key_exists("CB_ALL", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["CB_ALL"])
				|| $arSubscribe["CB_ALL"][$strTmp] == "I"
			)
			{
				$arSubscribe["CB_ALL"][$strTmp] = $value_default;
				$arSubscribe["CB_ALL"][$strTmp."_INHERITED"] = true;
			}

			$arSubscribe["CB_ALL"]["TITLE"]	= Loc::getMessage("SUBSCRIBE_CB_ALL", false, $lng);

			if (
				array_key_exists("NAME_FORMATTED", $arCreatedByTmp)
				&& strlen($arCreatedByTmp["NAME_FORMATTED"]) > 0
			)
			{
				$arSubscribe["CB_ALL"]["TITLE_1"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arCreatedByTmp) ? $arCreatedByTmp["~NAME_FORMATTED"] : $arCreatedByTmp["NAME_FORMATTED"]),
					Loc::getMessage("SUBSCRIBE_CB_ALL_1", false, $lng)
				);
				$arSubscribe["CB_ALL"]["TITLE_2"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arCreatedByTmp) ? $arCreatedByTmp["~NAME_FORMATTED"] : $arCreatedByTmp["NAME_FORMATTED"]),
					Loc::getMessage("SUBSCRIBE_CB_ALL_2", false, $lng)
				);
			}
		}
		else
		{
			if (array_key_exists("CB_EVENT", $arSubscribe))
				unset($arSubscribe["CB_EVENT"]);
			if (array_key_exists("CB_ALL", $arSubscribe))
				unset($arSubscribe["CB_ALL"]);
		}

		$arSubscribe["SITE_ID"] = (
			array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
			&& array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_SITE_ID"] == "Y"
			&& strlen($site_id) > 0
			?
				$site_id
			:
				false
		);

		$arResult["Subscription"] = $arSubscribe;

		$arResult["Transport"] = array(
			0 => array("Key" => "N", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_NONE", false, $lng)),
			1 => array("Key" => "M", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_MAIL", false, $lng)),
		);

		if (CBXFeatures::IsFeatureEnabled("WebMessenger"))
			$arResult["Transport"][] = array("Key" => "X", "Value" => Loc::getMessage("SUBSCRIBE_TRANSPORT_XMPP", false, $lng));
	}
	elseif ($action == "set")
	{
		$arFields = false;

		if (in_array($ls, array("EVENT", "ALL")))
		{
			$arFields = array(
				"USER_ID" => $USER->getId(),
				"ENTITY_TYPE" => $entity_type,
				"ENTITY_ID" => $entity_id,
				"ENTITY_CB" => "N"
			);

			if($ls == "EVENT")
				$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
			else
				$arEventID = array("all");

		}
		elseif (in_array($ls, array("CB_ALL")))
		{
			$arFields = array(
				"USER_ID" => $USER->getId(),
				"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" => $cb_id,
				"ENTITY_CB" => "Y"
			);

			$arEventID = array("all");
		}

		if ($arFields && strlen($transport) > 0)
		{
			if (
				$arFields["ENTITY_CB"] != "Y"
				&& array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
				&& array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
				&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_SITE_ID"] == "Y"
				&& strlen($site_id) > 0
			)
				$arFieldsVal["SITE_ID"] = $site_id;
			else
				$arFieldsVal["SITE_ID"] = false;

			if (strlen($transport) > 0)
				$arFieldsVal["TRANSPORT"] = $transport;

			foreach($arEventID as $event_id)
			{
				$arFields["EVENT_ID"] = $event_id;

				$dbResultTmp = CSocNetLogEvents::GetList(
					array(),
					$arFields,
					false,
					false,
					array("ID", "TRANSPORT")
				);

				$arFieldsSet = array_merge($arFields, $arFieldsVal);

				if ($arResultTmp = $dbResultTmp->Fetch())
				{
					if ($arFieldsVal["TRANSPORT"] == "I")
						CSocNetLogEvents::Delete($arResultTmp["ID"]);
					else
						$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
				}
				elseif($arFieldsVal["TRANSPORT"] != "I")
				{
					if (!array_key_exists("TRANSPORT", $arFieldsSet))
						$arFieldsSet["TRANSPORT"] = "I";

					$idTmp = CSocNetLogEvents::Add($arFieldsSet);
				}
			}
		}
	}
	elseif ($action == "set_transport_arr")
	{
		$arFields = false;

		if (is_array($ls_arr))
		{
			foreach($ls_arr as $ls => $transport)
			{
				$ls = trim($ls);

				if (in_array($ls, array("EVENT", "ALL")))
				{
					$arFields = array(
						"USER_ID" => $USER->getId(),
						"ENTITY_TYPE" => $entity_type,
						"ENTITY_ID" => $entity_id,
						"ENTITY_CB" => "N"
					);

					if($ls == "EVENT")
						$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
					else
						$arEventID = array("all");

				}
				elseif (in_array($ls, array("CB_ALL")))
				{
					$arFields = array(
						"USER_ID" => $USER->getId(),
						"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
						"ENTITY_ID" => $cb_id,
						"ENTITY_CB" => "Y"
					);

					$arEventID = array("all");
				}

				if ($arFields && strlen($transport) > 0)
				{
					if (
						$arFields["ENTITY_CB"] != "Y"
						&& array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
						&& array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
						&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_SITE_ID"] == "Y"
						&& strlen($site_id) > 0
					)
						$arFieldsVal["SITE_ID"] = $site_id;
					else
						$arFieldsVal["SITE_ID"] = false;

					if (strlen($transport) > 0)
						$arFieldsVal["TRANSPORT"] = $transport;

					foreach($arEventID as $event_id)
					{
						$arFields["EVENT_ID"] = $event_id;

						$dbResultTmp = CSocNetLogEvents::GetList(
							array(),
							$arFields,
							false,
							false,
							array("ID", "TRANSPORT")
						);

						$arFieldsSet = array_merge($arFields, $arFieldsVal);

						if ($arResultTmp = $dbResultTmp->Fetch())
						{
							if ($arFieldsVal["TRANSPORT"] == "I")
								CSocNetLogEvents::Delete($arResultTmp["ID"]);
							else
								$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
						}
						elseif($arFieldsVal["TRANSPORT"] != "I")
						{
							if (!array_key_exists("TRANSPORT", $arFieldsSet))
								$arFieldsSet["TRANSPORT"] = "I";

							$idTmp = CSocNetLogEvents::Add($arFieldsSet);
						}
					}
				}
			}
		}
	}
	elseif (
		$action == "change_follow"
		&& $USER->isAuthorized()
	)
	{
		$arResult["SUCCESS"] = (
			($strRes = CSocNetLogFollow::Set($USER->getId(), "L".intval($_REQUEST["log_id"]), ($_REQUEST["follow"] == "Y" ? "Y" : "N")))
				? "Y"
				: "N"
		);
	}

	if (empty($_REQUEST['mobile_action']))
	{
		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	}
	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>