<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Internals;

use	Bitrix\Main;

/**
 * Class OrderPropsRelationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderPropsRelation_Query query()
 * @method static EO_OrderPropsRelation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderPropsRelation_Result getById($id)
 * @method static EO_OrderPropsRelation_Result getList(array $parameters = [])
 * @method static EO_OrderPropsRelation_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsRelation createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsRelation_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsRelation wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsRelation_Collection wakeUpCollection($rows)
 */
class OrderPropsRelationTable extends Main\Entity\DataManager
{
	public const ENTITY_TYPE_PAY_SYSTEM = 'P';
	public const ENTITY_TYPE_DELIVERY = 'D';
	public const ENTITY_TYPE_LANDING = 'L';
	public const ENTITY_TYPE_TRADING_PLATFORM = 'T';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_order_props_relation';
	}

	public static function getMap()
	{
		return [
			'PROPERTY_ID' => [
				'primary' => true,
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			],
			'ENTITY_ID' => [
				'primary' => true,
				'data_type' => 'string',
				'validation' => [__CLASS__, 'getEntityValidators'],
			],
			'ENTITY_TYPE' => [
				'primary' => true,
				'data_type' => 'string',
				'values' => [
					self::ENTITY_TYPE_PAY_SYSTEM,
					self::ENTITY_TYPE_DELIVERY,
					self::ENTITY_TYPE_LANDING,
					self::ENTITY_TYPE_TRADING_PLATFORM,
				],
			],

			'lPROPERTY' => [
				'data_type' => 'Bitrix\Sale\Internals\OrderPropsTable',
				'reference' => ['=this.PROPERTY_ID' => 'ref.ID'],
				'join_type' => 'LEFT',
			],
		];
	}

	public static function getEntityValidators()
	{
		return array(
			new Main\Entity\Validator\Length(1, 35),
		);
	}

	public static function getRelationsByPropertyIdList(array $propertyIds) : array
	{
		static $relations = [];

		$diff = array_diff($propertyIds, array_keys($relations));
		if ($diff)
		{
			$dbRes = static::getList([
				'select' => ['PROPERTY_ID', 'ENTITY_ID', 'ENTITY_TYPE'],
				'filter' => ['@PROPERTY_ID' => $diff]
			]);

			while ($data = $dbRes->fetch())
			{
				$relations[$data['PROPERTY_ID']][] = [
					'ENTITY_ID' => $data['ENTITY_ID'],
					'ENTITY_TYPE' => $data['ENTITY_TYPE']
				];
			}

			foreach ($diff as $id)
			{
				$relations[$id] = $relations[$id] ?? [];
			}
		}

		return array_intersect_key($relations, array_fill_keys($propertyIds, true));
	}

	public static function getRelationsByPropertyId($propertyId) : array
	{
		$relations = static::getRelationsByPropertyIdList([$propertyId]);

		return $relations[$propertyId];
	}
}
