<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

/** @var CMain $APPLICATION */

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:voting.attached.result',
		'POPUP_COMPONENT_PARAMS' => [
			'SIGNED_ATTACH_ID' => (string)($_REQUEST['signedAttachId'] ?? ''),
		],
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => '/',
		'USE_PADDING' => false,
	],
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");