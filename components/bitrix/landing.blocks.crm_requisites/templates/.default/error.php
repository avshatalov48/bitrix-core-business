<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */

$APPLICATION->includeComponent(
	'bitrix:landing.blocks.message',
	'',
	[
		'MESSAGE' => $arResult['ERROR'] ?? null,
	],
	false
);
