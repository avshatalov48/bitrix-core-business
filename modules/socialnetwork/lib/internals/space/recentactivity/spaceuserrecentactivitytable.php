<?php
namespace Bitrix\Socialnetwork\Internals\Space\RecentActivity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class SpaceUserRecentActivityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> SPACE_ID int mandatory
 * <li> TYPE_ID string(32) mandatory
 * <li> ENTITY_ID int optional
 * <li> DATETIME datetime mandatory
 * </ul>
 *
 * @package Bitrix\Socialnetwork\Internals\Space\RecentActivity
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SpaceUserRecentActivity_Query query()
 * @method static EO_SpaceUserRecentActivity_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SpaceUserRecentActivity_Result getById($id)
 * @method static EO_SpaceUserRecentActivity_Result getList(array $parameters = [])
 * @method static EO_SpaceUserRecentActivity_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserRecentActivity createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserRecentActivity_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserRecentActivity wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserRecentActivity_Collection wakeUpCollection($rows)
 */

final class SpaceUserRecentActivityTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_sonet_space_user_recent_activity';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('USER_ID'))
				->configureRequired()
			,
			(new IntegerField('SPACE_ID'))
				->configureRequired()
			,
			(new StringField('TYPE_ID', ['validation' => [__CLASS__, 'validateTypeId']]))
				->configureRequired()
			,
			(new IntegerField('ENTITY_ID')),
			(new DatetimeField('DATETIME'))
				->configureRequired()
			,
			(new IntegerField('SECONDARY_ENTITY_ID')),
		];
	}

	/**
	 * Returns validators for TYPE_ID field.
	 *
	 * @return array
	 */
	public static function validateTypeId(): array
	{
		return [
			new LengthValidator(null, 32),
		];
	}

	public static function getUniqueFields(): array
	{
		return ['USER_ID', 'SPACE_ID', 'TYPE_ID', 'ENTITY_ID'];
	}
}