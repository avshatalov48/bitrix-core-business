<?php

namespace Bitrix\MessageService\Providers\Edna\Constants;

/**
 * Class
 * @see https://docs.edna.ru/kb/api-sending-messages/
 */
class ContentType
{
	public const TEXT = 'TEXT';
	public const IMAGE = 'IMAGE';
	public const DOCUMENT = 'DOCUMENT';
	public const VIDEO = 'VIDEO';
	public const AUDIO = 'AUDIO';
	public const BUTTON = 'BUTTON';
	public const LOCATION = 'LOCATION';
	public const LIST_PICKER = 'LIST_PICKER';

	public static function getAllTypeList(): array
	{
		return [
			self::TEXT,
			self::IMAGE,
			self::DOCUMENT,
			self::VIDEO,
			self::AUDIO,
			self::BUTTON,
			self::LOCATION,
			self::LIST_PICKER,
		];
	}
}