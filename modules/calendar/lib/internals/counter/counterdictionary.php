<?php

namespace Bitrix\Calendar\Internals\Counter;

/**
 * Class CounterDictionary
 *
 * @package Bitrix\Calendar\Internals\Counter
 */
class CounterDictionary
{
	// menu
	public const COUNTER_TOTAL = 'calendar';
	public const COUNTER_MY = 'calendar_my';
	public const COUNTER_INVITES = 'calendar_invites';
	public const COUNTER_SYNC_ERRORS = 'calendar_sync_errors';
	public const COUNTER_OPEN_EVENTS = 'calendar_open_events';
	public const COUNTER_GROUP_INVITES = 'calendar_group_invites';
	public const COUNTER_GROUP_INVITES_TPL = 'calendar_group_invites_%d';

	public const COUNTER_NEW_EVENT = 'new_event';

	// scorers
	public const SCORER_OPEN_EVENT = 'open_event';

	// meta props
	public const META_PROP_ALL = 'meta_all';
	public const META_PROP_OPEN_EVENTS = 'meta_open_events';
	public const META_PROP_NEW_EVENTS = 'meta_new_events';
}
