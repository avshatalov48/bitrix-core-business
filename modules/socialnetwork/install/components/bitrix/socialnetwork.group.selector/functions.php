<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
function filterByFeaturePerms(&$arGroups, $arFeaturePerms)
{
	$arGroupsIDs = array();
	foreach($arGroups as $value)
	{
		$arGroupsIDs[] = $value["ID"];
	}

	if (sizeof($arGroupsIDs) > 0)
	{
		$feature = $arFeaturePerms[0];
		$operations = $arFeaturePerms[1];
		if (!is_array($operations))
			$operations = explode(",", $operations);
		$arGroupsPerms = array();
		foreach($operations as $operation)
		{
			$tmpOps = CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arGroupsIDs, $feature, $operation);
			if (is_array($tmpOps))
			{
				foreach($tmpOps as $key=>$val)
				{
					if (!$arGroupsPerms[$key])
					{
						$arGroupsPerms[$key] = $val;
					}
				}
			}
		}
		$arGroupsActive = CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arGroupsIDs, $arFeaturePerms[0]);

		foreach ($arGroups as $key=>$group)
			if (!$arGroupsActive[$group["ID"]] || !$arGroupsPerms[$group["ID"]])
				unset($arGroups[$key]);
	}
	$arGroups = array_values($arGroups);
}

function group2JSItem($arGroup, $fieldPrevix = "")
{
	$arGroupTmp = array(
		"ID" => $arGroup[$fieldPrevix."ID"],
		"id" => $arGroup[$fieldPrevix."ID"],
		"title" => $arGroup[$fieldPrevix."NAME"],
		"description" => $arGroup[$fieldPrevix."DESCRIPTION"]
	);
	
	if (isset($arGroup[$fieldPrevix."IS_EXTRANET"]))
	{
		$arGroupTmp["IS_EXTRANET"] = $arGroup[$fieldPrevix."IS_EXTRANET"];
	}

	if($arGroup[$fieldPrevix."IMAGE_ID"])
	{
		$imageFile = CFile::GetFileArray($arGroup[$fieldPrevix."IMAGE_ID"]);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array("width" => 30, "height" => 30),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$arGroupTmp["image"] = $arFileTmp["src"];
		}
	}
	return $arGroupTmp;
}
?>