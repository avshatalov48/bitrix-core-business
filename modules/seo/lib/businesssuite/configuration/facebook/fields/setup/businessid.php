<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup;


use Bitrix\Main\SystemException;
use Bitrix\Seo\Service;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class BusinessId implements Fields\IField
{
	/**
	 * get engine
	 * @return mixed
	 */
	static function getDefaultValue()
	{
		if (!Service::isRegistered())
		{
			try
			{
				Service::register();
			}
			catch (SystemException $e)
			{
				return '';
			}
		}

		return Service::getEngine()?->getInterface()?->getAppID();
	}

	/**
	 * check value
	 * @param $value
	 *
	 * @return bool
	 */
	static function checkValue($value): bool
	{
		return $value === static::getDefaultValue();

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