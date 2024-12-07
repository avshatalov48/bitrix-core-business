<?php

/** @global CMain $APPLICATION */

const STOP_STATISTICS = true;

const DLSERVER = 'https://www.1c-bitrix.ru';
const DLPATH = '/download/files/locations/';
const DLMETHOD = 'GET';
const DLZIPFILE = 'zip_ussr.csv';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$wizard =  new CWizard("bitrix:sale.locations");
$wizard->IncludeWizardLang("scripts/loader.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_LOADER_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/location_import.php");

$arLoadParams = [
	'STEP' => (int)($_REQUEST['STEP'] ?? 0),
	'CSVFILE' => $_REQUEST['CSVFILE'] ?? '',
	'LOADZIP' => $_REQUEST['LOADZIP'] ?? '',
	'DLSERVER' => DLSERVER,
	'DLPORT' => null,
	'DLPATH' => DLPATH,
	'DLMETHOD' => DLMETHOD,
	'DLZIPFILE' => DLZIPFILE,
];

$arLoadResult = saleLocationLoadFile($arLoadParams);

if ($arLoadResult['ERROR'] !== '')
{
	echo $arLoadResult['ERROR'];

	if (isset($arLoadResult['RUN_ERROR']) && $arLoadResult['RUN_ERROR'] === true)
	{
		echo '<script>RunError()</script>';
	}
}
elseif (isset($arLoadResult['COMPLETE']) && $arLoadResult['COMPLETE'] === true)
{
	echo GetMessage('WSL_LOADER_ALL_LOADED');
	echo '<script>EnableButton();</script>';
}
elseif ($arLoadResult['STEP'] !== false)
{
	if ($arLoadResult['MESSAGE'] !== '')
	{
		echo $arLoadResult['MESSAGE'];
	}

	echo '<script>Run('.$arLoadResult['STEP'].')</script>';
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
