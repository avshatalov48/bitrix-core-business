<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$entitiesSearchFields = [
	[
		'name' => 'supertitle',
		'type' => 'string',
		'system' => true,
	],
	[
		'name' => 'SEARCH_PROPERTIES',
		'type' => 'string',
	],
	[
		'name' => 'PREVIEW_TEXT',
		'type' => 'string',
	],
	[
		'name' => 'DETAIL_TEXT',
		'type' => 'string',
	],
	[
		'name' => 'PARENT_NAME',
		'type' => 'string',
	],
	[
		'name' => 'PARENT_SEARCH_PROPERTIES',
		'type' => 'string',
	],
	[
		'name' => 'PARENT_PREVIEW_TEXT',
		'type' => 'string',
	],
	[
		'name' => 'PARENT_DETAIL_TEXT',
		'type' => 'string',
	],
];

return [
	'settings' => [
		'entities' => [
			[
				'id' => 'product',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'searchFields' => $entitiesSearchFields,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/product.svg',
							'captionOptions' => [
								'fitContent' => true,
								'maxWidth' => 150
							],
						],
					],
				],
			],
			[
				'id' => 'product_variation',
				'options' => [
					'dynamicLoad' => false,
					'dynamicSearch' => true,
					'searchFields' => $entitiesSearchFields,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/product.svg',
							'captionOptions' => [
								'fitContent' => true,
								'maxWidth' => 150
							],
						],
					],
				],
			],
			[
				'id' => 'variation',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'searchFields' => $entitiesSearchFields,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/product.svg',
							'captionOptions' => [
								'fitContent' => true,
								'maxWidth' => 150
							],
						],
					],
				],
			],
			[
				'id' => 'store',
				'options' => [
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/store.svg',
						],
					],
				]
			],
			[
				'id' => 'dynamic',
				'options' => [
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/dynamic.svg',
						],
					],
				]
			],
			[
				'id' => 'agent-contractor-product-variation',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'searchFields' => $entitiesSearchFields,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/product.svg',
							'captionOptions' => [
								'fitContent' => true,
								'maxWidth' => 150
							],
						],
					],
				],
			],
			[
				'id' => 'agent-contractor-section',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'searchFields' => $entitiesSearchFields,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/catalog/entity-selector/src/images/product.svg',
							'captionOptions' => [
								'fitContent' => true,
								'maxWidth' => 150
							],
						],
					],
					'tagOptions' => [
						'default' => [
							'textColor' => '#535c69',
							'bgColor' => '#d2f95f',
						]
					],
				],
			],
		],
	],
];