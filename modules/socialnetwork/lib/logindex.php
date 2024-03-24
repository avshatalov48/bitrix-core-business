<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

/**
 * Class LogIndexTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogIndex_Query query()
 * @method static EO_LogIndex_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogIndex_Result getById($id)
 * @method static EO_LogIndex_Result getList(array $parameters = [])
 * @method static EO_LogIndex_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogIndex_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogIndex wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogIndex_Collection wakeUpCollection($rows)
 */
class LogIndexTable extends Entity\DataManager
{
	public const ITEM_TYPE_LOG = 'L';
	public const ITEM_TYPE_COMMENT = 'LC';

	public static function getItemTypes(): array
	{
		return [
			self::ITEM_TYPE_LOG,
			self::ITEM_TYPE_COMMENT
		];
	}

	public static function getTableName(): string
	{
		return 'b_sonet_log_index';
	}

	public static function getMap(): array
	{
		return [
			'LOG_ID' => [
				'data_type' => 'integer',
			],
			'LOG_UPDATE' => [
				'data_type' => 'datetime',
			],
			'DATE_CREATE' => [
				'data_type' => 'datetime',
			],
			'ITEM_TYPE' => [
				'data_type' => 'string',
				'primary' => true,
			],
			'ITEM_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'CONTENT' => [
				'data_type' => 'text',
			],
		];
	}

	public static function set($params = []): bool
	{
		$itemType = ($params['itemType'] ?? self::ITEM_TYPE_LOG);
		$itemId = (int)($params['itemId'] ?? 0);
		$logId = (int)($params['logId'] ?? 0);
		$content = trim(($params['content'] ?? ''));

		if (
			!in_array($itemType, self::getItemTypes())
			|| $itemId <= 0
			|| $logId <= 0
			|| empty($content)
		)
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$value = $helper->forSql($content);
		$encryptedValue = sha1($content);

		$insertFields = [
			'ITEM_TYPE' => $helper->forSql($itemType),
			'ITEM_ID' => $itemId,
			'LOG_ID' => $logId,
			'CONTENT' => $value,
		];

		$updateFields = [
			'CONTENT' => new SqlExpression("CASE WHEN " . $helper->getSha1Function('?v') . " = '{$encryptedValue}' THEN ?v ELSE '{$value}' END", 'CONTENT', 'CONTENT'),
		];

		if (
			isset($params['logDateUpdate'])
			&& $params['logDateUpdate'] instanceof DateTime
		)
		{
			$insertFields['LOG_UPDATE'] = $params['logDateUpdate'];
			$updateFields['LOG_UPDATE'] = $params['logDateUpdate'];
		}

		if (
			isset($params['dateCreate'])
			&& $params['dateCreate'] instanceof DateTime
		)
		{
			$insertFields['DATE_CREATE'] = $params['dateCreate'];
			$updateFields['DATE_CREATE'] = $params['dateCreate'];
		}

		$merge = $helper->prepareMerge(
			static::getTableName(),
			[ 'ITEM_TYPE', 'ITEM_ID' ],
			$insertFields,
			$updateFields
		);

		if ($merge[0] != '')
		{
			$connection->query($merge[0]);
		}

		return true;
	}

	public static function setLogUpdate($params = []): bool
	{
		$logId = (int)($params['logId'] ?? 0);
		$value = (!empty($params['value']) ? $params['value'] : false);

		if ($logId <= 0)
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$now = $connection->getSqlHelper()->getCurrentDateTimeFunction();
		if (
			!$value
			|| mb_strtolower($value) == mb_strtolower($now)
		)
		{
			$value = new SqlExpression($now);
		}

		$updateFields = [
			"LOG_UPDATE" => $value,
		];

		$tableName = self::getTableName();
		list($prefix, $values) = $helper->prepareUpdate($tableName, $updateFields);
		$connection->queryExecute("UPDATE {$tableName} SET {$prefix} WHERE LOG_ID = " . $logId);

		return true;
	}
}
