<?php
return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Translate\\Controller'
		],
		'readonly' => true,
	],
	'console' => [
		'value' => [
			'commands' => [
				\Bitrix\Translate\Cli\IndexCommand::class,
			],
		],
		'readonly' => true,
	],
];
