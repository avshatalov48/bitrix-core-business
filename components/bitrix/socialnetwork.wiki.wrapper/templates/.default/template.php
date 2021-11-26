<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->IncludeComponent(
	'bitrix:wiki.menu',
	'',
	$arParams["WIKI_MENU_PARAMS"],
	$component
);

$ID = $APPLICATION->IncludeComponent(
	'bitrix:wiki.show',
	'',
	$arParams["WIKI_SHOW_PARAMS"],
	$component
);
?>
<br />
<?php
if (!empty($ID))
{
	$APPLICATION->IncludeComponent(
		'bitrix:wiki.discussion',
		'',
		$arParams["WIKI_DISCUSSION_PARAMS"],
		$component
	);
}