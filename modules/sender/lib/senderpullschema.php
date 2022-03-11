<?php

namespace Bitrix\Sender;

class SenderPullSchema
{
	public static function OnGetDependentModule()
	{
		return [
			'MODULE_ID' => 'sender',
			'USE' => [
				'PUBLIC_SECTION',
			],
		];
	}
}
