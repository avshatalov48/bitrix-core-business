<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}


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
		"CPushManager" => "classes/general/pull_push.php",
		"CGooglePushInteractive" => "classes/general/pushservices/google_push.php",

		"\\Bitrix\\Pull\\PushTable" => "lib/model/pushtable.php",
		"\\Bitrix\\Pull\\ChannelTable" => "lib/model/channeltable.php",
	]
);

\Bitrix\Pull\Loader::register();