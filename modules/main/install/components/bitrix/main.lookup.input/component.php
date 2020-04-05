<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/*
$arParams['INPUT_NAME'] - input name for value
$arParams['INPUT_NAME_STRING'] - input name for textarea (may be useful to get original value set by user)
$arParams['INPUT_VALUE'] - starting value. can be comma-separated string of IDs or array of IDs.
$arParams['INPUT_VALUE_STRING'] - starting value as string for textarea. Tokens like "Lastname Name <Email> [id]" will be parsed automatically. Tokens may be comma-separated, semicolon-separated or newline-separated.

if both INPUT_VALUE_STRING and INPUT_VALUE are specified, INPUT_VALUE will be ignored. INPUT_VALUE_STRING may be more preferrable by the cause of interface performance.

Input has two results: list of user IDs as array (INPUT_NAME) and original text typed by user (INPUT_NAME_STRING).

$arParams['CONTROL_ID'] - ID for external events handling

$arParams['INPUT_NAME_SUSPICIOUS'] - input for some suspicious tokens such as email addresses.
*/

$arParams['INPUT_NAME'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME']) ? $arParams['INPUT_NAME'] : false;
$arParams['INPUT_NAME_STRING'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME_STRING']) ? $arParams['INPUT_NAME_STRING'] : false;
$arParams['INPUT_NAME_SUSPICIOUS'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME_SUSPICIOUS']) ? $arParams['INPUT_NAME_SUSPICIOUS'] : false;

$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : 'ius_'.rand(1, 10000);

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), "", $arParams["NAME_TEMPLATE"]);

if (isset($arParams['INPUT_VALUE_STRING']))
{
	if (strlen($arParams['INPUT_VALUE_STRING']) > 0)
	{
		$arTokens = preg_split('/(?<=])[\n;,]+/', $arParams['~INPUT_VALUE_STRING']);
		$arTokens = array_unique($arTokens);

		foreach ($arTokens as $key => $token)
		{
			$arTokens[$key] = trim($token);
			if (strlen($arTokens[$key]) <= 0)
				unset($arTokens[$key]);
		}

		$arParams['INPUT_VALUE_STRING'] = implode("\n", $arTokens);
	}
}
elseif (isset($arParams['INPUT_VALUE']))
{
	if (!is_array($arParams['INPUT_VALUE']))
		$arParams['INPUT_VALUE'] = explode(',', $arParams['INPUT_VALUE']);

	foreach ($arParams['INPUT_VALUE'] as $key => $ID)
		$arParams['INPUT_VALUE'][$key] = intval(trim($ID));

	$arParams['INPUT_VALUE'] = array_unique($arParams['INPUT_VALUE']);
}
else
{
	$arParams['INPUT_VALUE_STRING'] = '';
}

CJSCore::Init(array("ajax"));

$APPLICATION->AddHeadScript($this->GetPath().'/script.js');

$this->IncludeComponentTemplate();

return $arParams['CONTROL_ID'];
