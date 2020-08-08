<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$APPLICATION->includeComponent(
	'bitrix:landing.blocks.message',
	'',
	[
		'MESSAGE' => !$arResult['APP_INFO']
					? Loc::getMessage('LANDING_TPL_APP_NOT_FOUND', [
						'#APP_CODE#' => $arResult['APP_CODE']
					])
					: Loc::getMessage('LANDING_TPL_BLOCK_NOT_FOUND', [
						'#APP_NAME#' => $arResult['APP_INFO']['APP_NAME']
					])
	],
	false
);