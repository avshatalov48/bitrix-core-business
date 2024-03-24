<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

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
 * <li> HOST_ID int optional
 * <li> OWNER_ID int optional
 * <li> CONFERENCE_ID string(64) optional
 * <li> PARENT_LINK_HASH string(64) optional
 * <li> CONTACT_ID int optional
 * <li> CONTACT_TYPE int optional
 * <li> MEMBERS_HASH string(64) optional
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
 * @method static \Bitrix\Calendar\Internals\EO_SharingLink createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLink_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_SharingLink wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLink_Collection wakeUpCollection($rows)
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
			(new IntegerField('ID',
				[]
			))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new IntegerField('OBJECT_ID',
				[]
			))
				->configureRequired(true)
			,
			(new StringField('OBJECT_TYPE',
				[
					'validation' => [__CLASS__, 'validateObjectType']
				]
			))
				->configureRequired(true)
			,
			(new StringField('HASH',
				[
					'validation' => [__CLASS__, 'validateHash']
				]
			))
				->configureRequired(true)
			,
			(new TextField('OPTIONS',
				[]
			))
			,
			(new BooleanField('ACTIVE',
				[]
			))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
			,
			(new DatetimeField('DATE_CREATE',
				[]
			))
				->configureRequired(true)
			,
			(new DatetimeField('DATE_EXPIRE',
				[]
			))
			,
			(new IntegerField('HOST_ID',
				[]
			))
			,
			(new IntegerField('OWNER_ID',
				[]
			))
			,
			(new StringField('CONFERENCE_ID',
				[
					'validation' => [__CLASS__, 'validateConferenceId']
				]
			))
			,
			(new StringField('PARENT_LINK_HASH',
				[
					'validation' => [__CLASS__, 'validateParentLinkHash']
				]
			))
			,
			(new IntegerField('CONTACT_ID',
				[]
			))
				->configureNullable()
			,
			(new IntegerField('CONTACT_TYPE',
				[]
			))
				->configureNullable()
			,
			(new StringField('MEMBERS_HASH',
				[
					'validation' => [__CLASS__, 'validateMembersHash']
				]
			))
			,
			(new IntegerField('FREQUENT_USE',
				[]
			))
			,
			(new OneToMany(
				'MEMBERS',
				SharingLinkMemberTable::class,
				'MEMBER',
			))
				->configureJoinType(Join::TYPE_LEFT),
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
	 * Returns validators for CONFERENCE_ID field.
	 *
	 * @return array
	 */
	public static function validateConferenceId(): array
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

	/**
	 * Returns validators for MEMBERS_HASH field.
	 *
	 * @return array
	 */
	public static function validateMembersHash(): array
	{
		return [
			new LengthValidator(null, 64),
		];
	}

}