<?php
namespace Bitrix\Main\Numerator\Model;

use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Application;
use Bitrix\Main\Result;

/**
 * Class NumeratorSequenceTable
 * @package Bitrix\Main\Numerator\Model
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_NumeratorSequence_Query query()
 * @method static EO_NumeratorSequence_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_NumeratorSequence_Result getById($id)
 * @method static EO_NumeratorSequence_Result getList(array $parameters = [])
 * @method static EO_NumeratorSequence_Entity getEntity()
 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection createCollection()
 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence wakeUpObject($row)
 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection wakeUpCollection($rows)
 */
class NumeratorSequenceTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_numerator_sequence';
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('NUMERATOR_ID'))
				->configureRequired(true)
				->configurePrimary(true)
			,
			(new StringField('KEY'))
				->configureRequired(true)
				->configurePrimary(true)
			,
			(new StringField('TEXT_KEY'))
				->configureRequired(true)
			,
			(new IntegerField('NEXT_NUMBER')),
			(new IntegerField('LAST_INVOCATION_TIME'))
				->configureRequired(true)
			,
		];
	}

	/**
	 * @param $numeratorId
	 * @param $numberHash
	 * @param $fields
	 * @param null $whereNextNumber
	 * @return bool|int
	 * @throws SqlQueryException
	 */
	public static function updateSettings($numeratorId, $numberHash, $fields, $whereNextNumber = null)
	{
		if ($whereNextNumber)
		{
			$conn = Application::getConnection();
			$helper = $conn->getSqlHelper();
			$update = $helper->prepareUpdate(static::getTableName(), $fields);
			$query = 'UPDATE ' . $helper->quote(static::getTableName())
					 . ' SET ' . $update[0]
					 . ' WHERE NUMERATOR_ID = ' . intval($numeratorId)
					 . " AND `KEY` = " . $helper->convertToDbString(md5($numberHash))
					 . " AND NEXT_NUMBER = " . intval($whereNextNumber) . ";";
			$conn->query($query);

			return $conn->getAffectedRowsCount();
		}
		$res = static::update([
			'NUMERATOR_ID' => intval($numeratorId),
			'KEY'          => md5($numberHash),
		], $fields);
		if ($res->isSuccess())
		{
			return $res->getAffectedRowsCount();
		}

		return 0;
	}

	/**
	 * @param $id
	 * @throws \Exception
	 */
	public static function deleteByNumeratorId($id)
	{
		$result = new Result();

		$list = static::getList([
			'filter' => [
				'=NUMERATOR_ID' => (int)$id,
			]
		]);
		while($row = $list->fetchObject())
		{
			$deleteResult = $row->delete();
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $numeratorId
	 * @param $numberHash
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getSettings($numeratorId, $numberHash)
	{
		$sequenceSettings = static::getList([
			'select' => ['NEXT_NUMBER', 'LAST_INVOCATION_TIME'],
			'filter' => ['=NUMERATOR_ID' => $numeratorId, '=KEY' => md5($numberHash),],
		])->fetch();
		return $sequenceSettings;
	}

	/**
	 * @param $numeratorId
	 * @param $numberHash
	 * @param $defaultNumber
	 * @param $lastInvocationTime
	 * @return array
	 * @throws SqlQueryException
	 */
	public static function setSettings($numeratorId, $numberHash, $defaultNumber, $lastInvocationTime)
	{
		try
		{
			$result = static::add([
				'NUMERATOR_ID'         => $numeratorId,
				'KEY'                  => md5($numberHash),
				'TEXT_KEY'             => mb_substr($numberHash, 0, 50),
				'LAST_INVOCATION_TIME' => $lastInvocationTime,
				'NEXT_NUMBER'          => $defaultNumber,
			]);
			if ($result->isSuccess())
			{
				return $result->getData();
			}
			return [];
		}
		catch (SqlQueryException $exc)
		{
			if (mb_stripos($exc->getMessage(), "Duplicate entry") !== false)
			{
				return [];
			}
			throw $exc;
		}
	}
}