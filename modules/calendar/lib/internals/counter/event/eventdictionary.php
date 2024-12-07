<?php

namespace Bitrix\Calendar\Internals\Counter\Event;

class EventDictionary
{
	public const EVENT_ATTENDEES_UPDATED = 'onEventAttendeesUpdated';
	public const SYNC_CHANGED = 'onSyncChanged';
	public const COUNTERS_UPDATE = 'onCountersUpdate';
	public const OPEN_EVENT_CREATED = 'onOpenEventCreated';
	public const OPEN_EVENT_DELETED = 'onOpenEventDeleted';
	public const OPEN_EVENT_SEEN = 'onOpenEventSeen';

	public const OPEN_EVENT_SCORER_UPDATED = 'openEventScorerUpdated';
}