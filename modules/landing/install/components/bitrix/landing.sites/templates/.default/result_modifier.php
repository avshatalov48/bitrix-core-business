<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var \LandingSitesComponent $component */

$arParams['SHOW_MASTER_BUTTON'] = 'N';

if ($arParams['TYPE'] == 'STORE')
{
	$arParams['SHOW_MASTER_BUTTON'] = \Bitrix\Landing\Site::getSiteIdByTemplate('store_v3') ? 'N' : 'Y';
}