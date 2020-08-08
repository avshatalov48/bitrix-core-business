<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class CIBlockPropertySequence
{
	const USER_TYPE = 'Sequence';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_NUMBER,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("IBLOCK_PROP_SEQUENCE_DESC"),
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
			"GetPublicEditHTML" => array(__CLASS__, "GetPropertyFieldHtml"),
			"PrepareSettings" =>array(__CLASS__, "PrepareSettings"),
			"GetSettingsHTML" =>array(__CLASS__, "GetSettingsHTML"),
			"GetAdminFilterHTML" => array(__CLASS__, "GetPublicFilterHTML"),
			"GetPublicFilterHTML" => array(__CLASS__, "GetPublicFilterHTML"),
			"AddFilterFields" => array(__CLASS__, "AddFilterFields"),
			"GetUIFilterProperty" => array(__CLASS__, "GetUIFilterProperty"),
			'GetUIEntityEditorProperty' => array(__CLASS__, 'GetUIEntityEditorProperty'),
			'GetUIEntityEditorPropertyEditHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyEditHtml'),
			'GetUIEntityEditorPropertyViewHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyViewHtml'),
		);
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

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["write"]==="Y")
			return '<input type="text" size="5" name="'.$strHTMLControlName["VALUE"].'" value="'.$current_value.'">';
		else
			return '<input disabled type="text" size="5" name="'.$strHTMLControlName["VALUE"].'" value="'.$current_value.'">'.
				'<input type="hidden" size="5" name="'.$strHTMLControlName["VALUE"].'" value="'.$current_value.'">';
	}

	public static function PrepareSettings($arProperty)
	{
		//This method not for storing sequence value in the database
		//but it just sets starting value for it
		if(
			is_array($arProperty["USER_TYPE_SETTINGS"])
			&& isset($arProperty["USER_TYPE_SETTINGS"]["current_value"])
			&& intval($arProperty["USER_TYPE_SETTINGS"]["current_value"]) > 0
		)
		{
			$seq = new CIBlockSequence($arProperty["IBLOCK_ID"], $arProperty["ID"]);
			$seq->SetNext($arProperty["USER_TYPE_SETTINGS"]["current_value"]);
		}

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["write"]==="Y")
			$strWritable = "Y";
		else
			$strWritable = "N";

		$arProperty['USER_TYPE_SETTINGS'] = array(
			"write" => $strWritable,
		);
		return $arProperty;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("SEARCHABLE", "WITH_DESCRIPTION", "ROW_COUNT", "COL_COUNT", "DEFAULT_VALUE")
		);

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["write"]==="Y")
			$bWritable = true;
		else
			$bWritable = false;

		$html = '
			<tr valign="top">
				<td>'.Loc::getMessage("IBLOCK_PROP_SEQ_SETTING_WRITABLE").':</td>
				<td><input type="checkbox" name="'.$strHTMLControlName["NAME"].'[write]" value="Y" '.($bWritable? 'checked="checked"': '').'></td>
			</tr>
		';

		if($arProperty["ID"] > 0)
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
			'type' => 'custom',
		];
	}

	public static function GetUIEntityEditorPropertyEditHtml(array $params = []) : string
	{
		$settings = $params['SETTINGS'] ?? [];
		$value = $params['VALUE'] ?? '';
		$paramsHTMLControl = [
			'MODE' => 'iblock_element_admin',
			'VALUE' => $params['FIELD_NAME'] ?? '',
		];
		return self::GetPropertyFieldHtml($settings, $value, $paramsHTMLControl);
	}

	public static function GetUIEntityEditorPropertyViewHtml(array $params = []) : string
	{
		$settings = $params['SETTINGS'] ?? [];
		$value = $params['VALUE'] ?? '';
		$paramsHTMLControl = [
			'MODE' => 'iblock_element_admin',
			'VALUE' => $params['FIELD_NAME'] ?? '',
		];
		return self::GetPropertyFieldHtml($settings, $value, $paramsHTMLControl);
	}
}