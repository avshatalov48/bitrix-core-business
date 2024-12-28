<?php

namespace Bitrix\Im\V2\Analytics\Event;

use Bitrix\Im\V2\Analytics\ChatAnalytics;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Chat\CollabChat;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Chat\CopilotChat;
use Bitrix\Im\V2\Chat\GeneralChannel;
use Bitrix\Im\V2\Chat\GeneralChat;
use Bitrix\Im\V2\Chat\GroupChat;
use Bitrix\Im\V2\Chat\OpenChannelChat;
use Bitrix\Im\V2\Chat\OpenChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Chat\VideoConfChat;
use Bitrix\Im\V2\Entity\User\UserCollaber;
use Bitrix\Im\V2\Entity\User\UserExternal;

class ChatEvent extends Event
{
	protected const CHAT_TYPE_CONDITIONS = [
		'collab' => ['instanceof' => CollabChat::class],

		'call' => ['entity' => 'CALL'],
		'crm' => ['entity' => 'CRM'],
		'mail' => ['entity' => 'MAIL'],
		'sonetGroup' => ['entity' => 'SONET_GROUP'],
		'tasks' => ['entity' => 'TASKS'],

		'calendar' => ['entity' => 'CALENDAR'],
		'support24Notifier' => ['entity' => 'SUPPORT24_NOTIFIER'],
		'support24Question' => ['entity' => 'SUPPORT24_QUESTION'],
		'thread' => ['entity' => 'THREAD'],
		'announcement' => ['entity' => 'ANNOUNCEMENT'],

		'generalChannel' => ['instanceof' => GeneralChat::class],
		'openChannel' => ['instanceof' => OpenChannelChat::class],
		'channel' => ['instanceof' => ChannelChat::class],

		'comment' => ['instanceof' => CommentChat::class],
		'open' => ['instanceof' => OpenChat::class],
		'general' => ['instanceof' => GeneralChat::class],
		'videoconf' => ['instanceof' => VideoConfChat::class],
		'copilot' => ['instanceof' => CopilotChat::class],
		'chat' => ['instanceof' => GroupChat::class],
		'user' => ['instanceof' => PrivateChat::class],
	];
	protected const CHAT_P1_CONDITIONS = [
		'chatType_collab' => ['instanceof' => CollabChat::class],

		'chatType_call' => ['entity' => 'CALL'],
		'chatType_crm' => ['entity' => 'CRM'],
		'chatType_mail' => ['entity' => 'MAIL'],
		'chatType_sonetGroup' => ['entity' => 'SONET_GROUP'],
		'chatType_tasks' => ['entity' => 'TASKS'],
		'chatType_calendar' => ['entity' => 'CALENDAR'],

		'chatType_generalChannel' => ['instanceof' => GeneralChannel::class],
		'chatType_openChannel' => ['instanceof' => OpenChannelChat::class],
		'chatType_channel' => ['instanceof' => ChannelChat::class],

		'chatType_comments' => ['instanceof' => CommentChat::class],
		'chatType_open' => ['instanceof' => OpenChat::class],
		'chatType_general' => ['instanceof' => GeneralChat::class],
		'chatType_videoconf' => ['instanceof' => VideoConfChat::class],
		'chatType_copilot' => ['instanceof' => CopilotChat::class],
		'chatType_chat' => ['instanceof' => GroupChat::class],

		'chatType_user' => ['instanceof' => PrivateChat::class],
	];

	protected function setDefaultParams(): self
	{
		$this
			->setChatP1()
			->setChatP2()
			->setChatP4()
			->setChatP5()
		;

		return $this;
	}

	public function setChatType(): self
	{
		$entityType = $this->chat->getEntityType();
		foreach (self::CHAT_TYPE_CONDITIONS as $typeName => $conditions)
		{
			if (array_key_exists('entity', $conditions) && $conditions['entity'] === $entityType)
			{
				$this->type = $typeName;

				return $this;
			}

			if (array_key_exists('instanceof', $conditions))
			{
				if (!is_array($conditions['instanceof']))
				{
					$conditions['instanceof'] = [$conditions['instanceof']];
				}

				foreach ($conditions['instanceof'] as $condition)
				{
					if ($this->chat instanceof $condition)
					{
						$this->type = $typeName;

						return $this;
					}
				}
			}
		}

		$this->type = '';

		return $this;
	}

	protected function setChatP1(): self
	{
		$entityType = $this->chat->getEntityType();

		foreach (self::CHAT_P1_CONDITIONS as $typeName => $conditions)
		{
			if (array_key_exists('entity', $conditions) && $conditions['entity'] === $entityType)
			{
				$this->p1 = $typeName;

				return $this;
			}

			if (array_key_exists('instanceof', $conditions) && $this->chat instanceof $conditions['instanceof'])
			{
				$this->p1 = $typeName;

				return $this;
			}
		}

		$this->p1 = 'custom';

		return $this;
	}

	protected function setChatP2(): self
	{
		$currentUser = $this->chat->getContext()->getUser();

		$this->p2 = match (true)
		{
			$currentUser instanceof UserCollaber => 'user_collaber',
			$currentUser instanceof UserExternal => 'user_extranet',
			default => 'user_intranet',
		};

		return $this;
	}

	protected function setChatP4(): self
	{
		if ($this->chat instanceof CollabChat)
		{
			$this->p4 = 'collabId_' . ($this->chat->getEntityId() ?? 0);

			return $this;
		}


		$parentChatId = $this->chat->getParentChatId();
		$this->p4 = 'parentChatId_' . ($parentChatId ?? 0);

		return $this;
	}

	protected function setChatP5(): self
	{
		if ($this->chat->getChatId() !== null)
		{
			$this->p5 = 'chatId_' . $this->chat->getChatId();
		}

		return $this;
	}

	protected function getTool(): string
	{
		return 'im';
	}

	protected function getCategory(string $eventName): string
	{
		$additionalCategories = $eventName === ChatAnalytics::SUBMIT_CREATE_NEW;

		return match (true)
		{
			$this->chat instanceof ChannelChat || $this->chat instanceof CommentChat => 'channel',
			$this->chat instanceof CopilotChat => 'copilot',
			$this->chat instanceof CollabChat => 'collab',
			$this->chat instanceof VideoConfChat && $additionalCategories => 'videoconf',
			default => 'chat',
		};
	}
}
