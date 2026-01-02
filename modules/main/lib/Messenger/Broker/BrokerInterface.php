<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Broker;

use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ProcessingParamInterface;
use Bitrix\Main\Messenger\Internals\Exception\Broker\AckFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\BrokerReadException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\RejectFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;

/**
 * @internal
 */
interface BrokerInterface
{
	/**
	 * @param string $queueId
	 * @param int $limit
	 *
	 * @return iterable
	 *
	 * @throws BrokerReadException
	 */
	public function get(string $queueId, int $limit = 50): iterable;

	/**
	 * @param string $queueId
	 *
	 * @return MessageBox|null
	 *
	 * @throws BrokerReadException
	 */
	public function getOne(string $queueId): ?MessageBox;

	/**
	 * @param MessageInterface $message
	 * @param string $queueId
	 * @param ProcessingParamInterface[] $params
	 *
	 * @throws SendFailedException
	 */
	public function send(MessageInterface $message, string $queueId, array $params = []): void;

	/**
	 * @param MessageBox $message
	 *
	 * @return void
	 *
	 * @throws AckFailedException
	 */
	public function ack(MessageBox $message): void;

	/**
	 * @param MessageBox $message
	 *
	 * @return void
	 *
	 * @throws RejectFailedException
	 */
	public function reject(MessageBox $message): void;
}
