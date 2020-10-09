<?php
return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\SocialNetwork\\Controller' => 'api',
			],
			'restIntegration' => [
				'enabled' => true
			],
		],
		'readonly' => true,
	],
	'ui.selector' => [
		'value' => [
			'socialnetwork.selector'
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'user',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\Socialnetwork\\Integration\\UI\\EntitySelector\\UserProvider'
					],
				],
				[
					'entityId' => 'project',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\Socialnetwork\\Integration\\UI\\EntitySelector\\ProjectProvider'
					],
				],
				[
					'entityId' => 'meta-user',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\Socialnetwork\\Integration\\UI\\EntitySelector\\MetaUserProvider'
					],
				]
			],
			'extensions' => ['socialnetwork.entity-selector'],
		],
		'readonly' => true,
	]
];