<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\Bitrix\Iblock\Controller',
			'restIntegration' => [
				'enabled' => true,
			],
			'namespaces' => [
				'\Bitrix\Iblock\Controller' => 'api'
			]
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'iblock-element-user-field',
					'provider' => [
						'moduleId' => 'iblock',
						'className' => '\Bitrix\Iblock\Integration\UI\EntitySelector\ElementUserFieldProvider',
					],
				],
				[
					'entityId' => 'iblock-section-user-field',
					'provider' => [
						'moduleId' => 'iblock',
						'className' => '\Bitrix\Iblock\Integration\UI\EntitySelector\SectionUserFieldProvider',
					],
				],
			],
		],
		'readonly' => true,
	],
];
