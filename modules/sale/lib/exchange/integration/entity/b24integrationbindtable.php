<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Main;

/**
 * Class B24IntegrationBindTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_B24IntegrationBind_Query query()
 * @method static EO_B24IntegrationBind_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_B24IntegrationBind_Result getById($id)
 * @method static EO_B24IntegrationBind_Result getList(array $parameters = [])
 * @method static EO_B24IntegrationBind_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationBind createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationBind_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationBind wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationBind_Collection wakeUpCollection($rows)
 */
class B24IntegrationBindTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_b24integration_bind';
	}

	public static function getMap()
	{
		return [
			new Main\Entity\IntegerField('ID'),
			new Main\Entity\IntegerField('SRC_ENTITY_TYPE_ID', [ 'primary' => true ]),
			new Main\Entity\IntegerField('SRC_ENTITY_ID', [ 'primary' => true ]),
			new Main\Entity\IntegerField('DST_ENTITY_TYPE_ID', [ 'primary' => true ]),
			new Main\Entity\IntegerField('DST_ENTITY_ID', [ 'primary' => true ]),
			new Main\Entity\DatetimeField('CREATED_TIME'),
			new Main\Entity\DatetimeField('LAST_UPDATED_TIME')
		];
	}

	public static function upsert(array $data)
	{
		$result = new Main\Entity\AddResult();

		$srcEntityTypeID = isset($data['SRC_ENTITY_TYPE_ID']) ? (int)$data['SRC_ENTITY_TYPE_ID'] : \CCrmOwnerType::Undefined;
		$srcEntityID = isset($data['SRC_ENTITY_ID']) ? (int)$data['SRC_ENTITY_ID'] : 0;

		$dstEntityTypeID = isset($data['DST_ENTITY_TYPE_ID']) ? (int)$data['DST_ENTITY_TYPE_ID'] : \CCrmOwnerType::Undefined;
		$dstEntityID = isset($data['DST_ENTITY_ID']) ? (int)$data['DST_ENTITY_ID'] : 0;

		$now = Main\Type\DateTime::createFromTimestamp(time() + \CTimeZone::GetOffset());

		$insertFields = [
			'SRC_ENTITY_TYPE_ID' => $srcEntityTypeID,
			'SRC_ENTITY_ID' => $srcEntityID,
			'DST_ENTITY_TYPE_ID' => $dstEntityTypeID,
			'DST_ENTITY_ID' => $dstEntityID,
			'CREATED_TIME' => $now,
			'LAST_UPDATED_TIME' => $now
		];

		$updateFields = [
			'LAST_UPDATED_TIME' => $now,
			'SRC_ENTITY_TYPE_ID' => $srcEntityTypeID,
			'SRC_ENTITY_ID' => $srcEntityID,
			'DST_ENTITY_TYPE_ID' => $dstEntityTypeID,
			'DST_ENTITY_ID' => $dstEntityID
		];

		$connection = Main\Application::getConnection();
		$queries = $connection->getSqlHelper()->prepareMerge(
			static::getTableName(),
			[
				'SRC_ENTITY_TYPE_ID',
				'SRC_ENTITY_ID',
				'DST_ENTITY_TYPE_ID',
				'DST_ENTITY_ID'
			],
			$insertFields,
			$updateFields
		);

		foreach($queries as $query)
		{
			$connection->queryExecute($query);
		}

		$result->setId(
			$connection->getInsertedId());

		return $result;
	}
}