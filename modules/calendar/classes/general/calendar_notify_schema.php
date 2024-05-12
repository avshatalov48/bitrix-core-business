<?

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CCalendarNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return [
			"calendar" => [
				"invite" => [
					"NAME" => Loc::getMessage('EC_NS_INVITE'),
					"SITE" => "Y",
					"MAIL" => "N",
					"PUSH" => "Y",
					"DISABLED" => [IM_NOTIFY_FEATURE_SITE]
				],
				"reminder" => [
					"NAME" => Loc::getMessage('EC_NS_REMINDER'),
					"SITE" => "Y",
					"MAIL" => "N",
					"PUSH" => "Y"
				],
				"change" => [
					"NAME" => Loc::getMessage('EC_NS_CHANGE'),
					"SITE" => "Y",
					"MAIL" => "N"
				],
				"info" => [
					"NAME" => Loc::getMessage('EC_NS_INFO_MSGVER_1'),
					"SITE" => "Y",
					"MAIL" => "N"
				],
				"event_comment" => [
					"NAME" => Loc::getMessage('EC_NS_EVENT_COMMENT'),
					"SITE" => "Y",
					"MAIL" => "N"
				],
				"delete_location" => [
					"NAME" => Loc::getMessage('EC_NS_DELETE_LOCATION_MSGVER_1'),
					"SITE" => "Y",
					"MAIL" => "N"
				]
			]
		];
	}
}

class CCalendarPullSchema
{
	public static function OnGetDependentModule()
	{
		return [
			'MODULE_ID' => "calendar",
			'USE' => ["PUBLIC_SECTION"]
		];
	}
}