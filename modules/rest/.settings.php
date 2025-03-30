<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Rest\\Controller',
			'restIntegration' => [
				'enabled' => true,
				'hideModuleScope' => true,
				'scopes' => [
					'appform',
					'configuration.import',
					'user',
				],
			],
		],
		'readonly' => true
	],
	'services' => [
		'value' => [
			'rest.service.apauth.password' => [
				'className' => \Bitrix\Rest\Service\APAuth\PasswordService::class,
			],
			'rest.service.apauth.permission' => [
				'className' => \Bitrix\Rest\Service\APAuth\PermissionService::class,
			],
			'rest.service.app' => [
				'constructor' => function () {
					return new \Bitrix\Rest\Service\AppService(
						new \Bitrix\Rest\Repository\AppRepository(
							new Bitrix\Rest\Model\Mapper\App()
						)
					);
				},
			],
			'rest.service.integration' => [
				'constructor' => function () {
					return new \Bitrix\Rest\Service\IntegrationService(
						new \Bitrix\Rest\Repository\IntegrationRepository(
							new Bitrix\Rest\Model\Mapper\Integration()
						)
					);
				},
			],
			'rest.repository.app' => [
				'constructor' => static function () {
					return new \Bitrix\Rest\Repository\AppRepository(
						new \Bitrix\Rest\Model\Mapper\App()
					);
				},
			],
			'rest.repository.integration' => [
				'constructor' => static function () {
					return new \Bitrix\Rest\Repository\IntegrationRepository(
						new \Bitrix\Rest\Model\Mapper\Integration()
					);
				},
			],
		],
		'readonly' => true,
	]
];
