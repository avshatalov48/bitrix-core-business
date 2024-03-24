<?php
namespace Bitrix\Location\Model;

use Bitrix\Location\Entity\Location\Type;
use Bitrix\Main;
use	Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations;

/**
 * Class LocationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EXTERNAL_ID string(50) optional
 * <li> SOURCE_CODE int optional
 * <li> LATITUDE double optional
 * <li> LONGITUDE double optional
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> POST_CODE string(16) optional
 * <li> TYPE int mandatory
 * </ul>
 *
 * @package Bitrix\Location\Model
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Location_Query query()
 * @method static EO_Location_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Location_Result getById($id)
 * @method static EO_Location_Result getList(array $parameters = array())
 * @method static EO_Location_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_Location createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_Location_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_Location wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_Location_Collection wakeUpCollection($rows)
 */

class LocationTable extends Main\ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_location';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\StringField('CODE'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 100))
				->configureUnique(true)
				->configureRequired(true),

			(new Fields\StringField('EXTERNAL_ID'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(null, 255)),

			(new Fields\StringField('SOURCE_CODE'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(null, 15)),

			new Fields\FloatField('LATITUDE', ['scale' => 6]),
			new Fields\FloatField('LONGITUDE', ['scale' => 6]),
			new Fields\DatetimeField('TIMESTAMP_X', [
				'default_value' => static fn() => new Main\Type\DateTime(),
			]),

			(new Fields\IntegerField('TYPE'))
				->configureRequired(true),

			// References

			(new Relations\OneToMany('NAME', LocationNameTable::class, 'LOCATION'))
				->configureJoinType('left'),

			(new Relations\OneToMany('ANCESTORS', HierarchyTable::class, 'ANCESTOR'))
				->configureJoinType('left'),

			(new Relations\OneToMany('DESCENDANTS', HierarchyTable::class, 'DESCENDANT'))
				->configureJoinType('left'),

			(new Relations\OneToMany('ADDRESSES', AddressTable::class, 'LOCATION'))
				->configureJoinType('left'),

			(new Fields\Relations\OneToMany('FIELDS', LocationFieldTable::class, 'LOCATION'))
				->configureJoinType('left')
		];
	}
}