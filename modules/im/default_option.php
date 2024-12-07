<?php
$im_default_option = array(
	'notify_flash_engine_type' => 'cache',
	'general_chat_id' => 0,
	'general_chat_message_join' => true,
	'general_chat_message_leave' => false,
	'allow_send_to_general_chat_all' => 'Y',
	'allow_send_to_general_chat_rights' => 'AU',
	'call_server' => 'N',
	'turn_server_self' => 'N',
	'turn_server' => 'turn.calls.bitrix24.com',
	'turn_server_firefox' => '54.217.240.163',
	'turn_server_login' => 'bitrix',
	'turn_server_password' => 'bitrix',
	'turn_server_max_users' => 4,
	'call_server_enabled' => true,
	'call_server_max_users' => 100,
	'open_chat_enable' => IsModuleInstalled('intranet')? true: false,
	'color_enable' => true,
	'correct_text' => false,
	'view_offline' => true,
	'view_group' => true,
	'send_by_enter' => true,
	'panel_position_horizontal' => 'right',
	'panel_position_vertical' => 'bottom',
	'load_last_message' => true,
	'load_last_notify' => true,
	'privacy_message' => 'all',
	'privacy_chat' => IsModuleInstalled('intranet')? 'all': 'contact',
	'privacy_call' => IsModuleInstalled('intranet')? 'all': 'contact',
	'start_chat_message' => IsModuleInstalled('intranet')? 'last': 'first',
	'privacy_search' => 'all',
	'privacy_profile' => 'all',
	'chat_extend_show_history' => true,
	'disk_storage_id' => 0,
	'disk_folder_avatar_id' => 0,
	'contact_list_birthday' => 'all',
	'contact_list_load' => true,
	'contact_list_show_all_bus' => false,
	'path_to_user_profile' => (!IsModuleInstalled("intranet") ? '/club/user/#user_id#/' : ''),
	'message_history_index' => false,
	'call_log_service' => '',
	'call_log_secret' => '',
	'call_server_url' => '',
);

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/im_options.php"))
{
	$additionalOptions = include($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/im_options.php");
	if (is_array($additionalOptions))
	{
		$im_default_option = array_merge($im_default_option, $additionalOptions);
	}
}