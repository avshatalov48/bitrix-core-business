<?php

use Bitrix\Main\Application;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $USER;

$initParams = function () {
	if (\Bitrix\Main\Loader::includeModule('pull'))
	{
		return [
			'lang_additional' => [
				'pull_server_enabled' => \CPullOptions::GetQueueServerStatus() ? 'Y' : 'N',
				'pull_config_timestamp' => \CPullOptions::GetConfigTimestamp(),
				'shared_worker_allowed' => CPullOptions::IsSharedWorkerAllowed() ? 'Y' : 'N',
				'pull_guest_mode' => defined('PULL_USER_ID') ? 'Y' : 'N',
				'pull_guest_user_id' => defined('PULL_USER_ID') ? (int)PULL_USER_ID : 0,
				'pull_worker_mtime' => filemtime(Application::getDocumentRoot() . '/bitrix/js/pull/worker/dist/pull.worker.bundle.js'),
			],
		];
	}
	else
	{
		return [
			'lang_additional' => [
				'pull_server_enabled' => 'N',
				'pull_config_timestamp' => 0,
				'shared_worker_allowed' => 'N',
				'pull_guest_mode' => 'N',
				'pull_guest_user_id' => 0,
			],
		];
	}
};

$allowNewPullClient = (function (int $currentUserId = 0) {
	if (defined('PULL_NEW_CLIENT_ENABLE') && PULL_NEW_CLIENT_ENABLE)
	{
		return true;
	}

	$allowedUsers = explode(";", \Bitrix\Main\Config\Option::get("pull", "new_client_users", ""));
	$allowedUsers = array_filter(array_map(fn($token) => (int)$token, $allowedUsers));

	return $currentUserId > 0 && in_array($currentUserId, $allowedUsers, true);
})(isset($USER) ? (int)$USER->getId() : 0);

if ($allowNewPullClient)
{
	return [
		'js' => [
			'./dist/pull.client.js',
		],
		'rel' => [
			'main.polyfill.core',
			'pull.util',
			'pull.connector',
			'pull.configholder',
		],
		'skip_core' => true,
		'oninit' => $initParams,
	];
}
else
{
	return [
		'js' => [
			'./pull.client.js',
		],
		'skip_core' => true,
		'oninit' => $initParams,
		'rel' => ['pull.protobuf', 'rest.client', 'promise'],
	];
}
