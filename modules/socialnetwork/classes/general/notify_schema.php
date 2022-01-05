<?php

IncludeModuleLangFile(__FILE__);

class CSocNetNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		$arResult = array(
			"socialnetwork" => array(
				"invite_group" => Array(
					"NAME" => GetMessage("SONET_NS_INVITE_GROUP_INFO"),
					"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE)
				),
				"invite_group_btn" => Array(
					"NAME" => GetMessage("SONET_NS_INVITE_GROUP_BTN"),
					"DISABLED" => [ IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_MAIL, IM_NOTIFY_FEATURE_PUSH ],
				),
				"inout_group" => Array(
					"NAME" => GetMessage("SONET_NS_INOUT_GROUP")
				),
				"moderators_group" => Array(
					"NAME" => GetMessage("SONET_NS_MODERATORS_GROUP")
				),
				"owner_group" => Array(
					"NAME" => GetMessage("SONET_NS_OWNER_GROUP")
				),
				"sonet_group_event" => Array(
					"NAME" => GetMessage("SONET_NS_SONET_GROUP_EVENT"),
					"PUSH" => 'Y'
				),
			),
		);

		if (CSocNetUser::IsFriendsAllowed())
		{
			$arResult["socialnetwork"]["inout_user"] = Array(
				"NAME" => GetMessage("SONET_NS_FRIEND")
			);
		}

		return $arResult;
	}
}

class CSocNetPullSchema
{
	public static function OnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "socialnetwork",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}
