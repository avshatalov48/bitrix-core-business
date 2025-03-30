<?php

namespace Bitrix\Currency\Compatible;

use Bitrix\Main;

/**
 * Class Tools
 * Provides various useful methods for old api.
 *
 * @package Bitrix\Currency\Compatible
 */
class Tools
{
	protected static string $datetimeTemplate;

	/**
	 * Return datetime template for old api emulation.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public static function getDatetimeExpressionTemplate(): string
	{
		if (!isset(self::$datetimeTemplate))
		{
			$helper = Main\Application::getConnection()->getSqlHelper();
			$format = Main\Context::getCurrent()->getCulture()->getDateTimeFormat();
			$datetimeFieldName = '#FIELD#';
			$datetimeField = $datetimeFieldName;
			if (\CTimeZone::enabled())
			{
				$diff = \CTimeZone::getOffset();
				if ($diff !== 0)
				{
					$datetimeField = $helper->addSecondsToDateTime($diff, $datetimeField);
				}
				unset($diff);
			}
			self::$datetimeTemplate = str_replace(
				['%', $datetimeFieldName],
				['%%', '%1$s'],
				$helper->formatDate($format, $datetimeField)
			);
			unset($datetimeField, $datetimeFieldName, $format, $helper);
		}

		return self::$datetimeTemplate;
	}
}
