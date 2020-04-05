<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('rest'))
	return;

$arComponentParameters = CRestUtil::getStandardParams();
?>