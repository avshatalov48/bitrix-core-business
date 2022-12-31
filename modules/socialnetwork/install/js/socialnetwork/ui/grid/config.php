<?php

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Tasks\Internals\Counter\Push\PushSender;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$langAdditional = [
	'PUSH_EVENT_MAIN_USER_COUNTER' => 'user_counter',
	'PUSH_EVENT_WORKGROUP_ADD' => PushEventDictionary::EVENT_WORKGROUP_ADD,
	'PUSH_EVENT_WORKGROUP_BEFORE_UPDATE' => PushEventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE,
	'PUSH_EVENT_WORKGROUP_UPDATE' => PushEventDictionary::EVENT_WORKGROUP_UPDATE,
	'PUSH_EVENT_WORKGROUP_DELETE' => PushEventDictionary::EVENT_WORKGROUP_DELETE,
	'PUSH_EVENT_WORKGROUP_USER_ADD' => PushEventDictionary::EVENT_WORKGROUP_USER_ADD,
	'PUSH_EVENT_WORKGROUP_USER_UPDATE' => PushEventDictionary::EVENT_WORKGROUP_USER_UPDATE,
	'PUSH_EVENT_WORKGROUP_USER_DELETE' => PushEventDictionary::EVENT_WORKGROUP_USER_DELETE,
	'PUSH_EVENT_WORKGROUP_FAVORITES_CHANGED' => PushEventDictionary::EVENT_WORKGROUP_FAVORITES_CHANGED,
	'PUSH_EVENT_WORKGROUP_PIN_CHANGED' => PushEventDictionary::EVENT_WORKGROUP_PIN_CHANGED,
];

if (Loader::includeModule('tasks'))
{
	$langAdditional['PUSH_EVENT_TASKS_PROJECT_COUNTER'] = PushSender::COMMAND_PROJECT;
	$langAdditional['PUSH_EVENT_TASKS_USER_COUNTER'] = PushSender::COMMAND_USER;
	$langAdditional['PUSH_EVENT_TASKS_PROJECT_READ_ALL'] = 'project_read_all';
	$langAdditional['PUSH_EVENT_TASKS_SCRUM_READ_ALL'] = 'scrum_read_all';
	$langAdditional['PUSH_EVENT_TASKS_COMMENT_READ_ALL'] = 'comment_read_all';
}

return [
	'rel' => [
		'popup',
		'ui.label',
		'ui.notification',
	],
	'js' => './dist/grid.bundle.js',
	'css' => './dist/grid.bundle.css',
	'lang_additional' => $langAdditional,
];
