<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage('SMOD_SALE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage('SMOD_MOBILEAPP_NOT_INSTALLED'));
	return;
}

$this->IncludeComponentTemplate();

return $arResult;
?>