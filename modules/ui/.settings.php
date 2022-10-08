<?
return [
	"controllers" => [
		"value" => [
			"defaultNamespace" => "\\Bitrix\\UI\\Controller"
		],
		"readonly" => true,
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
