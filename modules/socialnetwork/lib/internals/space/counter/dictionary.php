<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;

class Dictionary
{
	public const LEFT_MENU_SPACES = 'spaces';
	public const COUNTERS_WORKGROUP_TOTAL = 'countersWorkGroupRequestTotal';
	public const COUNTERS_WORKGROUP_REQUEST_OUT = 'countersWorkGroupRequestOut';
	public const COUNTERS_TASKS_TOTAL = 'countersTasksTotal';
	public const COUNTERS_CALENDAR_TOTAL = 'countersCalendarTotal';
	public const COUNTERS_LIVEFEED_TOTAL = 'countersLiveFeedTotal';

	public const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL,
		//EventDictionary::EVENT_SPACE_LIVEFEED_POST_VIEW,
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL,
		EventDictionary::EVENT_SPACE_LIVEFEED_READ_ALL,
	];
}