<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Date
 * @package Bitrix\Bizproc\BaseType
 */
class Date extends Base
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::DATE;
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
				$value = $value? (int)strtotime($value) : 0;
				break;
			case FieldType::DATE:
			case FieldType::DATETIME:
			case FieldType::STRING:
			case FieldType::TEXT:
				$value = (string) $value;
				if ($value)
				{
					if ($type == FieldType::DATE)
						$format = \FORMAT_DATE;
					elseif ($type == FieldType::DATETIME)
						$format = \FORMAT_DATETIME;
					else
						$format = static::getType() == FieldType::DATE ? \FORMAT_DATE : \FORMAT_DATETIME;

					if (\CheckDateTime($value, $format))
					{
						$value = date(Type\Date::convertFormatToPhp($format), \MakeTimeStamp($value, $format));
					}
					else
					{
						$value = date(Type\Date::convertFormatToPhp($format), strtotime($value));
					}
				}
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
				FieldType::DATE,
				FieldType::DATETIME,
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
		$name = static::generateControlName($field);
		$className = static::generateControlClassName($fieldType, $field);
		$renderResult = '';

		if ($renderMode & FieldType::RENDER_MODE_PUBLIC && $allowSelection)
		{
			$renderResult = '<input name="'.htmlspecialcharsbx($name).'" type="text" '
				.'class="'.htmlspecialcharsbx($className).'"
					value="'.htmlspecialcharsbx($value).'"
					placeholder="'.htmlspecialcharsbx($fieldType->getDescription()).'"
					data-role="inline-selector-target"
					data-selector-type="'.htmlspecialcharsbx($fieldType->getType()).'"
				>';
		}
		elseif ($renderMode & FieldType::RENDER_MODE_MOBILE)
		{
			$renderResult = '<div><input type="hidden" value="'
				.htmlspecialcharsbx($value).'" data-type="'
				.htmlspecialcharsbx(static::getType()).'" name="'.htmlspecialcharsbx($name).'"/>'
				.'<a href="#" onclick="return BX.BizProcMobile.showDatePicker(this, event);">'
				.($value? htmlspecialcharsbx($value) : Loc::getMessage('BPDT_DATE_MOBILE_SELECT')).'</a></div>';
		}
		else
		{
			\CJSCore::Init(['popup', 'date']);
			$renderResult = '<input type="text" name="'.htmlspecialcharsbx($name)
				.'" value="'.htmlspecialcharsbx($value).'" class="'.htmlspecialcharsbx($className).'"/>'
				.'<img src="/bitrix/js/main/core/images/calendar-icon.gif" alt="calendar" class="calendar-icon" '
				.'onclick="BX.calendar({node:this, field: this.previousSibling, bTime: '
				.(static::getType() == FieldType::DATETIME ? 'true' : 'false').'});" '
				.'onmouseover="BX.addClass(this, \'calendar-icon-hover\');" '
				.'onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"/>';
		}

		return $renderResult;
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
		$allowSelectionOrig = $allowSelection;
		if (($renderMode & FieldType::RENDER_MODE_PUBLIC))
		{
			$allowSelection = false;
		}

		$value = static::toSingleValue($fieldType, $value);
		$selectorValue = null;

		if ($allowSelection && \CBPActivity::isExpression($value))
		{
			$selectorValue = $value;
			$value = null;
		}

		$renderResult = static::renderControl($fieldType, $field, $value, $allowSelectionOrig, $renderMode);

		if ($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
	}

	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$allowSelectionOrig = $allowSelection;
		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$allowSelection = false;
		}

		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
		{
			$value = array($value);
		}

		$selectorValue = null;
		if ($allowSelection)
		{
			foreach ($value as $k => $v)
			{
				if (\CBPActivity::isExpression($v))
				{
					$selectorValue = $v;
					unset($value[$k]);
				}
			}
			$value = array_values($value);
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
				$allowSelectionOrig,
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

		if ($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
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

		if ($value !== null && is_string($value) && strlen($value) > 0)
		{
			if (\CBPActivity::isExpression($value))
				return $value;

			$format = static::getType() == FieldType::DATETIME ? \FORMAT_DATETIME : \FORMAT_DATE;
			if(!\CheckDateTime($value, $format))
			{
				$value = null;
				static::addError(array(
					'code' => 'ErrorValue',
					'message' => Loc::getMessage('BPDT_DATE_INVALID'),
					'parameter' => static::generateControlName($field),
				));
			}
			else
				$value = \ConvertDateTime($value, $format);
		}
		else
		{
			$value = null;
		}

		return $value;
	}
}