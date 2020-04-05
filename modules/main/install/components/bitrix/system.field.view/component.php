<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"]? true : false);
$arResult["VALUE"] = false;

if($arParams["arUserField"]["USER_TYPE"])
{
	$arResult["VALUE"] = $arParams["~arUserField"]["VALUE"];

	if (!is_array($arResult["VALUE"]))
		$arResult["VALUE"] = array($arResult["VALUE"]);
	if (empty($arResult["VALUE"]))
		$arResult["VALUE"] = array(null);
	$arResult["~VALUE"] = $arResult["VALUE"];
	$enum = array();
	if ($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"] == "enum")
	{
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID"=>$arParams["arUserField"]["ID"]));
		while($arEnum = $rsEnum->GetNext())
		{
			$enum[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		$arParams["arUserField"]["USER_TYPE"]["FIELDS"] = $enum;
	}

	foreach ($arResult["VALUE"] as $key => $res)
	{
		switch ($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"])
		{
			case "double":
				if (strlen($res)>0)
					$res = round(doubleval($res), $arParams["arUserField"]["SETTINGS"]["PRECISION"]);
				break;
			case "int":
				$res = intval($res);
				break;
			case "enum":
				$res = strlen($enum[$res]) > 0 ? $enum[$res] : htmlspecialcharsbx($res);
				break;
			default:
				if(is_string($res))
				{
					$res = htmlspecialcharsbx($res);
				}
				break;
		}
		$arResult["VALUE"][$key] = $res;
	}

	if($this->initComponentTemplate() || $arParams['skip_manager'])
	{
		$this->IncludeComponentTemplate();
	}
	else
	{
		$arParams['skip_manager'] = true;

		global $USER_FIELD_MANAGER;
		echo $USER_FIELD_MANAGER->GetPublicView($arParams["~arUserField"], $arParams);
	}
}
