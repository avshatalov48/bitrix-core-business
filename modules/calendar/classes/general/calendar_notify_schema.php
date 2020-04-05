<?
IncludeModuleLangFile(__FILE__);

class CCalendarNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return array(
			"calendar" => array(
				"invite" => Array(
					"NAME" => GetMessage('EC_NS_INVITE'),
					"SITE" => "Y",
					"MAIL" => "N",
					"PUSH" => "Y",
					"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE)
				),
				"reminder" => Array(
					"NAME" => GetMessage('EC_NS_REMINDER'),
					"SITE" => "Y",
					"MAIL" => "N",
					"PUSH" => "Y"
				),
				"change" => Array(
					"NAME" => GetMessage('EC_NS_CHANGE'),
					"SITE" => "Y",
					"MAIL" => "N"
				),
				"info" => Array(
					"NAME" => GetMessage('EC_NS_INFO'),
					"SITE" => "Y",
					"MAIL" => "N"
				),
				"event_comment" => Array(
					"NAME" => GetMessage('EC_NS_EVENT_COMMENT'),
					"SITE" => "Y",
					"MAIL" => "N"
				)
			)
		);
	}
}

class CCalendarPullSchema
{
	public static function OnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "calendar",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}