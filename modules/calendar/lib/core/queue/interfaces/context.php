<?php

namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface Context
{
	/**
	 * @param $body
	 * @param array $properties
	 * @param array $headers
	 *
	 * @return Message
	 */
	public function createMessage($body = null, array $properties = [], array $headers = []): Message;

	public function createQueue(string $queueName): Queue;

	public function createProducer(): Producer;

	public function createConsumer(): Consumer;
}