<?php

namespace Bitrix\Sender\Posting\SegmentThreadStrategy;

use Bitrix\Main\Application;
use Bitrix\Main\DB;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Model\GroupThreadTable;
use Bitrix\Sender\Internals\SqlBatch;
use Bitrix\Sender\Posting\Locker;
use Bitrix\Sender\PostingRecipientTable;

abstract class AbstractThreadStrategy implements ThreadStrategy
{
	/**
	 * @var int
	 */
	protected $threadId;

	protected $groupStateId;
	protected $offset;
	protected $perPage = 100000;
	protected const GROUP_THREAD_LOCK_KEY = 'group_thread_';
	public const THREAD_UNAVAILABLE = -1;
	public const THREAD_LOCKED = -2;
	public const THREAD_NEEDED = 1;


	/**
	 * Insert new group threads with ignore of conflicts
	 *
	 * @return void
	 */
	public function fillThreads(): void
	{
		$insertData = [];

		\CTimeZone::Disable();
		for ($thread = 0; $thread < static::THREADS_COUNT; $thread++)
		{
			$insertData[] = [
				'THREAD_ID' => $thread,
				'GROUP_STATE_ID' => $this->groupStateId,
				'THREAD_TYPE' => static::THREADS_COUNT,
				'EXPIRE_AT' => new DateTime(),
			];
		}

		SqlBatch::insert(GroupThreadTable::getTableName(), $insertData);
		\CTimeZone::Enable();
	}

	/**
	 * lock thread for duplicate select
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function lockThread(): ?int
	{
		if (!static::checkLock())
		{
			return self::THREAD_UNAVAILABLE;
		}

		\CTimeZone::Disable();
		$thread = GroupThreadTable::getList(
			[
				"select" => [
					"THREAD_ID",
					"STEP"
				],
				"filter" => [
					'=GROUP_STATE_ID' => $this->groupStateId,
					[
						'LOGIC' => 'OR',
						[
							'=STATUS' => GroupThreadTable::STATUS_NEW,
						],
						[
							'=STATUS' => GroupThreadTable::STATUS_IN_PROGRESS,
							'<EXPIRE_AT' => new DateTime()
						]
					]
				],
				"limit" => 1
			]
		)->fetch();
		\CTimeZone::Enable();

		if (!isset($thread["THREAD_ID"]))
		{
			return self::THREAD_UNAVAILABLE;
		}
		$this->threadId = (int)$thread["THREAD_ID"];
		$this->offset = $this->threadId === 0 && (int)$thread["STEP"] === 0
			? 0 : $this->threadId * $this->perPage + (static::lastThreadId() + 1) * $this->perPage * $thread["STEP"];

		$this->updateStatus(GroupThreadTable::STATUS_IN_PROGRESS);
		Locker::unlock(self::GROUP_THREAD_LOCK_KEY, $this->groupStateId);

		return $this->threadId;
	}

	/**
	 * Check threads is available and not need to insert
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function checkThreads(): ?int
	{
		if (!static::checkLock())
		{
			return self::THREAD_LOCKED;
		}

		$thread = GroupThreadTable::getList(
			[
				"select" => [
					"THREAD_ID",
					"STEP"
				],
				"filter" => [
					'=GROUP_STATE_ID' => $this->groupStateId
				],
				"limit" => 1
			]
		)->fetch();

		if (isset($thread["THREAD_ID"]))
		{
			return self::THREAD_UNAVAILABLE;
		}
		Locker::unlock(self::GROUP_THREAD_LOCK_KEY, $this->groupStateId);

		return self::THREAD_NEEDED;
	}

	/**
	 * update status with expire date
	 * @param $threadId
	 * @param $status
	 */
	public function updateStatus(string $status): bool
	{
		if ($status === GroupThreadTable::STATUS_DONE && !$this->checkToFinalizeStatus())
		{
			$status = GroupThreadTable::STATUS_NEW;
		}

		try
		{
			\CTimeZone::Disable();

			$counter = (int)($status === GroupThreadTable::STATUS_IN_PROGRESS);
			$tableName = GroupThreadTable::getTableName();
			$expireAt = (new \DateTime())->modify("+10 minutes")->format('Y-m-d H:i:s');
			$updateQuery = 'UPDATE ' . $tableName . ' 
			SET 
			STATUS = \'' . $status . '\',
			STEP = STEP + \'' . $counter . '\',
			EXPIRE_AT = \'' . $expireAt . '\'
			WHERE 
			THREAD_ID = ' . $this->threadId . ' 
			AND GROUP_STATE_ID = ' . $this->groupStateId;
			Application::getConnection()->query($updateQuery);

		} catch (\Exception $e)
		{
			return false;
		}
		finally
		{
			\CTimeZone::Enable();
		}

		return true;
	}

	/**
	 * checking that all threads are completed
	 * @return bool
	 */
	public function hasUnprocessedThreads(): bool
	{
		try
		{
			$threads = GroupThreadTable::getList(
				[
					"select" => ["THREAD_ID"],
					"filter" => [
						'@STATUS' => new SqlExpression(
							"?, ?", GroupThreadTable::STATUS_NEW, GroupThreadTable::STATUS_IN_PROGRESS
						),
						'=GROUP_STATE_ID' => $this->groupStateId,
						'!=THREAD_ID' => $this->threadId
					]
				]
			)->fetchAll();
		} catch (\Exception $e)
		{
		}

		return !empty($threads);
	}

	/**
	 * get current thread id
	 * @return int
	 */
	public function getThreadId(): ?int
	{
		return $this->threadId;
	}

	/**
	 * get last thread number
	 * @return int
	 */
	public function lastThreadId(): int
	{
		return static::THREADS_COUNT - 1;
	}

	/**
	 * @param int $groupStateId
	 *
	 * @return ThreadStrategy
	 */
	public function setGroupStateId(int $groupStateId): ThreadStrategy
	{
		$this->groupStateId = $groupStateId;

		return $this;
	}

	/**
	 * wait while threads are calculating
	 * @return bool
	 */
	protected function checkLock()
	{
		for ($i = 0; $i <= static::lastThreadId(); $i++)
		{
			if (Locker::lock(self::GROUP_THREAD_LOCK_KEY, $this->groupStateId))
			{
				return true;
			}
			sleep(rand(1, 7));
		}
		return false;
	}

	/**
	 * Finalize thread activity
	 */
	public function finalize()
	{
		if (!$this->checkToFinalizeStatus())
		{
			return false;
		}

		$tableName = GroupThreadTable::getTableName();
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$query = 'DELETE FROM ' . $sqlHelper->quote($tableName) . ' WHERE GROUP_STATE_ID=' . intval($this->groupStateId);
		try
		{
			Application::getConnection()->query($query);
		} catch (SqlQueryException $e)
		{
			return false;
		}

		return true;
	}

	private function checkToFinalizeStatus()
	{
		if ($this->threadId < static::lastThreadId())
		{
			return true;
		}

		return !static::hasUnprocessedThreads();
	}


	public function getOffset(): ?int
	{
		return intval($this->offset);
	}

	public function setPerPage(int $perPage)
	{
		$this->perPage = $perPage;
	}

	/**
	 * Returns true if sending not available
	 * @return bool
	 */
	public function isProcessLimited(): bool
	{
		return false;
	}

}
