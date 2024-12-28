<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Analytics\Event\MessageEvent;
use Bitrix\Im\V2\Message;

class MessageAnalytics extends ChatAnalytics
{
	protected const SEND_MESSAGE = 'send_message';
	protected const ADD_REACTION = 'add_reaction';
	protected const SHARE_MESSAGE = 'share_message';
	protected const DELETE_MESSAGE = 'delete_message';
	protected const ATTACH_FILE = 'attach_file';

	protected Message $message;

	public function __construct(Message $message)
	{
		parent::__construct($message->getChat());
		$this->message = $message;
	}

	public function addSendMessage(): void
	{
		$this->async(function () {
			if ($this->message->getMessageId() === null)
			{
				return;
			}

			if ($this->message->isForward() || $this->message->isSystem())
			{
				return;
			}

			$this
				->createMessageEvent(self::SEND_MESSAGE)
				?->setType((new MessageContent($this->message))->getComponentName())
				?->send()
			;

			$this->addAttachFilesEvent();
		});
	}

	public function addAddReaction(string $reaction): void
	{
		$this->async(function () use ($reaction) {
			$this
				->createMessageEvent(self::ADD_REACTION)
				?->setType($reaction)
				?->send()
			;
		});
	}

	public function addShareMessage(): void
	{
		$this->async(function () {
			$this
				->createMessageEvent(self::SHARE_MESSAGE)
				?->setType((new MessageContent($this->message))->getComponentName())
				?->send()
			;
		});
	}

	public function addDeleteMessage(string $messageType): void
	{
		$this->async(function () use ($messageType) {
			$this
				->createMessageEvent(self::DELETE_MESSAGE)
				?->setType($messageType)
				?->send()
			;
		});
	}

	protected function addAttachFilesEvent(): void
	{
		$files = $this->message->getFiles();
		$fileCount = $files->count();
		if ($fileCount < 1)
		{
			return;
		}

		$this
			->createMessageEvent(self::ATTACH_FILE)
			?->setFilesType($files)
			?->setFileP3($fileCount)
			?->send()
		;
	}

	protected function createMessageEvent(
		string $eventName,
	): ?MessageEvent
	{
		if (!$this->isChatTypeAllowed($this->chat))
		{
			return null;
		}

		return (new MessageEvent($eventName, $this->chat));
	}
}
