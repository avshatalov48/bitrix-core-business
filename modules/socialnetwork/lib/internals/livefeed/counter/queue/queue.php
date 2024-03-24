<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue;

use Bitrix\Main\Application;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Exception\CounterQueuePopException;

class Queue
{
	private array $popped = [];
	private static Queue|null $instance = null;
	private static array $inQueue = [];

	public static function isInQueue(int $userId): bool
	{
		if (!array_key_exists($userId, self::$inQueue))
		{
			$res = QueueTable::getRow([
				'filter' => [
					'=USER_ID' => $userId
				]
			]);

			self::$inQueue[$userId] = (bool) $res;
		}

		return self::$inQueue[$userId];
	}

	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * CounterQueue constructor.
	 */
	private function __construct()
	{

	}

	public function add(int $userId, string $type, array $logs): void
	{
		if (empty($logs))
		{
			return;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$req = [];
		foreach ($logs as $logId)
		{
			$req[] = $userId .',\''. $helper->forSql($type) . '\',' . (int)$logId;
		}

		$sql = "
			INSERT INTO " . $helper->quote(QueueTable::getTableName()) . "
			(" . $helper->quote('USER_ID') . ", " . $helper->quote('TYPE') . ", " . $helper->quote('SONET_LOG_ID') . ")
			VALUES
			(". implode("),(", $req) .")
		";

		$connection->queryExecute($sql);

		self::$inQueue[$userId] = true;
	}

	/**
	 * @param int $limit
	 * @return array
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function get(int $limit): array
	{
		if (!empty($this->popped))
		{
			throw new CounterQueuePopException();
		}

		$iterator = QueueTable::getList([
			'select' => [
				'ID',
				'USER_ID',
				'TYPE',
				'SONET_LOG_ID',
			],
			'order' => [
				'ID' => 'ASC',
			],
			'limit' => $limit,
		]);

		$queue = [];
		while ($row = $iterator->fetch())
		{
			$this->popped[] = $row['ID'];

			$userId = (int) $row['USER_ID'];
			$type = $row['TYPE'];
			$key = $userId.'_'.$type;

			if (!array_key_exists($key, $queue))
			{
				$queue[$userId.'_'.$type] = [
					'USER_ID' => $userId,
					'TYPE' => $type
				];
			}
			$queue[$key]['SONET_LOGS'][] = (int) $row['SONET_LOG_ID'];
		}

		return $queue;
	}

	/**
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function done(): void
	{
		if (empty($this->popped))
		{
			return;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sql = "
			DELETE
			FROM ". $helper->quote(QueueTable::getTableName()) ."
			WHERE ID IN (". implode(",", $this->popped) .")
		";
		$connection->queryExecute($sql);

		$this->popped = [];
	}
}
