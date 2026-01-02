<?php

use Bitrix\Im\Configuration\Notification;

IncludeModuleLangFile(__FILE__);

class CIMNotifySchema
{
	protected static $arNotifySchema = null;

	public function __construct()
	{
	}

	public static function GetNotifySchema()
	{
		if (is_null(self::$arNotifySchema))
		{
			self::$arNotifySchema = Notification::getDefaultSettings();
		}

		return self::$arNotifySchema;
	}

	public static function CheckDisableFeature($moduleId, $notifyEvent, $feature)
	{
		return (new Notification($moduleId, $notifyEvent))->checkDisableFeature($feature);
	}

	public static function GetDefaultFeature($moduleId, $notifyEvent, $feature)
	{
		return (new Notification($moduleId, $notifyEvent))->getDefaultFeature($feature);
	}

	public static function GetLifetime($moduleId, $notifyEvent)
	{
		return (new Notification($moduleId, $notifyEvent))->getLifetime();
	}

	public static function OnGetNotifySchema()
	{
		$config = [
			"im" => [
				"NAME" => GetMessage('IM_NS_IM'),
				"NOTIFY" => [
					"message" => [
						"NAME" => GetMessage('IM_NS_MESSAGE_NEW_MSGVER_2'),
						"PUSH" => 'Y',
						"DISABLED" => [IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP],
					],
					"chat" => [
						"NAME" => GetMessage('IM_NS_CHAT_NEW'),
						"MAIL" => 'N',
						"PUSH" => 'Y',
						"DISABLED" => [IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_MAIL],
					],
					"openChat" => [
						"NAME" => GetMessage('IM_NS_OPEN_NEW_MSGVER_2'),
						"MAIL" => 'N',
						"PUSH" => 'Y',
						"DISABLED" => [IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_MAIL],
					],
					"like" => [
						"NAME" => GetMessage('IM_NS_LIKE_MSGVER_1'),
					],
					"mention" => [
						"NAME" => GetMessage('IM_NS_MENTION_2'),
						"PUSH" => 'Y',
					],
					"videconf_new_guest" => [
						"NAME" => GetMessage('IM_NS_VIDEOCONF_NEW_GUEST'),
						"MAIL" => "N",
						"PUSH" => "Y",
					],
					"default" => [
						"NAME" => GetMessage('IM_NS_DEFAULT_MSGVER_1'),
						"PUSH" => 'N',
						"MAIL" => 'N',
					],
				],
			],
		];

		if (!IsModuleInstalled("b24network"))
		{
			$config["main"] = [
				"NAME" => GetMessage('IM_NS_MAIN'),
				"NOTIFY" => [
					"rating_vote" => [
						"NAME" => GetMessage('IM_NS_MAIN_RATING_VOTE'),
						"LIFETIME" => 86400*7,
					],
					"rating_vote_mentioned" => [
						"NAME" => GetMessage('IM_NS_MAIN_RATING_VOTE_MENTIONED'),
						"LIFETIME" => 86400*7,
					],
				],
			];
		}

		return $config;
	}
}
