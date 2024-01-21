<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/install/components/bitrix/main.user.link/component.php');



return (
	(\Bitrix\Main\Config\Option::get("socialnetwork", "allow_tooltip", "Y") == "Y")
		? array(
			"js" => "dist/tooltip.bundle.js",
			"css" => "dist/tooltip.bundle.css",
			"lang_additional" => array(
				'MAIN_UL_TOOLBAR_MESSAGES_CHAT' => Loc::getMessage('MAIN_UL_TOOLBAR_MESSAGES_CHAT'),
				'MAIN_UL_TOOLBAR_VIDEO_CALL' => Loc::getMessage('MAIN_UL_TOOLBAR_VIDEO_CALL'),
				'MAIN_UL_TOOLBAR_BIRTHDAY' => Loc::getMessage('MAIN_UL_TOOLBAR_BIRTHDAY'),
				'MAIN_UL_TOOLBAR_HONORED' => Loc::getMessage('MAIN_UL_TOOLBAR_HONORED'),
				'MAIN_UL_TOOLBAR_ABSENT' => Loc::getMessage('MAIN_UL_TOOLBAR_ABSENT'),
			),
			'rel' => [
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
		)
		: array()
);
