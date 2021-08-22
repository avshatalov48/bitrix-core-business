<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'settings' => [
		'entities' => [
			[
				'id' => 'im-bot',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
			[
				'id' => 'im-chat',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
			[
				'id' => 'im-user',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
		],
	],
];