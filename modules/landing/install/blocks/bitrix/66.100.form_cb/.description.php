<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDNGBLCK_66_100_NAME'),
		'section' => ['contacts'],
		'type' => ['page', 'store', 'smn'],
		'dynamic' => false,
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LNDNGBLCK_66_100_BUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LNDNGBLCK_66_100_BUTTON'),
			'type' => ['button'],
		],
	],
];