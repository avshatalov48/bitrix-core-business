<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\EventService;

class EventDictionary
{
	public const EVENT_WORKGROUP_ADD = 'onWorkgroupAdd';
	public const EVENT_WORKGROUP_BEFORE_UPDATE = 'onWorkgroupBeforeUpdate';
	public const EVENT_WORKGROUP_UPDATE = 'onWorkgroupUpdate';
	public const EVENT_WORKGROUP_DELETE = 'onWorkgroupDelete';
	public const EVENT_WORKGROUP_USER_ADD = 'onWorkgroupUserAdd';
	public const EVENT_WORKGROUP_USER_UPDATE = 'onWorkgroupUserUpdate';
	public const EVENT_WORKGROUP_USER_DELETE = 'onWorkgroupUserDelete';
	public const EVENT_WORKGROUP_FAVORITES_CHANGED = 'onWorkgroupFavoritesChanged';
	public const EVENT_WORKGROUP_PIN_CHANGED = 'onWorkgroupPinChanged';
}
