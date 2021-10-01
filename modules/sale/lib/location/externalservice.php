<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Location;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ExternalServiceTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ExternalService_Query query()
 * @method static EO_ExternalService_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ExternalService_Result getById($id)
 * @method static EO_ExternalService_Result getList(array $parameters = array())
 * @method static EO_ExternalService_Entity getEntity()
 * @method static \Bitrix\Sale\Location\EO_ExternalService createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Location\EO_ExternalService_Collection createCollection()
 * @method static \Bitrix\Sale\Location\EO_ExternalService wakeUpObject($row)
 * @method static \Bitrix\Sale\Location\EO_ExternalService_Collection wakeUpCollection($rows)
 */
class ExternalServiceTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_ext_srv';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),

			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_EXTERNAL_SERVICE_ENTITY_CODE_FIELD')
			),

			// virtual
			'EXTERNAL' => array(
				'data_type' => '\Bitrix\Sale\Location\External',
				'reference' => array(
					'=this.ID' => 'ref.SERVICE_ID'
				)
			),
		);
	}
}
