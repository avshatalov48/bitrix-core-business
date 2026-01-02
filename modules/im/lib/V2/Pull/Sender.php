<?php

namespace Bitrix\Im\V2\Pull;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Pull\Error;

/**
 * @internal
 */
class Sender
{
	/**
	 * @internal
	 * @see \Bitrix\Im\V2\Pull\Event::send()
	 * @param Event $event
	 * @return Result
	 */
	public function send(Event $event): Result
	{
		if (!Loader::includeModule('pull'))
		{
			return (new Result())->addError(new \Bitrix\Im\V2\Error(\Bitrix\Im\V2\Error::PULL_NOT_INSTALLED));
		}

		$basePull = $event->getBase();
		$results = [];

		if ($event->isGlobal())
		{
			return $this->sendGlobal($basePull);
		}

		$chat = $event->getTarget();

		if ($chat !== null && !$event->shouldSendToOnlySpecificRecipients())
		{
			$results = $this->processPublicSending($chat, $basePull);
		}

		foreach ($event->getPullByUsers() as $group)
		{
			$results[] = $this->sendPull($group->getRecipients(), $group->getParams());
		}

		if ($this->shouldSendMobilePush($chat, $event))
		{
			foreach ($event->getMobilePushByUsers() as $group)
			{
				$results[] = $this->sendPush($group->getRecipients(), $group->getParams());
			}
		}

		if ($event->shouldSendImmediately())
		{
			\Bitrix\Pull\Event::send();
		}

		return Result::merge(...$results);
	}

	protected function processPublicSending(Chat $chat, array $basePull): array
	{
		$results = [];

		if ($chat->needToSendPublicPull())
		{
			$results[] = $this->sendByTag('IM_PUBLIC_'. $chat->getChatId(), $basePull);
		}
		if ($chat->getType() === Chat::IM_TYPE_OPEN_CHANNEL)
		{
			$results[] = $this->sendSharedPull($basePull);
		}
		if ($chat->getType() === Chat::IM_TYPE_COMMENT)
		{
			$results[] = $this->sendByTag('IM_PUBLIC_COMMENT_' . $chat->getParentChatId(), $basePull);
		}

		return $results;
	}

	protected function shouldSendMobilePush(?Chat $chat, Event $event): bool
	{
		return
			$event->shouldSendMobilePush()
			&& $chat !== null
			&& !($chat instanceof Chat\CommentChat)
		;
	}

	protected function sendGlobal(array $pull): Result
	{
		return self::getPullEventResult(\CPullStack::AddShared($pull));
	}

	protected function sendByTag(string $tag, array $pull): Result
	{
		return self::getPullEventResult(\CPullWatch::AddToStack($tag, $pull));
	}

	protected function sendSharedPull(array $pull): Result
	{
		return self::getPullEventResult(Chat\OpenChannelChat::sendSharedPull($pull));
	}

	protected function sendPull(array $recipients, array $pull): Result
	{
		return self::getPullEventResult(\Bitrix\Pull\Event::add($recipients, $pull));
	}

	protected function sendPush(array $recipients, array $push): Result
	{
		return self::getPullEventResult(\Bitrix\Pull\Push::add($recipients, $push));
	}

	protected static function getPullEventResult(bool $isSuccess): Result
	{
		if ($isSuccess)
		{
			return new Result();
		}

		$error = \Bitrix\Pull\Event::getLastError();
		if ($error instanceof Error)
		{
			return (new Result())->addError(new \Bitrix\Main\Error($error->msg, $error->code, $error->params));
		}

		return new Result();
	}
}
