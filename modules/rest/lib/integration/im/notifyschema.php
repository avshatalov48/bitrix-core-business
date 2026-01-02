<?php

namespace Bitrix\Rest\Integration\Im;

use Bitrix\Main\Localization\Loc;

class NotifySchema
{
	public static function onGetNotifySchema()
	{
		return [
			'rest' => [
				'NAME' => Loc::getMessage('REST_NOTIFY_SCHEMA_BLOCK_TITLE'),
				'NOTIFY' => [
					'app_install' => [
						'NAME' => Loc::getMessage('REST_NOTIFY_SCHEMA_APP_INSTALL'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
					'app_install_request' => [
						'NAME' => Loc::getMessage('REST_NOTIFY_SCHEMA_APP_INSTALL_REQUEST'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
					'vi_user_data_request' => [
						'NAME' => Loc::getMessage('REST_NOTIFY_SCHEMA_VI_USER_DATA_REQUEST'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
				],
			],
		];
	}
}
