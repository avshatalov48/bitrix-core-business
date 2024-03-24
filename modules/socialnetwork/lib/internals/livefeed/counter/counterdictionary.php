<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;

class CounterDictionary
{
	public const LEGACY_COUNTER_ENABLED = 'sonet_use_legacy_counter'; // completely disables an old counter which uses the table: b_user_counter
	public const COUNTER_ENABLED_FOR_USER = 'sonet_use_new_counter_for_user';
	public const PREFIX = 'sonet_';
	public const META_PROP_ALL = 'all';
	public const META_PROP_GROUP = 'group';
	public const META_PROP_PROJECT = 'project';
	public const META_PROP_SONET = 'sonet';
	public const META_PROP_SCRUM = 'scrum';
	public const META_PROP_NONE = 'none';

	public const LEFT_MENU_SONET = 'sonet_total';

	public const
		COUNTER_TOTAL							= 'total',
		COUNTER_GROUP_TOTAL						= 'group_total',

		COUNTER_NEW_POSTS						= 'new_posts',
		COUNTER_NEW_COMMENTS					= 'new_comments',

		COUNTER_FLAG_COUNTED					= 'flag_computed_20210501',
		COUNTER_FLAG_CLEARED					= 'flag_cleared';

	public const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL,
		//EventDictionary::EVENT_SPACE_LIVEFEED_POST_VIEW,
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL,
		EventDictionary::EVENT_SPACE_LIVEFEED_READ_ALL,
	];
}