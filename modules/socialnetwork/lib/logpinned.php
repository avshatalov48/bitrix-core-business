<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class LogPinnedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogPinned_Query query()
 * @method static EO_LogPinned_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogPinned_Result getById($id)
 * @method static EO_LogPinned_Result getList(array $parameters = [])
 * @method static EO_LogPinned_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogPinned createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogPinned_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogPinned wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogPinned_Collection wakeUpCollection($rows)
 */
class LogPinnedTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_pinned';
	}

	public static function getMap()
	{
		$fieldsMap = [
			'LOG_ID' => [
				'data_type' => 'integer',
				'primary' => true
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'primary' => true
			],
			'PINNED_DATE' => [
				'data_type' => 'datetime',
			],
		];

		return $fieldsMap;
	}


	public static function set(array $params = [])
	{
		global $USER;

		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$userId = (isset($params['userId']) ? intval($params['userId']) : (is_object($USER) && $USER instanceof \CUser ? $USER->getId() : 0));

		if ($logId <= 0)
		{
			throw new Main\SystemException("Empty logId.");
		}

		if ($userId <= 0)
		{
			throw new Main\SystemException("Empty userId.");
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$insertFields = [
			'LOG_ID' => $logId,
			'USER_ID' => $userId,
			'PINNED_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		];

		$updateFields = [
			'PINNED_DATE' => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		];

		$merge = $helper->prepareMerge(
			static::getTableName(),
			[ 'LOG_ID', 'USER_ID' ],
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}
}
