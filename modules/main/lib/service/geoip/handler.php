<?php
namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class HandlerTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SORT int optional default 100
 * <li> ACTIVE bool optional default 'Y'
 * <li> CLASS_NAME string(255) mandatory
 * <li> CONFIG string optional
 * </ul>
 *
 * @package Bitrix\Main\Service\GeoIp
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Handler_Query query()
 * @method static EO_Handler_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Handler_Result getById($id)
 * @method static EO_Handler_Result getList(array $parameters = [])
 * @method static EO_Handler_Entity getEntity()
 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler_Collection createCollection()
 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler wakeUpObject($row)
 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler_Collection wakeUpCollection($rows)
 */

class HandlerTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_geoip_handlers';
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
				'title' => Loc::getMessage('MAIN_SRV_GEOIP_HNDL_ENTITY_ID_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MAIN_SRV_GEOIP_HNDL_ENTITY_SORT_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('MAIN_SRV_GEOIP_HNDL_ENTITY_ACTIVE_FIELD'),
			),
			'CLASS_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateClassName'),
				'title' => Loc::getMessage('MAIN_SRV_GEOIP_HNDL_ENTITY_CLASS_NAME_FIELD'),
			),
			'CONFIG' => array(
				'data_type' => 'text',
				'serialized' => true,
				'title' => Loc::getMessage('MAIN_SRV_GEOIP_HNDL_ENTITY_CONFIG_FIELD'),
			),
		);
	}

	/**
	 * Returns validators for CLASS_NAME field.
	 *
	 * @return array
	 */
	public static function validateClassName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}