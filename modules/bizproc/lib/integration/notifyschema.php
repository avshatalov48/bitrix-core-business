<?php

namespace Bitrix\Bizproc\Integration;

use Bitrix\Main\Localization\Loc;

class NotifySchema
{
	public static function onGetNotifySchema()
	{
		return [
			'bizproc' => [
				'NOTIFY' => [
					'activity' => [
						'NAME' => Loc::getMessage('BIZPROC_NOTIFY_SCHEMA_ACTIVITY'),
						'PUSH' => 'Y',
					],
				],
			],
		];
	}
}
