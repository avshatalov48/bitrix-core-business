<?php

namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Query;
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
	public static function getTableName(): string
	{
		return 'b_messageservice_restriction';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
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
	 * @return bool affected row counter
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

		if ($where !== '')
		{
			$where = ' WHERE ' . $where;
		}

		$helper = Application::getConnection()->getSqlHelper();
		$tableName = $helper->quote($table);
		$updateCounter = (new SqlExpression("?# = ?# + 1", 'COUNTER', 'COUNTER'))->compile();

		$sql = "UPDATE {$tableName} SET {$updateCounter} {$where}";

		Application::getConnection()->queryExecute($sql);

		return Application::getConnection()->getAffectedRowsCount() === 1;
	}

	/**
	 * @param string $code
	 * @param int $limit
	 * @param string $additionalParam
	 *
	 * @return bool affected row counter
	 */
	public static function updateCounterWithParam(string $code, int $limit, string $additionalParam): bool
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

		if ($where !== '')
		{
			$where = ' WHERE ' . $where;
		}

		$helper = Application::getConnection()->getSqlHelper();
		$tableName = $helper->quote($table);

		// If got duplicate by code+date,
		// when check for the same substring in ADDITIONAL_PARAMS,
		// then don't touch COUNTER and ADDITIONAL_PARAMS,
		// in otherwise increment COUNTER and append ADDITIONAL_PARAMS

		$updateCounter = (new SqlExpression(
			"?# = (CASE WHEN POSITION(?s IN ?#) = 0 THEN ?# + 1 ELSE ?# END)",
			'COUNTER',
			$encodedAdditionalParam,
			'ADDITIONAL_PARAMS',
			'COUNTER',
			'COUNTER'
		))->compile();

		$updateAdditionParams = (new SqlExpression(
			"?# = (CASE WHEN POSITION(?s IN ?#) = 0 THEN CONCAT_WS(' ', ?#, ?s) ELSE ?# END)",
			'ADDITIONAL_PARAMS',
			$encodedAdditionalParam,
			'ADDITIONAL_PARAMS',
			'ADDITIONAL_PARAMS',
			$encodedAdditionalParam,
			'ADDITIONAL_PARAMS'
		))->compile();

		$sql = "UPDATE {$tableName} SET {$updateCounter}, {$updateAdditionParams} {$where}";

		Application::getConnection()->queryExecute($sql);

		return Application::getConnection()->getAffectedRowsCount() === 1;
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public static function insertCounter(string $code): void
	{
		$helper = Application::getConnection()->getSqlHelper();
		$table = static::getTableName();

		$sql = $helper->prepareMerge(
			$table,
			['CODE', 'DATE_CREATE'],
			[
				'CODE' => $code,
				'DATE_CREATE' => new Date(),
				'COUNTER' => 1,
				'ADDITIONAL_PARAMS' => '',
			],
			[
				'COUNTER' => new SqlExpression("?#.?# + 1", $table, 'COUNTER')
			]
		)[0];

		Application::getConnection()->queryExecute($sql);
	}

	/**
	 * @param string $code
	 * @param string $additionalParam
	 * @return void
	 */
	public static function insertCounterWithParam(string $code, string $additionalParam): void
	{
		$helper = Application::getConnection()->getSqlHelper();
		$table = static::getTableName();
		$additionalParam = self::getMap()['ADDITIONAL_PARAMS']->encode([$additionalParam]);

		$sql = $helper->prepareMerge(
			$table,
			['CODE', 'DATE_CREATE'],
			[
				'CODE' => $code,
				'DATE_CREATE' => new Date(),
				'COUNTER' => 1,
				'ADDITIONAL_PARAMS' => $additionalParam,
			],
			[
				'COUNTER' => new SqlExpression(
					"(CASE WHEN POSITION(?s IN ?#.?#) = 0 THEN ?#.?# + 1 ELSE ?#.?# END)",
					$additionalParam,
					$table, 'ADDITIONAL_PARAMS',
					$table, 'COUNTER',
					$table, 'COUNTER'
				),
				'ADDITIONAL_PARAMS' => new SqlExpression(
					"(CASE WHEN POSITION(?s IN ?#.?#) = 0 THEN CONCAT_WS(' ', ?#.?#, ?s) ELSE ?#.?# END)",
					$additionalParam,
					$table, 'ADDITIONAL_PARAMS',
					$table, 'ADDITIONAL_PARAMS',
					$additionalParam,
					$table, 'ADDITIONAL_PARAMS'
				)
			]
		)[0];

		Application::getConnection()->queryExecute($sql);
	}
}