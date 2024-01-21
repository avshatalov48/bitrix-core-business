<?php

use Bitrix\Bizproc\Integration\UI\EntitySelector\TemplateProvider;
use Bitrix\Bizproc\Integration\UI\EntitySelector\ScriptTemplateProvider;
use Bitrix\Bizproc\Integration\UI\EntitySelector\AutomationTemplateProvider;

return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Bizproc\\Controller' => 'api',
			],
			'defaultNamespace' => '\\Bitrix\\Bizproc\\Controller',
			'restIntegration' => [
				'enabled' => false,
			],
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'bizproc.service.schedulerService' => [
				'className' => '\\CBPSchedulerService',
			],
			'bizproc.service.stateService' => [
				'className' => '\\CBPStateService',
			],
			'bizproc.service.trackingService' => [
				'className' => '\\CBPTrackingService',
			],
			'bizproc.service.taskService' => [
				'className' => '\\CBPTaskService',
			],
			'bizproc.service.historyService' => [
				'className' => '\\CBPHistoryService',
			],
			'bizproc.service.documentService' => [
				'className' => '\\CBPDocumentService',
			],
			'bizproc.service.analyticsService' => [
				'className' => '\\Bitrix\\Bizproc\\Service\\Analytics',
			],
			'bizproc.service.userService' => [
				'className' => '\\Bitrix\\Bizproc\\Service\\User',
			],
			'bizproc.debugger.service.trackingService' => [
				'className' => '\\Bitrix\\Bizproc\\Debugger\\Services\\TrackingService',
			],
			'bizproc.debugger.service.analyticsService' => [
				'className' => '\\Bitrix\\Bizproc\\Debugger\\Services\\AnalyticsService',
			],
		]
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'bizproc-template',
					'provider' => [
						'moduleId' => 'bizproc',
						'className' => TemplateProvider::class,
					],
				],
				[
					'entityId' => 'bizproc-script-template',
					'provider' => [
						'moduleId' => 'bizproc',
						'className' => ScriptTemplateProvider::class,
					],
				],
				[
					'entityId' => 'bizproc-automation-template',
					'provider' => [
						'moduleId' => 'bizproc',
						'className' => AutomationTemplateProvider::class,
					],
				],
			],
			'extensions' => ['bizproc.entity-selector'],
		],
		'readonly' => true,
	],
	'ui.uploader' => [
		'value' => [
			'allowUseControllers' => true,
		],
		'readonly' => true,
	],
];
