<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Bitrix\Main\Loader::includeModule('fileman'))
{
	echo \Bitrix\Fileman\UserField\Address::GetPublicView($arParams['arUserField'], array('printable' => $arParams['printable']));
}