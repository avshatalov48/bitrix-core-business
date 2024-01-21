<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Util;
use Bitrix\Main;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

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
	use DeleteByFilterTrait;
	
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
	 * Returns userfield entity code, to make userfields work with orm
	 *
	 * @return string
	 */
	public static function getUfId()
	{
		return Util::USER_FIELD_ENTITY_ID;
	}


	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new IntegerField('PARENT_ID'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_PARENT_ID_FIELD'))
			,
			(new BooleanField('ACTIVE'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_ACTIVE_FIELD'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
			,
			(new BooleanField('DELETED'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DELETED_FIELD'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
			,
			(new StringField('CAL_TYPE',
				[
					'validation' => [__CLASS__, 'validateCalType']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_CAL_TYPE_FIELD'))
			,
			(new IntegerField('OWNER_ID'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_OWNER_ID_FIELD'))
				->configureRequired(true)
			,
			(new StringField('NAME',
				[
					'validation' => [__CLASS__, 'validateName']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_NAME_FIELD'))
			,
			(new DatetimeField('DATE_FROM'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DATE_FROM_FIELD'))
			,
			(new DatetimeField('DATE_TO'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DATE_TO_FIELD'))
			,
			(new DatetimeField('ORIGINAL_DATE_FROM'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_ORIGINAL_DATE_FROM_FIELD'))
			,
			(new StringField('TZ_FROM',
				[
					'validation' => [__CLASS__, 'validateTzFrom']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TZ_FROM_FIELD'))
			,
			(new StringField('TZ_TO',
				[
					'validation' => [__CLASS__, 'validateTzTo']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TZ_TO_FIELD'))
			,
			(new IntegerField('TZ_OFFSET_FROM'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TZ_OFFSET_FROM_FIELD'))
			,
			(new IntegerField('TZ_OFFSET_TO'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TZ_OFFSET_TO_FIELD'))
			,
			(new IntegerField('DATE_FROM_TS_UTC'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DATE_FROM_TS_UTC_FIELD'))
			,
			(new IntegerField('DATE_TO_TS_UTC'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DATE_TO_TS_UTC_FIELD'))
			,
			(new BooleanField('DT_SKIP_TIME'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DT_SKIP_TIME_FIELD'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
			,
			(new IntegerField('DT_LENGTH'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DT_LENGTH_FIELD'))
			,
			(new StringField('EVENT_TYPE',
				[
					'validation' => [__CLASS__, 'validateEventType']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_EVENT_TYPE_FIELD'))
			,
			(new IntegerField('CREATED_BY'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_CREATED_BY_FIELD'))
				->configureRequired(true)
			,
			(new DatetimeField('DATE_CREATE'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DATE_CREATE_FIELD'))
			,
			(new DatetimeField('TIMESTAMP_X'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TIMESTAMP_X_FIELD'))
			,
			(new TextField('DESCRIPTION'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DESCRIPTION_FIELD'))
			,
			(new DatetimeField('DT_FROM'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DT_FROM_FIELD'))
			,
			(new DatetimeField('DT_TO'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DT_TO_FIELD'))
			,
			(new StringField('PRIVATE_EVENT',
				[
					'validation' => [__CLASS__, 'validatePrivateEvent']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_PRIVATE_EVENT_FIELD'))
			,
			(new StringField('ACCESSIBILITY',
				[
					'validation' => [__CLASS__, 'validateAccessibility']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_ACCESSIBILITY_FIELD'))
			,
			(new StringField('IMPORTANCE',
				[
					'validation' => [__CLASS__, 'validateImportance']
				]
			))->configureTitle(Loc::getMessage('EVENT_ENTITY_IMPORTANCE_FIELD'))
			,
			(new StringField('IS_MEETING',
				[
					'validation' => [__CLASS__, 'validateIsMeeting']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_IS_MEETING_FIELD'))
			,
			(new StringField('MEETING_STATUS',
				[
					'validation' => [__CLASS__, 'validateMeetingStatus']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_MEETING_STATUS_FIELD'))
			,
			(new IntegerField('MEETING_HOST'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_MEETING_HOST_FIELD'))
			,
			(new TextField('MEETING'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_MEETING_FIELD'))
			,
			(new StringField('LOCATION',
				[
					'validation' => [__CLASS__, 'validateLocation']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_LOCATION_FIELD'))
			,
			(new TextField('REMIND',
				[
					'validation' => [__CLASS__, 'validateRemind']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_REMIND_FIELD'))
			,
			(new StringField('COLOR',
				[
					'validation' => [__CLASS__, 'validateColor']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_COLOR_FIELD'))
			,
			(new StringField('TEXT_COLOR',
				[
					'validation' => [__CLASS__, 'validateTextColor']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_TEXT_COLOR_FIELD'))
			,
			(new StringField('RRULE',
				[
					'validation' => [__CLASS__, 'validateRrule']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_RRULE_FIELD'))
			,
			(new TextField('EXDATE',
				[]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_EXDATE_FIELD'))
			,
			(new StringField('DAV_XML_ID',
				[
					'validation' => [__CLASS__, 'validateDavXmlId']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DAV_XML_ID_FIELD'))
			,
			(new StringField('G_EVENT_ID',
				[
					'validation' => [__CLASS__, 'validateGEventId']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_G_EVENT_ID_FIELD'))
			,
			(new StringField('DAV_EXCH_LABEL',
				[
					'validation' => [__CLASS__, 'validateDavExchLabel']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_DAV_EXCH_LABEL_FIELD'))
			,
			(new StringField('CAL_DAV_LABEL',
				[
					'validation' => [__CLASS__, 'validateCalDavLabel']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_CAL_DAV_LABEL_FIELD'))
			,
			(new StringField('VERSION',
				[
					'validation' => [__CLASS__, 'validateVersion']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_VERSION_FIELD'))
			,
			(new TextField('ATTENDEES_CODES'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_ATTENDEES_CODES_FIELD'))
			,
			(new IntegerField('RECURRENCE_ID'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_RECURRENCE_ID_FIELD'))
			,
			(new StringField('RELATIONS',
				[
					'validation' => [__CLASS__, 'validateRelations']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_RELATIONS_FIELD'))
			,
			(new TextField('SEARCHABLE_CONTENT'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_SEARCHABLE_CONTENT_FIELD'))
			,
			(new IntegerField('SECTION_ID'))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_SECTION_ID_FIELD'))
			,
			(new StringField('SYNC_STATUS',
				[
					'validation' => [__CLASS__, 'validateSyncStatus']
				]
			))
				->configureTitle(Loc::getMessage('EVENT_ENTITY_SYNC_STATUS_FIELD'))
			,
			(new ReferenceField(
				'SECTION',
				SectionTable::class,
				Join::on('this.SECTION_ID', 'ref.ID'),
			))
			,
			(new ReferenceField(
				'EVENT_SECT',
				EventSectTable::class,
				Join::on('this.ID', 'ref.EVENT_ID'),
			))
			,
		];
	}

	/**
	 * Returns validators for CAL_TYPE field.
	 *
	 * @return array
	 */
	public static function validateCalType(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for TZ_FROM field.
	 *
	 * @return array
	 */
	public static function validateTzFrom(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for TZ_TO field.
	 *
	 * @return array
	 */
	public static function validateTzTo(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for EVENT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEventType(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for PRIVATE_EVENT field.
	 *
	 * @return array
	 */
	public static function validatePrivateEvent(): array
	{
		return [
			new LengthValidator(null, 10),
		];
	}

	/**
	 * Returns validators for ACCESSIBILITY field.
	 *
	 * @return array
	 */
	public static function validateAccessibility(): array
	{
		return [
			new LengthValidator(null, 10),
		];
	}

	/**
	 * Returns validators for IMPORTANCE field.
	 *
	 * @return array
	 */
	public static function validateImportance(): array
	{
		return [
			new LengthValidator(null, 10),
		];
	}

	/**
	 * Returns validators for IS_MEETING field.
	 *
	 * @return array
	 */
	public static function validateIsMeeting(): array
	{
		return [
			new LengthValidator(null, 1),
		];
	}

	/**
	 * Returns validators for MEETING_STATUS field.
	 *
	 * @return array
	 */
	public static function validateMeetingStatus(): array
	{
		return [
			new LengthValidator(null, 1),
		];
	}

	/**
	 * Returns validators for LOCATION field.
	 *
	 * @return array
	 */
	public static function validateLocation(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for COLOR field.
	 *
	 * @return array
	 */
	public static function validateColor(): array
	{
		return [
			new LengthValidator(null, 10),
		];
	}

	/**
	 * Returns validators for TEXT_COLOR field.
	 *
	 * @return array
	 */
	public static function validateTextColor(): array
	{
		return [
			new LengthValidator(null, 10),
		];
	}

	/**
	 * Returns validators for RRULE field.
	 *
	 * @return array
	 */
	public static function validateRrule(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for DAV_XML_ID field.
	 *
	 * @return array
	 */
	public static function validateDavXmlId(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for G_EVENT_ID field.
	 *
	 * @return array
	 */
	public static function validateGEventId(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for DAV_EXCH_LABEL field.
	 *
	 * @return array
	 */
	public static function validateDavExchLabel(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for CAL_DAV_LABEL field.
	 *
	 * @return array
	 */
	public static function validateCalDavLabel(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for VERSION field.
	 *
	 * @return array
	 */
	public static function validateVersion(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for RELATIONS field.
	 *
	 * @return array
	 */
	public static function validateRelations(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for SYNC_STATUS field.
	 *
	 * @return array
	 */
	public static function validateSyncStatus(): array
	{
		return [
			new LengthValidator(null, 20),
		];
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
}