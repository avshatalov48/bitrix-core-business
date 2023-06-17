<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/call.bundle.js',
	],
	'css' => [
		'./dist/call.bundle.css',
	],
	'rel' => [
		'im.lib.utils',
		'ui.switcher',
		'ui.dialogs.messagebox',
		'ui.buttons',
		'main.core.events',
		'main.popup',
		'main.core',
		'loader',
		'resize_observer',
		'webrtc_adapter',
		'im.lib.localstorage',
		'ui.hint',
		'voximplant',
	],
	'oninit' => function () {
		return [
			'lang_additional' => [
				'turn_server' => COption::GetOptionString('im', 'turn_server'),
				'turn_server_firefox' => COption::GetOptionString('im', 'turn_server_firefox'),
				'turn_server_login' => COption::GetOptionString('im', 'turn_server_login'),
				'turn_server_password' => COption::GetOptionString('im', 'turn_server_password'),
				'turn_server_max_users' => \Bitrix\Main\Config\Option::get('im', 'turn_server_max_users'),
				'call_server_enabled' => \Bitrix\Im\Call\Call::isCallServerEnabled() ? 'Y' : 'N',
				'call_server_max_users' => \Bitrix\Main\Config\Option::get('im', 'call_server_max_users'),
				'call_log_service' => \Bitrix\Im\Call\Call::getLogService(),
				'call_collect_stats' => COption::GetOptionString('im', 'collect_call_stats', 'N'),
				'call_docs_status' => \Bitrix\Im\Integration\Disk\Documents::getDocumentsInCallStatus(),
				'call_resumes_status' => \Bitrix\Im\Integration\Disk\Documents::getResumesOfCallStatus(),
				'jitsi_server' => COption::GetOptionString('im', 'jitsi_server'),
			],
		];
	},
	'skip_core' => false,
];
