<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Main\\Controller',
			'namespaces' => [
				'\\Bitrix\\Main\\Controller' => 'api',
			],
			'restIntegration' => [
				'enabled' => true,
				'hideModuleScope' => true,
				'scopes' => [
					'userfieldconfig',
				],
			],
		],
		'readonly' => true,
	],
	'console' => [
		'value' => [
			'commands' => [
				\Bitrix\Main\Cli\Command\Orm\AnnotateCommand::class,
				\Bitrix\Main\Cli\Command\Make\ComponentCommand::class,
				\Bitrix\Main\Cli\Command\Make\ControllerCommand::class,
				\Bitrix\Main\Cli\Command\Make\TabletCommand::class,
				\Bitrix\Main\Cli\Command\Dev\LocatorCodesCommand::class,
				\Bitrix\Main\Cli\Command\Dev\ModuleSkeletonCommand::class,
				\Bitrix\Main\Cli\Command\Update\ModulesCommand::class,
				\Bitrix\Main\Cli\Command\Update\LanguagesCommand::class,
				\Bitrix\Main\Cli\Command\Update\VersionsCommand::class,
			],
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'main.validation.service' => [
				'className' => \Bitrix\Main\Validation\ValidationService::class,
			],
		],
	],
];
