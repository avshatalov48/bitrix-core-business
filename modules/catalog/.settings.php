<?php

return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Catalog\\Controller',
			'restIntegration' => [
				'enabled' => true,
			],
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'product',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\ProductProvider',
					],
				],
				[
					'entityId' => 'section',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\SectionProvider',
					],
				],
				[
					'entityId' => 'brand',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\BrandProvider',
					],
				],
			],
			'extensions' => ['catalog.entity-selector'],
		],
		'readonly' => true,
	],
];