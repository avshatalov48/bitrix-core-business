<?php
return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Seo\\Controller' => 'api',
			],
			'restIntegration' => [
				'enabled' => false,
			],
		],
		'readonly' => true,
	],
	'ui.selector' => [
		'value' => [
			'seo.selector'
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'facebook_interests',
					'provider' => [
						'moduleId' => 'seo',
						'className' => '\\Bitrix\\Seo\\UI\\Provider\\InterestsProvider'
					],
				],
				[
					'entityId' => 'facebook_regions',
					'provider' => [
						'moduleId' => 'seo',
						'className' => '\\Bitrix\\Seo\\UI\\Provider\\RegionsProvider'
					],
				]
			],
			'extensions' => ['seo.entity-selector'],
		],
		'readonly' => true,
	]
];