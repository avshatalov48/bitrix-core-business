<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\EventService\Push;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;

class PushEventDictionary
{
	public const EVENT_WORKGROUP_ADD = 'workgroup_add';
	public const EVENT_WORKGROUP_BEFORE_UPDATE = 'workgroup_before_update';
	public const EVENT_WORKGROUP_UPDATE = 'workgroup_update';
	public const EVENT_WORKGROUP_DELETE = 'workgroup_delete';
	public const EVENT_WORKGROUP_USER_ADD = 'workgroup_user_add';
	public const EVENT_WORKGROUP_USER_UPDATE = 'workgroup_user_update';
	public const EVENT_WORKGROUP_USER_DELETE = 'workgroup_user_delete';
	public const EVENT_WORKGROUP_FAVORITES_CHANGED = 'workgroup_favorites_changed';
	public const EVENT_WORKGROUP_PIN_CHANGED = 'workgroup_pin_changed';
	public const EVENT_WORKGROUP_SUBSCRIBE_CHANGED = 'workgroup_subscribe_changed';
	public const EVENT_SPACE_USER_ROLE_CHANGE = 'space_user_role_change';
	public const EVENT_SPACE_RECENT_ACTIVITY_UPDATE = 'recent_activity_update';
	public const EVENT_SPACE_RECENT_ACTIVITY_DELETE = 'recent_activity_delete';
	public const EVENT_SPACE_RECENT_ACTIVITY_REMOVE_FROM_SPACE = 'recent_activity_remove_from_space';
	public const EVENT_SPACE_FEATURE_CHANGE = 'space_feature_change';

	public static function getPushEventType(string $eventType): ?string
	{
		$result = null;

		switch ($eventType)
		{
			case EventDictionary::EVENT_WORKGROUP_ADD:
				$result = self::EVENT_WORKGROUP_ADD;
				break;
			case EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE:
				$result = self::EVENT_WORKGROUP_BEFORE_UPDATE;
				break;
			case EventDictionary::EVENT_WORKGROUP_UPDATE:
				$result = self::EVENT_WORKGROUP_UPDATE;
				break;
			case EventDictionary::EVENT_WORKGROUP_DELETE:
				$result = self::EVENT_WORKGROUP_DELETE;
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
				$result = self::EVENT_WORKGROUP_USER_ADD;
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
				$result = self::EVENT_WORKGROUP_USER_UPDATE;
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$result = self::EVENT_WORKGROUP_USER_DELETE;
				break;
			case EventDictionary::EVENT_WORKGROUP_FAVORITES_CHANGED:
				$result = self::EVENT_WORKGROUP_FAVORITES_CHANGED;
				break;
			case EventDictionary::EVENT_WORKGROUP_PIN_CHANGED:
				$result = self::EVENT_WORKGROUP_PIN_CHANGED;
				break;
			case EventDictionary::EVENT_WORKGROUP_SUBSCRIBE_CHANGED:
				$result = self::EVENT_WORKGROUP_SUBSCRIBE_CHANGED;
				break;
			case EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE:
				$result = self::EVENT_SPACE_USER_ROLE_CHANGE;
				break;
			default:
		}

		return $result;
	}
}
