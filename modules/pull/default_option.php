<?php
$temporary = array(
	'path_to_listener' => "http://#DOMAIN#/bitrix/sub/",
	'path_to_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
	'path_to_modern_listener' => "http://#DOMAIN#/bitrix/sub/",
	'path_to_modern_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
	'path_to_mobile_listener' => "http://#DOMAIN#:8893/bitrix/sub/",
	'path_to_mobile_listener_secure' => "https://#DOMAIN#:8894/bitrix/sub/",
	'path_to_websocket' => "ws://#DOMAIN#/bitrix/subws/",
	'path_to_websocket_secure' => "wss://#DOMAIN#/bitrix/subws/",
	'path_to_publish' => 'http://127.0.0.1:8895/bitrix/pub/',
	'path_to_publish_web' => 'http://#DOMAIN#/bitrix/pubweb/',
	'path_to_publish_web_secure' => 'https://#DOMAIN#/bitrix/pubweb/',
	'path_to_json_rpc' => 'https://#DOMAIN#/bitrix/api/',
	'nginx_version' => 2,
	'nginx_command_per_hit' => 100,
	'nginx' => 'N',
	'nginx_headers' => 'Y',
	'push' => 'N',
	'push_message_per_hit' => 100,
	'push_service_url' => "https://cloud-messaging.bitrix24.com/send/",
	'websocket' => 'Y',
	'signature_key' => '',
	'signature_algo' => 'sha1',
	'guest' => 'N',
	'enable_protobuf' => 'Y',
	'limit_max_payload' => 1048576,
	'limit_max_messages_per_request' => 100,
	'limit_max_channels_per_request' => 100,
	'config_timestamp' => 0,
	'server_mode' => 'personal',
	'config_ttl' => 0, // in seconds
);

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/pull.php"))
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/pull.php");
	if (isset($pull_default_option))
	{
		$temporary = array_merge($temporary, $pull_default_option);
	}
}
$pull_default_option = $temporary;
unset($temporary);

