<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup;

use \DateTimeZone;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class Timezone implements Fields\IField, Fields\IAvailableFieldList
{
	/**
	 * get default timezone
	 * @return string
	 * @throws \Bitrix\Main\ObjectException
	 */
	static function getDefaultValue()
	{
		return (new DateTime)->getTimeZone()->getName();
	}

	/**
	 * get available timezones
	 * @return array
	 */
	static function getAvailableValues(): array
	{
		return DateTimeZone::listIdentifiers();

	}

	/**
	 * check timezone value
	 * @param $value
	 *
	 * @return bool
	 */
	static function checkValue($value): bool
	{
		return isset($value) && in_array($value,static::getAvailableValues());
	}

	/**
	 * @inheritDoc
	 */
	static function available(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	static function required(): bool
	{
		return true;
	}
}