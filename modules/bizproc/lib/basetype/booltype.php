<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Bool
 * @package Bitrix\Bizproc\BaseType
 */
class BoolType extends Base
{

	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::BOOL;
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
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @return string
	 */
	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		return mb_strtoupper($value) != 'N' && !empty($value)
			? Loc::getMessage('BPDT_BOOL_YES')
			: Loc::getMessage('BPDT_BOOL_NO');
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
			case FieldType::DOUBLE:
			case FieldType::INT:
				$value = (int)($value == 'Y');
				break;
			case FieldType::BOOL:
			case FieldType::STRING:
			case FieldType::TEXT:
				if (in_array(mb_strtolower($value), ['y', 'yes', 'true', '1'], true))
				{
					$value = 'Y';
				}
				elseif (in_array(mb_strtolower($value), ['n', 'no', 'false', '0'], true))
				{
					$value = 'N';
				}

				$value = $value == 'Y' ? 'Y' : 'N';
				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * Return conversion map for current type.
	 * @return array Map.
	 */
	public static function getConversionMap()
	{
		return array(
			array(
				FieldType::DOUBLE,
				FieldType::INT,
				FieldType::BOOL,
				FieldType::STRING,
				FieldType::TEXT
			)
		);
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
		$isPublicControl = $renderMode & FieldType::RENDER_MODE_PUBLIC;
		$className = $isPublicControl ? static::generateControlClassName($fieldType, $field) : '';

		$renderResult = sprintf(
			'<select id="%s" name="%s" class="%s">',
			htmlspecialcharsbx(static::generateControlId($field)),
			htmlspecialcharsbx(static::generateControlName($field)),
			htmlspecialcharsbx($className)
		);

		if (!$fieldType->isRequired())
		{
			$renderResult .= '<option value="">['.Loc::getMessage("BPDT_BOOL_NOT_SET").']</option>';
		}

		$renderResult .= sprintf(
			'<option value="Y"%s>%s</option>
				<option value="N"%s>%s</option>
			</select>',
			$value === 'Y' ? ' selected' : '',
			Loc::getMessage('BPDT_BOOL_YES'),
			$value === 'N' ? ' selected' : '',
			Loc::getMessage('BPDT_BOOL_NO')
		);

		return $renderResult;
	}

	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$allowSelection = false;
		}

		return parent::renderControlSingle($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$allowSelection = false;
		}

		return parent::renderControlMultiple($fieldType, $field, $value, $allowSelection, $renderMode);
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
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param array $request
	 * @return null|string
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);

		if ($value !== null && $value !== 'Y' && $value !== 'N')
		{
			if (is_bool($value))
			{
				$value = $value ? 'Y' : 'N';
			}
			elseif (is_string($value) && $value <> '')
			{
				$value = mb_strtolower($value);
				if (in_array($value, array('y', 'yes', 'true', '1')))
				{
					$value = 'Y';
				}
				elseif (in_array($value, array('n', 'no', 'false', '0')))
				{
					$value = 'N';
				}
				else
				{
					$value = null;
					static::addError(array(
						'code' => 'ErrorValue',
						'message' => Loc::getMessage('BPDT_BOOL_INVALID'),
						'parameter' => static::generateControlName($field),
					));
				}
			}
			else
			{
				$value = null;
			}
		}

		return $value;
	}

	public static function externalizeValue(FieldType $fieldType, $context, $value)
	{
		$map = $fieldType->getSettings()['ExternalValues'] ?? null;
		if ($map && isset($map[$value]))
		{
			return $map[$value];
		}

		return parent::externalizeValue($fieldType, $context, $value);
	}

	public static function compareValues($valueA, $valueB)
	{
		$valueA = \CBPHelper::getBool($valueA);
		$valueB = \CBPHelper::getBool($valueB);

		return parent::compareValues($valueA, $valueB);
	}
}