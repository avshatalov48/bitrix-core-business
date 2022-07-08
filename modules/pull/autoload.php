<?php

CModule::AddAutoloadClasses(
	"pull",
	[
		"CPullChannel" => "classes/general/pull_channel.php",
		"CPullStack" => "classes/mysql/pull_stack.php",
		"CPullWatch" => "classes/mysql/pull_watch.php",
		"CPullOptions" => "classes/general/pull_options.php",
		"CPullTableSchema" => "classes/general/pull_table_schema.php",

		"CPushDescription" => "classes/general/pushservices/services_descriptions.php",
		"CPullPush" => "classes/general/pull_push.php",
		"CPushMessage" => "classes/general/pushservices/push_message.php",
		"CPushService" => "classes/general/pushservices/push_service.php",
		"CPushManager" => "classes/general/pull_push.php",
		"CAppleMessage" => "classes/general/pushservices/apple_push.php",
		"CApplePush" => "classes/general/pushservices/apple_push.php",
		"CApplePushVoip" => "classes/general/pushservices/apple_push.php",
		"CGoogleMessage" => "classes/general/pushservices/google_push.php",
		"CGooglePush" => "classes/general/pushservices/google_push.php",
		"CHuaweiPushKitService" => "classes/general/pushservices/huawei_push.php",
		"CHuaweiPushKitMessage" => "classes/general/pushservices/huawei_push.php",
		"CGooglePushInteractive" => "classes/general/pushservices/google_push.php",

		"\\Bitrix\\Pull\\PushTable" => "lib/model/pushtable.php",
		"\\Bitrix\\Pull\\ChannelTable" => "lib/model/channeltable.php",
	]
);

\Bitrix\Pull\Loader::register();