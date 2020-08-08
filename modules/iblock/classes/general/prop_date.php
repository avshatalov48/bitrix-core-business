<?php
use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class CIBlockPropertyDate extends CIBlockPropertyDateTime
{
	const USER_TYPE = 'Date';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_STRING,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("IBLOCK_PROP_DATE_DESC"),
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
			"GetUIFilterProperty" => array(__CLASS__, "GetUIFilterProperty"),
			'GetUIEntityEditorProperty' => array(__CLASS__, 'GetUIEntityEditorProperty'),
			//"GetORMFields" => array(__CLASS__, "GetORMFields"),
		);
	}

	/**
	 * @param \Bitrix\Main\ORM\Entity $valueEntity
	 * @param Iblock\Property         $property
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function GetORMFields($valueEntity, $property)
	{
		$valueEntity->addField(
			(new \Bitrix\Main\ORM\Fields\DateField('DATE'))
				->configureFormat('Y-m-d')
				->configureColumnName($valueEntity->getField('VALUE')->getColumnName())
		);
	}

	public static function ConvertToDB($arProperty, $value)
	{
		if ($value["VALUE"] <> '')
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], CLang::GetDateFormat("SHORT"), "YYYY-MM-DD");

		return $value;
	}

	public static function ConvertFromDB($arProperty, $value, $format = '')
	{
		if($value["VALUE"] <> '')
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], "YYYY-MM-DD", CLang::GetDateFormat("SHORT"));

		return $value;
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
				'SHOW_TIME' => "N",
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$s .= ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return  CAdminCalendar::CalendarDate($strHTMLControlName["VALUE"], $value["VALUE"], 20, false).
		($arProperty["WITH_DESCRIPTION"]=="Y" && '' != trim($strHTMLControlName["DESCRIPTION"]) ?
			'&nbsp;<input type="text" size="20" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialcharsbx($value["DESCRIPTION"]).'">'
			:''
		);
	}

	/**
	 * @param array $property
	 * @param array $control
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $control, &$fields)
	{
		parent::GetUIFilterProperty($property, $control, $fields);
		unset($fields["time"]);
	}

	/**
	 * @param $settings
	 * @param $value
	 *
	 * @return array
	 */
	public static function GetUIEntityEditorProperty($settings, $value)
	{
		$dateTimeResult = parent::GetUIEntityEditorProperty($settings, $value);
		$dateTimeResult['data'] = [
			'enableTime' => false,
			'dateViewFormat' =>  \Bitrix\Main\Context::getCurrent()->getCulture()->getLongDateFormat(),
		];
		return $dateTimeResult;
	}
}