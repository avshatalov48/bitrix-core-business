<?
IncludeModuleLangFile(__FILE__);

class CAllSocNetUserPerms
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $arSocNetUserOperations, $arSocNetAllowedRelationsType;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GB_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GB_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "OPERATION_ID") || $ACTION=="ADD") && $arFields["OPERATION_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GG_EMPTY_OPERATION_ID"), "EMPTY_OPERATION_ID");
			return false;
		}
		elseif (is_set($arFields, "OPERATION_ID") && !array_key_exists($arFields["OPERATION_ID"], $arSocNetUserOperations))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["OPERATION_ID"], GetMessage("SONET_GG_ERROR_NO_OPERATION_ID")), "ERROR_NO_OPERATION_ID");
			return false;
		}

		if ((is_set($arFields, "RELATION_TYPE") || $ACTION=="ADD") && $arFields["RELATION_TYPE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GG_EMPTY_RELATION_TYPE"), "EMPTY_RELATION_TYPE");
			return false;
		}
		elseif (is_set($arFields, "RELATION_TYPE") && !in_array($arFields["RELATION_TYPE"], $arSocNetAllowedRelationsType))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["RELATION_TYPE"], GetMessage("SONET_GG_ERROR_NO_RELATION_TYPE")), "ERROR_NO_RELATION_TYPE");
			return false;
		}
		elseif (
			is_set($arFields, "RELATION_TYPE")
			&& $arFields["RELATION_TYPE"] == SONET_RELATIONS_TYPE_FRIENDS2
		)
		{
			$arFields["RELATION_TYPE"] = SONET_RELATIONS_TYPE_FRIENDS;
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);
		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_perms WHERE ID = ".$ID."", true);

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($userID))
			return false;

		$userID = intval($userID);
		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_perms WHERE USER_ID = ".$userID."", true);

		return $bSuccess;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserPerms::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_user_perms", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_user_perms SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else
		{
			$ID = False;
		}

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$dbResult = CSocNetUserPerms::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}
	
	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function GetOperationPerms($userID, $operation)
	{
		global $arSocNetUserOperations;
		static $arCachedUserPerms;

		if (
			is_array($userID) 
			&& !$arCachedUserPerms
		)
			$arCachedUserPerms = array();

		if (!is_array($userID))
		{
			$userID = intval($userID);
			if ($userID <= 0)
				return false;
		}

		$operation = mb_strtolower(Trim($operation));
		if (!array_key_exists($operation, $arSocNetUserOperations))
			return false;

		$arUserPerms = array();
		if (
			!is_array($userID)
			&& isset($GLOBALS["SONET_USER_PERMS_".$userID]) 
			&& is_array($GLOBALS["SONET_USER_PERMS_".$userID])
			&& !array_key_exists("SONET_USER_PERMS_".$userID, $_REQUEST)
		)
			$arUserPerms = $GLOBALS["SONET_USER_PERMS_".$userID];
		elseif (
			!is_array($userID)
			&& isset($arCachedUserPerms[$userID])
			&& is_array($arCachedUserPerms[$userID])
			&& !array_key_exists("SONET_USER_PERMS_".$userID, $_REQUEST)
		)
			$arUserPerms = $arCachedUserPerms[$userID];			
		else
		{
			$dbResult = CSocNetUserPerms::GetList(Array(), Array("USER_ID" => $userID));
			while ($arResult = $dbResult->Fetch())
			{
				if (!is_array($userID))
					$arUserPerms[$arResult["OPERATION_ID"]] = $arResult["RELATION_TYPE"];
				else
					$arCachedUserPerms[$arResult["USER_ID"]][$arResult["OPERATION_ID"]] = $arResult["RELATION_TYPE"];
			}
			if (!is_array($userID))
				$GLOBALS["SONET_USER_PERMS_".$userID] = $arUserPerms;
		}

		if (!is_array($userID))
		{
			$toUserOperationPerms = (
				array_key_exists($operation, $arUserPerms)
					? $arUserPerms[$operation]
					: $arSocNetUserOperations[$operation]
			);

			if ($toUserOperationPerms == SONET_RELATIONS_TYPE_FRIENDS2)
			{
				$toUserOperationPerms = SONET_RELATIONS_TYPE_FRIENDS;
			}

			return $toUserOperationPerms;
		}
		else
		{
			foreach ($userID as $user_id_tmp)
				if (!array_key_exists($user_id_tmp, $arCachedUserPerms))
					$arCachedUserPerms[$user_id_tmp] = array();

			return true;
		}
	}

	public static function CanPerformOperation($fromUserID, $toUserID, $operation, $bCurrentUserIsAdmin = false)
	{
		global $arSocNetUserOperations;

		$fromUserID = intval($fromUserID);
		$toUserID = intval($toUserID);
		if ($toUserID <= 0)
			return false;
		$operation = mb_strtolower(Trim($operation));
		if (!array_key_exists($operation, $arSocNetUserOperations))
			return false;

// use no profile private permission restrictions at the extranet site
		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
			return true;

		if ($bCurrentUserIsAdmin)
			return true;
		if ($fromUserID == $toUserID)
			return true;

		$usersRelation = CSocNetUserRelations::GetRelation($fromUserID, $toUserID);

		if ($usersRelation == SONET_RELATIONS_BAN && !IsModuleInstalled("im"))
			return false;

		$toUserOperationPerms = CSocNetUserPerms::GetOperationPerms($toUserID, $operation);

		if ($toUserOperationPerms == SONET_RELATIONS_TYPE_NONE)
			return false;
		if ($toUserOperationPerms == SONET_RELATIONS_TYPE_ALL)
			return true;

		if ($toUserOperationPerms == SONET_RELATIONS_TYPE_AUTHORIZED)
		{
			return ($fromUserID > 0);
		}

		if (
			$toUserOperationPerms == SONET_RELATIONS_TYPE_FRIENDS
			|| $toUserOperationPerms == SONET_RELATIONS_TYPE_FRIENDS2
		)
		{
			return CSocNetUserRelations::IsFriends($fromUserID, $toUserID);
		}

		return false;
	}

	public static function InitUserPerms($currentUserID, $userID, $bCurrentUserIsAdmin)
	{
		global $arSocNetUserOperations, $USER;

		$arReturn = array();

		$currentUserID = intval($currentUserID);
		$userID = intval($userID);

		if ($userID <= 0)
		{
			return false;
		}

		$arReturn["Operations"] = array();
		if ($currentUserID <= 0)
		{
			$arReturn["IsCurrentUser"] = false;
			$arReturn["Relation"] = false;
			$arReturn["Operations"]["modifyuser"] = false;
			$arReturn["Operations"]["viewcontacts"] = false;
			foreach ($arSocNetUserOperations as $operation => $defPerm)
			{
				$arReturn["Operations"][$operation] = CSocNetUserPerms::CanPerformOperation($currentUserID, $userID, $operation, false);
			}
		}
		else
		{
			$arReturn["IsCurrentUser"] = ($currentUserID == $userID);
			$arReturn["Relation"] = (
				$arReturn["IsCurrentUser"]
					? false
					: CSocNetUserRelations::GetRelation($currentUserID, $userID)
			);

			if (
				$bCurrentUserIsAdmin
				|| $arReturn["IsCurrentUser"]
			)
			{
				$arReturn["Operations"]["modifyuser"] = true;
				$arReturn["Operations"]["viewcontacts"] = true;
				foreach ($arSocNetUserOperations as $operation => $defPerm)
				{
					$arReturn["Operations"][$operation] = true;
				}
			}
			else
			{
				$arReturn["Operations"]["modifyuser"] = false;
				$arReturn["Operations"]["viewcontacts"] = (
					CSocNetUser::IsFriendsAllowed()
						? ($arReturn["Relation"] == SONET_RELATIONS_FRIEND)
						: true
				);
				foreach ($arSocNetUserOperations as $operation => $defPerm)
				{
					$arReturn["Operations"][$operation] = CSocNetUserPerms::CanPerformOperation($currentUserID, $userID, $operation, false);
				}
			}

			$arReturn["Operations"]["modifyuser_main"] = false;
			if ($arReturn["IsCurrentUser"])
			{
				if ($USER->CanDoOperation('edit_own_profile'))
				{
					$arReturn["Operations"]["modifyuser_main"] = true;
				}
			}
			elseif (
				$USER->CanDoOperation('edit_all_users')
				|| (
					$USER->CanDoOperation('edit_subordinate_users')
					&& count(array_diff(CUser::GetUserGroup($userID), CSocNetTools::GetSubordinateGroups($currentUserID))) <= 0
				)
			)
			{
				$arReturn["Operations"]["modifyuser_main"] = true;
			}
		}

		return $arReturn;
	}

	public static function SetPerm($userID, $feature, $perm)
	{
		$userID = intval($userID);
		$feature = Trim($feature);
		$perm = Trim($perm);

		$dbResult = CSocNetUserPerms::GetList(
			array(),
			array(
				"USER_ID" => $userID,
				"OPERATION_ID" => $feature,
			),
			false,
			false,
			array("ID")
		);

		if ($arResult = $dbResult->Fetch())
			$r = CSocNetUserPerms::Update($arResult["ID"], array("RELATION_TYPE" => $perm));
		else
			$r = CSocNetUserPerms::Add(array("USER_ID" => $userID, "OPERATION_ID" => $feature, "RELATION_TYPE" => $perm));

		if (!$r)
		{
			$errorMessage = "";
			if ($e = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage = $e->GetString();
			if ($errorMessage == '')
				$errorMessage = GetMessage("SONET_GF_ERROR_SET").".";

			$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_SET_RECORD");
			return false;
		}
		elseif ($feature == "viewprofile")
			unset($GLOBALS["SONET_USER_PERMS_".$userID]);

		return $r;
	}
}
