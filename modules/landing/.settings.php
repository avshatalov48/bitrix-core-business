<?php
return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Landing\\Controller' => 'api'
			],
			'defaultNamespace' => '\\Bitrix\\Landing\\Controller'
		],
		'readonly' => true
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'landing',
					'provider' => [
						'moduleId' => 'landing',
						'className' => '\\Bitrix\\Landing\\Connector\\Ui\\SelectorProvider'
					]
				]
			],
			'extensions' => ['landing.entity-selector']
		],
		'readonly' => true
	]
];