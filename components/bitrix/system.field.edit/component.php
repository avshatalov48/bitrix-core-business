<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\BaseType;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$arParams["bVarsFromForm"] = (isset($arParams["bVarsFromForm"]) && $arParams["bVarsFromForm"]);
$arResult["VALUE"] = false;
$arUserField = &$arParams["arUserField"];

if($arUserField["USER_TYPE"])
{
	if(!isset($arUserField["SETTINGS"]) || !is_array($arUserField["SETTINGS"]))
		$arUserField["SETTINGS"] = array();
	if(!isset($arUserField["USER_TYPE"]) || !is_array($arUserField["USER_TYPE"]))
		$arUserField["USER_TYPE"] = array();

	if(!$arParams["bVarsFromForm"])
	{
		if(
			(!isset($arUserField["ENTITY_VALUE_ID"]) || $arUserField["ENTITY_VALUE_ID"] <= 0)
			&& isset($arUserField["SETTINGS"]["DEFAULT_VALUE"])
			&& !is_array($arUserField["SETTINGS"]["DEFAULT_VALUE"])
			&& $arUserField["SETTINGS"]["DEFAULT_VALUE"] <> ''
		)
		{
			$arResult["VALUE"] = $arParams["~arUserField"]["SETTINGS"]["DEFAULT_VALUE"];
		}
		else
		{
			$arResult["VALUE"] = $arParams["~arUserField"]["VALUE"] ?? '';
		}
	}
	else
	{
		if (isset($arUserField["USER_TYPE"]["BASE_TYPE"]) && $arUserField["USER_TYPE"]["BASE_TYPE"] === "file")
		{
			$arResult["VALUE"] = $GLOBALS[$arUserField["FIELD_NAME"] . "_old_id"] ?? '';
		}
		else
		{
			$arResult["VALUE"] = $_REQUEST[$arUserField["FIELD_NAME"]] ?? '';
		}
	}

	if(!is_array($arResult["VALUE"]))
	{
		$arResult["VALUE"] = array($arResult["VALUE"]);
	}
	if(empty($arResult["VALUE"]))
	{
		$arResult["VALUE"] = array(null);
	}

	foreach($arResult["VALUE"] as $key => $res)
	{
		$baseType = $arUserField["USER_TYPE"]["BASE_TYPE"] ?? null;
		switch($baseType)
		{
			case "double":
				if($res <> '')
				{
					$res = round(doubleval($res), $arUserField["SETTINGS"]["PRECISION"]);
				}
				break;
			case "int":
				if($res <> '')
				{
					$res = intval($res);
				}
				break;
			default:
				if(
					is_string($res)
					&& empty($arUserField['USER_TYPE']['USE_FIELD_COMPONENT'])
				)
				{
					$res = htmlspecialcharsbx($res);
				}
				break;
		}
		$arResult["VALUE"][$key] = $res;
	}

	$arUserField["~FIELD_NAME"] = $arUserField["FIELD_NAME"];

	if (
		isset($arUserField["MULTIPLE"])
		&& $arUserField["MULTIPLE"]==="Y"
		&& empty($arUserField['USER_TYPE']['USE_FIELD_COMPONENT'])
	)
	{
		$arUserField["FIELD_NAME"] .= "[]";

		if (!empty($arResult["VALUE"]) && (!empty($arResult["VALUE"][count($arResult["VALUE"])-1])))
		{
			$arResult["VALUE"][] = null;
		}
	}

	if (isset($arUserField["USER_TYPE"]['CLASS_NAME']) && is_callable(array($arUserField["USER_TYPE"]['CLASS_NAME'], 'getlist')))
	{
		$enum = array();

		$showNoValue = $arUserField["MANDATORY"] != "Y"
			|| (isset($arParams["SHOW_NO_VALUE"]) && $arParams["SHOW_NO_VALUE"] == true);

		if($showNoValue
			&& ($arUserField["SETTINGS"]["DISPLAY"] != "CHECKBOX" || $arUserField["MULTIPLE"] <> "Y")
		)
		{
			$enum = array(null => (isset($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) && $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] <> '' ? htmlspecialcharsbx($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) : GetMessage("MAIN_NO")));
		}

		$rsEnum = call_user_func_array(
			array($arParams['arUserField']["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arParams['arUserField'],
			)
		);

		if(!$arParams["bVarsFromForm"] && ($arUserField["ENTITY_VALUE_ID"] <= 0))
			$arResult["VALUE"] = array();

		if(is_object($rsEnum))
		{
			while($arEnum = $rsEnum->GetNext())
			{
				$enum[$arEnum["ID"]] = $arEnum["VALUE"];
				if(!$arParams["bVarsFromForm"] && ($arUserField["ENTITY_VALUE_ID"] <= 0))
				{
					if($arEnum["DEF"] == "Y")
						$arResult["VALUE"][] = $arEnum["ID"];
				}
			}
		}
		$arUserField["USER_TYPE"]["FIELDS"] = $enum;
	}

	$arParams["form_name"] = !empty($arParams["form_name"]) ? $arParams["form_name"] : "form1";

	$arResult["RANDOM"] = (isset($arParams["RANDOM"]) && $arParams["RANDOM"] <> '' ? $arParams["RANDOM"] : $this->randString());

	if(!empty($arUserField['USER_TYPE']['USE_FIELD_COMPONENT']))
	{
		$arParams['skip_manager'] = true;

		if($arUserField['MULTIPLE'] === 'Y')
		{
			$arUserField['FIELD_NAME'] = $arUserField['~FIELD_NAME'];
		}
		$arParams['mode'] = ($arParams['mode'] ?? (
			(!empty($componentTemplate) && !empty($parentComponentTemplate)) ? $componentTemplate : BaseType::MODE_EDIT)
		);
		$arParams['VALUE'] = $arResult['VALUE'];
		$arParams['parentComponent'] = $this->getParent();
		$field = new \Bitrix\Main\UserField\Renderer($arUserField, $arParams);
		print $field->render();
	}
	else
	{
		if($this->initComponentTemplate() || $arParams['skip_manager'])
		{
			$APPLICATION->AddHeadScript($this->getPath() . "/script.js");

			$this->IncludeComponentTemplate();
		}
		else
		{
			$arParams['skip_manager'] = true;

			if($arUserField['MULTIPLE'] === 'Y')
			{
				$arUserField['FIELD_NAME'] = $arUserField['~FIELD_NAME'];
			}

			global $USER_FIELD_MANAGER;
			echo $USER_FIELD_MANAGER->GetPublicEdit($arUserField, $arParams);
		}
	}

}