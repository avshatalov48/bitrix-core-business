<?php
return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\SocialNetwork\\Controller' => 'api',
			],
			'restIntegration' => [
				'enabled' => true
			],
		],
		'readonly' => true,
	],
	'ui.selector' => [
		'value' => [
			'socialnetwork.selector'
		],
		'readonly' => true,
	]
];