<?
IncludeModuleLangFile(__FILE__);

$GLOBALS["SONET_FEATURES_CACHE"] = array();

use Bitrix\Socialnetwork\Integration;

class CAllSocNetFeatures
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION, $DB, $arSocNetAllowedEntityTypes;

		if ($ACTION != "ADD" && IntVal($ID) <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "ENTITY_TYPE") || $ACTION=="ADD") && StrLen($arFields["ENTITY_TYPE"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_TYPE"), "EMPTY_ENTITY_TYPE");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_TYPE"))
		{
			if (!in_array($arFields["ENTITY_TYPE"], $arSocNetAllowedEntityTypes))
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_NO_ENTITY_TYPE");
				return false;
			}
		}

		if ((is_set($arFields, "ENTITY_ID") || $ACTION=="ADD") && IntVal($arFields["ENTITY_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "EMPTY_ENTITY_ID");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_ID"))
		{
			$type = "";
			if (is_set($arFields, "ENTITY_TYPE"))
			{
				$type = $arFields["ENTITY_TYPE"];
			}
			elseif ($ACTION != "ADD")
			{
				$arRe = CSocNetFeatures::GetByID($ID);
				if ($arRe)
					$type = $arRe["ENTITY_TYPE"];
			}
			if (StrLen($type) <= 0)
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_CALC_ENTITY_TYPE"), "ERROR_CALC_ENTITY_TYPE");
				return false;
			}

			if ($type == SONET_ENTITY_GROUP)
			{
				$arResult = CSocNetGroup::GetByID($arFields["ENTITY_ID"]);
				if ($arResult == false)
				{
					$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
			elseif ($type == SONET_ENTITY_USER)
			{
				$dbResult = CUser::GetByID($arFields["ENTITY_ID"]);
				if (!$dbResult->Fetch())
				{
					$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_CALC_ENTITY_TYPE"), "ERROR_CALC_ENTITY_TYPE");
				return false;
			}
		}

		if ((is_set($arFields, "FEATURE") || $ACTION=="ADD") && StrLen($arFields["FEATURE"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_FEATURE_ID"), "EMPTY_FEATURE");
			return false;
		}
		elseif (is_set($arFields, "FEATURE"))
		{
			$arFields["FEATURE"] = strtolower($arFields["FEATURE"]);
			$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

			if (!array_key_exists($arFields["FEATURE"], $arSocNetFeaturesSettings))
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE");
				return false;
			}
		}

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_CREATE"), "EMPTY_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "DATE_UPDATE") && (!$DB->IsDate($arFields["DATE_UPDATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_UPDATE"), "EMPTY_DATE_UPDATE");
			return false;
		}

		if (
			(is_set($arFields, "ACTIVE") || $ACTION=="ADD")
			&& !in_array($arFields["ACTIVE"], array("Y", "N"))
		)
		{
			$arFields["ACTIVE"] = "Y";
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);
		$bSuccess = True;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeatures");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		$events = GetModuleEvents("socialnetwork", "OnSocNetFeatures");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		$DB->StartTransaction();

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_features2perms WHERE FEATURE_ID = ".$ID."", true);
		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_features WHERE ID = ".$ID."", true);

		if ($bSuccess)
		{
			$DB->Commit();
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_feature_".$ID);
			}
		}
		else
		{
			$DB->Rollback();
		}

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($userID))
			return false;

		$userID = IntVal($userID);

		$dbResult = CSocNetFeatures::GetList(array(), array("ENTITY_TYPE" => "U", "ENTITY_ID" => $userID), false, false, array("ID"));
		while ($arResult = $dbResult->Fetch())
		{
			$DB->Query("DELETE FROM b_sonet_features2perms WHERE FEATURE_ID = ".$arResult["ID"]."", true);
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_feature_".$arResult["ID"]);
			}
		}

		$DB->Query("DELETE FROM b_sonet_features WHERE ENTITY_TYPE = 'U' AND ENTITY_ID = ".$userID."", true);

		return true;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetFeatures::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeaturesUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_features", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_features SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (array_key_exists("ENTITY_TYPE", $arFields) && array_key_exists("ENTITY_ID", $arFields))
			{
				unset($GLOBALS["SONET_FEATURES_CACHE"][$arFields["ENTITY_TYPE"]][$arFields["ENTITY_ID"]]);
			}

			$events = GetModuleEvents("socialnetwork", "OnSocNetFeaturesUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_feature_".$ID);
			}
		}
		else
		{
			$ID = false;
		}

		return $ID;
	}

	public static function SetFeature($type, $id, $feature, $active, $featureName = false)
	{
		global $arSocNetAllowedEntityTypes, $APPLICATION, $DB, $CACHE_MANAGER;

		$type = Trim($type);
		if ((StrLen($type) <= 0) || !in_array($type, $arSocNetAllowedEntityTypes))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			return false;
		}

		$id = IntVal($id);
		if ($id <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "ERROR_EMPTY_ENTITY_ID");
			return false;
		}

		$feature = StrToLower(Trim($feature));
		if (StrLen($feature) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_FEATURE_ID"), "ERROR_EMPTY_FEATURE_ID");
			return false;
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
		if (
			!array_key_exists($feature, $arSocNetFeaturesSettings)
			|| !in_array($type, $arSocNetFeaturesSettings[$feature]["allowed"])
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE_ID");
			return false;
		}

		$active = ($active ? "Y" : "N");

		$dbResult = CSocNetFeatures::GetList(
			array(),
			array(
				"ENTITY_TYPE" => $type,
				"ENTITY_ID" => $id,
				"FEATURE" => $feature
			),
			false,
			false,
			array("ID", "ACTIVE")
		);

		if ($arResult = $dbResult->Fetch())
		{
			$r = CSocNetFeatures::Update(
				$arResult["ID"],
				array(
					"FEATURE_NAME" => $featureName,
					"ACTIVE" => $active,
					"=DATE_UPDATE" => $DB->CurrentTimeFunction()
				)
			);
			if ($r)
			{
				$CACHE_MANAGER->clearByTag("sonet_feature_all_".$type."_".$feature);
			}
		}
		else
		{
			$r = CSocNetFeatures::Add(array(
				"ENTITY_TYPE" => $type,
				"ENTITY_ID" => $id,
				"FEATURE" => $feature,
				"FEATURE_NAME" => $featureName,
				"ACTIVE" => $active,
				"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
				"=DATE_CREATE" => $DB->CurrentTimeFunction()
			));
		}

		if ($feature == 'chat')
		{
			$chatData = Integration\Im\Chat\Workgroup::getChatData(Array(
				'group_id' => $id,
				'skipAvailabilityCheck' => true
			));

			if (
				$active == 'Y'
				&& (
					empty($chatData[$id])
					|| intval($chatData[$id]) <= 0
				)
			)
			{
				Integration\Im\Chat\Workgroup::createChat(Array(
					'group_id' => $id
				));
			}
			elseif (
				$active == 'N'
				&& !empty($chatData[$id])
				&& intval($chatData[$id]) > 0
			)
			{
				Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::unlinkChat(array(
					'group_id' => $id
				));
			}
		}

		if (!$r)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();
			if (StrLen($errorMessage) <= 0)
				$errorMessage = GetMessage("SONET_GF_ERROR_SET").".";

			$APPLICATION->ThrowException($errorMessage, "ERROR_SET_RECORD");
			return false;
		}

		return $r;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$dbResult = CSocNetFeatures::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}

	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function IsActiveFeature($type, $id, $feature)
	{
		global $arSocNetAllowedEntityTypes, $APPLICATION;

		$type = Trim($type);
		if ((StrLen($type) <= 0) || !in_array($type, $arSocNetAllowedEntityTypes))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			return false;
		}

		$feature = StrToLower(Trim($feature));
		if (StrLen($feature) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_FEATURE_ID"), "ERROR_EMPTY_FEATURE_ID");
			return false;
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
		if (
			!array_key_exists($feature, $arSocNetFeaturesSettings)
			|| !in_array($type, $arSocNetFeaturesSettings[$feature]["allowed"])
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE_ID");
			return false;
		}

		$arFeatures = array();

		if (is_array($id))
		{
			$arGroupToGet = array();
			foreach($id as $group_id)
			{
				if ($group_id <= 0)
					$arReturn[$group_id] = false;
				else
				{
					if (array_key_exists("SONET_FEATURES_CACHE", $GLOBALS)
						&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type])
						&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type][$group_id])
						&& is_array($GLOBALS["SONET_FEATURES_CACHE"][$type][$group_id]))
					{
						$arFeatures[$group_id] = $GLOBALS["SONET_FEATURES_CACHE"][$type][$group_id];

						if (!array_key_exists($feature, $arFeatures[$group_id]))
						{
							$arReturn[$group_id] = true;
							continue;
						}

						$arReturn[$group_id] = ($arFeatures[$group_id][$feature]["ACTIVE"] == "Y");
					}
					else
					{
						$arGroupToGet[] = $group_id;
					}
				}
			}

			if(!empty($arGroupToGet))
			{
				$dbResult = CSocNetFeatures::GetList(Array(), Array("ENTITY_ID" => $arGroupToGet, "ENTITY_TYPE" => $type));
				while ($arResult = $dbResult->GetNext())
					$arFeatures[$arResult["ENTITY_ID"]][$arResult["FEATURE"]] = array("ACTIVE" => $arResult["ACTIVE"], "FEATURE_NAME" => $arResult["FEATURE_NAME"]);

				foreach($arGroupToGet as $group_id)
				{

					if (!array_key_exists("SONET_FEATURES_CACHE", $GLOBALS) || !is_array($GLOBALS["SONET_FEATURES_CACHE"]))
						$GLOBALS["SONET_FEATURES_CACHE"] = array();
					if (!array_key_exists($type, $GLOBALS["SONET_FEATURES_CACHE"]) || !is_array($GLOBALS["SONET_FEATURES_CACHE"][$type]))
						$GLOBALS["SONET_FEATURES_CACHE"][$type] = array();

					$GLOBALS["SONET_FEATURES_CACHE"][$type][$group_id] = $arFeatures[$group_id];

					if(!isset($arFeatures[$group_id]))
						$arFeatures[$group_id] = Array();
					if (!array_key_exists($feature, $arFeatures[$group_id]))
					{
						$arReturn[$group_id] = true;
						continue;
					}

					$arReturn[$group_id] = ($arFeatures[$group_id][$feature]["ACTIVE"] == "Y");
				}
			}

			return $arReturn;

		}
		else // not array
		{
			$id = IntVal($id);
			if ($id <= 0)
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "ERROR_EMPTY_ENTITY_ID");
				return false;
			}

			if (array_key_exists("SONET_FEATURES_CACHE", $GLOBALS)
				&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type])
				&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type][$id])
				&& is_array($GLOBALS["SONET_FEATURES_CACHE"][$type][$id]))
			{
				$arFeatures = $GLOBALS["SONET_FEATURES_CACHE"][$type][$id];
			}
			else
			{
				$dbResult = CSocNetFeatures::GetList(Array(), Array("ENTITY_ID" => $id, "ENTITY_TYPE" => $type));
				while ($arResult = $dbResult->GetNext())
					$arFeatures[$arResult["FEATURE"]] = array("ACTIVE" => $arResult["ACTIVE"], "FEATURE_NAME" => $arResult["FEATURE_NAME"]);

				if (!array_key_exists("SONET_FEATURES_CACHE", $GLOBALS) || !is_array($GLOBALS["SONET_FEATURES_CACHE"]))
					$GLOBALS["SONET_FEATURES_CACHE"] = array();
				if (!array_key_exists($type, $GLOBALS["SONET_FEATURES_CACHE"]) || !is_array($GLOBALS["SONET_FEATURES_CACHE"][$type]))
					$GLOBALS["SONET_FEATURES_CACHE"][$type] = array();

				$GLOBALS["SONET_FEATURES_CACHE"][$type][$id] = $arFeatures;
			}

			if (!array_key_exists($feature, $arFeatures))
				return true;

			return ($arFeatures[$feature]["ACTIVE"] == "Y");
		}
	}

	private static function getActiveFeaturesList($type, $id)
	{
		global $CACHE_MANAGER;

		$arFeatures = array();

		$cache = new CPHPCache;
		$cache_time = 31536000;
		$cache_id = $type."_".$id;
		$cache_path = "/sonet/features/".$type."/".intval($id / 1000)."/".$id."/";

		if ($cache->InitCache($cache_time, $cache_id, $cache_path))
		{
			$arCacheVars = $cache->GetVars();
			$arFeatures = $arCacheVars["FEATURES"];
		}
		else
		{
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($cache_path);
				$CACHE_MANAGER->RegisterTag("sonet_features_".$type."_".$id);
			}

			$dbResult = CSocNetFeatures::GetList(Array(), Array("ENTITY_ID" => $id, "ENTITY_TYPE" => $type));
			while ($arResult = $dbResult->GetNext())
			{
				$arFeatures[$arResult["FEATURE"]] = array("ACTIVE" => $arResult["ACTIVE"], "FEATURE_NAME" => $arResult["FEATURE_NAME"]);
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->RegisterTag("sonet_feature_".$arResult["ID"]);
				}
			}

			$arCacheData = Array(
				"FEATURES" => $arFeatures
			);
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->EndTagCache();
			}
			$cache->EndDataCache($arCacheData);
		}

		return $arFeatures;
	}

	public static function GetActiveFeatures($type, $id)
	{
		global $arSocNetAllowedEntityTypes, $APPLICATION, $CACHE_MANAGER;

		$type = Trim($type);
		if ((StrLen($type) <= 0) || !in_array($type, $arSocNetAllowedEntityTypes))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			return false;
		}

		$id = IntVal($id);
		if ($id <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "ERROR_EMPTY_ENTITY_ID");
			return false;
		}

		$arReturn = array();

		if (array_key_exists("SONET_FEATURES_CACHE", $GLOBALS)
			&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type])
			&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type][$id])
			&& is_array($GLOBALS["SONET_FEATURES_CACHE"][$type][$id]))
		{
			$arFeatures = $GLOBALS["SONET_FEATURES_CACHE"][$type][$id];
		}
		else
		{
			$arFeatures = self::getActiveFeaturesList($type, $id);

			if (!array_key_exists("SONET_FEATURES_CACHE", $GLOBALS) || !is_array($GLOBALS["SONET_FEATURES_CACHE"]))
			{
				$GLOBALS["SONET_FEATURES_CACHE"] = array();
			}
			if (!array_key_exists($type, $GLOBALS["SONET_FEATURES_CACHE"]) || !is_array($GLOBALS["SONET_FEATURES_CACHE"][$type]))
			{
				$GLOBALS["SONET_FEATURES_CACHE"][$type] = array();
			}

			$GLOBALS["SONET_FEATURES_CACHE"][$type][$id] = $arFeatures;
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();
		foreach ($arSocNetFeaturesSettings as $feature => $arr)
		{
			if (
				!array_key_exists("allowed", $arSocNetFeaturesSettings[$feature])
				|| !is_array($arSocNetFeaturesSettings[$feature]["allowed"])
				|| !in_array($type, $arSocNetFeaturesSettings[$feature]["allowed"])
			)
			{
				continue;
			}

			if (
				array_key_exists($feature, $arFeatures)
				&& ($arFeatures[$feature]["ACTIVE"] == "N")
			)
			{
				continue;
			}

			$arReturn[] = $feature;
		}

		return $arReturn;
	}

	public static function getActiveFeaturesNames($type, $id)
	{
		global $arSocNetAllowedEntityTypes, $APPLICATION;

		$type = Trim($type);
		if ((StrLen($type) <= 0) || !in_array($type, $arSocNetAllowedEntityTypes))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			return false;
		}

		$id = IntVal($id);
		if ($id <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "ERROR_EMPTY_ENTITY_ID");
			return false;
		}

		$arReturn = array();

		if (array_key_exists("SONET_FEATURES_CACHE", $GLOBALS)
			&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type])
			&& isset($GLOBALS["SONET_FEATURES_CACHE"][$type][$id])
			&& is_array($GLOBALS["SONET_FEATURES_CACHE"][$type][$id]))
		{
			$arFeatures = $GLOBALS["SONET_FEATURES_CACHE"][$type][$id];
		}
		else
		{
			$arFeatures = self::getActiveFeaturesList($type, $id);

			if (!array_key_exists("SONET_FEATURES_CACHE", $GLOBALS) || !is_array($GLOBALS["SONET_FEATURES_CACHE"]))
			{
				$GLOBALS["SONET_FEATURES_CACHE"] = array();
			}

			if (!array_key_exists($type, $GLOBALS["SONET_FEATURES_CACHE"]) || !is_array($GLOBALS["SONET_FEATURES_CACHE"][$type]))
			{
				$GLOBALS["SONET_FEATURES_CACHE"][$type] = array();
			}

			$GLOBALS["SONET_FEATURES_CACHE"][$type][$id] = $arFeatures;
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::getAllowedFeatures();
		foreach ($arSocNetFeaturesSettings as $feature => $arr)
		{
			if (
				!array_key_exists("allowed", $arSocNetFeaturesSettings[$feature]) 
				|| !in_array($type, $arSocNetFeaturesSettings[$feature]["allowed"])
			)
			{
				continue;
			}

			if (
				array_key_exists($feature, $arFeatures)
				&& ($arFeatures[$feature]["ACTIVE"] == "N")
			)
			{
				continue;
			}

			$arReturn[$feature] = $arFeatures[$feature]["FEATURE_NAME"];
		}

		return $arReturn;
	}
}
