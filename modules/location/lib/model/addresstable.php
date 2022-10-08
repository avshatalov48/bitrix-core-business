<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class AddressTable
 * @package Bitrix\Location\Model
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Address_Query query()
 * @method static EO_Address_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Address_Result getById($id)
 * @method static EO_Address_Result getList(array $parameters = array())
 * @method static EO_Address_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_Address createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_Address_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_Address wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_Address_Collection wakeUpCollection($rows)
 */
class AddressTable extends Main\ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_location_address';
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public static function getMap()
	{
		return array(
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			new Fields\IntegerField('LOCATION_ID'),

			(new Fields\StringField('LANGUAGE_ID'))
				->configureRequired(true)
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(2, 2)),

			new Fields\FloatField('LATITUDE', ['scale' => 6]),
			new Fields\FloatField('LONGITUDE', ['scale' => 6]),

			(new Fields\Relations\OneToMany('FIELDS', AddressFieldTable::class, 'ADDRESS'))
				->configureJoinType('left'),

			(new Fields\Relations\OneToMany('LINKS', AddressLinkTable::class, 'ADDRESS'))
				->configureJoinType('left'),

			(new Fields\Relations\Reference('LOCATION', LocationTable::class,
				Join::on('this.LOCATION_ID', 'ref.ID')))
				->configureJoinType('left')
		);
	}
}