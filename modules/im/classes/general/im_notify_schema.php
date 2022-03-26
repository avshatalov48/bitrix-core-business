<?

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
		$config = array(
			"im" => Array(
				"NAME" => GetMessage('IM_NS_IM'),
				"NOTIFY" => Array(
					"message" => Array(
						"NAME" => GetMessage('IM_NS_MESSAGE_NEW'),
						"PUSH" => 'Y',
						"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP)
					),
					"chat" => Array(
						"NAME" => GetMessage('IM_NS_CHAT_NEW'),
						"MAIL" => 'N',
						"PUSH" => 'Y',
						"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_MAIL)
					),
					"openChat" => Array(
						"NAME" => GetMessage('IM_NS_OPEN_NEW'),
						"MAIL" => 'N',
						"PUSH" => 'Y',
						"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_MAIL)
					),
					"like" => Array(
						"NAME" => GetMessage('IM_NS_LIKE'),
					),
					"mention" => Array(
						"NAME" => GetMessage('IM_NS_MENTION_2'),
						"PUSH" => 'Y',
					),
					"default" => Array(
						"NAME" => GetMessage('IM_NS_DEFAULT'),
						"PUSH" => 'N',
						"MAIL" => 'N',
					),
				)
			)
		);

		if (!IsModuleInstalled("b24network"))
		{
			$config["main"] = array(
				"NAME" => GetMessage('IM_NS_MAIN'),
				"NOTIFY" => Array(
					"rating_vote" => Array(
						"NAME" => GetMessage('IM_NS_MAIN_RATING_VOTE'),
						"LIFETIME" => 86400*7
					),
					"rating_vote_mentioned" => Array(
						"NAME" => GetMessage('IM_NS_MAIN_RATING_VOTE_MENTIONED'),
						"LIFETIME" => 86400*7
					),
				),
			);
		}

		return $config;
	}
}