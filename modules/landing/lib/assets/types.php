<?php

namespace Bitrix\Landing\Assets;

class Types
{
	public const KEY_RELATIVE = 'rel';
	public const TYPE_CSS = 'css';
	public const TYPE_JS = 'js';
	public const TYPE_LANG = 'lang';
	public const TYPE_LANG_ADDITIONAL = 'lang_additional';
	public const TYPE_FONT = 'font';

	/**
	 * Asset may use include.php, but we can overwriting them, or add unique extensions
	 *
	 * @return array
	 */
	public static function getAssetTypes(): array
	{
		return [
			self::KEY_RELATIVE,
			self::TYPE_LANG,
			self::TYPE_JS,
			self::TYPE_CSS,
			self::TYPE_FONT,
		];
	}
}