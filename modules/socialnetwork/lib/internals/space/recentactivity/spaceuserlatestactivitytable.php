<?php
namespace Bitrix\Socialnetwork\Internals\Space\RecentActivity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class SpaceUserLatestActivityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> SPACE_ID int mandatory
 * <li> ACTIVITY_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Socialnetwork\Internals\Space\RecentActivity
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SpaceUserLatestActivity_Query query()
 * @method static EO_SpaceUserLatestActivity_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SpaceUserLatestActivity_Result getById($id)
 * @method static EO_SpaceUserLatestActivity_Result getList(array $parameters = [])
 * @method static EO_SpaceUserLatestActivity_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserLatestActivity createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserLatestActivity_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserLatestActivity wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Space\RecentActivity\EO_SpaceUserLatestActivity_Collection wakeUpCollection($rows)
 */

final class SpaceUserLatestActivityTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_space_user_latest_activity';
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
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('USER_ID'))
				->configureRequired()
			,
			(new IntegerField('SPACE_ID'))
				->configureRequired()
			,
			(new IntegerField('ACTIVITY_ID'))
				->configureRequired()
			,
		];
	}
}