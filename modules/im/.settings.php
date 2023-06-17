<?php

return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Im\\V2\\Controller' => 'v2',
			],
			'defaultNamespace' => '\\Bitrix\\Im\\Controller',
			'restIntegration' => [
				'enabled' => true,
				'scopes' => ['im.import']
			]
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'Im.Messenger' => [
				'className' => \Bitrix\Im\V2\Service\Messenger::class,
			],
			'Im.Services.Message' => [
				'className' => \Bitrix\Im\Services\Message::class,
			],
			'Im.Services.MessageParam' => [
				'className' => \Bitrix\Im\Services\MessageParam::class,
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
					'className' => \Bitrix\Im\Integration\UI\EntitySelector\UserDataFilter::class,
				],
				[
					'id' => 'im.departmentDataFilter',
					'entityId' => 'department',
					'className' => \Bitrix\Im\Integration\UI\EntitySelector\DepartmentDataFilter::class,
				]
			],
			'entities' => [
				[
					'entityId' => 'im-bot',
					'provider' => [
						'moduleId' => 'im',
						'className' => \Bitrix\Im\Integration\UI\EntitySelector\BotProvider::class,
					],
				],
				[
					'entityId' => 'im-chat',
					'provider' => [
						'moduleId' => 'im',
						'className' => \Bitrix\Im\Integration\UI\EntitySelector\ChatProvider::class,
					],
				],
				[
					'entityId' => 'im-chat-user',
					'provider' => [
						'moduleId' => 'im',
						'className' => \Bitrix\Im\Integration\UI\EntitySelector\ChatUserProvider::class,
					],
				],
				[
					'entityId' => 'im-recent',
					'provider' => [
						'moduleId' => 'im',
						'className' => \Bitrix\Im\Integration\UI\EntitySelector\RecentChatProvider::class,
					],
				],
			],
			'extensions' => ['im.entity-selector'],
		],
		'readonly' => true,
	],
];