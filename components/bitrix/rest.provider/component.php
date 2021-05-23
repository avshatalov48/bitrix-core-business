<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('rest'))
	return;

$arServiceParams = $arParams;
$arServiceParams['CLASS'] = 'CRestProvider';

$APPLICATION->IncludeComponent('bitrix:rest.server', '', $arServiceParams, null, array('HIDE_ICONS' => false))
?>