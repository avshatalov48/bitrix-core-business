<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule('disk'))
{
	\Bitrix\Disk\Driver::getInstance()->getUserFieldManager()->showView($arParams, $arResult, $component->__parent);
}
?>
