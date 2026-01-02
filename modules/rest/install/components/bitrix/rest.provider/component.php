<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('rest'))
	return;

$APPLICATION->IncludeComponent('bitrix:rest.server', '', $arParams, null, array('HIDE_ICONS' => false))
?>