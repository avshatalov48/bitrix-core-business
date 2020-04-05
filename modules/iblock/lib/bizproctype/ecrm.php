<?php
namespace Bitrix\Iblock\BizprocType;

use Bitrix\Bizproc\FieldType;

class ECrm extends UserTypeProperty
{
	protected static $formatSeparator = ', ';

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $format Format name.
	 * @return string
	 */
	public static function formatValueMultiple(FieldType $fieldType, $value, $format = 'printable')
	{
		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
			$value = array($value);

		self::$formatSeparator = static::getFormatSeparator($format);

		return static::formatValuePrintable($fieldType, $value);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $format Format name.
	 * @return string
	 */
	public static function formatValueSingle(FieldType $fieldType, $value, $format = 'printable')
	{
		return static::formatValueMultiple($fieldType, $value, $format);
	}

	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		$property = static::getUserType($fieldType);
		$property['IBLOCK_ID'] = self::getIblockId($fieldType);
		if(empty($property['USER_TYPE_SETTINGS']))
			$property['USER_TYPE_SETTINGS'] = $fieldType->getOptions();

		if (array_key_exists('GetValuePrintable', $property))
		{
			return call_user_func_array($property['GetValuePrintable'], array($property, $value, self::$formatSeparator));
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param string $callbackFunctionName Callback name.
	 * @param mixed $value Field value.
	 * @return string
	 */
	public static function renderControlOptions(FieldType $fieldType, $callbackFunctionName, $value)
	{
		$property = static::getUserType($fieldType);
		if(empty($property['USER_TYPE_SETTINGS']))
			$property['USER_TYPE_SETTINGS'] = $fieldType->getOptions();

		if(array_key_exists('GetSettingsHTML', $property))
		{
			$fieldData = array();
			return call_user_func_array($property['GetSettingsHTML'], array($property,
				array('USE_BP' => true, 'CALLBACK_FUNCTION' => $callbackFunctionName, 'NAME' => 'ENTITY'), &$fieldData));
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param FieldType $fieldType Document field object.
	 * @param array $field Form field information.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		return static::renderControlMultiple($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param FieldType $fieldType Document field object.
	 * @param array $field Form field information.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$selectorValue = null;
		$typeValue = array();
		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
			$value = array($value);

		foreach ($value as $v)
		{
			if (\CBPActivity::isExpression($v))
				$selectorValue = $v;
			else
				$typeValue[] = $v;
		}
		// need to show at least one control
		if (empty($typeValue))
			$typeValue[] = null;

		$property = static::getUserType($fieldType);

		if(!empty($property['GetPublicEditHTMLMulty']))
		{
			$fieldName = static::generateControlName($field);
			$renderResult = call_user_func_array(
				$property['GetPublicEditHTMLMulty'],
				array(
					array(
						'IBLOCK_ID' => self::getIblockId($fieldType),
						'USER_TYPE_SETTINGS' => $fieldType->getOptions(),
						'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N',
						'IS_REQUIRED' => $fieldType->isRequired() ? 'Y' : 'N',
						'PROPERTY_USER_TYPE' => $property
					),
					array('VALUE' => $typeValue),
					array(
						'FORM_NAME' => $field['Form'],
						'VALUE' => $fieldName,
						'DESCRIPTION' => '',
					),
					true
				)
			);
		}
		else
		{
			$renderResult = static::renderControl($fieldType, $field, '', $allowSelection, $renderMode);
		}

		if($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
	}

	public static function extractValueSingle(FieldType $fieldType, array $field, array $request)
	{
		return static::extractValueMultiple($fieldType, $field, $request);
	}

	private static function getIblockId(FieldType $fieldType)
	{
		$documentType = $fieldType->getDocumentType();
		$type = explode('_', $documentType[2]);
		return intval($type[1]);
	}

	public static function toSingleValue(FieldType $fieldType, $value)
	{
		if (is_array($value))
		{
			$values = array_values($value);
			return isset($values[0]) ? $values[0] : null;
		}
		return parent::toSingleValue($fieldType, $value);
	}

}