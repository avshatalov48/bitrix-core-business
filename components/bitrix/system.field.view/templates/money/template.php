<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Bitrix\Main\Loader::includeModule('currency'))
{
	echo \Bitrix\Currency\UserField\Money::GetPublicView($arParams['arUserField']);
}