<?php

namespace Bitrix\Socialnetwork\Space\List;

final class Dictionary
{
	public const FILTER_MODES = [
		'all' => 'all',
		'my' => 'my',
		'other' => 'other',
	];

	public const SPACE_VISIBILITY_TYPES = [
		'open' => 'open',
		'closed' => 'closed',
		'secret' => 'secret',
	];

	public const USER_ROLES = [
		'nonMember' => 'nonMember',
		'applicant' => 'applicant',
		'invited' => 'invited',
		'member' => 'member',
	];

	public const
		FEATURE_GENERAL = 'general',
		FEATURE_DISCUSSIONS = 'discussions',
		FEATURE_TASKS = 'tasks',
		FEATURE_CALENDAR = 'calendar',
		FEATURE_FILES = 'files';

	public const AVAILABLE_FEATURES = [
		self::FEATURE_DISCUSSIONS => true,
		self::FEATURE_TASKS => true,
		self::FEATURE_CALENDAR => true,
		self::FEATURE_FILES => true,
	];

	public const SPACE_LIST_STATES = [
		'default' => 'default',
		'collapsed' => 'collapsed',
	];
}
