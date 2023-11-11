<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class ResourceTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_ID int optional
 * <li> CAL_TYPE string(100) optional
 * <li> RESOURCE_ID int mandatory
 * <li> PARENT_TYPE string(100) optional
 * <li> PARENT_ID int mandatory
 * <li> UF_ID int optional
 * <li> DATE_FROM_UTC datetime optional
 * <li> DATE_TO_UTC datetime optional
 * <li> DATE_FROM datetime optional
 * <li> DATE_TO datetime optional
 * <li> DURATION int optional
 * <li> SKIP_TIME string(1) optional
 * <li> TZ_FROM string(50) optional
 * <li> TZ_TO string(50) optional
 * <li> TZ_OFFSET_FROM int optional
 * <li> TZ_OFFSET_TO int optional
 * <li> CREATED_BY int mandatory
 * <li> DATE_CREATE datetime optional
 * <li> TIMESTAMP_X datetime optional
 * <li> SERVICE_NAME string(200) optional
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Resource_Query query()
 * @method static EO_Resource_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Resource_Result getById($id)
 * @method static EO_Resource_Result getList(array $parameters = [])
 * @method static EO_Resource_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Resource createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Resource wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUpCollection($rows)
 */
class ResourceTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_resource';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new IntegerField('EVENT_ID'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_EVENT_ID_FIELD'))
			,
			(new StringField('CAL_TYPE',
				[
					'validation' => [__CLASS__, 'validateCalType']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_CAL_TYPE_FIELD'))
			,
			(new IntegerField('RESOURCE_ID'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_RESOURCE_ID_FIELD'))
				->configureRequired(true)
			,
			(new StringField('PARENT_TYPE',
				[
					'validation' => [__CLASS__, 'validateParentType']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_PARENT_TYPE_FIELD'))
			,
			(new IntegerField('PARENT_ID'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_PARENT_ID_FIELD'))
				->configureRequired(true)
			,
			(new IntegerField('UF_ID'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_UF_ID_FIELD'))
			,
			(new DatetimeField('DATE_FROM_UTC'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DATE_FROM_UTC_FIELD'))
			,
			(new DatetimeField('DATE_TO_UTC'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DATE_TO_UTC_FIELD'))
			,
			(new DatetimeField('DATE_FROM'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DATE_FROM_FIELD'))
			,
			(new DatetimeField('DATE_TO'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DATE_TO_FIELD'))
			,
			(new IntegerField('DURATION'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DURATION_FIELD'))
			,
			(new StringField('SKIP_TIME',
				[
					'validation' => [__CLASS__, 'validateSkipTime']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_SKIP_TIME_FIELD'))
			,
			(new StringField('TZ_FROM',
				[
					'validation' => [__CLASS__, 'validateTzFrom']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_TZ_FROM_FIELD'))
			,
			(new StringField('TZ_TO',
				[
					'validation' => [__CLASS__, 'validateTzTo']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_TZ_TO_FIELD'))
			,
			(new IntegerField('TZ_OFFSET_FROM'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_TZ_OFFSET_FROM_FIELD'))
			,
			(new IntegerField('TZ_OFFSET_TO'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_TZ_OFFSET_TO_FIELD'))
			,
			(new IntegerField('CREATED_BY'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_CREATED_BY_FIELD'))
				->configureRequired(true)
			,
			(new DatetimeField('DATE_CREATE'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_DATE_CREATE_FIELD'))
			,
			(new DatetimeField('TIMESTAMP_X'))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_TIMESTAMP_X_FIELD'))
			,
			(new StringField('SERVICE_NAME',
				[
					'validation' => [__CLASS__, 'validateServiceName']
				]
			))
				->configureTitle(Loc::getMessage('RESOURCE_ENTITY_SERVICE_NAME_FIELD'))
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
	 * Returns validators for PARENT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateParentType(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for SKIP_TIME field.
	 *
	 * @return array
	 */
	public static function validateSkipTime(): array
	{
		return [
			new LengthValidator(null, 1),
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
	 * Returns validators for SERVICE_NAME field.
	 *
	 * @return array
	 */
	public static function validateServiceName(): array
	{
		return [
			new LengthValidator(null, 200),
		];
	}
}