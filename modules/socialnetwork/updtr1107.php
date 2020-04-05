<?
if (CModule::IncludeModule("socialnetwork"))
{
	$GLOBALS["DB"]->Query("TRUNCATE TABLE b_sonet_log_right");
	$rsLog = CSocNetLog::GetList(array("ID"=>"DESC"), array(), false, false, array("ID", "ENTITY_TYPE", "ENTITY_ID", "EVENT_ID"));
	while ($arLog = $rsLog->Fetch())
	{
		CSocNetLogRights::DeleteByLogID($arLog["ID"]);
		if (in_array($arLog["ENTITY_TYPE"], array(SONET_SUBSCRIBE_ENTITY_GROUP, SONET_SUBSCRIBE_ENTITY_USER)))
		{
			if ($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP && $arLog["EVENT_ID"] == "system")
				CSocNetLogRights::Add($arLog["ID"], array("SA", "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_OWNER, "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_MODERATOR, "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_USER));
			elseif ($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER && in_array($arLog["EVENT_ID"], array("system", "system_groups", "system_friends")))
			{
				switch ($arLog["EVENT_ID"])
				{
					case "system": $operation = "viewprofile"; break;
					case "system_groups": $operation = "viewgroups"; break;
					case "system_friends": $operation = "viewfriends"; break;
				}

				$perm = CSocNetUserPerms::GetOperationPerms($arLog["ENTITY_ID"], $operation);
				if (in_array($perm, array(SONET_RELATIONS_TYPE_FRIENDS2, SONET_RELATIONS_TYPE_FRIENDS)))
					CSocNetLogRights::Add($arLog["ID"], array("SA", "U".$arLog["ENTITY_ID"], "SU".$arLog["ENTITY_ID"]."_".$perm));
				elseif ($perm == SONET_RELATIONS_TYPE_NONE)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "U".$arLog["ENTITY_ID"]));
				elseif ($perm == SONET_RELATIONS_TYPE_AUTHORIZED)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "AU"));
				elseif ($perm == SONET_RELATIONS_TYPE_ALL)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "G2"));			
			}
			elseif ($featureID = CSocNetLogTools::FindFeatureByEventID($arLog["EVENT_ID"]))
				CSocNetLogRights::SetForSonet(
					$arLog["ID"], 
					$arLog["ENTITY_TYPE"], 
					$arLog["ENTITY_ID"], 
					$featureID, 
					$GLOBALS["arSocNetFeaturesSettings"][$featureID]["subscribe_events"][$arLog["EVENT_ID"]]["OPERATION"]
				);
			elseif ($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
				CSocNetLogRights::Add($arLog["ID"], array("SA", "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_OWNER, "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_MODERATOR, "SG".$arLog["ENTITY_ID"]."_".SONET_ROLES_USER));
			elseif ($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER)
			{
				$perm = CSocNetUserPerms::GetOperationPerms($arLog["ENTITY_ID"], "viewprofile");
				if (in_array($perm, array(SONET_RELATIONS_TYPE_FRIENDS2, SONET_RELATIONS_TYPE_FRIENDS)))
					CSocNetLogRights::Add($arLog["ID"], array("SA", "U".$arLog["ENTITY_ID"], "SU".$arLog["ENTITY_ID"]."_".$perm));
				elseif ($perm == SONET_RELATIONS_TYPE_NONE)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "U".$arLog["ENTITY_ID"]));
				elseif ($perm == SONET_RELATIONS_TYPE_AUTHORIZED)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "AU"));
				elseif ($perm == SONET_RELATIONS_TYPE_ALL)
					CSocNetLogRights::Add($arLog["ID"], array("SA", "G2"));	
					
// tasks!!!					
					
			}
		}
		elseif (in_array($arLog["ENTITY_TYPE"], array('R')))
		{
// reports
		}
		elseif (in_array($arLog["ENTITY_TYPE"], array('T')))
		{
// timeman
		}
		else
			CSocNetLogRights::Add($arLog["ID"], array("AU"));
	}
}
?>