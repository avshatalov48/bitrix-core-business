<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * Class SectionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) optional
 * <li> XML_ID string(100) optional
 * <li> EXTERNAL_ID string(100) optional
 * <li> ACTIVE bool optional default 'Y'
 * <li> DESCRIPTION string optional
 * <li> COLOR string(10) optional
 * <li> TEXT_COLOR string(10) optional
 * <li> EXPORT string(255) optional
 * <li> SORT int optional default 100
 * <li> CAL_TYPE string(100) optional
 * <li> OWNER_ID int optional
 * <li> CREATED_BY int mandatory
 * <li> PARENT_ID int optional
 * <li> DATE_CREATE datetime optional
 * <li> TIMESTAMP_X datetime optional
 * <li> DAV_EXCH_CAL string(255) optional
 * <li> DAV_EXCH_MOD string(255) optional
 * <li> CAL_DAV_CON string(255) optional
 * <li> CAL_DAV_CAL string(255) optional
 * <li> CAL_DAV_MOD string(255) optional
 * <li> IS_EXCHANGE string(1) optional
 * <li> GAPI_CALENDAR_ID string(255) optional
 * <li> SYNC_TOKEN string(100) optional
 * </ul>
 *
 * @package Bitrix\Calendar
 **/

class SectionTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_section';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SECTION_ENTITY_ID_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('SECTION_ENTITY_NAME_FIELD'),
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('SECTION_ENTITY_XML_ID_FIELD'),
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalId'),
				'title' => Loc::getMessage('SECTION_ENTITY_EXTERNAL_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SECTION_ENTITY_ACTIVE_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('SECTION_ENTITY_DESCRIPTION_FIELD'),
			),
			'COLOR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateColor'),
				'title' => Loc::getMessage('SECTION_ENTITY_COLOR_FIELD'),
			),
			'TEXT_COLOR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTextColor'),
				'title' => Loc::getMessage('SECTION_ENTITY_TEXT_COLOR_FIELD'),
			),
			'EXPORT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExport'),
				'title' => Loc::getMessage('SECTION_ENTITY_EXPORT_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SECTION_ENTITY_SORT_FIELD'),
			),
			'CAL_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCalType'),
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_TYPE_FIELD'),
			),
			'OWNER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SECTION_ENTITY_OWNER_ID_FIELD'),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SECTION_ENTITY_CREATED_BY_FIELD'),
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SECTION_ENTITY_PARENT_ID_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_CREATE_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SECTION_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'DAV_EXCH_CAL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDavExchCal'),
				'title' => Loc::getMessage('SECTION_ENTITY_DAV_EXCH_CAL_FIELD'),
			),
			'DAV_EXCH_MOD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDavExchMod'),
				'title' => Loc::getMessage('SECTION_ENTITY_DAV_EXCH_MOD_FIELD'),
			),
			'CAL_DAV_CON' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCalDavCon'),
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_DAV_CON_FIELD'),
			),
			'CAL_DAV_CAL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCalDavCal'),
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_DAV_CAL_FIELD'),
			),
			'CAL_DAV_MOD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCalDavMod'),
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_DAV_MOD_FIELD'),
			),
			'IS_EXCHANGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateIsExchange'),
				'title' => Loc::getMessage('SECTION_ENTITY_IS_EXCHANGE_FIELD'),
			),
			'GAPI_CALENDAR_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateGapiCalendarId'),
				'title' => Loc::getMessage('SECTION_ENTITY_GAPI_CALENDAR_ID_FIELD'),
			),
			'SYNC_TOKEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSyncToken'),
				'title' => Loc::getMessage('SECTION_ENTITY_SYNC_TOKEN_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for COLOR field.
	 *
	 * @return array
	 */
	public static function validateColor()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	/**
	 * Returns validators for TEXT_COLOR field.
	 *
	 * @return array
	 */
	public static function validateTextColor()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	/**
	 * Returns validators for EXPORT field.
	 *
	 * @return array
	 */
	public static function validateExport()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CAL_TYPE field.
	 *
	 * @return array
	 */
	public static function validateCalType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for DAV_EXCH_CAL field.
	 *
	 * @return array
	 */
	public static function validateDavExchCal()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for DAV_EXCH_MOD field.
	 *
	 * @return array
	 */
	public static function validateDavExchMod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CAL_DAV_CON field.
	 *
	 * @return array
	 */
	public static function validateCalDavCon()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CAL_DAV_CAL field.
	 *
	 * @return array
	 */
	public static function validateCalDavCal()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CAL_DAV_MOD field.
	 *
	 * @return array
	 */
	public static function validateCalDavMod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for IS_EXCHANGE field.
	 *
	 * @return array
	 */
	public static function validateIsExchange()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
	 * Returns validators for GAPI_CALENDAR_ID field.
	 *
	 * @return array
	 */
	public static function validateGapiCalendarId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for SYNC_TOKEN field.
	 *
	 * @return array
	 */
	public static function validateSyncToken()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
}