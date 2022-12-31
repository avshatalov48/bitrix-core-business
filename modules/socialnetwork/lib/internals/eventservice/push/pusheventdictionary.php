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
			default:
		}

		return $result;
	}
}
