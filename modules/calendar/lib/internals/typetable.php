<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class TypeTable
 *
 * Fields:
 * <ul>
 * <li> XML_ID string(255) mandatory
 * <li> NAME string(255) optional
 * <li> DESCRIPTION string optional
 * <li> EXTERNAL_ID string(100) optional
 * <li> ACTIVE bool optional default 'Y'
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Type_Query query()
 * @method static EO_Type_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Type_Result getById($id)
 * @method static EO_Type_Result getList(array $parameters = [])
 * @method static EO_Type_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Type createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Type wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection wakeUpCollection($rows)
 */
class TypeTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_type';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new StringField('XML_ID',
				[
					'validation' => [__CLASS__, 'validateXmlId']
				]
			))
				->configureTitle(Loc::getMessage('TYPE_ENTITY_XML_ID_FIELD'))
				->configurePrimary(true)
			,
			(new StringField('NAME',
				[
					'validation' => [__CLASS__, 'validateName']
				]
			))
				->configureTitle(Loc::getMessage('TYPE_ENTITY_NAME_FIELD'))
			,
			(new TextField('DESCRIPTION'))
				->configureTitle(Loc::getMessage('TYPE_ENTITY_DESCRIPTION_FIELD'))
			,
			(new StringField('EXTERNAL_ID',
				[
					'validation' => [__CLASS__, 'validateExternalId']
				]
			))
				->configureTitle(Loc::getMessage('TYPE_ENTITY_EXTERNAL_ID_FIELD'))
			,
			(new BooleanField('ACTIVE'))
				->configureTitle(Loc::getMessage('TYPE_ENTITY_ACTIVE_FIELD'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
			,
		];
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId(): array
	{
		return [
			new LengthValidator(null, 255),
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
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}
}