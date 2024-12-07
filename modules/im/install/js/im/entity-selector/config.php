<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

Loc::loadMessages(__DIR__.'/options.php');

return [
	'settings' => [
		'entities' => [
			[
				'id' => 'im-bot',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'itemOptions' => [
						'default' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_BOT_SUPERTITLE'),
							'textColor' => '#725acc'
						],
						'network' => [
							'textColor' => '#0a962f'
						],
						'support24' => [
							'textColor' => '#0165af'
						],
					],
				],
			],
			[
				'id' => 'im-chat',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'itemOptions' => [
						'CHANNEL' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_CHANNEL_SUPERTITLE_MSGVER_1'),
						],
						'ANNOUNCEMENT' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_ANNOUNCEMENT_SUPERTITLE'),
						],
						'GROUP' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_GROUP_SUPERTITLE'),
						],
						'VIDEOCONF' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_VIDEOCONF_SUPERTITLE'),
						],
						'CALL' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_CALL_SUPERTITLE'),
						],
						'CRM' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_CRM_SUPERTITLE'),
						],
						'SONET_GROUP' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_SONET_GROUP_SUPERTITLE'),
						],
						'CALENDAR' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_CALENDAR_SUPERTITLE'),
						],
						'TASKS' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_TASKS_SUPERTITLE'),
						],
						'SUPPORT24_NOTIFIER' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_SUPPORT24_NOTIFIER_SUPERTITLE'),
							'textColor' => '#0165af',
						],
						'SUPPORT24_QUESTION' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_SUPPORT24_QUESTION_SUPERTITLE'),
							'textColor' => '#0165af',
						],
						'LINES' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_LINES_SUPERTITLE'),
							'textColor' => '#0a962f'
						],
						'LIVECHAT' => [
							'supertitle' => Loc::getMessage('IM_ENTITY_SELECTOR_LINES_SUPERTITLE'),
							'textColor' => '#0a962f'
						],
					],
				],
			],
			[
				'id' => 'im-chat-user',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				]
			],
			[
				'id' => 'im-user',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
			],
			[
				'id' => 'im-recent',
				'options' => [
					'dynamicLoad' => true,
				],
			],
			[
				'id' => 'imbot-network',
				'options' => [
					'dynamicSearch' => true,
				],
			],
		],
	],
];