<?php

namespace Bitrix\Calendar\Core\Queue\Queue;

use Bitrix\Calendar\Core\Base\EntityInterface;
use \Bitrix\Calendar\Core\Queue\Interfaces;

class Queue implements Interfaces\Queue, EntityInterface
{
	private int $id;
	private string $name;

	public function __construct(int $id, string $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * Gets the name of this queue. This is a destination one consumes messages from.
	 */
	public function getQueueName(): string
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getQueueId(): int
	{
		return $this->id;
	}

	/**
	 * @return int|null
	 */
	public function getId(): int
	{
		return $this->getQueueId();
	}
}