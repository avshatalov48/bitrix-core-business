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
				[
					'entityId' => 'iblock-property-element',
					'provider' => [
						'moduleId' => 'iblock',
						'className' => '\Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider',
					],
				],
				[
					'entityId' => 'iblock-property-element-xml',
					'provider' => [
						'moduleId' => 'iblock',
						'className' => '\Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementXmlProvider',
					],
				],
				[
					'entityId' => 'iblock-property-section',
					'provider' => [
						'moduleId' => 'iblock',
						'className' => '\Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertySectionProvider',
					],
				],
			],
		],
		'readonly' => true,
	],
];
