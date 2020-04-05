<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => Array(
		'/bitrix/js/pull/client/pull.client.js',
	),
	'skip_core' => true,
	'oninit' => function()
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return array();
		}
		return array(
			'lang_additional' => array(
				'pull_server_enabled' => \CPullOptions::GetQueueServerStatus() ? 'Y' : 'N',
				'pull_config_timestamp' => \CPullOptions::GetConfigTimestamp(),
			)
		);
	},
	'rel' => array('pull.protobuf', 'rest.client', 'promise')
);