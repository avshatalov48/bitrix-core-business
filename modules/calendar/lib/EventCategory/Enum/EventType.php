<?php

namespace Bitrix\Calendar\EventCategory\Enum;

final class EventType
{
	public const AFTER_EVENT_CATEGORY_CREATE = 'afterEventCategoryCreate';
	public const AFTER_EVENT_CATEGORY_UPDATE = 'afterEventCategoryUpdate';
	public const AFTER_EVENT_CATEGORY_DELETE = 'afterEventCategoryDelete';
	public const AFTER_EVENT_CATEGORY_ATTENDEES_DELETE = 'afterEventCategoryAttendeesDelete';
}
