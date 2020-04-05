<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Type\Date,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class CIBlockPropertyDateTime
{
	const USER_TYPE = 'DateTime';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_STRING,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("IBLOCK_PROP_DATETIME_DESC"),
			//optional handlers
			"GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
			"GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
			"GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
			"CheckFields" => array(__CLASS__, "CheckFields"),
			"ConvertToDB" => array(__CLASS__, "ConvertToDB"),
			"ConvertFromDB" => array(__CLASS__, "ConvertFromDB"),
			"GetSettingsHTML" => array(__CLASS__, "GetSettingsHTML"),
			"GetAdminFilterHTML" => array(__CLASS__, "GetAdminFilterHTML"),
			"GetPublicFilterHTML" => array(__CLASS__, "GetPublicFilterHTML"),
			"AddFilterFields" => array(__CLASS__, "AddFilterFields"),
		);
	}

	public static function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered)
	{
		$filtered = false;

		//TODO: remove this condition after main 17.0.0 will be stable
		$existFilterOptions = class_exists('\Bitrix\Main\UI\Filter\Options') && method_exists('\Bitrix\Main\UI\Filter\Options', 'getFilter');

		$from = "";
		$from_name = $strHTMLControlName["VALUE"].'_from';
		if(isset($_REQUEST[$from_name]))
		{
			$from = $_REQUEST[$from_name];
		}
		elseif(isset($strHTMLControlName["GRID_ID"]) &&
			isset($_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$from_name]))
		{
			$from = $_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$from_name];
		}
		elseif($existFilterOptions && isset($strHTMLControlName["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($strHTMLControlName["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			$from = !empty($filterData[$from_name]) ? $filterData[$from_name] : "";
		}

		if($from)
		{
			if(CheckDateTime($from))
			{
				$from = static::ConvertToDB($arProperty, array("VALUE"=>$from));
				$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from["VALUE"];
				$filtered = true;
			}
			else
			{
				$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from;
				$filtered = true;
			}
		}

		$to = "";
		$to_name = $strHTMLControlName["VALUE"].'_to';
		if(isset($_REQUEST[$to_name]))
		{
			$to = $_REQUEST[$to_name];
		}
		elseif(isset($strHTMLControlName["GRID_ID"]) &&
			isset($_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$to_name]))
		{
			$to = $_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$to_name];
		}
		elseif($existFilterOptions && isset($strHTMLControlName["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($strHTMLControlName["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			$to = !empty($filterData[$to_name]) ? $filterData[$to_name] : "";
			if($to)
			{
				$dateFormat = Date::convertFormatToPhp(CSite::getDateFormat());
				$dateParse = date_parse_from_format($dateFormat, $to);
				if(!strlen($dateParse["hour"]) && !strlen($dateParse["minute"]) && !strlen($dateParse["second"]))
				{
					$timeFormat = Date::convertFormatToPhp(CSite::getTimeFormat());
					$to .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
				}
			}
		}

		if($to)
		{
			if(CheckDateTime($to))
			{
				$to = static::ConvertToDB($arProperty, array("VALUE"=>$to));
				$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to["VALUE"];
				$filtered = true;
			}
			else
			{
				$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to;
				$filtered = true;
			}
		}
	}

	public static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$to_name = $strHTMLControlName["VALUE"].'_to';

		$lAdmin = new CAdminList($strHTMLControlName["TABLE_ID"]);
		$lAdmin->InitFilter(array(
			$from_name,
			$to_name,
		));

		$from = isset($GLOBALS[$from_name])? $GLOBALS[$from_name]: "";
		$to = isset($GLOBALS[$to_name])? $GLOBALS[$to_name]: "";

		return  CAdminCalendar::CalendarPeriod($from_name, $to_name, $from, $to);
	}

	public static function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		/** @var CMain */
		global $APPLICATION;

		$from_name = $strHTMLControlName["VALUE"].'_from';
		$to_name = $strHTMLControlName["VALUE"].'_to';

		if (isset($_REQUEST[$from_name]))
			$from = $_REQUEST[$from_name];
		elseif (
			isset($strHTMLControlName["GRID_ID"])
			&& isset($_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$from_name])
		)
			$from = $_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$from_name];
		else
			$from = "";

		if (isset($_REQUEST[$to_name]))
			$to = $_REQUEST[$to_name];
		elseif (
			isset($strHTMLControlName["GRID_ID"])
			&& isset($_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$to_name])
		)
			$to = $_SESSION["main.interface.grid"][$strHTMLControlName["GRID_ID"]]["filter"][$to_name];
		else
			$to = "";

		ob_start();

		$APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'FORM_NAME' => $strHTMLControlName["FORM_NAME"],
				'SHOW_INPUT' => 'Y',
				'INPUT_NAME' => $from_name,
				'INPUT_VALUE' => $from,
				'INPUT_NAME_FINISH' => $to_name,
				'INPUT_VALUE_FINISH' => $to,
				'INPUT_ADDITIONAL_ATTR' => 'size="10"',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);

		$s = ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if (strlen($value["VALUE"]) > 0)
		{
			if (!CheckDateTime($value["VALUE"]))
				$value = static::ConvertFromDB($arProperty, $value, $strHTMLControlName["DATETIME_FORMAT"]);

			if (isset($strHTMLControlName["MODE"]))
			{
				if ($strHTMLControlName["MODE"] == "CSV_EXPORT")
					return $value["VALUE"];
				elseif ($strHTMLControlName["MODE"] == "SIMPLE_TEXT")
					return $value["VALUE"];
				elseif ($strHTMLControlName["MODE"] == "ELEMENT_TEMPLATE")
					return $value["VALUE"];
			}
			return str_replace(" ", "&nbsp;", htmlspecialcharsEx($value["VALUE"]));
		}

		return '';
	}

	public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
	{
		/** @var CMain */
		global $APPLICATION;

		$s = '<input type="text" name="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" size="25" value="'.htmlspecialcharsbx($value["VALUE"]).'" />';
		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'FORM_NAME' => $strHTMLControlName["FORM_NAME"],
				'INPUT_NAME' => $strHTMLControlName["VALUE"],
				'INPUT_VALUE' => $value["VALUE"],
				'SHOW_TIME' => "Y",
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$s .= ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if(strlen($value["VALUE"])>0)
		{
			if(!CheckDateTime($value["VALUE"]))
				$value = static::ConvertFromDB($arProperty, $value);
			return str_replace(" ", "&nbsp;", htmlspecialcharsex($value["VALUE"]));
		}
		else
			return '&nbsp;';
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return  CAdminCalendar::CalendarDate($strHTMLControlName["VALUE"], $value["VALUE"], 20, true).
			($arProperty["WITH_DESCRIPTION"]=="Y" && '' != trim($strHTMLControlName["DESCRIPTION"]) ?
				'&nbsp;<input type="text" size="20" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialcharsbx($value["DESCRIPTION"]).'">'
				:''
			);
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	public static function CheckFields($arProperty, $value)
	{
		$arResult = array();
		if(strlen($value["VALUE"])>0 && !CheckDateTime($value["VALUE"]))
			$arResult[] = Loc::getMessage("IBLOCK_PROP_DATETIME_ERROR_NEW", array("#FIELD_NAME#" => $arProperty["NAME"]));
		return $arResult;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	public static function ConvertToDB($arProperty, $value)
	{
		if (strlen($value["VALUE"]) > 0)
		{
			try
			{
				$time = Bitrix\Main\Type\DateTime::createFromUserTime($value['VALUE']);

				$value['VALUE'] = $time->format("Y-m-d H:i:s");
			}
			catch(Bitrix\Main\ObjectException $e)
			{
			}
		}

		return $value;
	}

	public static function ConvertFromDB($arProperty, $value, $format = '')
	{
		if (strlen($value["VALUE"]) > 0)
		{
			try
			{
				$time = new Bitrix\Main\Type\DateTime($value['VALUE'], "Y-m-d H:i:s");
				$time->toUserTime();

				if ($format === 'SHORT')
					$phpFormat = $time->convertFormatToPhp(FORMAT_DATE);
				elseif ($format === 'FULL')
					$phpFormat = $time->convertFormatToPhp(FORMAT_DATETIME);
				elseif ($format)
					$phpFormat = $time->convertFormatToPhp($format);
				else
					$phpFormat = $time->getFormat();

				$value["VALUE"] = $time->format($phpFormat);
				$value["VALUE"] = str_replace(" 00:00:00", "", $value["VALUE"]);
			}
			catch(Bitrix\Main\ObjectException $e)
			{
			}
		}

		return $value;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT"),
		);

		return '';
	}
}