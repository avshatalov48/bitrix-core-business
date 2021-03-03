<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup;

use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class BusinessType implements Fields\IField
{

	const BUSINESS_VERTICAL_ECOMMERCE = "ECOMMERCE";
	const BUSINESS_VERTICAL_SERVICES = "SERVICES";

	/**
	 * get default value
	 * @return string
	 */
	static function getDefaultValue()
	{
		return static::BUSINESS_VERTICAL_ECOMMERCE;

	}

	/**
	 * get available values
	 * @return string[]
	 */
	protected static function getAvailableValues(): array
	{
		return [static::BUSINESS_VERTICAL_ECOMMERCE, static::BUSINESS_VERTICAL_SERVICES];
	}

	/**
	 * check type
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