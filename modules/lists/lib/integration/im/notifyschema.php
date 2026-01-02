<?php

namespace Bitrix\Lists\Integration\Im;

use Bitrix\Main\Localization\Loc;

class NotifySchema
{
	public static function onGetNotifySchema()
	{
		return [
			'lists' => [
				'NAME' => Loc::getMessage('LISTS_NOTIFY_SCHEMA_BLOCK_TITLE'),
				'NOTIFY' => [
					'event_lists_comment_add' => [
						'NAME' => Loc::getMessage('LISTS_NOTIFY_SCHEMA_COMMENT_ADD'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
					'mention' => [
						'NAME' => Loc::getMessage('LISTS_NOTIFY_SCHEMA_MENTION'),
						"MAIL" => "N",
						"PUSH" => "N",
						"DISABLED" => [
							IM_NOTIFY_FEATURE_PUSH,
						],
					],
				],
			],
		];
	}
}
