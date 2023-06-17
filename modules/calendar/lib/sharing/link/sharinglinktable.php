<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class SharingLinkTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> OBJECT_ID int mandatory
 * <li> OBJECT_TYPE string(32) mandatory
 * <li> HASH string(64) mandatory
 * <li> OPTIONS unknown optional
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> DATE_CREATE datetime mandatory
 * <li> DATE_EXPIRE datetime optional
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SharingLink_Query query()
 * @method static EO_SharingLink_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SharingLink_Result getById($id)
 * @method static EO_SharingLink_Result getList(array $parameters = [])
 * @method static EO_SharingLink_Entity getEntity()
 * @method static \Bitrix\Calendar\Sharing\Link\EO_SharingLink createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Sharing\Link\EO_SharingLink_Collection createCollection()
 * @method static \Bitrix\Calendar\Sharing\Link\EO_SharingLink wakeUpObject($row)
 * @method static \Bitrix\Calendar\Sharing\Link\EO_SharingLink_Collection wakeUpCollection($rows)
 */

class SharingLinkTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_sharing_link';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			new IntegerField(
				'OBJECT_ID',
				[
					'required' => true,
				]
			),
			new StringField(
				'OBJECT_TYPE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateObjectType'],
				]
			),
			new StringField(
				'HASH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateHash'],
				]
			),
			new TextField('OPTIONS'),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
				]
			),
			new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
				]
			),
			new DatetimeField(
				'DATE_EXPIRE',
				[
				]
			),
			new IntegerField(
				'HOST_ID',
				[
				]
			),
			new IntegerField(
				'OWNER_ID',
				[
				]
			),
			new StringField(
				'CONFERENCE_ID',
				[
				]
			),
			new StringField(
				'PARENT_LINK_HASH',
				[
					'validation' => [__CLASS__, 'validateParentLinkHash'],
				]
			),
			new IntegerField(
				'CONTACT_ID',
				[
				]
			),
			new IntegerField(
				'CONTACT_TYPE',
				[
				]
			),
		];
	}

	/**
	 * Returns validators for OBJECT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateObjectType(): array
	{
		return [
			new LengthValidator(null, 32),
		];
	}

	/**
	 * Returns validators for HASH field.
	 *
	 * @return array
	 */
	public static function validateHash(): array
	{
		return [
			new LengthValidator(null, 64),
		];
	}

	/**
	 * Returns validators for PARENT_LINK_HASH field.
	 *
	 * @return array
	 */
	public static function validateParentLinkHash(): array
	{
		return [
			new LengthValidator(null, 64),
		];
	}
}