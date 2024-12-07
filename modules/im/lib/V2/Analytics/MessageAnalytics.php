<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Disk\TypeFile;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main\ArgumentException;

class MessageAnalytics extends ChatAnalytics
{
	protected const FILE_TYPES = [
		TypeFile::IMAGE        => 'image',
		TypeFile::VIDEO        => 'video',
		TypeFile::DOCUMENT     => 'document',
		TypeFile::ARCHIVE      => 'archive',
		TypeFile::SCRIPT       => 'script',
		TypeFile::UNKNOWN      => 'unknown',
		TypeFile::PDF          => 'pdf',
		TypeFile::AUDIO        => 'audio',
		TypeFile::KNOWN        => 'known',
		TypeFile::VECTOR_IMAGE => 'vector-image',
	];

	private function getMessageType(Chat $chat): string
	{
		if ($chat instanceof Chat\ChannelChat)
		{
			return 'post';
		}
		elseif ($chat instanceof Chat\CommentChat)
		{
			return 'comment';
		}

		return 'none';
	}

	public function addSendMessage(int|Message $message): void
	{
		$this->async(function () use ($message)
		{
			try {

				if (!$message instanceof Message)
				{
					$message = $this->getMessage($message);
				}

				if ($message->isForward())
				{
					return; // only share_message
				}

				if ($message->isSystem())
				{
					return; // not needed
				}

				$chat = $this->getChat($message->getChatId());
				$messageType = $this->getMessageType($chat);
				$this
					->createChatEvent('send_message', $chat)
					->setType($messageType)
					->send()
				;

				$files = $message->getFiles();

				foreach ($files as $file) {
					$this
						->createChatEvent('attach_file', $chat)
						->setType(self::FILE_TYPES[$file->getDiskFile()->getTypeFile()] ?? '')
						->send()
					;
				}
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	/**
	 * @param int $chatId
	 * @param string $reaction
	 * @return void
	 */
	public function addAddReaction(int $chatId, string $reaction): void
	{
		$this->async(function () use ($chatId, $reaction)
		{
			try
			{
				$chat = $this->getChat($chatId);
				$this
					->createChatEvent('add_reaction', $chat)
					->setType($reaction)
					->send()
				;
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	/**
	 * @param Chat $chat
	 * @param Message $message
	 * @return void
	 */
	public function addShareMessage(Chat $chat, Message $message): void
	{
		$this->async(function () use ($chat, $message)
		{
			try
			{
				$this
					->createChatEvent('share_message', $chat)
					->setType((new MessageContent($message))->getComponentName())
					->send()
				;

			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	/**
	 * @param Chat $chat
	 * @param string $messageType
	 * @return void
	 */
	public function addDeleteMessage(Chat $chat, string $messageType): void
	{
		$this->async(function () use ($chat, $messageType)
		{
			try
			{
				$this
					->createChatEvent('delete_message', $chat, false)
					->setType($messageType)
					->send()
				;

			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}
}
