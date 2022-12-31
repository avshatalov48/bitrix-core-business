<?php

namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface Consumer
{
	/**
	 * Gets the Queue associated with this queue receiver.
	 */
	public function getQueue(): Queue;

	/**
	 * Receives the next message that arrives within the specified timeout interval.
	 * This call blocks until a message arrives, the timeout expires, or this message consumer is closed.
	 * A timeout of zero never expires, and the call blocks indefinitely.
	 *
	 * Timeout is in milliseconds
	 */
	public function receive(): ?Message;

	/**
	 * Tell the MQ broker that the message was processed successfully.
	 */
	public function acknowledge(Message $message): void;

	/**
	 * Tell the MQ broker that the message was rejected.
	 */
	public function reject(Message $message, bool $requeue = false): void;
}