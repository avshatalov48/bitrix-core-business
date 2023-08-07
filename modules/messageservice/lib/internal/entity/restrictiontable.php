<?php

namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;

/**
 * Class RestrictionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CODE string(128) mandatory
 * <li> COUNT int optional
 * <li> DATE_CREATE date mandatory
 * </ul>
 *
 * @package Bitrix\Messageservice
 **/

class RestrictionTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_messageservice_restriction';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => (new IntegerField('ID', []))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			'CODE' => (new StringField('CODE', [
				'validation' => function()
					{
						return[
							new LengthValidator(null, 128),
						];
					},
				]))
				->configureRequired(true)
			,
			'COUNTER' => (new IntegerField('COUNTER', [])),
			'DATE_CREATE' => (new DateField('DATE_CREATE', []))
				->configureRequired(true)
			,
			'ADDITIONAL_PARAMS' => (new ArrayField('ADDITIONAL_PARAMS', []))
				->configureSerializeCallback(static function($value) {
					$preparedValue = [];
					foreach($value as $entity)
					{
						$preparedValue[] = "|$entity|";
					}
					$result = implode(' ', $preparedValue);

					return $result;
				})
				->configureUnserializeCallback(static function($value) {
					if ((string)$value === '')
					{
						return [];
					}

					$result = [];
					foreach(explode(' ', $value) as $entity)
					{
						$result[] = trim($entity, '|');
					}

					return $result;
				})
			,
		];
	}

	/**
	 * @param string $filteringCode
	 * @param int $filteringCounter
	 *
	 * @return bool affected row counter
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws SqlQueryException
	 */
	public static function updateCounter(string $filteringCode, int $filteringCounter): bool
	{
		$entity = static::getEntity();
		$table = static::getTableName();

		$filter = Query::filter()
			->where('CODE', $filteringCode)
			->where('COUNTER','<=', $filteringCounter)
			->where('DATE_CREATE', new Date())
		;

		$where = Query::buildFilterSql($entity, $filter);

		if($where !== '')
		{
			$where = ' where ' . $where;
		}

		$helper = Application::getConnection()->getSqlHelper();
		$tableName = $helper->quote($table);

		$sql = "UPDATE {$tableName} SET COUNTER = COUNTER + 1 {$where}";

		Application::getConnection()->queryExecute($sql);

		return Application::getConnection()->getAffectedRowsCount() === 1;
	}

	/**
	 * @param string $code
	 * @param int $limit
	 * @param string $additionalParam
	 *
	 * @return bool affected row counter
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public static function updateCounterWithParam(string $code, int $limit, string $additionalParam)
	{
		$entity = static::getEntity();
		$table = static::getTableName();
		$encodedAdditionalParam = self::getMap()['ADDITIONAL_PARAMS']->encode([$additionalParam]);

		$filter = Query::filter()
			->where('CODE', $code)
			->where('COUNTER','<=', $limit)
			->where('DATE_CREATE', new Date())
		;

		$where = Query::buildFilterSql($entity, $filter);

		if($where !== '')
		{
			$where = ' where ' . $where;
		}

		$helper = Application::getConnection()->getSqlHelper();
		$tableName = $helper->quote($table);

		$sql = "
			UPDATE {$tableName}
			SET
				COUNTER = IF (
					LOCATE('{$encodedAdditionalParam}', ADDITIONAL_PARAMS) = 0,
					COUNTER + 1,
					COUNTER
				),
				ADDITIONAL_PARAMS = IF (
					LOCATE('{$encodedAdditionalParam}', ADDITIONAL_PARAMS) = 0,
					CONCAT_WS(' ', ADDITIONAL_PARAMS, '{$encodedAdditionalParam}'),
					'{$encodedAdditionalParam}'
					
				)
			{$where}
		";

		Application::getConnection()->queryExecute($sql);

		return Application::getConnection()->getAffectedRowsCount() === 1;
	}

	public static function insertCounter(string $code): void
	{
		$helper = Application::getConnection()->getSqlHelper();
		$table = static::getTableName();

		$insert = $helper->prepareInsert($table, [
			'CODE' => $code,
			'COUNTER' => 1,
			'DATE_CREATE' => new Date(),
		]);
		[$columns, $values] = $insert;
		$tableName = $helper->quote($table);

		$sql = "
			INSERT INTO {$tableName} ({$columns})
			VALUES ({$values})
			ON DUPLICATE KEY UPDATE COUNTER = COUNTER + 1
		";

		Application::getConnection()->queryExecute($sql);
	}

	public static function insertCounterWithParam(string $code, string $additionalParam)
	{
		$helper = Application::getConnection()->getSqlHelper();
		$table = static::getTableName();
		$additionalParam = self::getMap()['ADDITIONAL_PARAMS']->encode([$additionalParam]);

		$insert = $helper->prepareInsert($table, [
			'CODE' => $code,
			'COUNTER' => 1,
			'DATE_CREATE' => new Date(),
			'ADDITIONAL_PARAMS' => $additionalParam,
		]);
		[$columns, $values] = $insert;
		$tableName = $helper->quote($table);

		$sql = "
			INSERT INTO {$tableName} ({$columns})
			VALUES ({$values})
			ON DUPLICATE KEY UPDATE
			COUNTER = IF(
				LOCATE('{$additionalParam}', ADDITIONAL_PARAMS) = 0,
				COUNTER + 1,
				COUNTER
			),
			ADDITIONAL_PARAMS = IF(
				LOCATE('{$additionalParam}', ADDITIONAL_PARAMS) = 0,
				'{$additionalParam}',
				CONCAT_WS(' ', ADDITIONAL_PARAMS, '{$additionalParam}')
			)
		";

		Application::getConnection()->queryExecute($sql);
	}
}