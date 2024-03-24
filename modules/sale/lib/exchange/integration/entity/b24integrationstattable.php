<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Sale\Exchange\Integration\EntityType;

/**
 * Class B24integrationStatTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_B24integrationStat_Query query()
 * @method static EO_B24integrationStat_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_B24integrationStat_Result getById($id)
 * @method static EO_B24integrationStat_Result getList(array $parameters = [])
 * @method static EO_B24integrationStat_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat_Collection wakeUpCollection($rows)
 */
class B24integrationStatTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_sale_b24integration_stat';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap(): array
	{
		return [
			new Main\Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Main\Entity\EnumField('ENTITY_TYPE_ID', [
				'required' => true,
				'values' => [
					EntityType::ORDER,
				]]),
			new Main\Entity\IntegerField('ENTITY_ID', ['required' => true]),
			new Main\Entity\DatetimeField('DATE_UPDATE'),
			new Main\Entity\DatetimeField('TIMESTAMP_X', ['default_value' => new Main\Type\DateTime()]),
			new Main\Entity\IntegerField('PROVIDER_ID', ['required' => true]),
			new Main\Entity\StringField("CURRENCY", ['required' => true]),
			new Main\Entity\EnumField("STATUS", [
				'required' => true,
				'values' => [
					StatusType::SUCCESS_NAME,
					StatusType::PROCESS_NAME,
					StatusType::FAULTY_NAME
				]]),
			new Main\Entity\StringField("XML_ID", ['required' => true]),
			new Main\Entity\FloatField("AMOUNT", ['required' => true]),
		];
	}

	protected static function upsertPrepareParams(array $data): array
	{
		$entityTypeID = (int)($data['ENTITY_TYPE_ID'] ??  EntityType::UNDEFINED);
		$entityID = (int)($data['ENTITY_ID'] ?? 0);
		$providerID = (int)($data['PROVIDER_ID'] ?? 0);
		$dateUpdate = $data['DATE_UPDATE'] ?? null;
		$currency = $data['CURRENCY'] ?? null;
		$status = $data['STATUS'] ?? null;
		$xmlId = $data['XML_ID'] ?? null;
		$amount = (float)($data['AMOUNT'] ?? 0.0);

		return [
			'ENTITY_TYPE_ID' => $entityTypeID,
			'ENTITY_ID' => $entityID,
			'DATE_UPDATE' => $dateUpdate,
			'TIMESTAMP_X' => new Main\DB\SqlExpression(
				Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction()
			),
			'PROVIDER_ID' => $providerID,
			'CURRENCY' => $currency,
			'STATUS' => $status,
			'XML_ID' => $xmlId,
			'AMOUNT' => $amount,
		];
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function upsert(array $data): AddResult
	{
		$result = new AddResult();
		$connection = Main\Application::getConnection();

		static::checkFields($result, null, $data);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$updateFields = $insertFields = static::upsertPrepareParams($data);

		$queries = $connection->getSqlHelper()->prepareMerge(
			static::getTableName(),
			[
				'ENTITY_TYPE_ID',
				'ENTITY_ID'
			],
			$insertFields,
			$updateFields
		);

		foreach($queries as $query)
		{
			$connection->queryExecute($query);
		}

		$result->setId(
			$connection->getInsertedId()
		);

		return $result;
	}

	/**
	 * @param array $items
	 * @return AddResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function modify(array $items): AddResult
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$r = static::checkModifyFields($items);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$names = [
			'ENTITY_TYPE_ID',
			'ENTITY_ID',
			'DATE_UPDATE',
			'TIMESTAMP_X',
			'PROVIDER_ID',
			'CURRENCY',
			'STATUS',
			'XML_ID',
			'AMOUNT',
		];

		$values = [];
		foreach ($items as $item)
		{
			$values[] = static::upsertPrepareParams($item);
		}

		$query = $helper->prepareMergeValues(
			static::getTableName(),
			[
				'ENTITY_ID',
				'ENTITY_TYPE_ID',
				'PROVIDER_ID',
			],
			$values,
			$names
		);

		$connection->queryExecute($query);

		return $r;
	}

	/**
	 * @param array $list
	 * @return AddResult
	 * @throws ArgumentException
	 * @throws Main\SystemException
	 */
	protected static function checkModifyFields(array $list): AddResult
	{
		$result = new AddResult();
		$error = [];

		foreach ($list as $k=>$fields)
		{
			$r = new AddResult();

			static::checkFields($r, null, $fields);
			if (!$r->isSuccess())
			{
				$error[] = '['.$k.'] '.implode(', ', $r->getErrorMessages()).'.';
			}
		}

		if (!empty($error))
		{
			$result->addError(new Main\Error(implode(', ', $error)));
		}

		return $result;
	}
}
