<?php

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UI\Filter;
use Bitrix\Iblock;

class CIBlockPropertyDateTime
{
	public const USER_TYPE = 'DateTime';

	public const FORMAT_FULL = 'Y-m-d H:i:s';
	public const FORMAT_SHORT = 'Y-m-d';

	public static function GetUserTypeDescription()
	{
		return [
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_STRING,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("IBLOCK_PROP_DATETIME_DESC"),
			//optional handlers
			"GetPublicViewHTML" => [__CLASS__, "GetPublicViewHTML"],
			"GetPublicEditHTML" => [__CLASS__, "GetPublicEditHTML"],
			"GetAdminListViewHTML" => [__CLASS__, "GetAdminListViewHTML"],
			"GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
			"CheckFields" => [__CLASS__, "CheckFields"],
			"ConvertToDB" => [__CLASS__, "ConvertToDB"],
			"ConvertFromDB" => [__CLASS__, "ConvertFromDB"],
			"GetSettingsHTML" => [__CLASS__, "GetSettingsHTML"],
			"GetAdminFilterHTML" => [__CLASS__, "GetAdminFilterHTML"],
			"GetPublicFilterHTML" => [__CLASS__, "GetPublicFilterHTML"],
			"AddFilterFields" => [__CLASS__, "AddFilterFields"],
			"GetUIFilterProperty" => [__CLASS__, "GetUIFilterProperty"],
			'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
		];
	}

	public static function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered)
	{
		$filtered = false;

		$from = '';
		$from_name = $strHTMLControlName['VALUE'] . '_from';
		if (isset($strHTMLControlName['FILTER_ID']))
		{
			$filterOption = new Filter\Options($strHTMLControlName['FILTER_ID']);
			$filterData = $filterOption->getFilter();
			$from = !empty($filterData[$from_name]) ? $filterData[$from_name] : '';
		}
		elseif (isset($_REQUEST[$from_name]))
		{
			$from = $_REQUEST[$from_name];
		}
		elseif (
			isset($strHTMLControlName['GRID_ID'])
			&& isset($_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$from_name])
		)
		{
			$from = $_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$from_name];
		}

		if ($from)
		{
			$filterKey = '>=PROPERTY_' . $arProperty['ID'];
			if (CheckDateTime($from))
			{
				$from = static::ConvertToDB(
					$arProperty,
					['VALUE' => $from]
				);
				$arFilter[$filterKey] = $from['VALUE'];
			}
			else
			{
				$arFilter[$filterKey] = $from;
			}
			$filtered = true;
		}

		$to = '';
		$to_name = $strHTMLControlName['VALUE'] . '_to';
		if (isset($strHTMLControlName['FILTER_ID']))
		{
			$filterOption = new Filter\Options($strHTMLControlName['FILTER_ID']);
			$filterData = $filterOption->getFilter();
			$to = !empty($filterData[$to_name]) ? $filterData[$to_name] : '';
			if ($to)
			{
				$dateFormat = Date::convertFormatToPhp(CSite::getDateFormat());
				$dateParse = date_parse_from_format($dateFormat, $to);
				if (!mb_strlen($dateParse['hour']) && !mb_strlen($dateParse['minute']) && !mb_strlen($dateParse['second']))
				{
					$timeFormat = Date::convertFormatToPhp(CSite::getTimeFormat());
					$to .= ' ' . date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
				}
			}
		}
		elseif (isset($_REQUEST[$to_name]))
		{
			$to = $_REQUEST[$to_name];
		}
		elseif (
			isset($strHTMLControlName['GRID_ID'])
			&& isset($_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$to_name])
		)
		{
			$to = $_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$to_name];
		}

		if ($to)
		{
			$filterKey = '<=PROPERTY_'.$arProperty['ID'];
			if (CheckDateTime($to))
			{
				$to = static::ConvertToDB(
					$arProperty,
					['VALUE' => $to]
				);
				$arFilter[$filterKey] = $to['VALUE'];
			}
			else
			{
				$arFilter[$filterKey] = $to;
			}
			$filtered = true;
		}
	}

	public static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		$from_name = $strHTMLControlName["VALUE"] . '_from';
		$to_name = $strHTMLControlName["VALUE"] . '_to';

		$lAdmin = new CAdminList($strHTMLControlName["TABLE_ID"]);
		$lAdmin->InitFilter(array(
			$from_name,
			$to_name,
		));

		$from = $GLOBALS[$from_name] ?? "";
		$to = $GLOBALS[$to_name] ?? "";

		return  CAdminCalendar::CalendarPeriod($from_name, $to_name, $from, $to);
	}

	public static function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		/** @var CMain $APPLICATION*/
		global $APPLICATION;

		$from_name = $strHTMLControlName['VALUE'] . '_from';
		$to_name = $strHTMLControlName['VALUE'] . '_to';

		if (isset($_REQUEST[$from_name]))
		{
			$from = $_REQUEST[$from_name];
		}
		elseif (
			isset($strHTMLControlName['GRID_ID'])
			&& isset($_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$from_name])
		)
		{
			$from = $_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$from_name];
		}
		else
		{
			$from = '';
		}

		if (isset($_REQUEST[$to_name]))
		{
			$to = $_REQUEST[$to_name];
		}
		elseif (
			isset($strHTMLControlName['GRID_ID'])
			&& isset($_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$to_name])
		)
		{
			$to = $_SESSION['main.interface.grid'][$strHTMLControlName['GRID_ID']]['filter'][$to_name];
		}
		else
		{
			$to = '';
		}

		ob_start();

		$APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'FORM_NAME' => $strHTMLControlName['FORM_NAME'],
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
		if ($value["VALUE"] <> '')
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
		/** @var CMain $APPLICATION*/
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
		if($value["VALUE"] <> '')
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
		$arResult = [];
		$dateTimeValue = (string)($value["VALUE"] ?? '');
		if ($dateTimeValue !== '')
		{
			if (
				!CheckDateTime($dateTimeValue)
				&& !static::checkInternalFormatValue($dateTimeValue)
			)
			{
				$arResult[] = Loc::getMessage(
					'IBLOCK_PROP_DATETIME_ERROR_NEW',
					[
						'#FIELD_NAME#' => $arProperty['NAME'],
					]
				);
			}
		}

		return $arResult;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	public static function ConvertToDB($arProperty, $value)
	{
		$dateTimeValue = (string)($value['VALUE'] ?? '');
		if ($dateTimeValue !== '')
		{
			if (!static::checkInternalFormatValue($dateTimeValue))
			{
				try
				{
					$time = Bitrix\Main\Type\DateTime::createFromUserTime($dateTimeValue);

					$value['VALUE'] = $time->format(static::FORMAT_FULL);
				}
				catch (Bitrix\Main\ObjectException $e)
				{
				}
			}
			else
			{
				$value['VALUE'] = $dateTimeValue;
			}
		}

		return $value;
	}

	public static function ConvertFromDB($arProperty, $value, $format = '')
	{
		$dateTimeValue = (string)($value['VALUE'] ?? '');
		if ($dateTimeValue !== '')
		{
			try
			{
				$time = new Bitrix\Main\Type\DateTime($dateTimeValue, self::FORMAT_FULL);
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
		$arPropertyFields = [
			'HIDE' => [
				'ROW_COUNT',
				'COL_COUNT',
			],
		];

		return '';
	}

	/**
	 * @param array $property
	 * @param array $strHTMLControlName
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
	{
		$fields['type'] = 'date';
		$fields['time'] = true;
		$fields['filterable'] = '';
		$fields['operators'] = [
			'default' => '=',
			'exact' => '=',
			'range' => '><',
			'more' => '>',
			'less' => '<',
		];
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		$culture = Context::getCurrent()->getCulture();

		return [
			'type' => ($settings['MULTIPLE'] === 'Y') ? 'multidatetime' : 'datetime',
			'data' => [
				'enableTime' => true,
				'dateViewFormat' =>  $culture->getLongDateFormat() . ' ' . $culture->getShortTimeFormat(),
			]
		];
	}

	protected static function checkInternalFormatValue(string $value): bool
	{
		if ($value === '')
		{
			return false;
		}

		$correctValue = date_parse_from_format(self::FORMAT_FULL, $value);
		if ($correctValue['warning_count'] === 0 && $correctValue['error_count'] === 0)
		{
			return true;
		}

		$correctValue = date_parse_from_format(self::FORMAT_SHORT, $value);

		return ($correctValue['warning_count'] === 0 && $correctValue['error_count'] === 0);
	}
}
