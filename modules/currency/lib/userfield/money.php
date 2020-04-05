<?php
namespace Bitrix\Currency\UserField;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\UserField\TypeBase;

Loc::loadLanguageFile(__FILE__);

class Money extends TypeBase
{
	const USER_TYPE_ID = 'money';
	const DB_SEPARATOR = '|';

	public static function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage("USER_TYPE_MONEY_DESCRIPTION"),
			"BASE_TYPE" => 'string',//\CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
		);
	}

	public static function GetDBColumnType($userField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "varchar(200)";
			case "oracle":
				return "varchar2(200 char)";
			case "mssql":
				return "varchar(200)";
		}
		return '';
	}

	public static function OnBeforeSave($userField, $value)
	{
		list($value, $currency) = static::unFormatFromDB($value);
		if($value !== '')
			return static::formatToDB($value, $currency);
		else
			return '';
	}

	public static function PrepareSettings($userField)
	{
		list($value, $currency) = static::unFormatFromDB($userField["SETTINGS"]["DEFAULT_VALUE"]);
		if ($value !== '')
		{
			if ($currency === '')
				$currency = CurrencyManager::getBaseCurrency();
			$value = static::formatToDB($value, $currency);
		}

		return array(
			"DEFAULT_VALUE" => $value
		);
	}

	public static function GetSettingsHTML($userField = false, $control, $fromForm)
	{
		$currencyList = Editor::getListCurrency();

		$result = '';
		if ($fromForm)
		{
			$value = $GLOBALS[$control["NAME"]]["DEFAULT_VALUE"];
		}
		elseif (is_array($userField))
		{
			$value = htmlspecialcharsbx($userField["SETTINGS"]["DEFAULT_VALUE"]);
		}
		else
		{
			$defaultValue = '';
			$defaultCurrency = '';
			foreach($currencyList as $currencyInfo)
			{
				if($currencyInfo['BASE'] == 'Y')
				{
					$defaultCurrency = $currencyInfo['CURRENCY'];
				}
			}

			$value = static::formatToDB($defaultValue, $defaultCurrency);
		}

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_MONEY_DEFAULT_VALUE").':</td>
			<td>'.static::getInput($userField, $control["NAME"].'[DEFAULT_VALUE]', $value).'</td>
		</tr>
		';

		static::initDisplay(array('core_uf_money'));

		return $result;
	}

	public static function getEditFormHTML($userField, $control)
	{
		return static::GetPublicEdit($userField, array());
	}

	public static function getEditFormHTMLMulty($userField, $control)
	{
		return static::GetPublicEdit($userField, array());
	}

	public static function GetAdminListViewHTML($userField, $control)
	{
		$explode = static::unFormatFromDB($control['VALUE']);
		$currentValue = $explode[0] ? $explode[0] : '';
		$currentCurrency = $explode[1] ? $explode[1] : '';

		if(!$currentCurrency)
		{
			return intval($currentValue) ? $currentValue : '';
		}

		if(!empty($controlSettings['MODE']))
		{
			switch($controlSettings['MODE'])
			{
				case 'CSV_EXPORT':
					return $control['VALUE'];
				case 'SIMPLE_TEXT':
					return $currentValue;
				case 'ELEMENT_TEMPLATE':
					return $currentValue;
			}
		}

		return \CCurrencyLang::CurrencyFormat($currentValue, $currentCurrency, true);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		$fieldName = static::getFieldName($userField, $additionalParameters);
		$value = static::getFieldValue($userField, $additionalParameters);

		$html = '';

		$first = true;
		foreach($value as $res)
		{
			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			$res = static::getInput($userField, $fieldName, $res);
			$html .= static::getHelper()->wrapSingleField($res);
		}

		if($userField["MULTIPLE"] == "Y" && $additionalParameters["SHOW_BUTTON"] != "N")
		{
			$html .= static::getHelper()->getCloneButton($fieldName);
		}

		static::initDisplay(array('core_uf_money'));

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		$value = static::normalizeFieldValue($userField["VALUE"]);

		$html = '';
		$first = true;
		foreach($value as $res)
		{
			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			$explode = static::unFormatFromDB($res);
			$currentValue = strlen($explode[0]) > 0 ? doubleval($explode[0]) : '';
			$currentCurrency = $explode[1] ? $explode[1] : '';

			$format = \CCurrencyLang::GetFormatDescription($currentCurrency);

			$currentValue = number_format((float)$currentValue, $format['DECIMALS'], $format['DEC_POINT'], $format['THOUSANDS_SEP']);
			$currentValue = \CCurrencyLang::applyTemplate($currentValue, $format['FORMAT_STRING']);

			if(strlen($userField['PROPERTY_VALUE_LINK']) > 0)
			{
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', urlencode($res), $userField['PROPERTY_VALUE_LINK'])).'">'.$currentValue.'</a>';
			}
			else
			{
				$res = $currentValue;
			}

			$html .= static::getHelper()->wrapSingleField($res);
		}

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		$value = static::normalizeFieldValue($userField['VALUE']);

		$text = '';
		$first = true;
		foreach ($value as $res)
		{
			if(!$first)
				$text .= ', ';
			$first = false;

			$explode = static::unformatFromDB($res);
			$currentValue = strlen($explode[0]) > 0 ? doubleval($explode[0]) : '';
			$currentCurrency = $explode[1] ? $explode[1] : '';

			$format = \CCurrencyLang::GetFormatDescription($currentCurrency);

			$currentValue = number_format((float)$currentValue, $format['DECIMALS'], $format['DEC_POINT'], $format['THOUSANDS_SEP']);
			$currentValue = \CCurrencyLang::applyTemplate($currentValue, $format['FORMAT_STRING']);

			$text .= $currentValue;
		}

		return $text;
	}

	protected static function formatToDB($value, $currency)
	{
		$value = $value === '' ? '' : doubleval($value);

		if($value === '')
		{
			return '';
		}
		else
		{
			return $value.static::DB_SEPARATOR.trim($currency);
		}
	}

	protected static function unFormatFromDB($value)
	{
		return explode(static::DB_SEPARATOR, $value);
	}

	/**
	 * @deprecated deprecated since currency 17.5.2
	 *
	 * @return null|array
	 */
	public static function getListCurrency()
	{
		return Editor::getListCurrency();
	}

	protected static function getInput($userField, $fieldName, $dbValue)
	{
		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			'bitrix:currency.money.input',
			'',
			array(
				'CONTROL_ID' => $userField['FIELD_NAME'].'_'.Random::getString(5),
				'FIELD_NAME' => $fieldName,
				'VALUE' => $dbValue,
				'EXTENDED_CURRENCY_SELECTOR' => 'Y'
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);

		return ob_get_clean();
	}
}