<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Broker;

use Bitrix\Main\Messenger\Entity\MessageBox;

class AckFailedException extends BrokerWriteException
{
	protected function buildActualMessage(MessageBox $messageBox): string
	{
		return 'Unable to ack message in queue ' . $messageBox->getQueueId();
	}
}
