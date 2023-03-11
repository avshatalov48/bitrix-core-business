<?php

namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc;

/**
 * Class String
 * @package Bitrix\Bizproc\BaseType
 */
class StringType extends Base
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::STRING;
	}

	/**
	 * Normalize single value.
	 *
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @return mixed Normalized value
	 */
	public static function toSingleValue(FieldType $fieldType, $value)
	{
		if (is_array($value))
		{
			$value = current(\CBPHelper::makeArrayFlat($value));
		}

		return $value;
	}

	public static function externalizeValue(FieldType $fieldType, $context, $value)
	{
		if (is_array($value))
		{
			return (string)current(\CBPHelper::makeArrayFlat($value));
		}

		return parent::externalizeValue($fieldType, $context, $value);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return null|mixed
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();
		switch ($type)
		{
			case FieldType::BOOL:
				$value = mb_strtolower((string)$value);
				$value = in_array($value, ['y', 'yes', 'true', '1']) ? 'Y' : 'N';
				break;
			case FieldType::DATE:
			case FieldType::DATETIME:
				$value = (string)$value;

				if (Bizproc\BaseType\Value\DateTime::isSerialized($value))
				{
					break;
				}

				if ($value)
				{
					$format = ($type == FieldType::DATE) ? \FORMAT_DATE : \FORMAT_DATETIME;
					if (\CheckDateTime($value, $format))
					{
						$value = date(
							Main\Type\Date::convertFormatToPhp($format),
							\CBPHelper::makeTimestamp($value, $format)
						);
					}
					else
					{
						$value = date(Main\Type\Date::convertFormatToPhp($format), strtotime($value));
					}
				}
				break;
			case FieldType::DOUBLE:
				$value = str_replace(' ', '', str_replace(',', '.', $value));
				$value = (float)$value;
				break;
			case FieldType::INT:
				$value = str_replace(' ', '', $value);
				$value = (int)$value;
				break;
			case FieldType::STRING:
			case FieldType::TEXT:
				$value = (string)$value;
				break;
			case FieldType::USER:
				$value = trim($value);
				if (
					mb_strpos($value, 'user_') === false
					&& mb_strpos($value, 'group_') === false
					&& !preg_match('#^[0-9]+$#', $value)
				)
				{
					$value = null;
				}
				break;
			case FieldType::TIME:
				$value = trim((string)$value);

				$value =
					Bizproc\BaseType\Value\Time::isCorrect($value)
						? (string)(new Bizproc\BaseType\Value\Time($value))
						: null
				;

				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * Return conversion map for current type.
	 *
	 * @return array Map.
	 */
	public static function getConversionMap()
	{
		return [
			[
				FieldType::BOOL,
				FieldType::DATE,
				FieldType::DATETIME,
				FieldType::DOUBLE,
				FieldType::INT,
				FieldType::STRING,
				FieldType::TEXT,
				FieldType::USER,
			],
		];
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param mixed $value
	 * @param bool $allowSelection
	 * @param int $renderMode
	 * @return string
	 */
	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($allowSelection && !($renderMode & FieldType::RENDER_MODE_PUBLIC))
		{
			return static::renderControlSelector($field, $value, 'combine', '', $fieldType);
		}

		return parent::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param int $renderMode Control render mode.
	 * @return bool
	 */
	public static function canRenderControl($renderMode)
	{
		return true;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$value = static::toSingleValue($fieldType, $value);

		return static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlMultiple(
		FieldType $fieldType,
		array $field,
		$value,
		$allowSelection,
		$renderMode
	)
	{
		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
		{
			$value = [$value];
		}

		if (empty($value))
		{
			$value[] = null;
		}

		$controls = [];

		foreach ($value as $k => $v)
		{
			$singleField = $field;
			$singleField['Index'] = $k;
			$controls[] = static::renderControl(
				$fieldType,
				$singleField,
				$v,
				$allowSelection,
				$renderMode
			);
		}

		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$renderResult = static::renderPublicMultipleWrapper($fieldType, $field, $controls);
		}
		else
		{
			$renderResult = static::wrapCloneableControls($controls, static::generateControlName($field));
		}

		return $renderResult;
	}

	public static function mergeValue(FieldType $fieldType, array $baseValue, $appendValue): array
	{
		if (\CBPHelper::isEmptyValue($baseValue))
		{
			return (array)$appendValue;
		}

		if (!is_array($appendValue))
		{
			$baseValue[] = $appendValue;

			return $baseValue;
		}

		if (!\CBPHelper::isAssociativeArray($baseValue) && !\CBPHelper::isAssociativeArray($appendValue))
		{
			return array_values(array_merge($baseValue, $appendValue));
		}

		return $baseValue + $appendValue;
	}
}