<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => Array(
		'./pull.client.js',
	),
	'skip_core' => true,
	'oninit' => function()
	{
		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			return array(
				'lang_additional' => array(
					'pull_server_enabled' => \CPullOptions::GetQueueServerStatus() ? 'Y' : 'N',
					'pull_config_timestamp' => \CPullOptions::GetConfigTimestamp(),
					'pull_guest_mode' => defined('PULL_USER_ID') ? 'Y' : 'N',
					'pull_guest_user_id' => defined('PULL_USER_ID') ? (int)PULL_USER_ID : 0
				)
			);
		}
		else
		{
			return array(
				'lang_additional' => array(
					'pull_server_enabled' => 'N',
					'pull_config_timestamp' => 0,
					'pull_guest_mode' => 'N',
					'pull_guest_user_id' => 0
				)
			);
		}
	},
	'rel' => array('pull.protobuf', 'rest.client', 'promise')
);