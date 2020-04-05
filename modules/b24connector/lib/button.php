<?
namespace Bitrix\B24Connector;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Button
 * @package Bitrix\B24Connector
 */
class Button
{
	const ENUM_TYPE_OPEN_LINE = 'openline';
	const ENUM_TYPE_CRM_FORM = 'crmform';
	const ENUM_TYPE_CALLBACK = 'callback';

	const ENUM_LOCATION_TOP_LEFT = 1;
	const ENUM_LOCATION_TOP_MIDDLE = 2;
	const ENUM_LOCATION_TOP_RIGHT = 3;
	const ENUM_LOCATION_BOTTOM_RIGHT = 4;
	const ENUM_LOCATION_BOTTOM_MIDDLE = 5;
	const ENUM_LOCATION_BOTTOM_LEFT = 6;

	/**
	 * @return array
	 */
	public static function getTypeList()
	{
		return array(
			self::ENUM_TYPE_OPEN_LINE => Loc::getMessage('B24C_BUTTON_TYPE_NAME_' . strtoupper(self::ENUM_TYPE_OPEN_LINE)),
			self::ENUM_TYPE_CRM_FORM => Loc::getMessage('B24C_BUTTON_TYPE_NAME_' . strtoupper(self::ENUM_TYPE_CRM_FORM)),
			self::ENUM_TYPE_CALLBACK => Loc::getMessage('B24C_BUTTON_TYPE_NAME_' . strtoupper(self::ENUM_TYPE_CALLBACK))
		);
	}

	/**
	 * @return array
	 */
	public static function getLocationList()
	{
		return array(
			self::ENUM_LOCATION_TOP_LEFT => Loc::getMessage('B24C_BUTTON_LOCATION_TOP_LEFT'),
			self::ENUM_LOCATION_TOP_MIDDLE => Loc::getMessage('B24C_BUTTON_LOCATION_TOP_MIDDLE'),
			self::ENUM_LOCATION_TOP_RIGHT => Loc::getMessage('B24C_BUTTON_LOCATION_TOP_RIGHT'),
			self::ENUM_LOCATION_BOTTOM_RIGHT => Loc::getMessage('B24C_BUTTON_LOCATION_BOTTOM_RIGHT'),
			self::ENUM_LOCATION_BOTTOM_MIDDLE => Loc::getMessage('B24C_BUTTON_LOCATION_BOTTOM_MIDDLE'),
			self::ENUM_LOCATION_BOTTOM_LEFT => Loc::getMessage('B24C_BUTTON_LOCATION_BOTTOM_LEFT'),
		);
	}
}