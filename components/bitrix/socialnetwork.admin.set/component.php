<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule('socialnetwork'))
{
	ShowError(GetMessage('SONET_MODULE_NOT_INSTALL'));
	return;
}

$arResult = [
	'FatalError' => '',
	'ErrorMessage' => '',
	'PROCESS_ONLY' => ($arParams['PROCESS_ONLY'] ?? ''),
];

if (CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
{
	$arResult['IS_SESSION_ADMIN'] = $arResult['SHOW_BANNER'] = CSocNetUser::IsEnabledModuleAdmin();
}

$this->IncludeComponentTemplate();
