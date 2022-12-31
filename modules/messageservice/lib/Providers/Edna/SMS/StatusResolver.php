<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\MessageService\MessageStatus;

class StatusResolver implements \Bitrix\MessageService\Providers\StatusResolver
{

	public function resolveStatus(string $serviceStatus): ?int
	{
		$serviceStatus = mb_strtolower($serviceStatus);
		switch ($serviceStatus)
		{
			case 'read':
			case 'sent':
				return MessageStatus::SENT;
			case 'enqueued':
				return MessageStatus::QUEUED;
			case 'delayed':
				return MessageStatus::ACCEPTED;
			case 'delivered':
				return MessageStatus::DELIVERED;
			case 'undelivered':
				return MessageStatus::UNDELIVERED;
			case 'failed':
			case 'cancelled':
				return MessageStatus::FAILED;
			default:
				return
					mb_strpos($serviceStatus, 'error') === 0
						? MessageStatus::ERROR
						: null
					;
		}
	}
}