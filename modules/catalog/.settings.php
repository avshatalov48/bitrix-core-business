<?php

return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Catalog\\Controller',
			'restIntegration' => [
				'enabled' => true,
				'eventBind' => [
					'\\Bitrix\\Catalog\\Controller\\Price',
					'\\Bitrix\\Catalog\\Controller\\Product',
					'\\Bitrix\\Catalog\\Controller\\Measure',
					'\\Bitrix\\Catalog\\Controller\\RoundingRule',
					'\\Bitrix\\Catalog\\Controller\\PriceType',
				]
			],
		],
		'readonly' => true,
	],
	'userField' => [
		'value' => [
			'access' => '\\Bitrix\\Catalog\\UserField\\UserFieldAccess',
		],
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
					'entityId' => 'variation',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\VariationProvider',
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
				[
					'entityId' => 'agent-contractor-product-variation',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\AgentContract\\ProductVariationProvider',
					],
				],
				[
					'entityId' => 'agent-contractor-section',
					'provider' => [
						'moduleId' => 'catalog',
						'className' => '\\Bitrix\\Catalog\\v2\\Integration\\UI\\EntitySelector\\AgentContract\\SectionProvider',
					],
				],
			],
			'extensions' => ['catalog.entity-selector'],
		],
		'readonly' => true,
	],
	'ui.uploader' => [
		'value' => [
			'allowUseControllers' => true,
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
