<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Broker;

use Bitrix\Main\Messenger\Entity\MessageBox;

class SendFailedException extends BrokerWriteException
{
	protected function buildActualMessage(MessageBox $messageBox): string
	{
		return 'Unable to send message to queue ' . $messageBox->getQueueId();
	}
}
