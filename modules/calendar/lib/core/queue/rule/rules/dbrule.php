<?php

namespace Bitrix\Calendar\Core\Queue\Rule\Rules;

use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\HandledMessage;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Queue\Queue\Queue;

abstract class DbRule implements Interfaces\RouteRule
{
	/**
	 * @param Interfaces\Message $message
	 * @return Result|null
	 */
	public function route(Interfaces\Message $message): ?HandledMessage
	{
		/** @var Queue $queue */
		if ($queue = $this->getTargetQueue($message))
		{
			return (new HandledMessage())
				->setMessage($message)
				->setQueue($queue)
				->setHash($this->getMessageHash($message));
		}

		return null;
	}

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return Queue|null
	 */
	abstract protected function getTargetQueue(Interfaces\Message $message): ?Queue;

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return string
	 */
	abstract protected function getMessageHash(Interfaces\Message $message): string;
}