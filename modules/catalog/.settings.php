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
					'entityId' => 'barcode',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\BarcodeProvider',
					],
				],
				[
					'entityId' => 'product',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\ProductProvider',
					],
				],
				[
					'entityId' => 'product_variation',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\ProductVariationProvider',
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
				[
					'entityId' => 'contractor',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\ContractorProvider',
					],
				],
				[
					'entityId' => 'store',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\StoreProvider',
					],
				],
			],
			'extensions' => ['catalog.entity-selector'],
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'catalog.integration.pullmanager' => [
				'className' => '\\Bitrix\\Catalog\\Integration\\PullManager',
			],
		],
		'readonly' => true,
	]
];
