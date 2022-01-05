<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Rest\\Controller',
			'restIntegration' => [
				'enabled' => true,
				'hideModuleScope' => true,
				'scopes' => [
					'configuration.import',
				],
			],
		],
		'readonly' => true
	]
];