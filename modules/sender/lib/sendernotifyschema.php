<?php

namespace Bitrix\Sender;

class SenderNotifySchema
{
	public static function OnGetNotifySchema()
	{
		return [
			"sender" => [
				"group_prepared" => [
					"NAME" => GetMessage("SENDER_SEGMENT_NOTIFY"),
					"SITE" => "Y",
					"MAIL" => "N",
					"XMPP" => "N",
					"PUSH" => "N",
					"DISABLED" => [
						IM_NOTIFY_FEATURE_XMPP,
						IM_NOTIFY_FEATURE_MAIL,
						IM_NOTIFY_FEATURE_PUSH,
					],
				],
			],
		];
	}
}
