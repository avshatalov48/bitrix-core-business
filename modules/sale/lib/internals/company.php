<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


/**
 * Class CompanyTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Company_Query query()
 * @method static EO_Company_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Company_Result getById($id)
 * @method static EO_Company_Result getList(array $parameters = [])
 * @method static EO_Company_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_Company createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_Company_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_Company wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_Company_Collection wakeUpCollection($rows)
 */
class CompanyTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_company';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'NAME' => array(
				'data_type' => 'string',
				'required'   => true
			),
			'LOCATION_ID' => array(
				'data_type' => 'string'
			),
			'LOCATION' => array(
				'data_type' => 'Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_ID' => 'ref.CODE'
				)
			),
			'CODE'  => array(
				'data_type' => 'string'
			),
			'XML_ID'  => array(
				'data_type' => 'string'
			),
			'ACTIVE'  => array(
				'data_type' => 'string'
			),
			'DATE_CREATE'  => array(
				'data_type' => 'datetime'
			),
			'DATE_MODIFY'  => array(
				'data_type' => 'datetime'
			),
			'CREATED_BY'  => array(
				'data_type' => 'integer',
			),
			'CREATED'  => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array(
					'=this.CREATED_BY' => 'ref.ID'
				)
			),
			'MODIFIED_BY'  => array(
				'data_type' => 'integer'
			),
			'MODIFIED'  => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array(
					'=this.MODIFIED_BY' => 'ref.ID'
				)
			),
			'ADDRESS' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer',
				'default_value' => 100
			)
		);
	}

	public static function getUfId()
	{
		return 'SALE_COMPANY';
	}

}