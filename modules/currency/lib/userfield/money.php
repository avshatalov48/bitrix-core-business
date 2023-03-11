<?php
namespace Bitrix\Currency\UserField;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\UserField\TypeBase;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Money
 * @package Bitrix\Currency\UserField
 * @deprecated
 */

class Money extends TypeBase
{
	const USER_TYPE_ID = MoneyType::USER_TYPE_ID;
	const DB_SEPARATOR = MoneyType::DB_SEPARATOR;

	/**
	 * @return array
	 */
	public static function getUserTypeDescription()
	{
		return MoneyType::getUserTypeDescription();
	}

	/**
	 * @param $userField
	 * @param array $additionalParameters
	 * @return string
	 */
	public static function getPublicView($userField, $additionalParameters = [])
	{
		return MoneyType::renderView($userField, $additionalParameters);
	}

	/**
	 * @param $userField
	 * @param array $additionalParameters
	 * @return string
	 */
	public static function getPublicEdit($userField, $additionalParameters = [])
	{
		return MoneyType::renderEdit($userField, $additionalParameters);
	}

	/**
	 * @param array|bool $userField
	 * @param $additionalParameters
	 * @param $fromForm
	 * @return string
	 */
	public static function getSettingsHtml($userField, $additionalParameters, $fromForm)
	{
		return MoneyType::renderSettings($userField, $additionalParameters, $fromForm);
	}

	/**
	 * @param $userField
	 * @param $additionalParameters
	 * @return string
	 */
	public static function getEditFormHtml($userField, $additionalParameters)
	{
		return MoneyType::renderEditForm($userField, $additionalParameters);
	}

	/**
	 * @param $userField
	 * @param $additionalParameters
	 * @return string
	 */
	public static function getAdminListViewHtml($userField, $additionalParameters)
	{
		return MoneyType::renderAdminListView($userField, $additionalParameters);
	}

	/**
	 * @param $userField
	 * @param $additionalParameters
	 * @return string
	 */
	public static function getAdminListEditHtml($userField, $additionalParameters)
	{
		return MoneyType::renderAdminListEdit($userField, $additionalParameters);
	}

	/**
	 * @param $userField
	 * @return string
	 */
	public static function getPublicText($userField)
	{
		return MoneyType::renderText($userField);
	}

	/**
	 * @param $userField
	 * @return string
	 */
	public static function GetDBColumnType($userField)
	{
		return MoneyType::getDbColumnType();
	}

	/**
	 * @param $userField
	 * @param $value
	 * @return string
	 */
	public static function OnBeforeSave($userField, $value)
	{
		return MoneyType::onBeforeSave($userField, $value);
	}

	/**
	 * @param $userField
	 * @return array
	 */
	public static function PrepareSettings($userField)
	{
		return MoneyType::prepareSettings($userField);
	}

	/**
	 * @param $value
	 * @param $currency
	 * @return string
	 */
	protected static function formatToDB($value, $currency)
	{
		return MoneyType::formatToDb($value, $currency);
	}

	/**
	 * @param $value
	 * @return array
	 */
	protected static function unFormatFromDB($value)
	{
		return MoneyType::unFormatFromDb($value);
	}

	/**
	 * @param $userField
	 * @param $control
	 * @return string
	 * @deprecated
	 */
	public static function getEditFormHtmlMulty($userField, $control)
	{
		return MoneyType::renderEditForm($userField, $additionalParameters);
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

	/**
	 * @param $userField
	 * @param $fieldName
	 * @param $dbValue
	 * @return false|string
	 * @deprecated
	 */
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