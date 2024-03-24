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
				],
			],
		],
		'readonly' => true
	]
];