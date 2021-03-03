<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup;

use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class Channel implements Fields\IField
{
	const CHANNEL_COMMERCE_OFFSITE = "COMMERCE_OFFSITE";
	const CHANNEL_MARKETPLACE = "MARKETPLACE";
	const CHANNEL_COMMERCE = "COMMERCE";
	const CHANNEL_SIGNALS = "SIGNALS";
	const CHANNEL_FEED  = "FEED";

	/**
	 * @return null
	 */
	static function getDefaultValue()
	{
		return null;
	}

	/**
	 * check value
	 * @param $value
	 *
	 * @return bool
	 */
	static function checkValue($value): bool
	{
		return is_string($value) && in_array($value,[
			static::CHANNEL_COMMERCE,
			static::CHANNEL_COMMERCE_OFFSITE,
			static::CHANNEL_FEED,
			static::CHANNEL_MARKETPLACE,
			static::CHANNEL_SIGNALS
		]);

	}

	/**
	 * @inheritDoc
	 */
	static function available(): bool
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	static function required(): bool
	{
		return false;
	}
}