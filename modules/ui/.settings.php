<?php

return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\UI\\Avatar\\Controller' => 'avatar',
			],
			'defaultNamespace' => '\\Bitrix\\UI\\Controller',
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
