<?php
	return [
		'controllers' => [
			'value' => [
				'namespaces' => [
					'\\Bitrix\\Calendar\\Controller' => 'api',
				],
			],
			'readonly' => true,
		],
		'services' => [
			'value' => [
				'calendar.service.google.helper' => [
					'className' => '\\Bitrix\\Calendar\\Sync\\Google\\Helper',
				],
				'calendar.service.caldav.helper' => [
					'className' => '\\Bitrix\\Calendar\\Sync\\Caldav\\Helper',
				],
			],
			'readonly' => true,
		],
	];