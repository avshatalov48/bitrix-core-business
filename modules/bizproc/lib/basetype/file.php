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
		$formats['src'] = [
			'callable'  => 'formatValueSrc',
			'separator' => ', ',
		];

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
			{
				$value = array_keys($value);
			}
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
				.'&i='.$value.'&h='.md5($file['SUBDIR']).']'
				.htmlspecialcharsbx($file['ORIGINAL_NAME'])
				.'[/url]';
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
		return [
			[
				FieldType::FILE
			]
		];
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
		{
			$value = array_keys($value);
		}

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
		{
			return '';
		}

		$classNameHtml = htmlspecialcharsbx(static::generateControlClassName($fieldType, $field));
		$idHtml = htmlspecialcharsbx(static::generateControlId($field));
		$nameHtml = htmlspecialcharsbx(static::generateControlName($field));

		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$msg = htmlspecialcharsbx(Loc::getMessage('BPDT_FILE_CHOOSE_FILE'));
			$onchange = 'this.nextSibling.textContent = BX.Bizproc.FieldType.File.parseLabel(this.value);';
			$onchange = htmlspecialcharsbx($onchange);

			return <<<HTML
				<div class="{$classNameHtml}">
					<span>
						<span class="webform-small-button">{$msg}</span>
					</span>
					<input type="file" id="{$idHtml}" name="{$nameHtml}" onchange="{$onchange}">
					<span class="bizproc-type-control-file-label"></span>
				</div>
HTML;
		}

		return '<input type="file" class="'.$classNameHtml.'" id="'.$idHtml.'" name="'.$nameHtml.'">';
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param mixed $value
	 * @param bool $allowSelection
	 * @param int $renderMode
	 * @return string
	 */
	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($allowSelection && $renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			return self::renderPublicSelectableControlSingle($fieldType, $field, $value);
		}

		return parent::renderControlSingle($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	private static function renderPublicSelectableControlSingle(FieldType $fieldType, array $field, $value)
	{
		$name = static::generateControlName($field);
		$className = static::generateControlClassName($fieldType, $field);
		$className = str_replace('file', 'file-selectable', $className);

		return '<input type="text" class="'.htmlspecialcharsbx($className)
			.'" name="'.htmlspecialcharsbx($name).'" value="'.htmlspecialcharsbx((string) $value)
			.'" placeholder="'.htmlspecialcharsbx($fieldType->getDescription()).'" value="'.htmlspecialcharsbx((string) $value).'"'
			.' data-role="inline-selector-target" data-selector-type="file"'
			.'/>';
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
		if ($renderMode & FieldType::RENDER_MODE_DESIGNER && !$allowSelection)
		{
			return '';
		}

		if ($allowSelection && $renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			return self::renderPublicSelectableControlMultiple($fieldType, $field, $value);
		}

		if ($renderMode & FieldType::RENDER_MODE_DESIGNER)
		{
			if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
			{
				$value = [$value];
			}

			// need to show at least one control
			if (empty($value))
			{
				$value[] = null;
			}

			$controls = [];

			foreach ($value as $k => $v)
			{
				$singleField = $field;
				$singleField['Index'] = $k;
				$controls[] = parent::renderControlSingle($fieldType, $singleField, $v, $allowSelection, $renderMode);
			}

			return static::wrapCloneableControls($controls, static::generateControlName($field));
		}

		return parent::renderControlMultiple($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	private static function renderPublicSelectableControlMultiple(FieldType $fieldType, array $field, $value)
	{
		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
		{
			$value = [$value];
		}

		// need to show at least one control
		if (empty($value))
		{
			$value[] = null;
		}

		$controls = [];

		foreach ($value as $k => $v)
		{
			$singleField = $field;
			$singleField['Index'] = $k;
			$controls[] = static::renderPublicSelectableControlSingle(
				$fieldType,
				$singleField,
				$v
			);
		}

		return static::renderPublicMultipleWrapper($fieldType, $field, $controls);
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
				static::addError([
					'code'      => 'ErrorValue',
					'message'   => Loc::getMessage('BPDT_FILE_SECURITY_ERROR'),
					'parameter' => static::generateControlName($field),
				]);
			}
			else
			{
				if (!array_key_exists('MODULE_ID', $value) || strlen($value['MODULE_ID']) <= 0)
					$value['MODULE_ID'] = 'bizproc';

				$value = \CFile::saveFile($value, 'bizproc_wf', true);
				if (!$value)
				{
					$value = null;
					static::addError([
						'code'      => 'ErrorValue',
						'message'   => Loc::getMessage('BPDT_FILE_INVALID'),
						'parameter' => static::generateControlName($field),
					]);
				}
			}
		}
		elseif (\CBPActivity::isExpression($value))
		{
			//It`s OK
		}
		else
		{
			$value = null;
		}

		return $value;
	}

}