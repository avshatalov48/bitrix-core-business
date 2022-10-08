<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class AddressFieldTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AddressField_Query query()
 * @method static EO_AddressField_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_AddressField_Result getById($id)
 * @method static EO_AddressField_Result getList(array $parameters = array())
 * @method static EO_AddressField_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_AddressField createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_AddressField_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_AddressField wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_AddressField_Collection wakeUpCollection($rows)
 */
class AddressFieldTable extends Main\ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_location_addr_fld';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [

			(new Fields\IntegerField('ADDRESS_ID'))
				->configureRequired(true)
				->configurePrimary(true),

			(new Fields\IntegerField('TYPE'))
				->configureRequired(true)
				->configurePrimary(true),

			(new Fields\StringField('VALUE'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(null, 1024)),

			(new Fields\StringField('VALUE_NORMALIZED'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(null, 1024)),

			// Ref

			(new Fields\Relations\Reference('ADDRESS', AddressTable::class,
				Join::on('this.ADDRESS_ID', 'ref.ID')))
				->configureJoinType('inner')
		];
	}

	public static function deleteByAddressId(int $addressId)
	{
		Main\Application::getConnection()->queryExecute("
			DELETE 
				FROM ".self::getTableName()." 
			WHERE 
				ADDRESS_ID=".(int)$addressId
		);
	}
}