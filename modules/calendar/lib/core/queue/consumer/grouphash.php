<?php

namespace Bitrix\Calendar\Core\Queue\Consumer;

use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\HandledMessage;
use Bitrix\Calendar\Core\Queue\Message\HandledMessageMapper;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Internals\QueueHandledMessageTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\SystemException;

class GroupHash implements Interfaces\Consumer
{
	private const MESSAGE_LIMIT = 10;
	private const HANDLED_MESSAGE_HEADER_ID = '~handledMessageId';
	private const HANDLED_MESSAGE_HEADER_HASH = '~handledMessageHash';

	private Interfaces\Queue $queue;

	private ?HandledMessageMapper $handledMessageMapper = null;

	public function __construct(Interfaces\Queue $queue)
	{
		$this->queue = $queue;
	}

	public function getQueue(): Interfaces\Queue
	{
		return $this->queue;
	}

	/**
	 * @return Interfaces\Message|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function receive(): ?Interfaces\Message
	{
		$row = QueueHandledMessageTable::query()
			->addGroup('HASH')
			->setLimit(1)
			->registerRuntimeField('MAX_ID', [
				'data_type' => 'string',
				'expression' => ['MAX(%s)', 'ID']
			])
			->setSelect(['MAX_ID', 'HASH'])
			->addFilter('QUEUE_ID', $this->getQueue()->getQueueId())
			->exec()->fetch()
		;
		if ($row)
		{
			$handledMessageId = (int)$row['MAX_ID'];
			/** @var HandledMessage $handledMessage */
			$handledMessage = $this->getHandledMessageMapper()->getById($handledMessageId);

			$handledMessage->getMessage()
				->setHeader(self::HANDLED_MESSAGE_HEADER_ID, $handledMessageId)
				->setHeader(self::HANDLED_MESSAGE_HEADER_HASH, $row['HASH'])
			;

			return $handledMessage->getMessage();
		}

		return null;
	}

	public function acknowledge(Interfaces\Message $message): void
	{
		global $DB;

		$id = (int)$message->getHeader(self::HANDLED_MESSAGE_HEADER_ID);
		$hash = $message->getHeader(self::HANDLED_MESSAGE_HEADER_HASH);
		if ($id && $hash)
		{
			$DB->Query("
				DELETE FROM b_calendar_queue_handled_message
				WHERE ID <= " . $id . " 
				AND HASH = '" . $hash . "'
			");
		}
	}

	/**
	 * @param Interfaces\Message $message
	 * @param bool $requeue
	 *
	 * @return void
	 */
	public function reject(Interfaces\Message $message, bool $requeue = false): void
	{
		global $DB;

		$id = (int)$message->getHeader(self::HANDLED_MESSAGE_HEADER_ID);
		$hash = $message->getHeader(self::HANDLED_MESSAGE_HEADER_HASH);
		if ($id && $hash)
		{
			$DB->Query("
				DELETE FROM b_calendar_queue_handled_message
				WHERE ID <= " . $id . " 
				AND HASH = '" . $hash . "'
			");

			$this->onAfterReject($message);
		}
	}

	/**
	 * @return HandledMessageMapper
	 */
	private function getHandledMessageMapper(): HandledMessageMapper
	{
		if ($this->handledMessageMapper === null)
		{
			$this->handledMessageMapper = new HandledMessageMapper();
		}

		return $this->handledMessageMapper;
	}

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return void
	 */
	private function onAfterReject(Interfaces\Message $message)
	{
		// TODO: implement it
	}
}