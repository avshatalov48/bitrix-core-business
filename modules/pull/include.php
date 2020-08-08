<?
IncludeModuleLangFile(__FILE__);

define("PULL_REVISION_WEB", 19);
define("PULL_REVISION_MOBILE", 3);

global $APPLICATION, $DBType;

CModule::AddAutoloadClasses(
	"pull",
	array(
		"CPullChannel" => "classes/general/pull_channel.php",
		"CPullStack" => "classes/".$DBType."/pull_stack.php",
		"CPullWatch" => "classes/".$DBType."/pull_watch.php",
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
		"CGooglePushInteractive" => "classes/general/pushservices/google_push.php",

		"\\Bitrix\\Pull\\PushTable" => "lib/model/push.php",
		"\\Bitrix\\Pull\\ChannelTable" => "lib/model/channel.php",
	)
);

CJSCore::RegisterExt('pull', array(
	'skip_core' => true,
	'rel' => array('pull.client')
));

\Bitrix\Pull\Loader::register();