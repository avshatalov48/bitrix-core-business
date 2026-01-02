<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Receiver;

use Bitrix\Main\Messenger\Broker\BrokerInterface;
use Bitrix\Main\Messenger\Internals\Exception\ReceiverException;

/**
 * @internal
 */
interface ReceiverInterface
{
	/**
	 * @throws ReceiverException
	 */
	public function run(): void;

	public function setLimit(int $limit): self;

	public function setQueueId(string $queueId): self;

	public function setBroker(BrokerInterface $broker): self;
}
