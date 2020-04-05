<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class File
 * @package Bitrix\Bizproc\BaseType
 */
class File extends Base
{

	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::FILE;
	}

	/**
	 * Get formats list.
	 * @return array
	 */
	public static function getFormats()
	{
		$formats = parent::getFormats();
		$formats['src'] = array(
			'callable' =>'formatValueSrc',
			'separator' => ', ',
		);
		return $formats;
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
			if (\CBPHelper::isAssociativeArray($value))
				$value = array_keys($value);
			reset($value);
			$value = current($value);
		}
		return $value;
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		$value = (int) $value;
		$iterator = \CFile::getByID($value);
		if ($file = $iterator->fetch())
		{
			return '[url=/bitrix/tools/bizproc_show_file.php?f='.urlencode($file['FILE_NAME']).'&hash='
				.md5($file['FILE_NAME'])
				.'&i='.$value.'&h='.md5($file['SUBDIR']).']'.htmlspecialcharsbx($file['ORIGINAL_NAME']).'[/url]';
		}
		return '';
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueSrc(FieldType $fieldType, $value)
	{
		$value = (int) $value;
		$file = \CFile::getFileArray($value);
		if ($file && $file['SRC'])
		{
			return $file['SRC'];
		}
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return null
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();
		switch ($type)
		{
			case FieldType::FILE:
				$value = (int) $value;
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
				FieldType::FILE
			)
		);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return array
	 */
	public static function convertValueMultiple(FieldType $fieldType, $value, $toTypeClass)
	{
		$value = (array) $value;
		if (\CBPHelper::isAssociativeArray($value))
			$value = array_keys($value);

		return parent::convertValueMultiple($fieldType, $value, $toTypeClass);
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
		if ($renderMode & FieldType::RENDER_MODE_DESIGNER)
			return '';

		$className = static::generateControlClassName($fieldType, $field);

		return '<input type="file" class="'.htmlspecialcharsbx($className).'" id="'
			.htmlspecialcharsbx(static::generateControlId($field))
			.'" name="'.htmlspecialcharsbx(static::generateControlName($field)).'">';
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($renderMode & FieldType::RENDER_MODE_DESIGNER)
		{
			if (is_array($value) && !\CBPHelper::isAssociativeArray($value))
			{
				reset($value);
				$value = current($value);
			}
			return parent::renderControlSingle($fieldType, $field, $value, $allowSelection, $renderMode);
		}
		return parent::renderControlMultiple($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param array $request
	 * @return null|int
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);

		if (is_array($value) && !empty($value['name']) && !empty($value['tmp_name']))
		{
			if (!is_uploaded_file($value['tmp_name']))
			{
				$value = null;
				static::addError(array(
					'code' => 'ErrorValue',
					'message' => Loc::getMessage('BPDT_FILE_SECURITY_ERROR'),
					'parameter' => static::generateControlName($field),
				));
			}
			else
			{
				if (!array_key_exists('MODULE_ID', $value) || strlen($value['MODULE_ID']) <= 0)
					$value['MODULE_ID'] = 'bizproc';

				$value = \CFile::saveFile($value, 'bizproc_wf', true);
				if (!$value)
				{
					$value = null;
					static::addError(array(
						'code' => 'ErrorValue',
						'message' => Loc::getMessage('BPDT_FILE_INVALID'),
						'parameter' => static::generateControlName($field),
					));
				}
			}
		}
		else
		{
			$value = null;
		}

		return $value;
	}

}