<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * @var array $arResult
 */

if (is_array($arResult['VALUE']) && count($arResult['VALUE']) > 0)
{
	if(!CModule::IncludeModule("iblock"))
	{
		return;
	}

	if (isset($arParams["inChain"]) && $arParams["inChain"] == "Y")
	{
		$heads = array();
		foreach($arResult['VALUE'] as $sectionID)
		{
			$depth = 1;
			$rsPath = CIBlockSection::GetNavChain($arParams["arUserField"]["SETTINGS"]["IBLOCK_ID"], $sectionID, array("ID", "NAME", "DEPTH_LEVEL"));
			while($arPath = $rsPath->GetNext())
			{
				if($depth == 1)
				{
					$heads[$arPath["ID"]] = $arPath["ID"];
					$depth++;
				}
				$arResult["CHAIN"][$sectionID][] = $arPath;
			}
		}
		$arResult["MULTI_HEAD"] = (count($heads) > 1);
	}
	else
	{
		$arValue = array();
		$dbRes = CIBlockSection::GetList(array('left_margin' => 'asc'), array('ID' => $arResult['VALUE']), false, array("ID", "NAME"));
		while ($arRes = $dbRes->GetNext())
		{
			$arValue[$arRes['ID']] = $arRes['NAME'];
		}
		$arResult['VALUE'] = $arValue;
	}
}
