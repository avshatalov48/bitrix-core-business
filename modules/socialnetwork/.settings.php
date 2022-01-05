<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Socialnetwork\\Controller',
			'namespaces' => [
				'\\Bitrix\\Socialnetwork\\Controller' => 'api',
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
					'entityId' => 'fired-user',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\Socialnetwork\\Integration\\UI\\EntitySelector\\FiredUserProvider'
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
				],
				[
					'entityId' => 'project-tag',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\SocialNetwork\\Integration\\UI\\EntitySelector\\ProjectTagProvider',
					],
				],
				[
					'entityId' => 'project-roles',
					'provider' => [
						'moduleId' => 'socialnetwork',
						'className' => '\\Bitrix\\SocialNetwork\\Integration\\UI\\EntitySelector\\ProjectRolesProvider',
					],
				],
			],
			'extensions' => ['socialnetwork.entity-selector'],
		],
		'readonly' => true,
	]
];
