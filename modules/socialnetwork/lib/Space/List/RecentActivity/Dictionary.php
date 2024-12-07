<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

class Dictionary
{
	const ENTITY_TYPE = [
		'livefeed' => 'livefeed',
		'task' => 'task',
		'calendar' => 'calendar',
		'membership' => 'membership',
		'livefeed_comment' => 'livefeed_comment',
		'task_comment' => 'task_comment',
		'calendar_comment' => 'calendar_comment',
	];

	const COMMON_TO_COMMENT_ENTITY_TYPE = [
		self::ENTITY_TYPE['livefeed'] => self::ENTITY_TYPE['livefeed_comment'],
		self::ENTITY_TYPE['task'] => self::ENTITY_TYPE['task_comment'],
		self::ENTITY_TYPE['calendar'] => self::ENTITY_TYPE['calendar_comment'],
	];
}