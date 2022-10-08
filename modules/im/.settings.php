<?php

use Bitrix\Im\Integration\UI\EntitySelector\DepartmentDataFilter;

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
				],
				[
					'id' => 'im.departmentDataFilter',
					'entityId' => 'department',
					'className' => DepartmentDataFilter::class,
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
				[
					'entityId' => 'im-chat-user',
					'provider' => [
						'moduleId' => 'im',
						'className' => '\\Bitrix\\Im\\Integration\\UI\\EntitySelector\\ChatUserProvider',
					],
				],
				[
					'entityId' => 'im-recent',
					'provider' => [
						'moduleId' => 'im',
						'className' => '\\Bitrix\\Im\\Integration\\UI\\EntitySelector\\RecentChatProvider',
					],
				],
			],
			'extensions' => ['im.entity-selector'],
		],
		'readonly' => true,
	],
];