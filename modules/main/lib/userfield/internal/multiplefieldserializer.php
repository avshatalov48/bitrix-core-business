<?php

namespace Bitrix\Main\UserField\Internal;


use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class MultipleFieldSerializer
{
	public static function setMultipleFieldSerialization(ArrayField $entityField, $fieldAsType, array $userField)
	{
		if ($fieldAsType instanceof \Bitrix\Main\ORM\Fields\DatetimeField)
		{
			$useTimezone = ($userField['SETTINGS']['USE_TIMEZONE'] ?? 'Y') === 'Y';
			$entityField->configureSerializeCallback([static::class, 'serializeMultipleDatetime']);
			if ($useTimezone)
			{
				$entityField->configureUnserializeCallback([static::class, 'unserializeMultipleDatetimeWithTimezone']);
			}
			else
			{
				$entityField->configureUnserializeCallback([static::class, 'unserializeMultipleDatetime']);
			}

		}
		elseif ($fieldAsType instanceof \Bitrix\Main\ORM\Fields\DateField)
		{
			$entityField->configureSerializeCallback([static::class, 'serializeMultipleDate']);
			$entityField->configureUnserializeCallback([static::class, 'unserializeMultipleDate']);
		} else
		{
			$entityField->configureSerializationPhp();
		}
	}

	public static function serializeMultipleDatetime($value)
	{
		if (is_array($value) || $value instanceof \Traversable)
		{
			$tmpValue = [];

			foreach ($value as $k => $singleValue)
			{
				$dateTime = ($singleValue instanceof DateTime)
					? $singleValue
					: DateTime::createFromUserTime($singleValue)
				;

				$tmpValue[$k] = $dateTime->format(\Bitrix\Main\UserFieldTable::MULTIPLE_DATETIME_FORMAT);
			}

			return serialize($tmpValue);
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 * @throws ObjectException
	 */
	public static function unserializeMultipleDatetime($value)
	{
		if ($value != '')
		{
			$items = self::unserializeMultipleDatetimeWithTimezone($value);
			foreach ($items as $datetime)
			{
				/** @var $datetime DateTime */
				$datetime->disableUserTime();
			}

			return $items;
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 * @throws ObjectException
	 */
	public static function unserializeMultipleDatetimeWithTimezone($value)
	{
		if ($value != '')
		{
			$value = unserialize(
				$value,
				['allowed_classes' => [
					Date::class,
					DateTime::class,
					\DateTime::class,
				]]
			);

			foreach ($value as &$singleValue)
			{
				if ($singleValue instanceof DateTime)
				{
					$singleValue = $singleValue->format(\Bitrix\Main\UserFieldTable::MULTIPLE_DATETIME_FORMAT);
				}
				try
				{
					//try new independent datetime format
					$singleValue = new DateTime($singleValue, \Bitrix\Main\UserFieldTable::MULTIPLE_DATETIME_FORMAT);
				} catch (ObjectException $e)
				{
					//try site format
					$singleValue = new DateTime($singleValue);
				}
			}
		}

		return $value;
	}

	/**
	 * @param Date[] $value
	 *
	 * @return string
	 */
	public static function serializeMultipleDate($value)
	{
		if (is_array($value) || $value instanceof \Traversable)
		{
			$tmpValue = [];

			foreach ($value as $k => $singleValue)
			{
				$date = ($singleValue instanceof Date)
					? $singleValue
					: DateTime::createFromUserTime($singleValue)
				;

				$tmpValue[$k] = $date->format(\Bitrix\Main\UserFieldTable::MULTIPLE_DATE_FORMAT);
			}

			return serialize($tmpValue);
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 * @throws ObjectException
	 */
	public static function unserializeMultipleDate($value)
	{
		if ($value != '')
		{
			$value = unserialize(
				$value,
				['allowed_classes' => [
					Date::class,
					DateTime::class,
					\DateTime::class,
				]]
			);

			foreach ($value as &$singleValue)
			{
				if ($singleValue instanceof Date)
				{
					$singleValue = $singleValue->format(\Bitrix\Main\UserFieldTable::MULTIPLE_DATE_FORMAT);
				}
				try
				{
					//try new independent datetime format
					$singleValue = new Date($singleValue, \Bitrix\Main\UserFieldTable::MULTIPLE_DATE_FORMAT);
				} catch (ObjectException $e)
				{
					//try site format
					$singleValue = new Date($singleValue);
				}
			}
		}

		return $value;
	}
}
