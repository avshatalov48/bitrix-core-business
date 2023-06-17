<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Int
 * @package Bitrix\Bizproc\BaseType
 */
class IntType extends Double
{

	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::INT;
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
			reset($value);
			$value = current($value);
		}
		return $value;
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param array $request
	 * @return null|int
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = Base::extractValue($fieldType, $field, $request);

		if ($value !== null && is_string($value) && $value <> '')
		{
			if (\CBPActivity::isExpression($value))
				return $value;

			$value = str_replace(' ', '', $value);
			if (preg_match('#^[0-9\-]+$#', $value))
			{
				$value = (int) $value;
			}
			else
			{
				$value = null;
				static::addError(array(
					'code' => 'ErrorValue',
					'message' => Loc::getMessage('BPDT_INT_INVALID'),
					'parameter' => static::generateControlName($field),
				));
			}
		}
		elseif (is_numeric($value))
		{
			$value = (int)$value;
		}
		else
		{
			$value = null;
		}

		return $value;
	}
}