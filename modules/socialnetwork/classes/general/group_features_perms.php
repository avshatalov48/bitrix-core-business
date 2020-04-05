<?
IncludeModuleLangFile(__FILE__);

$GLOBALS["arSonetFeaturesPermsCache"] = array();

class CAllSocNetFeaturesPerms
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION, $arSocNetAllowedRolesForFeaturesPerms, $arSocNetAllowedEntityTypes, $arSocNetAllowedRelationsType;

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		if ($ACTION != "ADD" && IntVal($ID) <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "FEATURE_ID") || $ACTION=="ADD") && IntVal($arFields["FEATURE_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GFP_EMPTY_GROUP_FEATURE_ID"), "EMPTY_FEATURE_ID");
			return false;
		}
		elseif (is_set($arFields, "FEATURE_ID"))
		{
			$arResult = CSocNetFeatures::GetByID($arFields["FEATURE_ID"]);
			if ($arResult == false)
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["FEATURE_ID"], GetMessage("SONET_GFP_ERROR_NO_GROUP_FEATURE_ID")), "ERROR_NO_FEATURE_ID");
				return false;
			}
		}

		$groupFeature = "";
		$groupFeatureType = "";

		if ((is_set($arFields, "OPERATION_ID") || $ACTION=="ADD") && StrLen($arFields["OPERATION_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GFP_EMPTY_OPERATION_ID"), "EMPTY_OPERATION_ID");
			return false;
		}
		elseif (is_set($arFields, "OPERATION_ID"))
		{
			$arFields["OPERATION_ID"] = strtolower($arFields["OPERATION_ID"]);

			if (is_set($arFields, "FEATURE_ID"))
			{
				$arGroupFeature = CSocNetFeatures::GetByID($arFields["FEATURE_ID"]);
				if ($arGroupFeature != false)
				{
					$groupFeature = $arGroupFeature["FEATURE"];
					$groupFeatureType = $arGroupFeature["ENTITY_TYPE"];
				}
			}
			elseif ($ACTION != "ADD" && IntVal($ID) > 0)
			{
				$dbGroupFeature = CSocNetFeaturesPerms::GetList(
					array(),
					array("ID" => $ID),
					false,
					false,
					array("FEATURE_FEATURE", "FEATURE_ENTITY_TYPE")
				);
				if ($arGroupFeature = $dbGroupFeature->Fetch())
				{
					$groupFeature = $arGroupFeature["FEATURE_FEATURE"];
					$groupFeatureType = $arGroupFeature["FEATURE_ENTITY_TYPE"];
				}
			}
			if (
				StrLen($groupFeature) <= 0 
				|| !array_key_exists($groupFeature, $arSocNetFeaturesSettings)
			)
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GFP_BAD_OPERATION_ID"), "BAD_OPERATION_ID");
				return false;
			}

			if (!array_key_exists($arFields["OPERATION_ID"], $arSocNetFeaturesSettings[$groupFeature]["operations"]))
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GFP_NO_OPERATION_ID"), "NO_OPERATION_ID");
				return false;
			}
		}

		if ((is_set($arFields, "ROLE") || $ACTION=="ADD") && strlen($arFields["ROLE"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GFP_EMPTY_ROLE"), "EMPTY_ROLE");
			return false;
		}
		elseif (is_set($arFields, "ROLE"))
		{
			if (StrLen($groupFeatureType) <= 0)
			{
				if (is_set($arFields, "FEATURE_ID"))
				{
					$arGroupFeature = CSocNetFeatures::GetByID($arFields["FEATURE_ID"]);
					if ($arGroupFeature != false)
					{
						$groupFeatureType = $arGroupFeature["ENTITY_TYPE"];
					}
				}
				elseif ($ACTION != "ADD" && IntVal($ID) > 0)
				{
					$dbGroupFeature = CSocNetFeaturesPerms::GetList(
						array(),
						array("ID" => $ID),
						false,
						false,
						array("FEATURE_FEATURE", "FEATURE_ENTITY_TYPE")
					);
					if ($arGroupFeature = $dbGroupFeature->Fetch())
					{
						$groupFeatureType = $arGroupFeature["FEATURE_ENTITY_TYPE"];
					}
				}
			}
			if (StrLen($groupFeatureType) <= 0 || !in_array($groupFeatureType, $arSocNetAllowedEntityTypes))
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_TYPE"), "BAD_TYPE");
				return false;
			}
			if ($groupFeatureType == SONET_ENTITY_GROUP)
			{
				if (!in_array($arFields["ROLE"], $arSocNetAllowedRolesForFeaturesPerms))
				{
					$APPLICATION->ThrowException(str_replace("#ID#", $arFields["ROLE"], GetMessage("SONET_GFP_ERROR_NO_ROLE")), "ERROR_NO_SITE");
					return false;
				}
			}
			else
			{
				if (!in_array($arFields["ROLE"], $arSocNetAllowedRelationsType))
				{
					$APPLICATION->ThrowException(str_replace("#ID#", $arFields["ROLE"], GetMessage("SONET_GFP_ERROR_NO_ROLE")), "ERROR_NO_SITE");
					return false;
				}
				elseif($arFields["ROLE"] == SONET_RELATIONS_TYPE_FRIENDS2)
				{
					$arFields["ROLE"] = SONET_RELATIONS_TYPE_FRIENDS;
				}
			}
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

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeaturesPermsDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		$events = GetModuleEvents("socialnetwork", "OnSocNetFeaturesPermsDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_features2perms WHERE ID = ".$ID."", true);
			if ($bSuccess)
			{
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->ClearByTag("sonet_features2perms_".$ID);
				}
				else
				{
					$dbGroupFeaturePerm = CSocNetFeaturesPerms::GetList(
						array(),
						array("ID" => $ID),
						false,
						false,
						array("FEATURE_ENTITY_TYPE", "FEATURE_ENTITY_ID")
					);
					if ($arGroupFeaturePerm = $dbGroupFeaturePerm->Fetch())
					{
						$cache = new CPHPCache;
						$cache->CleanDir("/sonet/features_perms/".$arGroupFeaturePerm["FEATURE_ENTITY_TYPE"]."/".intval($arGroupFeaturePerm["FEATURE_ENTITY_ID"] / 1000)."/".$arGroupFeaturePerm["FEATURE_ENTITY_ID"]."/");
					}
				}
			}
		}

		return $bSuccess;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetFeaturesPerms::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeaturesPermsUpdate");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields)) === false)
			{
				return false;
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_sonet_features2perms", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_features2perms SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$events = GetModuleEvents("socialnetwork", "OnSocNetFeaturesPermsUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_features2perms_".$ID);
			}
			else
			{
				$dbGroupFeaturePerm = CSocNetFeaturesPerms::GetList(
					array(),
					array("ID" => $ID),
					false,
					false,
					array("FEATURE_ENTITY_TYPE", "FEATURE_ENTITY_ID")
				);
				if ($arGroupFeaturePerm = $dbGroupFeaturePerm->Fetch())
				{
					$cache = new CPHPCache;
					$cache->CleanDir("/sonet/features_perms/".$arGroupFeaturePerm["FEATURE_ENTITY_TYPE"]."/".intval($arGroupFeaturePerm["FEATURE_ENTITY_ID"] / 1000)."/".$arGroupFeaturePerm["FEATURE_ENTITY_ID"]."/");
				}
			}
		}
		else
		{
			$ID = False;
		}

		return $ID;
	}

	public static function SetPerm($featureID, $operation, $perm)
	{
		global $APPLICATION;

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		$featureID = IntVal($featureID);
		$operation = Trim($operation);
		$perm = Trim($perm);

		$dbResult = CSocNetFeaturesPerms::GetList(
			array(),
			array(
				"FEATURE_ID" => $featureID,
				"OPERATION_ID" => $operation,
			),
			false,
			false,
			array("ID", "FEATURE_ENTITY_TYPE", "FEATURE_ENTITY_ID", "FEATURE_FEATURE", "OPERATION_ID", "ROLE")
		);

		if ($arResult = $dbResult->Fetch())
			$r = CSocNetFeaturesPerms::Update($arResult["ID"], array("ROLE" => $perm));
		else
			$r = CSocNetFeaturesPerms::Add(array("FEATURE_ID" => $featureID, "OPERATION_ID" => $operation, "ROLE" => $perm));

		if (!$r)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();
			if (StrLen($errorMessage) <= 0)
				$errorMessage = GetMessage("SONET_GF_ERROR_SET").".";

			$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_SET_RECORD");
			return false;
		}
		else
		{
			if (!$arResult)
			{
				$arFeature = CSocNetFeatures::GetByID($featureID);
				$entity_type = $arFeature["ENTITY_TYPE"];
				$entity_id = $arFeature["ENTITY_ID"];
				$feature = $arFeature["FEATURE"];
			}
			else
			{
				$entity_type = $arResult["FEATURE_ENTITY_TYPE"];
				$entity_id = $arResult["FEATURE_ENTITY_ID"];
				$feature = $arResult["FEATURE_FEATURE"];
			}

			if(empty($arResult) || $arResult["ROLE"] != $perm)
			{
				CSocNetSearch::SetFeaturePermissions(
					$entity_type,
					$entity_id,
					$feature,
					(
						$arResult
						&& $arResult["ROLE"] != $perm
							? $arResult["OPERATION_ID"]
							: $operation
					),
					$perm
				);
			}

			if (
				!in_array($feature, array("tasks", "files", "blog"))
				&& is_array($arSocNetFeaturesSettings[$feature]["subscribe_events"]))
			{
				$arEventsTmp = array_keys($arSocNetFeaturesSettings[$feature]["subscribe_events"]);
				$rsLog = CSocNetLog::GetList(
					array(), 
					array(
						"ENTITY_TYPE" => $entity_type,
						"ENTITY_ID" => $entity_id,
						"EVENT_ID" => $arEventsTmp
					), 
					false, 
					false, 
					array("ID", "EVENT_ID")
				);
				while($arLog = $rsLog->Fetch())
				{
					CSocNetLogRights::DeleteByLogID($arLog["ID"]);
					CSocNetLogRights::SetForSonet(
						$arLog["ID"], 
						$entity_type, 
						$entity_id, 
						$feature, 
						$arSocNetFeaturesSettings[$feature]["subscribe_events"][$arLog["EVENT_ID"]]["OPERATION"]
					);
				}
			}
		}

		return $r;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$dbResult = CSocNetFeaturesPerms::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}

	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function CurrentUserCanPerformOperation($type, $id, $feature, $operation, $site_id = SITE_ID)
	{
		$userID = 0;
		if (is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAuthorized())
			$userID = IntVal($GLOBALS["USER"]->GetID());

		$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin($site_id);

		return CSocNetFeaturesPerms::CanPerformOperation($userID, $type, $id, $feature, $operation, $bCurrentUserIsAdmin);
	}

	public static function CanPerformOperation($userID, $type, $id, $feature, $operation, $bCurrentUserIsAdmin = false)
	{
		global $arSocNetAllowedEntityTypes;

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		$userID = IntVal($userID);

		if ((is_array($id) && count($id) <= 0) || (!is_array($id) && $id <= 0))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GF_EMPTY_ENTITY_ID"), "ERROR_EMPTY_ENTITY_ID");
			return false;
		}

		$type = Trim($type);
		if ((StrLen($type) <= 0) || !in_array($type, $arSocNetAllowedEntityTypes))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			return false;
		}

		$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm($type, $id, $feature, $operation);

		if ($type == SONET_ENTITY_GROUP)
		{
			$bWorkWithClosedGroups = (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") == "Y");
			if (is_array($id))
			{
				$arGroupToGet = array();
				foreach($id as $group_id)
				{
					if ($featureOperationPerms[$group_id] == false)
					{
						$arReturn[$group_id] = false;
					}
					else
					{
						$arGroupToGet[] = $group_id;
					}
				}

				$arGroupToGet = array_unique($arGroupToGet);

				$userRoleInGroup = CSocNetUserToGroup::GetUserRole($userID, $arGroupToGet);
				$arGroupToGet = array();
				if (is_array($userRoleInGroup))
				{
					foreach($userRoleInGroup as $group_id => $role)
					{
						if ($userRoleInGroup[$group_id] == SONET_ROLES_BAN)
						{
							$arReturn[$group_id] = false;
						}
						else
						{
							$arGroupToGet[] = $group_id;
						}
					}
				}

				$arGroupToGet = array_unique($arGroupToGet);

				if (
					(is_array($arGroupToGet) && count($arGroupToGet) <= 0)
					|| (!is_array($arGroupToGet) && intval($arGroupToGet) <= 0)
				)
				{
					$arReturn = array();
					foreach($id as $group_id)
					{
						$arReturn[$group_id] = false;
					}
					return $arReturn;
				}

				$resGroupTmp = CSocNetGroup::GetList(
					array("ID" => "ASC"),
					array("@ID" => $arGroupToGet),
					false,
					false,
					array('ID', 'VISIBLE', 'CLOSED')
				);
				while ($arGroupTmp = $resGroupTmp->Fetch())
				{
					if (
						$arGroupTmp["CLOSED"] == "Y" 
						&& !in_array($operation, $arSocNetFeaturesSettings[$feature]["minoperation"])
					)
					{
						if (!$bWorkWithClosedGroups)
						{
							$arReturn[$arGroupTmp["ID"]] = false;
							continue;
						}
						else
						{
							$featureOperationPerms[$arGroupTmp["ID"]] = SONET_ROLES_OWNER;
						}
					}

					if ($bCurrentUserIsAdmin)
					{
						$arReturn[$arGroupTmp["ID"]] = true;
						continue;
					}

					if ($featureOperationPerms[$arGroupTmp["ID"]] == SONET_ROLES_ALL)
					{
						if ($arGroupTmp["VISIBLE"] == "N")
						{
							$featureOperationPerms[$arGroupTmp["ID"]] = SONET_ROLES_USER;
						}
						else
						{
							$arReturn[$arGroupTmp["ID"]] = true;
							continue;
						}
					}

					if ($featureOperationPerms[$arGroupTmp["ID"]] == SONET_ROLES_AUTHORIZED)
					{
						if ($userID > 0)
						{
							$arReturn[$arGroupTmp["ID"]] = true;
							continue;
						}
						else
						{
							$arReturn[$arGroupTmp["ID"]] = false;
							continue;
						}
					}

					if ($userRoleInGroup[$arGroupTmp["ID"]] == false)
					{
						$arReturn[$arGroupTmp["ID"]] = false;
						continue;
					}

					if ($featureOperationPerms[$arGroupTmp["ID"]] == SONET_ROLES_MODERATOR)
					{
						if ($userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_MODERATOR || $userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_OWNER)
						{
							$arReturn[$arGroupTmp["ID"]] = true;
							continue;
						}
						else
						{
							$arReturn[$arGroupTmp["ID"]] = false;
							continue;
						}
					}
					elseif ($featureOperationPerms[$arGroupTmp["ID"]] == SONET_ROLES_USER)
					{
						if ($userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_MODERATOR || $userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_OWNER || $userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_USER)
						{
							$arReturn[$arGroupTmp["ID"]] = true;
							continue;
						}
						else
						{
							$arReturn[$arGroupTmp["ID"]] = false;
							continue;
						}
					}
					elseif ($featureOperationPerms[$arGroupTmp["ID"]] == SONET_ROLES_OWNER)
					{
						if ($userRoleInGroup[$arGroupTmp["ID"]] == SONET_ROLES_OWNER)
						{
							$arReturn[$arGroupTmp["ID"]] = true;
							continue;
						}
						else
						{
							$arReturn[$arGroupTmp["ID"]] = false;
							continue;
						}
					}
				}

				return $arReturn;

			}
			else // not array of groups
			{
				$id = IntVal($id);

				if ($featureOperationPerms == false)
				{
					return false;
				}

				$userRoleInGroup = CSocNetUserToGroup::GetUserRole($userID, $id);
				if ($userRoleInGroup == SONET_ROLES_BAN)
				{
					return false;
				}

				$arGroupTmp = CSocNetGroup::GetByID($id);

				if (
					$arGroupTmp["CLOSED"] == "Y" 
					&& !in_array($operation, $arSocNetFeaturesSettings[$feature]["minoperation"])
				)
				{
					if (!$bWorkWithClosedGroups)
					{
						return false;
					}
					else
					{
						$featureOperationPerms = SONET_ROLES_OWNER;
					}
				}

				if ($bCurrentUserIsAdmin)
				{
					return true;
				}

				if ($featureOperationPerms == SONET_ROLES_ALL)
				{
					if ($arGroupTmp["VISIBLE"] == "N")
					{
						$featureOperationPerms = SONET_ROLES_USER;
					}
					else
					{
						return true;
					}
				}

				if ($featureOperationPerms == SONET_ROLES_AUTHORIZED)
				{
					return ($userID > 0);
				}

				if ($userRoleInGroup == false)
				{
					return false;
				}

				if ($featureOperationPerms == SONET_ROLES_MODERATOR)
				{
					return (in_array($userRoleInGroup, array(SONET_ROLES_MODERATOR, SONET_ROLES_OWNER)));
				}
				elseif ($featureOperationPerms == SONET_ROLES_USER)
				{
					return (in_array($userRoleInGroup, array(SONET_ROLES_MODERATOR, SONET_ROLES_OWNER, SONET_ROLES_USER)));
				}
				elseif ($featureOperationPerms == SONET_ROLES_OWNER)
				{
					return ($userRoleInGroup == SONET_ROLES_OWNER);
				}
			}
		}
		else // user
		{
			if (is_array($id))
			{

				foreach($id as $entity_id)
				{

					if ($featureOperationPerms[$entity_id] == false)
					{
						$arReturn[$entity_id] = false;
						continue;
					}

					$usersRelation = CSocNetUserRelations::GetRelation($userID, $entity_id);

					if ($type == SONET_ENTITY_USER && $userID == $entity_id)
					{
						$arReturn[$entity_id] = true;
						continue;
					}

					if ($bCurrentUserIsAdmin)
					{
						$arReturn[$entity_id] = true;
						continue;
					}

					if ($userID == $entity_id)
					{
						$arReturn[$entity_id] = true;
						continue;
					}

					if ($usersRelation == SONET_RELATIONS_BAN)
					{
						if (!IsModuleInstalled("im"))
						{
							$arReturn[$entity_id] = false;
							continue;
						}
					}

					if ($featureOperationPerms[$entity_id] == SONET_RELATIONS_TYPE_NONE)
					{
						$arReturn[$entity_id] = false;
						continue;
					}

					if ($featureOperationPerms[$entity_id] == SONET_RELATIONS_TYPE_ALL)
					{
						$arReturn[$entity_id] = true;
						continue;
					}

					if ($featureOperationPerms[$entity_id] == SONET_RELATIONS_TYPE_AUTHORIZED)
					{
						$arReturn[$entity_id] = ($userID > 0);
						continue;
					}

					if (
						$featureOperationPerms[$entity_id] == SONET_RELATIONS_TYPE_FRIENDS
						|| $featureOperationPerms[$entity_id] == SONET_RELATIONS_TYPE_FRIENDS2
					)
					{
						$arReturn[$entity_id] = CSocNetUserRelations::IsFriends($userID, $entity_id);
						continue;
					}
				}

				return $arReturn;
			}
			else // not array
			{

				if ($featureOperationPerms == false)
					return false;

				if ($type == SONET_ENTITY_USER && $userID == $id)
					return true;

				if ($bCurrentUserIsAdmin)
					return true;

				if ($userID == $id)
					return true;

				$usersRelation = CSocNetUserRelations::GetRelation($userID, $id);
				if ($usersRelation == SONET_RELATIONS_BAN && !IsModuleInstalled("im"))
					return false;

				if ($featureOperationPerms == SONET_RELATIONS_TYPE_NONE)
					return false;

				if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
					return true;

				if ($featureOperationPerms == SONET_RELATIONS_TYPE_AUTHORIZED)
				{
					return ($userID > 0);
				}

				if (
					$featureOperationPerms == SONET_RELATIONS_TYPE_FRIENDS
					|| $featureOperationPerms == SONET_RELATIONS_TYPE_FRIENDS2
				)
				{
					return CSocNetUserRelations::IsFriends($userID, $id);
				}
			}

		}

		return false;
	}

	public static function GetOperationPerm($type, $id, $feature, $operation)
	{
		global $arSocNetAllowedEntityTypes, $APPLICATION, $CACHE_MANAGER;

		static $arSonetGroupCache = array();

		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		$type = Trim($type);
		if (
			(strlen($type) <= 0)
			|| !in_array($type, $arSocNetAllowedEntityTypes)
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_ENTITY_TYPE"), "ERROR_EMPTY_TYPE");
			if (is_array($id))
			{
				$arReturn = array();
				foreach($id as $TmpGroupID)
				{
					$arReturn[$TmpGroupID] = false;
				}

				return $arReturn;
			}
			else
			{
				return false;
			}
		}

		$feature = strtolower(trim($feature));
		if (strlen($feature) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_EMPTY_FEATURE_ID"), "ERROR_EMPTY_FEATURE_ID");
			if (is_array($id))
			{
				$arReturn = array();
				foreach($id as $TmpGroupID)
				{
					$arReturn[$TmpGroupID] = false;
				}

				return $arReturn;
			}
			else
			{
				return false;
			}
		}

		if (
			!array_key_exists($feature, $arSocNetFeaturesSettings) 
			|| !array_key_exists("allowed", $arSocNetFeaturesSettings[$feature])
			|| !in_array($type, $arSocNetFeaturesSettings[$feature]["allowed"])
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GF_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE_ID");
			if (is_array($id))
			{
				$arReturn = array();
				foreach($id as $TmpGroupID)
				{
					$arReturn[$TmpGroupID] = false;
				}

				return $arReturn;
			}
			else
			{
				return false;
			}
		}

		$operation = StrToLower(Trim($operation));
		if (
			!array_key_exists("operations", $arSocNetFeaturesSettings[$feature])
			|| !array_key_exists($operation, $arSocNetFeaturesSettings[$feature]["operations"])
		)
		{
			if (is_array($id))
			{
				$arReturn = array();
				foreach($id as $TmpGroupID)
				{
					$arReturn[$TmpGroupID] = false;
				}

				return $arReturn;
			}
			else
			{
				return false;
			}
		}

		global $arSonetFeaturesPermsCache;
		if (!isset($arSonetFeaturesPermsCache) || !is_array($arSonetFeaturesPermsCache))
		{
			$arSonetFeaturesPermsCache = array();
		}

		if (is_array($id))
		{
			$arFeaturesPerms = array();
			$arGroupToGet = array();
			foreach($id as $TmpGroupID)
			{
				$arFeaturesPerms[$TmpGroupID] = array();

				if (!array_key_exists($type."_".$TmpGroupID, $arSonetFeaturesPermsCache))
				{
					$arGroupToGet[] = $TmpGroupID;
				}
				else
				{
					$arFeaturesPerms[$TmpGroupID] = $arSonetFeaturesPermsCache[$type."_".$TmpGroupID];
				}
			}

			$arGroupToGet = array_unique($arGroupToGet);

			if (!empty($arGroupToGet))
			{
				$rsSonetGroup = CSocNetGroup::GetList(
					array(),
					array('@ID' => $arGroupToGet),
					false,
					false,
					array('ID', 'VISIBLE', 'OPENED')
				);
				while ($arSonetGroup = $rsSonetGroup->Fetch())
				{
					if (!isset($arSonetGroupCache[$arSonetGroup['ID']]))
					{
						$arSonetGroupCache[$arSonetGroup['ID']] = array(
							'VISIBLE' => $arSonetGroup['VISIBLE'],
							'OPENED' => $arSonetGroup['OPENED']
						);
					}
				}

				$dbResult = CSocNetFeaturesPerms::GetList(
					Array(),
					Array(
						"@FEATURE_ENTITY_ID" => $arGroupToGet,
						"FEATURE_ENTITY_TYPE" => $type,
						"FEATURE_ACTIVE" => "Y"
					),
					false,
					false,
					array("OPERATION_ID", "FEATURE_ENTITY_ID", "FEATURE_FEATURE", "ROLE")
				);
				while ($arResult = $dbResult->Fetch())
				{
					if (
						!array_key_exists($arResult["FEATURE_ENTITY_ID"], $arFeaturesPerms)
						|| !array_key_exists($arResult["FEATURE_FEATURE"], $arFeaturesPerms[$arResult["FEATURE_ENTITY_ID"]])
					)
					{
						$arFeaturesPerms[$arResult["FEATURE_ENTITY_ID"]][$arResult["FEATURE_FEATURE"]] = array();
					}
					$arFeaturesPerms[$arResult["FEATURE_ENTITY_ID"]][$arResult["FEATURE_FEATURE"]][$arResult["OPERATION_ID"]] = $arResult["ROLE"];
				}
			}

			$arReturn = array();

			foreach($id as $TmpEntityID)
			{
				$arSonetFeaturesPermsCache[$type."_".$TmpEntityID] = $arFeaturesPerms[$TmpEntityID];

				if ($type == SONET_ENTITY_GROUP)
				{
					if (
						!array_key_exists($feature, $arFeaturesPerms[$TmpEntityID])
						|| !array_key_exists($operation, $arFeaturesPerms[$TmpEntityID][$feature])
					)
					{
						$perm = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_GROUP];

						if (
							isset($arSonetGroupCache[$TmpEntityID])
							&& $arSonetGroupCache[$TmpEntityID]['OPENED'] == 'Y'
							&& $arSonetGroupCache[$TmpEntityID]['VISIBLE'] == 'Y'
//							&& in_array($feature, array("blog", "tasks", "photo"))
							&& in_array($feature, array("blog"))
							&& ($perm == SONET_ROLES_USER)
							&& !empty($arSocNetFeaturesSettings[$feature]["minoperation"])
							&& (
								(
									is_array($arSocNetFeaturesSettings[$feature]["minoperation"])
									&& in_array($operation, $arSocNetFeaturesSettings[$feature]["minoperation"])
								)
								|| (
									!is_array($arSocNetFeaturesSettings[$feature]["minoperation"])
									&& $operation == $arSocNetFeaturesSettings[$feature]["minoperation"]
								)
							)
						)
						{
							$featureOperationPerms = SONET_ROLES_AUTHORIZED;
						}
						else
						{
							$featureOperationPerms = $perm;
						}
					}
					else
					{
						$featureOperationPerms = $arFeaturesPerms[$TmpEntityID][$feature][$operation];
					}
				}
				else
				{
					if (!array_key_exists($feature, $arFeaturesPerms[$TmpEntityID]))
					{
						$featureOperationPerms = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_USER];
					}
					elseif (!array_key_exists($operation, $arFeaturesPerms[$TmpEntityID][$feature]))
					{
						$featureOperationPerms = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_USER];
					}
					else
					{
						$featureOperationPerms = $arFeaturesPerms[$TmpEntityID][$feature][$operation];
					}

					if ($featureOperationPerms == SONET_RELATIONS_TYPE_FRIENDS2)
					{
						$featureOperationPerms = SONET_RELATIONS_TYPE_FRIENDS;
					}
				}

				$arReturn[$TmpEntityID] = $featureOperationPerms;
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

			$arFeaturesPerms = array();
			if (array_key_exists($type."_".$id, $arSonetFeaturesPermsCache))
			{
				$arFeaturesPerms = $arSonetFeaturesPermsCache[$type."_".$id];
			}
			else
			{
				$cache = new CPHPCache;
				$cache_time = 31536000;
				$cache_id = "entity_"."_".$type."_".$id;
				$cache_path = "/sonet/features_perms/".$type."/".intval($id / 1000)."/".$id."/";

				$arTmp = array();

				if ($cache->InitCache($cache_time, $cache_id, $cache_path))
				{
					$arCacheVars = $cache->GetVars();
					$arTmp = $arCacheVars["RESULT"];
				}
				else
				{
					$cache->StartDataCache($cache_time, $cache_id, $cache_path);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->StartTagCache($cache_path);
					}

					$dbResult = CSocNetFeaturesPerms::GetList(
						Array(),
						Array(
							"FEATURE_ENTITY_ID" => $id,
							"FEATURE_ENTITY_TYPE" => $type,
							"FEATURE_ACTIVE" => "Y"
						),
						false,
						false,
						array("ID", "OPERATION_ID", "FEATURE_ID", "FEATURE_FEATURE", "ROLE")
					);
					while ($arResult = $dbResult->Fetch())
					{
						if (defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->RegisterTag("sonet_features2perms_".$arResult["ID"]);
						}
						$arTmp[] = $arResult;
					}

					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$dbResult = CSocNetFeatures::GetList(
							Array(),
							Array("ENTITY_ID" => $id, "ENTITY_TYPE" => $type),
							false,
							false,
							array("ID")
						);
						while ($arResult = $dbResult->Fetch())
						{
							$CACHE_MANAGER->RegisterTag("sonet_feature_".$arResult["ID"]);
						}
					}

					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						if ($type == SONET_ENTITY_GROUP)
						{
							$CACHE_MANAGER->RegisterTag("sonet_group_".$id);
							$CACHE_MANAGER->RegisterTag("sonet_group");
						}
						elseif ($type == SONET_ENTITY_USER)
						{
							$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($id / TAGGED_user_card_size));
						}

						$CACHE_MANAGER->RegisterTag("sonet_features_".$type."_".$id);
					}

					$arCacheData = Array(
						"RESULT" => $arTmp
					);

					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->EndTagCache();
					}

					$cache->EndDataCache($arCacheData);
				}

				foreach($arTmp as $arResult)
				{
					if (!array_key_exists($arResult["FEATURE_FEATURE"], $arFeaturesPerms))
					{
						$arFeaturesPerms[$arResult["FEATURE_FEATURE"]] = array();
					}
					$arFeaturesPerms[$arResult["FEATURE_FEATURE"]][$arResult["OPERATION_ID"]] = $arResult["ROLE"];
				}
				$arSonetFeaturesPermsCache[$type."_".$id] = $arFeaturesPerms;
			}

			if ($type == SONET_ENTITY_GROUP)
			{
				if (
					!array_key_exists($feature, $arFeaturesPerms)
					|| !array_key_exists($operation, $arFeaturesPerms[$feature])
				)
				{
					if (
						!isset($arSonetGroupCache[$id])
						&& ($arSonetGroup = CSocNetGroup::GetByID($id))
					)
					{
						$arSonetGroupCache[$id] = array(
							'OPENED' => $arSonetGroup['OPENED'],
							'VISIBLE' => $arSonetGroup['VISIBLE']
						);
					}

					$perm = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_GROUP];

					if (
						isset($arSonetGroupCache[$id])
						&& $arSonetGroupCache[$id]['OPENED'] == 'Y'
						&& $arSonetGroupCache[$id]['VISIBLE'] == 'Y'
//						&& in_array($feature, array("blog", "tasks", "photo"))
						&& in_array($feature, array("blog"))
						&& ($perm == SONET_ROLES_USER)
						&& !empty($arSocNetFeaturesSettings[$feature]["minoperation"])
						&& (
							(
								is_array($arSocNetFeaturesSettings[$feature]["minoperation"])
								&& in_array($operation, $arSocNetFeaturesSettings[$feature]["minoperation"])
							)
							|| (
								!is_array($arSocNetFeaturesSettings[$feature]["minoperation"])
								&& $operation == $arSocNetFeaturesSettings[$feature]["minoperation"]
							)
						)
					)
					{
						$featureOperationPerms = SONET_ROLES_AUTHORIZED;
					}
					else
					{
						$featureOperationPerms = $perm;
					}
				}
				else
				{
					$featureOperationPerms = $arFeaturesPerms[$feature][$operation];
				}
			}
			else
			{
				if (!array_key_exists($feature, $arFeaturesPerms))
				{
					$featureOperationPerms = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_USER];
				}
				elseif (!array_key_exists($operation, $arFeaturesPerms[$feature]))
				{
					$featureOperationPerms = $arSocNetFeaturesSettings[$feature]["operations"][$operation][SONET_ENTITY_USER];
				}
				else
				{
					$featureOperationPerms = $arFeaturesPerms[$feature][$operation];
				}

				if ($featureOperationPerms == SONET_RELATIONS_TYPE_FRIENDS2)
				{
					$featureOperationPerms = SONET_RELATIONS_TYPE_FRIENDS;
				}
			}

			return $featureOperationPerms;
		}
	}
}
