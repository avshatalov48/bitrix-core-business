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
					'delegate_task' => [
						'NAME' => Loc::getMessage('BIZPROC_NOTIFY_SCHEMA_DELEGATE_TASK'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
					'wi_locked' => [
						'NAME' => Loc::getMessage('BIZPROC_NOTIFY_SCHEMA_WI_LOCKED'),
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
