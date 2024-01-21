<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\UserField\Types\BaseType;

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

$arParams["bVarsFromForm"] = isset($arParams["bVarsFromForm"]) && $arParams["bVarsFromForm"];
$arResult["VALUE"] = false;

if(!empty($arParams["arUserField"]["USER_TYPE"]))
{
	$arResult["VALUE"] = $arParams["~arUserField"]["VALUE"];

	if(!is_array($arResult["VALUE"]))
		$arResult["VALUE"] = array($arResult["VALUE"]);
	if(empty($arResult["VALUE"]))
		$arResult["VALUE"] = array(null);
	$arResult["~VALUE"] = $arResult["VALUE"];
	$enum = array();
	if($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"] == "enum")
	{
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => $arParams["arUserField"]["ID"]));
		while($arEnum = $rsEnum->GetNext())
		{
			$enum[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		$arParams["arUserField"]["USER_TYPE"]["FIELDS"] = $enum;
	}

	foreach($arResult["VALUE"] as $key => $res)
	{
		switch($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"])
		{
			case "double":
				if($res <> '')
					$res = round(doubleval($res), $arParams["arUserField"]["SETTINGS"]["PRECISION"]);
				break;
			case "int":
				$res = intval($res);
				break;
			default:
				if(
					is_string($res)
					&& empty($arParams['arUserField']['USER_TYPE']['USE_FIELD_COMPONENT'])
				)
				{
					$res = htmlspecialcharsbx($res);
				}
				break;
		}
		$arResult["VALUE"][$key] = $res;
	}

	if (!empty($arParams['arUserField']['USER_TYPE']['USE_FIELD_COMPONENT']))
	{
		$arParams['skip_manager'] = true;
		$arParams['mode'] = ($arParams['mode'] ?? (
			(!empty($componentTemplate) && !empty($parentComponentTemplate)) ? $componentTemplate : BaseType::MODE_VIEW)
		);
		$arParams['VALUE'] = $arResult['VALUE'];
		$arParams['parentComponent'] = $this->getParent();
		$field = new \Bitrix\Main\UserField\Renderer($arParams['arUserField'], $arParams);
		print $field->render();
	}
	else
	{
		if ((isset($arParams['skip_manager']) && $arParams['skip_manager']) || $this->initComponentTemplate())
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
}
