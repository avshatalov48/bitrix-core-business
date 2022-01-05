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
	'services' => [
		'value' => [
			'seo.leadads.service' => [
				"className" => static function(){
					return \Bitrix\Seo\LeadAds\Service::getInstance();
				}
			],
			'seo.business.service' => [
				'className' => static function() {
					return \Bitrix\Seo\BusinessSuite\Service::getInstance();
				}
			],
			'seo.business.adapter' => [
				'className' => static function() {
					return \Bitrix\Seo\BusinessSuite\ServiceAdapter::loadFacebookService();
				}
			],
			'seo.business.conversion' => [
				'className' => '\\Bitrix\\Seo\\Conversion\\Facebook\\Conversion',
				'constructorParams' => static function () : array {
					$locator = \Bitrix\Main\DI\ServiceLocator::getInstance();
					if ($locator->has('seo.business.service'))
					{
						return [$locator->get('seo.business.service')];
					}
					return [];
				}
			]
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