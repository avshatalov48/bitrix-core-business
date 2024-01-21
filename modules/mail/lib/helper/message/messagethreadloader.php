<?php

namespace Bitrix\Mail\Helper\Message;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Mail\Internals\MessageClosureTable;

final class MessageThreadLoader
{
	private array $threadBeforeMessageId = [];
	private array $threadAfterMessageId = [];

	public function __construct(
		private int $messageId,
	){}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	/**
	 * returns an array of message ids sorted in ascending order
	 * @return int[]
	 */
	public function getThreadMessageIds(): array
	{
		$threadMessageIds = [$this->messageId];
		if ($this->threadBeforeMessageId)
		{
			$threadMessageIds = array_merge($this->threadBeforeMessageId, $threadMessageIds);
		}
		if ($this->threadAfterMessageId)
		{
			$threadMessageIds = array_merge($threadMessageIds, $this->threadAfterMessageId);
		}

		return $threadMessageIds;
	}

	/**
	 * clears the message id array if you need to separately load the previous or next messages relative to the set messageId
	 * @return void
	 */
	public function clearThreadMessageIds(): void
	{
		$this->threadBeforeMessageId = [];
		$this->threadAfterMessageId = [];
	}

	public function loadBeforeThreadMessageIds(?int $limit = null): void
	{
		$this->threadBeforeMessageId = [];

		$query = MessageClosureTable::query()
			->setSelect(['PARENT_ID'])
			->where('MESSAGE_ID', $this->messageId)
			->where('PARENT_ID', '<', $this->messageId)
			->setOrder(['PARENT_ID' => 'ASC'])
		;

		if ($limit)
		{
			$query->setLimit($limit);
		}

		foreach ($query->fetchAll() as $item)
		{
			$this->threadBeforeMessageId[] = (int)$item['PARENT_ID'];
		}
	}

	public function loadAfterThreadMessageIds(?int $limit = null): void
	{
		$this->threadAfterMessageId = [];

		$query = MessageClosureTable::query()
			->setSelect(['MESSAGE_ID'])
			->where('PARENT_ID', $this->messageId)
			->where('MESSAGE_ID', '>', $this->messageId)
			->setOrder(['PARENT_ID' => 'ASC'])
		;

		if ($limit)
		{
			$query->setLimit($limit);
		}

		foreach ($query->fetchAll() as $item)
		{
			$this->threadAfterMessageId[] = (int)$item['MESSAGE_ID'];
		}
	}

	public function loadFullThreadMessageIds(?int $limit = null): void
	{
		$this->loadBeforeThreadMessageIds($limit);
		$this->loadAfterThreadMessageIds($limit);
	}
}