<?php

namespace Bitrix\Im\Call;

/**
 * Class VideoStrategyType
 * @package BX\Im\Call
 * @see BX.Call.VideoStrategy.Type
 */
class VideoStrategyType {
	public const ALLOW_ALL = 'AllowAll';
	public const ALLOW_NONE = 'AllowNone';
	public const ONLY_SPEAKER = 'OnlySpeaker';
	public const CURRENTLY_TALKING = 'CurrentlyTalking';

	public static function getList()
	{
		return [static::ALLOW_ALL, static::ALLOW_NONE, static::ONLY_SPEAKER, static::CURRENTLY_TALKING];
	}
}