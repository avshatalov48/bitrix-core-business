<?php

namespace Bitrix\Bizproc\UserType;

use Bitrix\Bizproc\BaseType;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Loader;

class UserFieldBase extends BaseType\Base
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::STRING;
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		global $APPLICATION, $USER_FIELD_MANAGER;

		$userType = static::getUserType($fieldType);

		if ($userType === 'crm')
		{
			return self::formatCrmValuePrintable($fieldType, $value);
		}

		$userFieldType = $USER_FIELD_MANAGER->GetUserType($userType);
		$userField = [
			'ENTITY_ID' => sprintf('%s_%s',
				mb_strtoupper($fieldType->getDocumentType()[0]),
				mb_strtoupper($fieldType->getDocumentType()[2])
			),
			'FIELD_NAME' => 'UF_XXXXXXX',
			'USER_TYPE_ID' => $userType,
			'SORT' => 100,
			'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N',
			'MANDATORY' => $fieldType->isRequired() ? 'Y' : 'N',
			'EDIT_FORM_LABEL' => $userFieldType['DESCRIPTION'],
			'VALUE' => $value,
			'USER_TYPE' => $userFieldType
		];

		if ($settings = $fieldType->getSettings())
		{
			$userField['SETTINGS'] = $settings;
		}

		if ($userType === 'boolean' && ($value === 'Y' || $value === 'N'))
		{
			//Convert bizproc boolean values (Y/N) in to UF boolean values (1/0)
			$userField['VALUE'] = $value = ($value === 'Y') ? 1 : 0;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:system.field.view',
			$userType,
			[
				'arUserField' => $userField,
				'bVarsFromForm' => false,
				'form_name' => "",
				'printable' => true,
				'FILE_MAX_HEIGHT' => 400,
				'FILE_MAX_WIDTH' => 400,
				'FILE_SHOW_POPUP' => true
			],
			false,
			['HIDE_ICONS' => 'Y']
		);

		return HTMLToTxt(ob_get_clean(), maxlen: 0);
	}

	/**
	 * @param FieldType $fieldType Document field object.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class manager name.
	 * @return null|mixed
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		if (is_array($value) && isset($value['VALUE']))
			$value = $value['VALUE'];

		$value = (string)$value;

		return BaseType\StringType::convertTo($fieldType, $value, $toTypeClass);
	}

	/**
	 * Return conversion map for current type.
	 * @return array Map.
	 */
	public static function getConversionMap()
	{
		return BaseType\StringType::getConversionMap();
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
		global $USER_FIELD_MANAGER, $APPLICATION;

		$selectorValue = null;
		$typeValue = [];
		if (!is_array($value) || \CBPHelper::isAssociativeArray($value))
		{
			$value = [$value];
		}

		foreach ($value as $v)
		{
			if (\CBPActivity::isExpression($v))
			{
				$selectorValue = $v;
			}
			else
			{
				$typeValue[] = is_array($v) && isset($v['VALUE']) ? $value['VALUE'] : $v;
			}
		}

		$sType = static::getUserType($fieldType);
		$value = $typeValue;

		$arUserFieldType = $USER_FIELD_MANAGER->GetUserType($sType);

		$userField = [
			'ENTITY_ID' => sprintf('%s_%s',
				mb_strtoupper($fieldType->getDocumentType()[0]),
				mb_strtoupper($fieldType->getDocumentType()[2])
			),
			'FIELD_NAME' => static::generateControlName($field),
			'USER_TYPE_ID' => $sType,
			'SORT' => 100,
			'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N',
			'MANDATORY' => $fieldType->isRequired() ? 'Y' : 'N',
			'EDIT_IN_LIST' => 'Y',
			'EDIT_FORM_LABEL' => $arUserFieldType['DESCRIPTION'],
			'VALUE' => $value,
			'USER_TYPE' => $arUserFieldType,
			'SETTINGS' => [],
			'ENTITY_VALUE_ID' => 1,
		];

		if ($sType == 'boolean' && ($userField['VALUE'] == "Y" || $userField['VALUE'] == "N"))
		{
			$userField['VALUE'] = ($userField['VALUE'] == "Y") ? 1 : 0;
		}

		$userField['SETTINGS'] = $fieldType->getSettings();

		if (in_array($sType, ['iblock_element', 'iblock_section', 'boolean']) && ($renderMode & FieldType::RENDER_MODE_DESIGNER))
		{
			//TODO: fix checkboxes values
			$userField['SETTINGS']['DISPLAY'] = 'LIST';
		}
		elseif ($sType == 'crm' && empty($userField['SETTINGS']))
		{
			$userField['SETTINGS'] = ['LEAD' => 'Y', 'CONTACT' => 'Y', 'COMPANY' => 'Y', 'DEAL' => 'Y'];
		}
		elseif ($sType === 'iblock_element' && $renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$userField['SETTINGS']['DISPLAY'] = 'UI';
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:system.field.edit',
			$sType,
			[
				'arUserField' => $userField,
				'bVarsFromForm' => false,
				'form_name' => $field['Form'],
				'FILE_MAX_HEIGHT' => 400,
				'FILE_MAX_WIDTH' => 400,
				'FILE_SHOW_POPUP' => true
			],
			false,
			['HIDE_ICONS' => 'Y']
		);

		$renderResult = ob_get_clean();

		if ($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
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
		return static::renderControlSingle($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @inheritdoc
	 */
	public static function extractValueSingle(FieldType $fieldType, array $field, array $request)
	{
		static::cleanErrors();
		$result = static::extractValue($fieldType, $field, $request);

		$nameText = $field['Field'].'_text';
		$text = isset($request[$nameText]) ? $request[$nameText] : null;
		if (\CBPActivity::isExpression($text))
		{
			$result = $text;
		}

		return $result;
	}

	protected static function getUserType(FieldType $fieldType)
	{
		return mb_substr($fieldType->getType(), 3);
	}

	private static function formatCrmValuePrintable(FieldType $fieldType, $value)
	{
		if (!Loader::includeModule('crm'))
		{
			return '';
		}

		$defaultTypeName = 'LEAD';
		foreach ($fieldType->getSettings() as $typeName => $flag)
		{
			if ($flag === 'Y')
			{
				$defaultTypeName = $typeName;
				break;
			}
		}

		return self::prepareCrmUserTypeValueView($value, $defaultTypeName);
	}

	private static function prepareCrmUserTypeValueView($value, $defaultTypeName = '')
	{
		$typeId = $id = null;
		$parts = explode('_', $value);

		if (count($parts) > 1)
		{
			$typeId = \CCrmOwnerTypeAbbr::ResolveTypeID($parts[0]);
			$id = $parts[1];
		}
		elseif ($defaultTypeName !== '')
		{
			$typeId = \CCrmOwnerType::resolveID($defaultTypeName);
			$id = $value;
		}

		if (!$typeId || !$id)
		{
			return '';
		}

		$entityName = \CCrmOwnerType::getCaption($typeId, $id, false);
		$entityDesc = \CCrmOwnerType::GetDescription($typeId);
		$entityUrl = \CCrmOwnerType::GetDetailsUrl($typeId, $id, false);

		return sprintf('[b]%s:[/b] [url=%s]%s[/url]', $entityDesc, $entityUrl, $entityName);
	}
}