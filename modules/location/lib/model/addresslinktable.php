<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class AddressLinkTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AddressLink_Query query()
 * @method static EO_AddressLink_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_AddressLink_Result getById($id)
 * @method static EO_AddressLink_Result getList(array $parameters = array())
 * @method static EO_AddressLink_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_AddressLink createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_AddressLink_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_AddressLink wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_AddressLink_Collection wakeUpCollection($rows)
 */
class AddressLinkTable extends Main\ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_location_addr_link';
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
				->configurePrimary(true),

			(new Fields\StringField('ENTITY_ID'))
				->configurePrimary(true)
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 100)),

			// todo: int
			(new Fields\StringField('ENTITY_TYPE'))
				->configurePrimary(true)
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 50)),

			// Ref

			(new Fields\Relations\Reference('ADDRESS', AddressTable::class,
				Join::on('this.ADDRESS_ID', 'ref.ID')))
				->configureJoinType('inner')
		];
	}

	/**
	 * @param int $addressId
	 * @throws Main\Db\SqlQueryException
	 */
	public static function deleteByAddressId(int $addressId): void
	{
		Main\Application::getConnection()->queryExecute("
			DELETE 
				FROM ".self::getTableName()." 
			WHERE 
				ADDRESS_ID=".(int)$addressId
		);
	}

	/**
	 * @param string $entityType
	 * @throws Main\Db\SqlQueryException
	 */
	public static function deleteByEntityType(string $entityType): void
	{
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		Main\Application::getConnection()->queryExecute("
			DELETE 
				FROM ".self::getTableName()." 
			WHERE				
				ENTITY_TYPE = '".$sqlHelper->forSql($entityType)."'"
		);
	}
}