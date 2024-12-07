<?php

namespace Bitrix\MessageService\Queue\Event;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\MessageService\Queue\Event\Result\BeforeSendMessageFromQueueEventResult;
use Bitrix\MessageService\Sender\Result\SendMessage;

final class BeforeSendMessageFromQueueEvent extends Event
{
	public const TYPE = 'OnBeforeSendMessageFromQueue';

	public function __construct(array $messageFields)
	{
		parent::__construct(
			'messageservice',
			self::TYPE,
			[
				'message' => $messageFields,
			]
		);
	}

	/**
	 * Processing event's results after sending.
	 *
	 * @return SendMessage|null if the handlers return errors or an entire `SendMessage` object, the method will return the object.
	 */
	public function processResults(): ?SendMessage
	{
		$errors = [];

		foreach ($this->getResults() as $eventResult)
		{
			if ($eventResult->getType() === EventResult::ERROR)
			{
				if ($eventResult instanceof BeforeSendMessageFromQueueEventResult)
				{
					if (isset($eventResult->sendMessage))
					{
						return $eventResult->sendMessage;
					}
					elseif (isset($eventResult->errors))
					{
						foreach ($eventResult->errors as $item)
						{
							$errors[] = $item;
						}
					}
					else
					{
						$errors[] = new Error('Unknown error');
					}
				}
				else
				{
					$errors[] = new Error('Unknown error');
				}
			}
		}

		if (empty($errors))
		{
			return null;
		}

		$result = new SendMessage();
		$result->addErrors($errors);

		return $result;
	}
}
