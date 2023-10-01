<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Sale;

/**
 * Class CompanyLocationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CompanyLocation_Query query()
 * @method static EO_CompanyLocation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CompanyLocation_Result getById($id)
 * @method static EO_CompanyLocation_Result getList(array $parameters = [])
 * @method static EO_CompanyLocation_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CompanyLocation createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CompanyLocation_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CompanyLocation wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CompanyLocation_Collection wakeUpCollection($rows)
 */
final class CompanyLocationTable extends Sale\Location\Connector
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_company2location';
	}

	public static function getLinkField()
	{
		return 'COMPANY_ID';
	}

	public static function getLocationLinkField()
	{
		return 'LOCATION_CODE';
	}

	public static function getTargetEntityName()
	{
		return '\Bitrix\Sale\Internals\CompanyTable';
	}

	public static function getMap()
	{
		return array(

			'COMPANY_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true
			),
			'LOCATION_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'primary' => true
			),
			'LOCATION_TYPE' => array(
				'data_type' => 'string',
				'default' => self::DB_LOCATION_FLAG,
				'required' => true,
				'primary' => true
			),

			// virtual
			'LOCATION' => array(
				'data_type' => '\Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_CODE' => 'ref.CODE',
					'=this.LOCATION_TYPE' => array('?', self::DB_LOCATION_FLAG)
				)
			),
			'GROUP' => array(
				'data_type' => '\Bitrix\Sale\Location\Group',
				'reference' => array(
					'=this.LOCATION_CODE' => 'ref.CODE',
					'=this.LOCATION_TYPE' => array('?', self::DB_GROUP_FLAG)
				)
			),

			'COMPANY' => array(
				'data_type' => static::getTargetEntityName(),
				'reference' => array(
					'=this.COMPANY_ID' => 'ref.ID'
				)
			),
		);
	}
}
