<?php

namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main;
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

		$formats['publink'] = [
			'callable'  => 'formatValuePublicLink',
			'separator' => ', ',
		];

		$formats['shortlink'] = [
			'callable'  => 'formatValueShortLink',
			'separator' => ', ',
		];

		$formats['name'] = [
			'callable'  => 'formatValueName',
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
		return '';
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueName(FieldType $fieldType, $value)
	{
		$value = (int) $value;
		$file = \CFile::getFileArray($value);
		if ($file && ($file['ORIGINAL_NAME'] || $file['FILE_NAME']))
		{
			return $file['ORIGINAL_NAME'] ?: $file['FILE_NAME'];
		}
		return '';
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValuePublicLink(FieldType $fieldType, $value)
	{
		$fileId = (int) $value;
		if ($fileId)
		{
			return \Bitrix\Bizproc\Controller\File::getPublicLink($fileId);
		}
		return '';
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueShortLink(FieldType $fieldType, $value)
	{
		$pubLink = static::formatValuePublicLink($fieldType, $value);
		if ($pubLink)
		{
			return Main\Engine\UrlManager::getInstance()->getHostUrl().\CBXShortUri::getShortUri($pubLink);
		}
		return '';
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

		if ($renderMode & FieldType::RENDER_MODE_MOBILE)
		{
			return self::renderMobileControl($fieldType, $field, $value);
		}

		return parent::renderControlSingle($fieldType, $field, $value, $allowSelection, $renderMode);
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

		if ($renderMode & FieldType::RENDER_MODE_MOBILE)
		{
			return self::renderMobileControl($fieldType, $field, $value);
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

	private static function renderPublicSelectableControlSingle(FieldType $fieldType, array $field, $value)
	{
		$name = static::generateControlName($field);
		$className = static::generateControlClassName($fieldType, $field);
		$className = str_replace('file', 'file-selectable', $className);

		return sprintf(
			'<input type="text" class="%s" name="%s" value="%s" placeholder="%s" data-role="inline-selector-target" data-selector-type="file" data-property="%s"/>',
			htmlspecialcharsbx($className),
			htmlspecialcharsbx($name),
			htmlspecialcharsbx((string)$value),
			htmlspecialcharsbx($fieldType->getDescription()),
			htmlspecialcharsbx(Main\Web\Json::encode($fieldType->getProperty()))
		);
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

	private static function renderMobileControl(FieldType $fieldType, array $field, $value)
	{
		/** @var \CMain */
		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'mobile',
			[
				'MODULE_ID' => 'bizproc',
				'CONTROL_ID' => static::generateControlId($field),
				'ALLOW_UPLOAD' => 'A',
				'INPUT_NAME' => static::generateControlName($field),
				'INPUT_VALUE' => $value,
				'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N'
			]
		);

		return ob_get_clean();
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
				if (!array_key_exists('MODULE_ID', $value) || $value['MODULE_ID'] == '')
					$value['MODULE_ID'] = 'bizproc';

				$value = \CFile::saveFile($value, 'bizproc_wf');
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
		elseif (is_numeric($value) && defined('BX_MOBILE'))
		{
			$file = \CFile::getById($value)->fetch();
			if (!$file || $file['MODULE_ID'] !== 'bizproc')
			{
				$value = null;
			}
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	public static function externalizeValue(FieldType $fieldType, $context, $value)
	{
		if ($context === 'rest' && is_numeric($value))
		{
			return \CRestUtil::GetFile($value);
		}

		return parent::externalizeValue($fieldType, $context, $value);
	}

	public static function internalizeValue(FieldType $fieldType, $context, $value)
	{
		if ($context === 'rest')
		{
			$fileFields = \CRestUtil::saveFile($value);

			if ($fileFields)
			{
				$fileFields['MODULE_ID'] = 'bizproc';
				return (int) \CFile::saveFile($fileFields, 'bizproc_rest');
			}
		}

		return parent::internalizeValue($fieldType, $context, $value);
	}

}