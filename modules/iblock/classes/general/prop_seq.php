<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

class CIBlockPropertySequence
{
	/** @deprecated */
	public const USER_TYPE = Iblock\PropertyTable::USER_TYPE_SEQUENCE;

	public static function GetUserTypeDescription()
	{
		return [
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_NUMBER,
			'USER_TYPE' => Iblock\PropertyTable::USER_TYPE_SEQUENCE,
			'DESCRIPTION' => Loc::getMessage('IBLOCK_PROP_SEQUENCE_DESC'),
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetPublicEditHTML' => [__CLASS__, 'GetPropertyFieldHtml'],
			'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
			'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
			'GetAdminFilterHTML' => [__CLASS__, 'GetPublicFilterHTML'],
			'GetPublicFilterHTML' => [__CLASS__, 'GetPublicFilterHTML'],
			'AddFilterFields' => [__CLASS__, 'AddFilterFields'],
			'GetUIFilterProperty' => [__CLASS__, 'GetUIFilterProperty'],
			'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
			'GetUIEntityEditorPropertyEditHtml' => [__CLASS__, 'GetUIEntityEditorPropertyEditHtml'],
			'GetUIEntityEditorPropertyViewHtml' => [__CLASS__, 'GetUIEntityEditorPropertyViewHtml'],
		];
	}

	public static function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered)
	{
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$from = isset($_REQUEST[$from_name])? $_REQUEST[$from_name]: "";
		if (isset($strHTMLControlName["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($strHTMLControlName["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			$from = (!empty($filterData[$from_name]) ? $filterData[$from_name] : "");
			if ($from)
			{
				$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from;
				$filtered = true;
			}
		}
		elseif ($from)
		{
			$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from;
			$filtered = true;
		}

		$to_name = $strHTMLControlName["VALUE"].'_to';
		$to = isset($_REQUEST[$to_name])? $_REQUEST[$to_name]: "";
		if (isset($strHTMLControlName["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($strHTMLControlName["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			$to = (!empty($filterData[$to_name]) ? $filterData[$to_name] : "");
			if ($to)
			{
				$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to;
				$filtered = true;
			}
		}
		elseif ($to)
		{
			$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to;
			$filtered = true;
		}
	}

	public static function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$to_name = $strHTMLControlName["VALUE"].'_to';
		$from = isset($_REQUEST[$from_name])? $_REQUEST[$from_name]: "";
		$to = isset($_REQUEST[$to_name])? $_REQUEST[$to_name]: "";

		return '
			<input name="'.htmlspecialcharsbx($from_name).'" value="'.htmlspecialcharsbx($from).'" size="8" type="text"> ...
			<input name="'.htmlspecialcharsbx($to_name).'" value="'.htmlspecialcharsbx($to).'" size="8" type="text">
		';
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		if($value["VALUE"] > 0 && !$strHTMLControlName["COPY"])
		{
			$current_value = intval($value["VALUE"]);
		}
		else
		{
			$seq = new CIBlockSequence($arProperty["IBLOCK_ID"], $arProperty["ID"]);
			$current_value = $seq->GetNext();
		}

		$fieldName = $strHTMLControlName['VALUE'] ?? '';
		if (($arProperty['USER_TYPE_SETTINGS']['write'] ?? 'N') === 'Y')
		{
			return '<input type="text" size="5" name="' . $fieldName . '" value="' . $current_value . '">';
		}
		else
		{
			return
				'<input disabled type="text" size="5" name="' . $fieldName . '" value="' . $current_value . '">'
				. '<input type="hidden" size="5" name="' . $fieldName . '" value="'. $current_value. '">'
			;
		}
	}

	public static function PrepareSettings($arProperty)
	{
		//This method not for storing sequence value in the database
		//but it just sets starting value for it
		if(
			is_array($arProperty['USER_TYPE_SETTINGS'])
			&& isset($arProperty['USER_TYPE_SETTINGS']['current_value'])
			&& (int)($arProperty['USER_TYPE_SETTINGS']['current_value']) > 0
		)
		{
			$seq = new CIBlockSequence($arProperty['IBLOCK_ID'], $arProperty['ID']);
			$seq->SetNext((int)$arProperty['USER_TYPE_SETTINGS']['current_value']);
		}

		$strWritable = ($arProperty['USER_TYPE_SETTINGS']['write'] ?? 'N') === 'Y' ? 'Y' : 'N';

		$arProperty['USER_TYPE_SETTINGS'] = [
			'write' => $strWritable,
		];

		return $arProperty;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("SEARCHABLE", "WITH_DESCRIPTION", "ROW_COUNT", "COL_COUNT", "DEFAULT_VALUE")
		);

		$bWritable = ($arProperty['USER_TYPE_SETTINGS']['write'] ?? 'N') === 'Y';

		$html = '
			<tr valign="top">
				<td>'.Loc::getMessage("IBLOCK_PROP_SEQ_SETTING_WRITABLE").':</td>
				<td>
					<input type="hidden" name="'.$strHTMLControlName["NAME"].'[write]" value="N">
					<input type="checkbox" name="'.$strHTMLControlName["NAME"].'[write]" value="Y" '.($bWritable? 'checked="checked"': '').'>
				</td>
			</tr>
		';

		if ((int)($arProperty['ID'] ?? 0) > 0)
		{
			$seq = new CIBlockSequence($arProperty["IBLOCK_ID"], $arProperty["ID"]);
			$current_value = $seq->GetCurrent();
			return $html.'
			<tr valign="top">
				<td>'.Loc::getMessage("IBLOCK_PROP_SEQ_SETTING_CURRENT_VALUE").':</td>
				<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"].'[current_value]" value="'.$current_value.'"></td>
			</tr>
			';
		}
		else
		{
			$current_value = 1;
			return $html.'
			<tr valign="top">
				<td>'.Loc::getMessage("IBLOCK_PROP_SEQ_SETTING_CURRENT_VALUE").':</td>
				<td><input disabled type="text" size="5" name="'.$strHTMLControlName["NAME"].'[current_value]" value="'.$current_value.'"></td>
			</tr>
			';
		}
	}

	/**
	 * @param array $property
	 * @param array $control
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $control, &$fields)
	{
		$fields["type"] = "number";
		$fields["filterable"] = "";
		$fields["operators"] = array(
			"default" => "=",
			"exact" => "=",
			"enum" => "@",
			"range" => "><",
			"more" => ">",
			"less" => "<"
		);
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		return [
			'type' => $settings['MULTIPLE'] === 'Y' ? 'multinumber' : 'number',
		];
	}
}
