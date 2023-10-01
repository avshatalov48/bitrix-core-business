<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Entity\DataManager;

/**
 * Class Check2EntitiesTable
 * @package Bitrix\Sale\Cashbox\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CheckRelatedEntities_Query query()
 * @method static EO_CheckRelatedEntities_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CheckRelatedEntities_Result getById($id)
 * @method static EO_CheckRelatedEntities_Result getList(array $parameters = [])
 * @method static EO_CheckRelatedEntities_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CheckRelatedEntities createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CheckRelatedEntities_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CheckRelatedEntities wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CheckRelatedEntities_Collection wakeUpCollection($rows)
 */
class CheckRelatedEntitiesTable extends DataManager
{
	const ENTITY_TYPE_PAYMENT = 'P';
	const ENTITY_TYPE_SHIPMENT = 'S';

	public static function getTableName()
	{
		return 'b_sale_check_related_entities';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'autocomplete' => true,
				'autoincrement' => true,
				'data_type' => 'integer',
			),
			'CHECK_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
			'ENTITY_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
			'ENTITY_TYPE' => array(
				'required' => true,
				'data_type' => 'string',
			),
			'ENTITY_CHECK_TYPE' => array(
				'data_type' => 'string',
			),
		);
	}
}
