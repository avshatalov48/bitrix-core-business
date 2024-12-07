<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Throwable;

abstract class AbstractAnalytics
{
	protected function getChat(int $chatId): Chat
	{
		return Chat::getInstance($chatId);
	}

	/**
	 * @throws ArgumentException
	 */
	protected function getMessage(int $messageId): Message
	{
		$message = new Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new ArgumentException('Invalid message id ' . $messageId);
		}

		return $message;
	}

	abstract protected function getTool(): string;

	protected function createEvent(string $eventName, string $category): AnalyticsEvent
	{
		return new AnalyticsEvent($eventName, $this->getTool(), $category);
	}

	protected function async(callable $job): void
	{
		Application::getInstance()->addBackgroundJob($job);
	}

	protected function logException(Throwable $exception): void
	{
		///
	}
}