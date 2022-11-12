<?
return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\UI\\Avatar\\Controller' => 'avatar'
			],
			'defaultNamespace' => '\\Bitrix\\UI\\Controller'
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'ui.entityform.scope' => [
				'className' => '\\Bitrix\\Ui\\EntityForm\\Scope',
			],
		]
	],
	'ui.uploader' => [
		'value' => [
			'allowUseControllers' => true,
		],
		'readonly' => true,
	],
];
