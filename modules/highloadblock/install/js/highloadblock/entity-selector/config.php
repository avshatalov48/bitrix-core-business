<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Highloadblock\Integration\UI\EntitySelector;

if (!\Bitrix\Main\Loader::includeModule('highloadblock'))
{
	return [];
}

return [
	'settings' => [
		'entities' => [
			[
				'id' => EntitySelector\ElementProvider::ENTITY_ID,
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
		],
	],
];
