<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class CIBlockPropertyXmlID
{
	const USER_TYPE = 'ElementXmlID';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_STRING,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("IBLOCK_PROP_XMLID_DESC"),
			"GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
			"GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
			"GetSettingsHTML" => array(__CLASS__, "GetSettingsHTML"),
			'GetUIEntityEditorProperty' => array(__CLASS__, 'GetUIEntityEditorProperty'),
			'GetUIEntityEditorPropertyEditHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyEditHtml'),
			'GetUIEntityEditorPropertyViewHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyViewHtml'),
		);
	}

	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if(isset($strHTMLControlName['MODE']) && $strHTMLControlName["MODE"] == "CSV_EXPORT")
		{
			return $value["VALUE"];
		}
		elseif($value["VALUE"] <> '')
		{
			if(!isset($cache[$value["VALUE"]]))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = $ar_res;
				else
					$cache[$value["VALUE"]] = $value["VALUE"];
			}

			if (isset($strHTMLControlName['MODE']) && ($strHTMLControlName["MODE"] == "SIMPLE_TEXT" || $strHTMLControlName["MODE"] == 'ELEMENT_TEMPLATE'))
			{
				if (is_array($cache[$value["VALUE"]]))
					return $cache[$value["VALUE"]]["~NAME"];
				else
					return $cache[$value["VALUE"]];
			}
			else
			{
				if (is_array($cache[$value["VALUE"]]))
					return '<a href="'.$cache[$value["VALUE"]]["DETAIL_PAGE_URL"].'">'.$cache[$value["VALUE"]]["NAME"].'</a>';
				else
					return htmlspecialcharsex($cache[$value["VALUE"]]);
			}
		}
		else
		{
			return '';
		}
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if($value["VALUE"] <> '')
		{
			if(!array_key_exists($value["VALUE"], $cache))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = htmlspecialcharsbx($ar_res['NAME']).
					' [<a href="'.
					'/bitrix/admin/iblock_element_edit.php?'.
					'type='.urlencode($ar_res['IBLOCK_TYPE_ID']).
					'&amp;IBLOCK_ID='.$ar_res['IBLOCK_ID'].
					'&amp;ID='.$ar_res['ID'].
					'&amp;lang='.LANGUAGE_ID.
					'" title="'.Loc::getMessage("IBLOCK_PROP_EL_EDIT").'">'.$ar_res['ID'].'</a>]';
				else
					$cache[$value["VALUE"]] = htmlspecialcharsbx($value["VALUE"]);
			}
			return $cache[$value["VALUE"]];
		}
		else
		{
			return '&nbsp;';
		}
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$ar_res = false;
		if($value["VALUE"] <> '')
		{
			$db_res = CIBlockElement::GetList(
				array(),
				array("=XML_ID" => $value["VALUE"], "SHOW_HISTORY" => "Y"),
				false,
				false,
				array("ID", "IBLOCK_ID", "NAME")
			);
			$ar_res = $db_res->GetNext();
		}

		if(!$ar_res)
			$ar_res = array("NAME" => "");

		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		return  '<input name="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" id="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" value="'.htmlspecialcharsEx($value["VALUE"]).'" size="20" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.CUtil::JSEscape('/bitrix/admin/iblock_element_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($strHTMLControlName["VALUE"]).'&get_xml_id=Y&a=b'.($fixIBlock ? '&iblockfix=y' : '').'&tableId='.$windowTableId).'\', 900, 700);">'.
			'&nbsp;<span id="sp_'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" >'.$ar_res['NAME'].'</span>';
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT", "WITH_DESCRIPTION"),
		);
		return '';
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
		$value = [
			'VALUE' => $params['VALUE'] ?? ''
		];
		$paramsHTMLControl = [
			'MODE' => 'iblock_element_admin',
			'VALUE' => $params['FIELD_NAME'] ?? '',
		];
		return static::GetPublicViewHTML($settings, $value, $paramsHTMLControl);
	}
}