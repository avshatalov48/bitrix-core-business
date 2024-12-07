<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

class CIBlockPropertySKU extends CIBlockPropertyElementAutoComplete
{
	/** @deprecated */
	public const USER_TYPE = Iblock\PropertyTable::USER_TYPE_SKU;

	public static function GetUserTypeDescription()
	{
		return [
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_ELEMENT,
			'USER_TYPE' => Iblock\PropertyTable::USER_TYPE_SKU,
			'DESCRIPTION' => Loc::getMessage('BT_UT_SKU_DESCRIPTION'),
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
			'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
			'GetAdminListViewHTML' => [__CLASS__,'getAdminListViewHTMLExtended'],
			'GetAdminFilterHTML' => [__CLASS__,'GetAdminFilterHTML'],
			'GetSettingsHTML' => [__CLASS__,'GetSettingsHTML'],
			'PrepareSettings' => [__CLASS__,'PrepareSettings'],
			'AddFilterFields' => [__CLASS__,'AddFilterFields'],
			'GetUIFilterProperty' => [__CLASS__, 'GetUIFilterProperty'],
			'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
			'GetUIEntityEditorPropertyEditHtml' => [__CLASS__, 'GetUIEntityEditorPropertyEditHtml'],
			'GetUIEntityEditorPropertyViewHtml' => [__CLASS__, 'GetUIEntityEditorPropertyViewHtml'],
		];
	}

	public static function PrepareSettings($arFields)
	{
		/*
		 * VIEW				- view type
		 * SHOW_ADD			- show button for add new values in linked iblock
		 * MAX_WIDTH		- max width textarea and input in pixels
		 * MIN_HEIGHT		- min height textarea in pixels
		 * MAX_HEIGHT		- max height textarea in pixels
		 * BAN_SYM			- banned symbols string
		 * REP_SYM			- replace symbol
		 * OTHER_REP_SYM	- non standart replace symbol
		 * IBLOCK_MESS		- get lang mess from linked iblock
		 * remove SHOW_ADD manage
		 */
		$arResult = parent::PrepareSettings($arFields);
		$arResult['SHOW_ADD'] = 'N';
		$arFields['USER_TYPE_SETTINGS'] = $arResult;
		$arFields['MULTIPLE'] = 'N';

		return $arFields;
	}

	public static function GetSettingsHTML($arFields,$strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT", "MULTIPLE_CNT", "MULTIPLE"),
			"SET" => array("MULTIPLE" => "N"),
			'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('BT_UT_SKU_SETTING_TITLE'),
		);

		$arSettings = static::PrepareSettings($arFields);
		if (isset($arSettings['USER_TYPE_SETTINGS']))
			$arSettings = $arSettings['USER_TYPE_SETTINGS'];

		$strResult = '<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_VIEW').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[VIEW]', static::GetPropertyViewsList(true),htmlspecialcharsbx($arSettings['VIEW'])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_MAX_WIDTH').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_WIDTH]" value="'.intval($arSettings['MAX_WIDTH']).'">&nbsp;'.Loc::getMessage('BT_UT_SKU_SETTING_COMMENT_MAX_WIDTH').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_MIN_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MIN_HEIGHT]" value="'.intval($arSettings['MIN_HEIGHT']).'">&nbsp;'.Loc::getMessage('BT_UT_SKU_SETTING_COMMENT_MIN_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_MAX_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_HEIGHT]" value="'.intval($arSettings['MAX_HEIGHT']).'">&nbsp;'.Loc::getMessage('BT_UT_SKU_SETTING_COMMENT_MAX_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_BAN_SYMBOLS').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[BAN_SYM]" value="'.htmlspecialcharsbx($arSettings['BAN_SYM']).'"></td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SKU_SETTING_REP_SYMBOL').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[REP_SYM]', static::GetReplaceSymList(true),htmlspecialcharsbx($arSettings['REP_SYM'])).'&nbsp;<input type="text" name="'.$strHTMLControlName["NAME"].'[OTHER_REP_SYM]" size="1" maxlength="1" value="'.htmlspecialcharsbx($arSettings['OTHER_REP_SYM']).'"></td>
		</tr>';

		return $strResult;
	}

	public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
	{
		$elementId = (int)($arValue['VALUE'] ?? 0);
		$element = self::getElement($elementId);
		if (!$element)
		{
			return '';
		}

		$viewMode = '';
		$resultKey = '';
		if (!empty($strHTMLControlName['MODE']))
		{
			switch ($strHTMLControlName['MODE'])
			{
				case 'CSV_EXPORT':
					$viewMode = 'CSV_EXPORT';
					$resultKey = 'ID';
					break;
				case 'EXTERNAL_ID':
					$viewMode = 'EXTERNAL_ID';
					$resultKey = 'XML_ID';
					break;
				case 'SIMPLE_TEXT':
					$viewMode = 'SIMPLE_TEXT';
					$resultKey = 'NAME';
					break;
				case 'ELEMENT_TEMPLATE':
					$viewMode = 'ELEMENT_TEMPLATE';
					$resultKey = 'NAME';
					break;
			}
		}

		if ($viewMode !== '' && $resultKey !== '')
		{
			return $element[$resultKey];
		}

		return htmlspecialcharsbx($element['NAME']) . ' [' . $elementId . ']';
	}

	public static function getAdminListViewHTMLExtended(array $property, array $value, $control): string
	{
		$result = '';
		if ($value['VALUE'])
		{
			$isPublicMode = (defined("PUBLIC_MODE") && (int)PUBLIC_MODE === 1);

			if ($isPublicMode)
			{
				$result .= self::GetPublicViewHTML($property, $value, $control);
			}
			else
			{
				$result .= self::GetAdminListViewHTML($property, $value, $control);
			}
		}

		return $result;
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		$result = parent::GetUIEntityEditorProperty($settings, $value);
		$result['allowedMultiple'] = false;

		return $result;
	}

	private static function getElement(int $elementId): ?array
	{
		if ($elementId <= 0)
		{
			return null;
		}

		$iterator = CIBlockElement::GetList(
			[],
			[
				'ID' => $elementId,
			],
			false,
			false,
			[
				'ID',
				'IBLOCK_ID',
				'NAME',
				'XML_ID',
			]
		);
		$element = $iterator->Fetch();

		if ($element)
		{
			return $element;
		}

		return null;
	}
}

/** @deprecated */
const BT_UT_SKU_CODE = Iblock\PropertyTable::USER_TYPE_SKU;
