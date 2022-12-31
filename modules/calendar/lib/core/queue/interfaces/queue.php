<?php
namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface Queue
{
	/**
	 * Gets the name of this queue. This is a destination one consumes messages from.
	 */
	public function getQueueName(): string;

	public function getQueueId(): int;
}
