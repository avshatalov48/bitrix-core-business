<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('BLOCK_11_3_NAME'),
		'section' => ['tariffs'],
		'namespace' => 'bitrix',
		'only_for_license' => 'nfr',
	],
	'nodes' => [
		'bitrix:landing.blocks.tariffs' => [
			'type' => 'component',
		],
		'.landing-block-link-1' => [
			'name' => Loc::getMessage('BLOCK_11_3_LINK_1'),
			'type' => 'link',
		],
		'.landing-block-link-2' => [
			'name' => Loc::getMessage('BLOCK_11_3_LINK_2'),
			'type' => 'link',
		],
		'.landing-block-link-3' => [
			'name' => Loc::getMessage('BLOCK_11_3_LINK_3'),
			'type' => 'link',
		],
	],
	'style' => [],
];