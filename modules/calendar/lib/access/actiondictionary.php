<?php

namespace Bitrix\Calendar\Access;

class ActionDictionary
{
	public const
		ACTION_SECTION_ACCESS = 'section_access',
		ACTION_SECTION_EDIT = 'section_edit',
		ACTION_SECTION_ADD = 'section_add',
		ACTION_SECTION_EVENT_VIEW_FULL = 'section_event_view_full',
		ACTION_SECTION_EVENT_VIEW_COMMENTS = 'section_event_view_comments',
		ACTION_SECTION_EVENT_VIEW_TIME = 'section_event_view_time',
		ACTION_SECTION_EVENT_VIEW_TITLE = 'section_event_view_title',

		ACTION_EVENT_ADD = 'event_add',
		ACTION_EVENT_EDIT = 'event_edit',
		ACTION_EVENT_DELETE = 'event_delete',
		ACTION_EVENT_VIEW_FULL = 'event_view_full',
		ACTION_EVENT_VIEW_COMMENTS = 'event_view_comments',
		ACTION_EVENT_VIEW_TIME = 'event_view_time',
		ACTION_EVENT_VIEW_TITLE = 'event_view_title',
		ACTION_EVENT_EDIT_ATTENDEES = 'event_edit_attendees',
		ACTION_EVENT_EDIT_LOCATION = 'event_edit_location',

		ACTION_TYPE_ACCESS = 'type_access',
		ACTION_TYPE_EDIT = 'type_edit',
		ACTION_TYPE_VIEW = 'type_view';

	public static function getOldActionKeysMap(): array
	{
		return [
			self::ACTION_SECTION_ACCESS => \CCalendarSect::OPERATION_EDIT_ACCESS,
			self::ACTION_SECTION_EDIT => \CCalendarSect::OPERATION_EDIT_SECTION,

			self::ACTION_EVENT_ADD => \CCalendarSect::OPERATION_ADD,
			self::ACTION_EVENT_EDIT => \CCalendarSect::OPERATION_EDIT,
			self::ACTION_SECTION_EVENT_VIEW_FULL => \CCalendarSect::OPERATION_VIEW_FULL,
			self::ACTION_SECTION_EVENT_VIEW_TIME => \CCalendarSect::OPERATION_VIEW_TIME,
			self::ACTION_SECTION_EVENT_VIEW_TITLE => \CCalendarSect::OPERATION_VIEW_TITLE,

			self::ACTION_TYPE_ACCESS => \CCalendarType::OPERATION_EDIT_ACCESS,
			self::ACTION_TYPE_EDIT => \CCalendarType::OPERATION_EDIT,
			self::ACTION_TYPE_VIEW => \CCalendarType::OPERATION_VIEW,
		];
	}

	public static function getOldActionKeyByNewActionKey(string $actionId)
	{
		$actionMap = self::getOldActionKeysMap();
		if (array_key_exists($actionId, $actionMap))
		{
			return $actionMap[$actionId];
		}
		return null;
	}
}