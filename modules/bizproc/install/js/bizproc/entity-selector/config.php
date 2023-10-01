<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('bizproc'))
{
	return [];
}

return [
	'settings' => [
		'entities' => [
			[
				'id' => 'bizproc-template',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
			[
				'id' => 'bizproc-script-template',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
			[
				'id' => 'bizproc-automation-template',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			]
		],
	],
];