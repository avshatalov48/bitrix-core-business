<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'settings' => [
		'entities' => [
			[
				'id' => 'landing',
				'options' => [
					'itemOptions' => [
						'default' => [
							'avatarOptions' => [
								'bgSize' => 'cover',
							],
						],
						'folder' => [
							'avatar' => '/bitrix/js/landing/entity-selector/src/images/icon-folder.svg',
						],
					],
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			]
		]
	]
];
