<?php

namespace Bitrix\Calendar\Event\Enum;

final class EventType
{
	public const AFTER_CALENDAR_EVENT_CREATED = 'afterCalendarEventCreated';
	public const AFTER_CALENDAR_EVENT_EDITED = 'afterCalendarEventEdited';
	public const AFTER_CALENDAR_EVENT_DELETED = 'afterCalendarEventDeleted';
	public const AFTER_OPEN_EVENT_CREATED = 'afterOpenEventCreated';
	public const AFTER_OPEN_EVENT_EDITED = 'afterOpenEventEdited';
	public const AFTER_OPEN_EVENT_DELETED = 'afterOpenEventDeleted';
}