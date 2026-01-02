<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>

<?php
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */
/** @var array $arParams */

$APPLICATION->IncludeComponent(
	'bitrix:landing.site_copilot',
	'.default',
	[
		'TYPE' => $arParams['TYPE'],
		'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
	],
	$component
);
?>
