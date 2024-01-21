<?php

return [
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'highloadblock-element',
					'provider' => [
						'moduleId' => 'highloadblock',
						'className' => '\Bitrix\Highloadblock\Integration\UI\EntitySelector\ElementProvider',
					],
				]
			],
			'extensions' => ['highloadblock.entity-selector'],
		],
		'readonly' => true,
	],
];
