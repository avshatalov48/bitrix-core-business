<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Bitrix\Main\Loader::includeModule('fileman'))
{
	$arUserField = $arParams['arUserField'];
	if($arUserField['MULTIPLE'] === 'Y')
	{
		$arUserField['FIELD_NAME'] = str_replace('[]', '', $arUserField['FIELD_NAME']);
	}
	echo \Bitrix\Fileman\UserField\Address::getPublicEdit($arUserField, array('bVarsFromForm' => $arParams['bVarsFromForm']));
}