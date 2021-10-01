<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Sale\Exchange\Integration\EntityType;

/**
 * Class B24integrationStatTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_B24integrationStat_Query query()
 * @method static EO_B24integrationStat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_B24integrationStat_Result getById($id)
 * @method static EO_B24integrationStat_Result getList(array $parameters = array())
 * @method static EO_B24integrationStat_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationStat_Collection wakeUpCollection($rows)
 */
class B24integrationStatTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_b24integration_stat';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
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

	protected static function upsertPrepareParams(array $data)
	{
		$entityTypeID = isset($data['ENTITY_TYPE_ID']) ? (int)$data['ENTITY_TYPE_ID'] : \CCrmOwnerType::Undefined;
		$entityID = isset($data['ENTITY_ID']) ? (int)$data['ENTITY_ID'] : 0;
		$providerID = isset($data['PROVIDER_ID']) ? (int)$data['PROVIDER_ID'] : 0;
		$dateUpdate = isset($data['DATE_UPDATE']) ? $data['DATE_UPDATE']: null;
		$currency = isset($data['CURRENCY']) ? $data['CURRENCY'] : null;
		$status = isset($data['STATUS']) ? $data['STATUS'] : null;
		$xmlId = isset($data['XML_ID']) ? $data['XML_ID'] : null;
		$amount = isset($data['AMOUNT']) ? (double)$data['AMOUNT'] : 0.0;

		$now = 	Main\Type\DateTime::createFromTimestamp(time() + \CTimeZone::GetOffset());

		$fields = [
			'ENTITY_TYPE_ID' => $entityTypeID,
			'ENTITY_ID' => $entityID,
			'DATE_UPDATE' => $dateUpdate,
			'TIMESTAMP_X' => $now,
			'PROVIDER_ID' => $providerID,
			'CURRENCY' => $currency,
			'STATUS' => $status,
			'XML_ID' => $xmlId,
			'AMOUNT' => $amount,
		];

		return $fields;
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function upsert(array $data)
	{
		$result = new Main\Entity\AddResult();
		$connection = Main\Application::getConnection();

		static::checkFields($result, null, $data);
		if($result->isSuccess() == false)
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
			$connection->getInsertedId());

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
	public static function modify(array $items)
	{
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$r = static::checkModifyFields($items);
		if($r->isSuccess() == false)
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
			'AMOUNT'
		];

		foreach ($names as $name)
		{
			$duplicate[] = $name.' = VALUES('.$name.')';
		}

		foreach ($items as $item)
		{
			$fields = static::upsertPrepareParams($item);

			$valuesData = [
				$fields['ENTITY_TYPE_ID'],
				$fields['ENTITY_ID'],
				$sqlHelper->convertToDbDateTime($fields['DATE_UPDATE']),
				$sqlHelper->convertToDbDateTime($fields['TIMESTAMP_X']),
				$fields['PROVIDER_ID'],
				'\''.$sqlHelper->forSql($fields['CURRENCY']).'\'',
				'\''.$sqlHelper->forSql($fields['STATUS']).'\'',
				'\''.$sqlHelper->forSql($fields['XML_ID']).'\'',
				'\''.$fields['AMOUNT'].'\''
			];
			$values[] = '('.implode(',', $valuesData).')';
		}

		$query = '
				INSERT INTO '.static::getTableName().'
					('.implode(', ', $names).')
					VALUES '.implode(',', $values).'
					ON DUPLICATE KEY UPDATE
				'.implode(', ', $duplicate).'
			';

		Application::getConnection()->query($query);

		return $r;
	}

	/**
	 * @param $list
	 * @return AddResult
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	static protected function checkModifyFields(array $list)
	{
		$result = new AddResult();
		$error = [];

		foreach ($list as $k=>$fields)
		{
			$r = new AddResult();

			static::checkFields($r, null, $fields);
			if($r->isSuccess() == false)
			{
				$error[] = '['.$k.'] '.implode(', ', $r->getErrorMessages()).'.';
			}
		}

		if(count($error)>0)
		{
			$result->addError(new Main\Error(implode(', ', $error)));
		}

		return $result;
	}
}