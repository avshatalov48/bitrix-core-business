<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_3_NAME'),
		'section' => ['store'],
		'dynamic' => false,
		'subtype' => ['menu'],
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_3_TITLE'),
			'type' => 'link',
		],
		'.landing-block-node-phone' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_3_LINK'),
			'type' => 'link',
			'skipContent' => true,
			'group' => 'phone',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-paddings', 'header-on-scroll', 'header-position'],
		],
		'nodes' => [
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_3_LINK_TEXT_2'),
				'type' => ['typo'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_header'],
	],
	'groups' => [
		'phone' => Loc::getMessage('LNDBLCK_STOREMENUV3_3_PHONE'),
	],
];