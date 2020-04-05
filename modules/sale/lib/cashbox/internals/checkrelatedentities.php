<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Entity\DataManager;

/**
 * Class Check2EntitiesTable
 * @package Bitrix\Sale\Cashbox\Internals
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
