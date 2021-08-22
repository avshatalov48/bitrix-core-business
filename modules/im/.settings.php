<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Im\\Controller',
			'restIntegration' => [
				'enabled' => true
			]
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'Im.Services.Message' => [
				'className' => '\\Bitrix\\Im\\Services\\Message',
			],
			'Im.Services.MessageParam' => [
				'className' => '\\Bitrix\\Im\\Services\\MessageParam',
			],
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'filters' => [
				[
					'id' => 'im.userDataFilter',
					'entityId' => 'user',
					'className' => '\\Bitrix\\Im\\Integration\\UI\\EntitySelector\\UserDataFilter',
				]
			],
			'entities' => [
				[
					'entityId' => 'im-bot',
					'provider' => [
						'moduleId' => 'im',
						'className' => '\\Bitrix\\Im\\Integration\\UI\\EntitySelector\\BotProvider',
					],
				],
				[
					'entityId' => 'im-chat',
					'provider' => [
						'moduleId' => 'im',
						'className' => '\\Bitrix\\Im\\Integration\\UI\\EntitySelector\\ChatProvider',
					],
				],
			],
			'extensions' => ['im.entity-selector'],
		],
		'readonly' => true,
	],
];