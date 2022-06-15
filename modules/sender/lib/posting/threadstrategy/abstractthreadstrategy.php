<?php

namespace Bitrix\Sender\Posting\ThreadStrategy;

use Bitrix\Main\Application;
use Bitrix\Main\DB;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Model\PostingThreadTable;
use Bitrix\Sender\PostingRecipientTable;

abstract class AbstractThreadStrategy implements IThreadStrategy
{
	/**
	 * @var int
	 */
	protected $threadId;

	protected $postingId;

	protected $select;

	protected $filter;
	protected $runtime;

	/**
	 *
	 * @return array
	 */
	public function fillThreads(): void
	{
		$tableName = PostingThreadTable::getTableName();

		$insertData = [];
		for ($thread = 0; $thread < static::THREADS_COUNT; $thread++)
		{
			$insertData[] = '('.$thread.', '.$this->postingId.', \''.static::THREADS_COUNT.'\')';
		}
		$query = '
				INSERT INTO `'.$tableName.'`(THREAD_ID, POSTING_ID, THREAD_TYPE)
					VALUES '.implode(',', $insertData).'
					ON DUPLICATE KEY UPDATE
				THREAD_ID = VALUES(THREAD_ID)
			';

		try
		{
			Application::getConnection()->query($query);
		}
		catch (SqlQueryException $e)
		{

		}
	}

	/**
	 * @param $limit
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getRecipients(int $limit): Result
	{
		static::setRuntime();
		static::setFilter();
		static::setSelect();

		// select all recipients of posting, only not processed
		$recipients = PostingRecipientTable::getList(
			[
				'select'  => $this->select,
				'filter'  => $this->filter,
				'runtime' => $this->runtime,
				'order' => ['STATUS' => 'DESC'],
				'limit'   => $limit
			]
		);

		$recipients->addFetchDataModifier(
			function($row)
			{
				$row['FIELDS'] = is_array($row['FIELDS']) ? $row['FIELDS'] : [];

				return $row;
			}
		);

		return $recipients;
	}

	abstract protected function setRuntime(): void;

	protected function setFilter() : void
	{
		$this->filter = ['IS_UNSUB' => 'N'];
	}
	protected function setSelect(): void
	{
		$this->select = [
			'*',
			'NAME'                     => 'CONTACT.NAME',
			'CONTACT_CODE'             => 'CONTACT.CODE',
			'CONTACT_TYPE_ID'          => 'CONTACT.TYPE_ID',
			'CONTACT_IS_SEND_SUCCESS'  => 'CONTACT.IS_SEND_SUCCESS',
			'CONTACT_BLACKLISTED'      => 'CONTACT.BLACKLISTED',
			'CONTACT_UNSUBSCRIBED'      => 'CONTACT.IS_UNSUB',
			'CONTACT_CONSENT_STATUS'   => 'CONTACT.CONSENT_STATUS',
			'CONTACT_MAILING_UNSUBSCRIBED'     => 'MAILING_SUB.IS_UNSUB',
			'CONTACT_CONSENT_REQUEST'  => 'CONTACT.CONSENT_REQUEST',
			'CAMPAIGN_ID'              => 'POSTING.MAILING_ID',
		];
	}

	/**
	 * lock thread for duplicate select
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function lockThread(): void
	{
		if(!static::checkLock())
		{
			return;
		}
		$thread = PostingThreadTable::getList(
			[
				"select" => ["THREAD_ID"],
				"filter" => [
					'=POSTING_ID' => $this->postingId,
					[
						'LOGIC' => 'OR',
						[
							'=STATUS' => PostingThreadTable::STATUS_NEW,
						],
						[
							'=STATUS'    => PostingThreadTable::STATUS_IN_PROGRESS,
							'<EXPIRE_AT' => new DateTime()
						]
					]
				],
				"limit"  => 1
			]
		)->fetchAll();

		if (!isset($thread[0]) && !isset($thread[0]["THREAD_ID"]))
		{
			return;
		}
		$this->threadId = $thread[0]["THREAD_ID"];
		$this->updateStatus(PostingThreadTable::STATUS_IN_PROGRESS);
		$this->unlock();
	}

	/**
	 * lock table from selecting of the thread
	 * @return bool
	 * @throws SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function lock()
	{
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$lockDb = $connection->query(
				sprintf(
					"SELECT GET_LOCK('posting_thread_%d', 0) as L",
					$this->postingId
				),
				false,
				"File: ".__FILE__."<br>Line: ".__LINE__
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] == "1")
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * update status with expire date
	 * @param $threadId
	 * @param $status
	 */
	public function updateStatus(string $status): bool
	{
		if($status === PostingThreadTable::STATUS_DONE && !$this->checkToFinalizeStatus())
		{
			$status = PostingThreadTable::STATUS_NEW;
		}

		try
		{
			$tableName   = PostingThreadTable::getTableName();
			$expireAt    = (new \DateTime())->modify("+10 minutes")->format('Y-m-d H:i:s');
			$updateQuery = 'UPDATE '.$tableName.' 
			SET 
			STATUS = \''.$status.'\',
			EXPIRE_AT = \''.$expireAt.'\'
			WHERE 
			THREAD_ID = '.$this->threadId.' 
			AND POSTING_ID = '.$this->postingId;
			Application::getConnection()->query($updateQuery);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * unlock table for select
	 * @return bool
	 * @throws SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function unlock()
	{
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$lockDb = $connection->query(
				sprintf(
					"SELECT RELEASE_LOCK('posting_thread_%d') as L",
					$this->postingId
				)
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] != "0")
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * checking that all threads are completed
	 * @return bool
	 */
	public function hasUnprocessedThreads(): bool
	{
		try
		{
			$threads = PostingThreadTable::getList(
				[
					"select" => ["THREAD_ID"],
					"filter" => [
						'@STATUS'     => new SqlExpression(
							"?, ?", PostingThreadTable::STATUS_NEW, PostingThreadTable::STATUS_IN_PROGRESS
						),
						'=POSTING_ID' => $this->postingId,
						'!=THREAD_ID' => $this->threadId
					]
				]
			)->fetchAll();
		}
		catch (\Exception $e)
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
	 * @param int $postingId
	 *
	 * @return TenThreadsStrategy
	 */
	public function setPostingId(int $postingId): IThreadStrategy
	{
		$this->postingId = $postingId;

		return $this;
	}

	/**
	 * wait while threads are calculating
	 * @return bool
	 */
	protected function checkLock()
	{
		for($i = 0; $i <= static::lastThreadId(); $i++)
		{
			if ($this->lock())
			{
				return true;
			}
			sleep(rand(1,7));
		}
		return false;
	}

	/**
	 * Finalize thread activity
	 */
	public function finalize()
	{
		if(!$this->checkToFinalizeStatus())
		{
			return false;
		}

		$tableName = PostingThreadTable::getTableName();
		$query = 'DELETE FROM `'.$tableName.'` WHERE POSTING_ID='.intval($this->postingId);
		try
		{
			Application::getConnection()->query($query);
		}
		catch (SqlQueryException $e)
		{
			return false;
		}

		return true;
	}

	private function checkToFinalizeStatus()
	{
		if($this->threadId < static::lastThreadId())
		{
			return true;
		}

		return !static::hasUnprocessedThreads();
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