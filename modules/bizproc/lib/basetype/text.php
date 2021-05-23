<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;

/**
 * Class Text
 * @package Bitrix\Bizproc\BaseType
 */
class Text extends StringType
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::TEXT;
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
		$isPublic = ($renderMode & FieldType::RENDER_MODE_PUBLIC);

		if ($allowSelection && !$isPublic)
		{
			return static::renderControlSelector($field, $value, 'combine', '', $fieldType);
		}

		$name = static::generateControlName($field);
		$controlId = static::generateControlId($field);
		$className = static::generateControlClassName($fieldType, $field);

		$selectorAttributes = '';
		if ($isPublic && $allowSelection)
		{
			$selectorAttributes = sprintf(
				'data-role="inline-selector-target" data-property="%s" ',
				htmlspecialcharsbx(Main\Web\Json::encode($fieldType->getProperty()))
			);
		}

		return sprintf(
			'<textarea id="%s" class="%s" placeholder="%s" rows="5" cols="40"  name="%s" %s>%s</textarea>',
			htmlspecialcharsbx($controlId),
			htmlspecialcharsbx($className),
			htmlspecialcharsbx($fieldType->getDescription()),
			htmlspecialcharsbx($name),
			$selectorAttributes,
			htmlspecialcharsbx((string)$value)
		);
	}
}