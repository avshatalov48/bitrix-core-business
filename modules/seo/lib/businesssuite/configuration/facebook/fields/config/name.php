<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

use Bitrix\Main\Config;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class Name implements IConfigField
{
	/**
	 * get default value
	 * @return mixed|string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	static function getDefaultValue()
	{
		return ['name' => Config\Option::get('main','site_name',null) ?? (defined('SITE_SERVER_NAME')? SITE_SERVER_NAME : '')];
	}

	/**
	 * check name
	 * @param $value
	 *
	 * @return bool
	 */
	static function checkValue($value): bool
	{
		return is_array($value) && array_key_exists('name',$value) && is_string($value['name']);
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

	public static function prepareValue($value)
	{
		return $value;

	}
}