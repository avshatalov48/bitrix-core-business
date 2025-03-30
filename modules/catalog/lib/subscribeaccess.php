<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Entity,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Application;

/**
 * Class SubscribeAccessTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SubscribeAccess_Query query()
 * @method static EO_SubscribeAccess_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SubscribeAccess_Result getById($id)
 * @method static EO_SubscribeAccess_Result getList(array $parameters = [])
 * @method static EO_SubscribeAccess_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_SubscribeAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_SubscribeAccess_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_SubscribeAccess wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_SubscribeAccess_Collection wakeUpCollection($rows)
 */
class SubscribeAccessTable extends Entity\DataManager
{
	const TOKEN_LIFE_TIME = 3600;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_subscribe_access';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_FROM' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => fn() => new DateTime(),
			),
			'USER_CONTACT' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'SUBSCRIBE' => array(
				'data_type' => 'Bitrix\Catalog\SubscribeTable',
				'reference' => array('=this.USER_CONTACT' => 'ref.USER_CONTACT'),
			),
			'TOKEN' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateToken'),
			),
		);
	}

	/**
	 * Returns validators for TOKEN field.
	 *
	 * @return array
	 */
	public static function validateToken()
	{
		return array(
			new Entity\Validator\Length(null, 6),
		);
	}

	/**
	 * Method deletes the old data, depending on the life time TOKEN.
	 *
	 * @return void
	 */
	public static function clearOldRows()
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('delete from '.$helper->quote(static::getTableName()).' where '
			.$helper->quote('DATE_FROM').' < '.$helper->addSecondsToDateTime(-(static::TOKEN_LIFE_TIME))
		);
	}
}