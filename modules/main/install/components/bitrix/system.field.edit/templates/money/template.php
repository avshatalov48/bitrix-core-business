<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Bitrix\Main\Loader::includeModule('currency'))
{
	$arUserField = $arParams['arUserField'];
	if($arUserField['MULTIPLE'] === 'Y')
	{
		$arUserField['FIELD_NAME'] = str_replace('[]', '', $arUserField['FIELD_NAME']);
	}

	echo \Bitrix\Currency\UserField\Money::getPublicEdit($arUserField, array('bVarsFromForm' => $arParams['bVarsFromForm']));
}