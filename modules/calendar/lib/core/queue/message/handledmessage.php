<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Queue\Queue\Queue;

class HandledMessage implements EntityInterface
{
	private ?int $id = null;

	private Message $message;

	private Queue $queue;

	private string $hash;

	private Date $dateCreate;

	/**
	 * @return Message
	 */
	public function getMessage(): Message
	{
		return $this->message;
	}

	/**
	 * @param Message $message
	 * @return HandledMessage
	 */
	public function setMessage(Message $message): self
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @return Queue
	 */
	public function getQueue(): Queue
	{
		return $this->queue;
	}

	/**
	 * @param Queue $queue
	 * @return HandledMessage
	 */
	public function setQueue(Queue $queue): self
	{
		$this->queue = $queue;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * @param string $hash
	 * @return HandledMessage
	 */
	public function setHash(string $hash): self
	{
		$this->hash = $hash;
		return $this;
	}

	/**
	 * @return Date
	 */
	public function getDateCreate(): Date
	{
		return $this->dateCreate;
	}

	/**
	 * @param Date $dateCreate
	 * @return HandledMessage
	 */
	public function setDateCreate(Date $dateCreate): self
	{
		$this->dateCreate = $dateCreate;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @param int|null $id
	 *
	 * @return HandledMessage
	 */
	public function setId(?int $id): self
	{
		$this->id = $id;
		return $this;
	}
}