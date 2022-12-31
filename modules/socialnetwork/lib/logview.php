<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class LogViewTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogView_Query query()
 * @method static EO_LogView_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogView_Result getById($id)
 * @method static EO_LogView_Result getList(array $parameters = [])
 * @method static EO_LogView_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogView createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogView_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogView wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogView_Collection wakeUpCollection($rows)
 */
class LogViewTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_view';
	}

	public static function getMap()
	{
		return [
			'USER_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'EVENT_ID' => [
				'data_type' => 'string',
				'primary' => true,
			],
			'TYPE' => [
				'data_type' => 'boolean',
				'values' => [ 'N', 'Y' ],
			],
		];
	}

	public static function getDefaultValue($eventId, $full = false)
	{
		$result = 'Y';

		$eventId = trim($eventId);
		if($eventId !== '')
		{
			throw new Main\SystemException('Empty eventId.');
		}
		if (!$full)
		{
			$eventId = \CSocNetLogTools::findFullSetByEventID($eventId);
		}

		$res = self::getList([
			'order' => [],
			'filter' => [
				'=USER_ID' => 0,
				'=EVENT_ID' => \Bitrix\Main\Application::getConnection()->getSqlHelper()->forSql($eventId),
			],
			'select' => [ 'TYPE' ],
		]);

		if ($row = $res->fetch())
		{
			$result = $row['TYPE'];
		}

		return $result;
	}

	public static function set($userId, $eventId, $type): void
	{
		$userId = (int)$userId;
		$type = ($type === 'Y' ? 'Y' : 'N');
		$eventId = trim($eventId);
		if ($eventId === '')
		{
			throw new Main\SystemException('Empty eventId.');
		}
		$eventId = \CSocNetLogTools::findFullSetByEventID($eventId);

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		foreach ($eventId as $val)
		{
			$insertFields = [
				'USER_ID' => $userId,
				'TYPE' => $type,
				'EVENT_ID' => $helper->forSql($val),
			];

			$updateFields = [
				'TYPE' => $type,
			];

			$merge = $helper->prepareMerge(
				static::getTableName(),
				[ 'USER_ID', 'EVENT_ID' ],
				$insertFields,
				$updateFields
			);

			if ($merge[0] !== '')
			{
				$connection->query($merge[0]);
			}
		}
	}

	public static function checkExpertModeAuto($userId, $tasksNum, $pageSize): bool
	{
		$result = false;

		$userId = (int)$userId;
		$tasksNum = (int)$tasksNum;
		$pageSize = (int)$pageSize;

		if (
			$userId <= 0
			|| $pageSize <= 0
		)
		{
			return false;
		}

		if (
			$tasksNum >= 5
			&& ($tasksNum / $pageSize) >= 0.25
		)
		{
			$isAlreadyChecked = \CUserOptions::getOption('socialnetwork', '~log_expertmode_checked', 'N', $userId);
			if ($isAlreadyChecked !== 'Y')
			{
				self::set($userId, 'tasks', 'N');
				\CUserOptions::setOption('socialnetwork', '~log_expertmode_checked', 'Y', false, $userId);
				$result = true;
			}
		}

		return $result;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException('Use set() method of the class.');
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException('Use set() method of the class.');
	}
}
