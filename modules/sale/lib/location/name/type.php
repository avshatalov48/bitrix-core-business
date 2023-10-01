<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Location\Name;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Sale\Location;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class TypeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Type_Query query()
 * @method static EO_Type_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Type_Result getById($id)
 * @method static EO_Type_Result getList(array $parameters = [])
 * @method static EO_Type_Entity getEntity()
 * @method static \Bitrix\Sale\Location\Name\EO_Type createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Location\Name\EO_Type_Collection createCollection()
 * @method static \Bitrix\Sale\Location\Name\EO_Type wakeUpObject($row)
 * @method static \Bitrix\Sale\Location\Name\EO_Type_Collection wakeUpCollection($rows)
 */
class TypeTable extends NameEntity
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_type_name';
	}

	public static function getReferenceFieldName()
	{
		return 'TYPE_ID';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_NAME_TYPE_ENTITY_SHORT_NAME_FIELD')
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_NAME_TYPE_ENTITY_SHORT_LANGUAGE_ID_FIELD')
			),

			'TYPE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_LOCATION_NAME_TYPE_ENTITY_SHORT_TYPE_ID_FIELD')
			),
			'TYPE' => array(
				'data_type' => '\Bitrix\Sale\Location\Type',
				'required' => true,
				'reference' => array(
					'=this.TYPE_ID' => 'ref.ID'
				)
			),

			'CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(*)'
				)
			),
		);
	}
}
