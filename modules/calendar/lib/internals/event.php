<?php


namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

/**
 * Class EventTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Event_Query query()
 * @method static EO_Event_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Event_Result getById($id)
 * @method static EO_Event_Result getList(array $parameters = [])
 * @method static EO_Event_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Event createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Event wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection wakeUpCollection($rows)
 */
class EventTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_event';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SECTION_ENTITY_ID_FIELD'),
			)),
			new Entity\IntegerField('PARENT_ID', array(
				'title' => Loc::getMessage('SECTION_ENTITY_PARENT_ID_FIELD'),
			)),
			new Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SECTION_ENTITY_ACTIVE_FIELD'),
			)),
			new Entity\BooleanField('DELETED', array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SECTION_ENTITY_DELETED_FIELD'),
			)),
			new Entity\StringField('CAL_TYPE', array(
				'validation' => array(__CLASS__, 'validateCalType'),
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_TYPE_FIELD'),
			)),
			new Entity\IntegerField('OWNER_ID', array(
				'title' => Loc::getMessage('SECTION_ENTITY_OWNER_ID_FIELD'),
			)),
			new Entity\StringField('NAME', array(
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('SECTION_ENTITY_NAME_FIELD'),
			)),
			new Entity\DatetimeField('DATE_FROM', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_FROM_FIELD'),
			)),
			new Entity\DatetimeField('DATE_TO', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_TO_FIELD'),
			)),
			new Entity\StringField('TZ_FROM', array(
				'validation' => array(__CLASS__, 'validateTzFrom'),
				'title' => Loc::getMessage('SECTION_ENTITY_TZ_FROM_FIELD'),
			)),
			new Entity\StringField('TZ_TO', array(
				'validation' => array(__CLASS__, 'validateTzTo'),
				'title' => Loc::getMessage('SECTION_ENTITY_TZ_TO_FIELD'),
			)),
			new Entity\IntegerField('TZ_OFFSET_FROM', array(
				'title' => Loc::getMessage('SECTION_ENTITY_TZ_OFFSET_FROM_FIELD'),
			)),
			new Entity\IntegerField('TZ_OFFSET_TO', array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SECTION_ENTITY_TZ_OFFSET_TO_FIELD'),
			)),
			new Entity\IntegerField('DATE_FROM_TS_UTC', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_FROM_TS_UTC_FIELD'),
			)),
			new Entity\IntegerField('DATE_TO_TS_UTC', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_TO_TS_UTC_FIELD'),
			)),
			new Entity\BooleanField('DT_SKIP_TIME', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SECTION_ENTITY_DT_SKIP_TIME_FIELD'),
			)),
			new Entity\IntegerField('DT_LENGTH', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DT_LENGTH_FIELD'),
			)),
			new Entity\IntegerField('CREATED_BY', array(
				'required' => true,
				'title' => Loc::getMessage('SECTION_ENTITY_CREATED_BY_FIELD'),
			)),
			new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DATE_CREATE_FIELD'),
			)),
			new Entity\DatetimeField('TIMESTAMP_X', array(
				'title' => Loc::getMessage('SECTION_ENTITY_TIMESTAMP_X_FIELD'),
			)),
			new Entity\TextField('DESCRIPTION', array(
				'title' => Loc::getMessage('SECTION_ENTITY_DESCRIPTION_FIELD'),
			)),
			new Entity\StringField('PRIVATE_EVENT', array(
				'validation' => array(__CLASS__, 'validatePrivateEvent'),
				'title' => Loc::getMessage('SECTION_ENTITY_PRIVATE_EVENT_FIELD'),
			)),
			new Entity\StringField('ACCESSIBILITY', array(
				'validation' => array(__CLASS__, 'validateAccessibility'),
				'title' => Loc::getMessage('SECTION_ENTITY_ACCESSIBILITY_FIELD'),
			)),
			new Entity\StringField('IMPORTANCE', array(
				'validation' => array(__CLASS__, 'validateImportance'),
				'title' => Loc::getMessage('SECTION_ENTITY_IMPORTANCE_FIELD'),
			)),
			new Entity\StringField('IS_MEETING', array(
				'title' => Loc::getMessage('SECTION_ENTITY_IS_MEETING_FIELD'),
			)),
			new Entity\StringField('MEETING_STATUS', array(
				'validation' => array(__CLASS__, 'validateMeetingStatus'),
				'title' => Loc::getMessage('SECTION_ENTITY_MEETING_STATUS_FIELD'),
			)),
			new Entity\IntegerField('MEETING_HOST', array(
				'title' => Loc::getMessage('SECTION_ENTITY_MEETING_HOST_FIELD'),
			)),
			new Entity\StringField('MEETING', array(
				'title' => Loc::getMessage('SECTION_ENTITY_MEETING_FIELD'),
			)),
			new Entity\StringField('LOCATION', array(
				'validation' => array(__CLASS__, 'validateLocation'),
				'title' => Loc::getMessage('SECTION_ENTITY_LOCATION_FIELD'),
			)),
			new Entity\StringField('REMIND', array(
				'validation' => array(__CLASS__, 'validateRemind'),
				'title' => Loc::getMessage('SECTION_ENTITY_REMIND_FIELD'),
			)),
			new Entity\StringField('COLOR', array(
				'validation' => array(__CLASS__, 'validateColor'),
				'title' => Loc::getMessage('SECTION_ENTITY_COLOR_FIELD'),
			)),
			new Entity\StringField('TEXT_COLOR', array(
				'validation' => array(__CLASS__, 'validateTextColor'),
				'title' => Loc::getMessage('SECTION_ENTITY_TEXT_COLOR_FIELD'),
			)),
			new Entity\StringField('RRULE', array(
				'validation' => array(__CLASS__, 'validateRrule'),
				'title' => Loc::getMessage('SECTION_ENTITY_RRULE_FIELD'),
			)),
			new Entity\TextField('EXDATE', array(
				'title' => Loc::getMessage('SECTION_ENTITY_EXDATE_FIELD'),
			)),
			new Entity\StringField('DAV_XML_ID', array(
				'validation' => array(__CLASS__, 'validateDavXmlId'),
				'title' => Loc::getMessage('SECTION_ENTITY_DAV_XML_ID_FIELD'),
			)),
			new Entity\StringField('CAL_DAV_LABEL', array(
				'title' => Loc::getMessage('SECTION_ENTITY_CAL_DAV_LABEL_FIELD'),
			)),
			new Entity\StringField('DAV_EXCH_LABEL', array(
				'validation' => array(__CLASS__, 'validateDavExchLabel'),
				'title' => Loc::getMessage('SECTION_ENTITY_DAV_EXCH_LABEL_FIELD'),
			)),
			new Entity\StringField('VERSION', array(
				'validation' => array(__CLASS__, 'validateVersion'),
				'title' => Loc::getMessage('SECTION_ENTITY_VERSION_FIELD'),
			)),
			new Entity\StringField('ATTENDEES_CODES', array(
				'validation' => array(__CLASS__, 'validateAttendeesCodes'),
				'title' => Loc::getMessage('SECTION_ENTITY_ATTENDEES_CODES_FIELD'),
			)),
			new Entity\IntegerField('RECURRENCE_ID', array(
				'title' => Loc::getMessage('SECTION_ENTITY_RECURRENCE_ID_FIELD'),
			)),
			new Entity\IntegerField('RELATIONS', array(
				'title' => Loc::getMessage('SECTION_ENTITY_RELATIONS_FIELD'),
			)),
			new Entity\TextField('SEARCHABLE_CONTENT', array(
				'title' => Loc::getMessage('SECTION_ENTITY_SEARCHABLE_CONTENT_FIELD'),
			)),
			new Entity\IntegerField('SECTION_ID', array(
				'title' => Loc::getMessage('SECTION_ENTITY_SECTION_ID_FIELD'),
			)),
			new Entity\StringField('G_EVENT_ID', array(
				'title' => Loc::getMessage('SECTION_ENTITY_G_EVENT_ID_FIELD'),
			)),
			new Entity\DatetimeField('ORIGINAL_DATE_FROM', array(
				'title' => Loc::getMessage('SECTION_ENTITY_ORIGINAL_DATE_FROM_FIELD'),
			)),
			new Entity\StringField('SYNC_STATUS', array(
				'validation' => array(__CLASS__, 'validateSyncStatus'),
				'title' => Loc::getMessage('SECTION_ENTITY_SYNC_STATUS'),
			)),
			new Entity\StringField('EVENT_TYPE', array(
				'validation' => array(__CLASS__, 'validateEventType'),
				'title' => Loc::getMessage('SECTION_ENTITY_EVENT_TYPE'),
			)),
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
	 * Returns validators for EVENT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEventType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for TZ_FROM field.
	 *
	 * @return array
	 */
	public static function validateTzFrom()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for TZ_TO field.
	 *
	 * @return array
	 */
	public static function validateTzTo()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for ACCESSIBILITY field.
	 *
	 * @return array
	 */
	public static function validateAccessibility()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}

	/**
	 * Returns validators for PRIVATE_EVENT field.
	 *
	 * @return array
	 */
	public static function validatePrivateEvent()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}

	/**
	 * Returns validators for IMPORTANCE field.
	 *
	 * @return array
	 */
	public static function validateImportance()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
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
	 * Returns validators for LOCATION field.
	 *
	 * @return array
	 */
	public static function validateLocation()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for REMIND field.
	 *
	 * @return array
	 */
	public static function validateRemind()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for RRULE field.
	 *
	 * @return array
	 */
	public static function validateRrule()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for DAV_XML_ID field.
	 *
	 * @return array
	 */
	public static function validateDavXmlId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for DAV_EXCH_LABEL field.
	 *
	 * @return array
	 */
	public static function validateDavExchLabel()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for CAL_DAV_LABEL field.
	 *
	 * @return array
	 */
	public static function validateCalDavLabel()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for VERSION field.
	 *
	 * @return array
	 */
	public static function validateVersion()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for ATTENDEES_CODES field.
	 *
	 * @return array
	 */
	public static function validateAttendeesCodes()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for RELATIONS field.
	 *
	 * @return array
	 */
	public static function validateRelations()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * @return Entity\Validator\Length[]
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateMeetingStatus(): array
	{
		return [
			new Main\Entity\Validator\Length(null, 1),
		];
	}

	/**
	 * @return Entity\Validator\Length[]
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateSyncStatus(): array
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
}