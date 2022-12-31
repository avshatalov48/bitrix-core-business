<?php

namespace Bitrix\Calendar\Core\Queue\Consumer;

use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\HandledMessage;
use Bitrix\Calendar\Core\Queue\Message\HandledMessageMapper;
use Bitrix\Calendar\Core;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

class Simple implements Interfaces\Consumer
{
	private const HANDLED_MESSAGE_HEADER_NAME = '~handledMessageId';

	private Interfaces\Queue $queue;

	private ?HandledMessageMapper $handledMessageMapper = null;

	private int $packSize = 100;

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
		static $handledMessageMap = null;
		if ($handledMessageMap === null)
		{
			$handledMessageMap = $this->getHandledMessageMapper()->getMap(
				[
					'QUEUE_ID' => $this->getQueue()->getQueueId(),
				],
				$this->getPackSize(),
				[
					'DATE_CREATE' => 'ASC'
				],
			);
		}
		/** @var HandledMessage $row */
		if ($row = $handledMessageMap->fetch())
		{
			$message = $row->getMessage();
			$message->setHeader(self::HANDLED_MESSAGE_HEADER_NAME, $row->getId());
		}

		return null;
	}

	public function acknowledge(Interfaces\Message $message): void
	{
		if ($id = $message->getHeader(self::HANDLED_MESSAGE_HEADER_NAME))
		{
			$message->setHeader(self::HANDLED_MESSAGE_HEADER_NAME, null);
			$this->deleteHandledMessageByMessageId($id);
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
		if ($id = $message->getHeader(self::HANDLED_MESSAGE_HEADER_NAME))
		{
			$message->setHeader(self::HANDLED_MESSAGE_HEADER_NAME, null);
			$this->deleteHandledMessageByMessageId($id);

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

	private function deleteHandledMessageByMessageId($id)
	{
		$handledMessage = (new HandledMessage())->setId($id);
		$this->getHandledMessageMapper()->delete($handledMessage);
	}

	/**
	 * @return int
	 */
	public function getPackSize(): int
	{
		return $this->packSize;
	}

	/**
	 * @param int $packSize
	 */
	public function setPackSize(int $packSize): void
	{
		$this->packSize = $packSize;
	}
}