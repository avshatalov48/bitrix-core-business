<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


/**
 * Class CompanyServiceTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CompanyService_Query query()
 * @method static EO_CompanyService_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CompanyService_Result getById($id)
 * @method static EO_CompanyService_Result getList(array $parameters = array())
 * @method static EO_CompanyService_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CompanyService createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CompanyService_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CompanyService wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CompanyService_Collection wakeUpCollection($rows)
 */
class CompanyServiceTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_company2service';
	}

	public static function getMap()
	{
		return array(
			'COMPANY_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SERVICE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SERVICE_TYPE' => array(
				'data_type' => 'integer',
				'primary' => true
			),
		);
	}
}