<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\ArgumentException;

class ChatAnalytics extends AbstractAnalytics
{
	private const CHAT_TYPE_CONDITIONS = [
		'lines' => [
			'instanceof' => [
				Chat\OpenLineChat::class,
				Chat\OpenLineLiveChat::class,
			],
		],
		'call' => ['entity' => Chat\EntityLink::TYPE_CALL],
		'crm' => ['entity' => Chat\EntityLink::TYPE_CRM],
		'mail' => ['entity' => Chat\EntityLink::TYPE_MAIL],
		'sonetGroup' => ['entity' => Chat\EntityLink::TYPE_SONET],
		'tasks' => ['entity' => Chat\EntityLink::TYPE_TASKS],

		'calendar' => ['entity' => 'CALENDAR'],
		'support24Notifier' => ['entity' => 'SUPPORT24_NOTIFIER'],
		'support24Question' => ['entity' => 'SUPPORT24_QUESTION'],
		'thread' => ['entity' => 'THREAD'],
		'announcement' => ['entity' => 'ANNOUNCEMENT'],

		'generalChannel' => ['instanceof' => Chat\GeneralChannel::class],
		'openChannel' => ['instanceof' => Chat\OpenChannelChat::class],
		'channel' => ['instanceof' => Chat\ChannelChat::class],

		'comment' => ['instanceof' => Chat\CommentChat::class],
		'open' => ['instanceof' => Chat\OpenChat::class],
		'general' => ['instanceof' => Chat\GeneralChat::class],
		'videoconf' => ['instanceof' => Chat\VideoConfChat::class],
		'copilot' => ['instanceof' => Chat\CopilotChat::class],
		'chat' => ['instanceof' => Chat\GroupChat::class],
		'user' => ['instanceof' => Chat\PrivateChat::class],
	];

	private const CHAT_P1_CONDITIONS = [
		'chatType_call' => ['entity' => Chat\EntityLink::TYPE_CALL],
		'chatType_crm' => ['entity' => Chat\EntityLink::TYPE_CRM],
		'chatType_mail' => ['entity' => Chat\EntityLink::TYPE_MAIL],
		'chatType_sonetGroup' => ['entity' => Chat\EntityLink::TYPE_SONET],
		'chatType_tasks' => ['entity' => Chat\EntityLink::TYPE_TASKS],
		'chatType_calendar' => ['entity' => 'CALENDAR'],

		'chatType_generalChannel' => ['instanceof' => Chat\GeneralChannel::class],
		'chatType_openChannel' => ['instanceof' => Chat\OpenChannelChat::class],
		'chatType_channel' => ['instanceof' => Chat\ChannelChat::class],

		'chatType_comments' => ['instanceof' => Chat\CommentChat::class],
		'chatType_open' => ['instanceof' => Chat\OpenChat::class],
		'chatType_general' => ['instanceof' => Chat\GeneralChat::class],
		'chatType_videoconf' => ['instanceof' => Chat\VideoConfChat::class],
		'chatType_copilot' => ['instanceof' => Chat\CopilotChat::class],
		'chatType_chat' => ['instanceof' => Chat\GroupChat::class],

		'chatType_user' => ['instanceof' => Chat\PrivateChat::class],
	];

	private static array $oneTimeEvents = [];

	private static array|bool $blockSingleUserEvents = [];

	private function isFirstTimeEvent(Chat|int $chat, string $eventName): bool
	{
		$chatId = ($chat instanceof Chat) ? $chat->getId() : $chat;
		$result = self::$oneTimeEvents[$eventName][$chatId] ?? true;
		self::$oneTimeEvents[$eventName][$chatId] = false;

		return $result;
	}

	public function blockSingleUserEvents(Chat|int|null $chat = null): void
	{
		if (null === $chat)
		{
			self::$blockSingleUserEvents = true;

			return;
		}

		$chatId = ($chat instanceof Chat) ? $chat->getId() : $chat;
		self::$blockSingleUserEvents[$chatId] = true;
	}

	private function isSingleUserEventsBlocked(Chat|int $chat): bool
	{
		if (!is_array(self::$blockSingleUserEvents))
		{
			return true;
		}

		$chatId = ($chat instanceof Chat) ? $chat->getId() : $chat;

		return self::$blockSingleUserEvents[$chatId] ?? false;
	}

	protected function getP1(Chat $chat): ?string
	{
		$entityType = $chat->getEntityType();

		foreach (self::CHAT_P1_CONDITIONS as $typeName => $conditions)
		{
			if (array_key_exists('entity', $conditions) && $conditions['entity'] === $entityType)
			{
				return $typeName;
			}

			if (array_key_exists('instanceof', $conditions) && $chat instanceof $conditions['instanceof'])
			{
				return $typeName;
			}
		}

		return 'custom';
	}

	private function getChatType(Chat $chat): string
	{
		$entityType = $chat->getEntityType();
		foreach (self::CHAT_TYPE_CONDITIONS as $typeName => $conditions)
		{
			if (array_key_exists('entity', $conditions) && $conditions['entity'] === $entityType)
			{
				return $typeName;
			}
			if (array_key_exists('instanceof', $conditions))
			{
				if (!is_array($conditions['instanceof']))
				{
					$conditions['instanceof'] = [$conditions['instanceof']];
				}
				foreach ($conditions['instanceof'] as $condition)
				{
					if ($chat instanceof $condition)
					{
						return $typeName;
					}
				}
			}
		}
		return '';
	}

	protected function getCategory(Chat $chat): string
	{
		if ($chat instanceof Chat\ChannelChat || $chat instanceof Chat\CommentChat)
		{
			return 'channel';
		}
		elseif ($chat instanceof Chat\CopilotChat)
		{
			return 'copilot';
		}

		return 'chat';
	}

	/**
	 * @throws ArgumentException
	 */
	private function createRawChatEvent(string $eventName, string $category, Chat $chat, bool $parentIdNeeded = true): AnalyticsEvent
	{
		$event = $this->createEvent($eventName, $category);

		if ($parentIdNeeded)
		{
			$p4 = $chat->getParentChatId();

			if (null !== $p4)
			{
				$event->setP4('parentChatId_' . $p4);
			}
		}

		$chatId = $chat->getChatId();

		if (null !== $chatId)
		{
			$event->setP5('chatId_' . $chatId);
		}

		return $event;
	}

	/**
	 * @throws ArgumentException
	 */
	protected function createChatEvent(
		string $eventName,
		Chat $chat,
		bool $parentIdNeeded = true,
	): AnalyticsEvent
	{
		$category = $this->getCategory($chat);
		$event = $this->createRawChatEvent($eventName, $category, $chat, $parentIdNeeded);
		$p1 = $this->getP1($chat);

		if (null !== $p1)
		{
			$event->setP1($p1);
		}

		return $event;
	}

	public function addSubmitCreateNew(Result $createResult): void
	{
		$this->async(function () use ($createResult)
		{
			/** @var Chat|null $chat */
			$chat = $createResult->getResult()['CHAT'] ?? null;

			if (!$chat instanceof Chat)
			{
				return;
			}

			$chatType = $this->getChatType($chat);

			try
			{
				if (in_array($chatType, ['generalChannel', 'openChannel', 'channel', 'comment'], true))
				{
					$aCategory = 'channel';
				}
				elseif ($chatType === 'copilot')
				{
					$aCategory = 'copilot';
				}
				elseif ($chatType === 'videoconf')
				{
					$aCategory = 'videoconf';
				}
				else
				{
					$aCategory = 'chat';
				}

				$this
					->createRawChatEvent(
						'submit_create_new',
						$aCategory,
						$chat,
					)
					->setType($chatType)
					->send()
				;
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	public function addAddUser(Chat $chat, Reason $reason = Reason::DEFAULT, bool $isJoin = false): void
	{
		if ($this->isSingleUserEventsBlocked($chat))
		{
			return;
		}

		$this->async(function () use ($chat, $reason, $isJoin)
		{
			if ($isJoin)
			{
				$eventName = 'join';
			}
			else
			{
				$eventName = match ($reason) {
					Reason::STRUCTURE => 'add_department',
					default => 'add_user',
				};
			}

			try
			{
				$this
					->createChatEvent($eventName, $chat, false)
					->send()
				;
				(new CopilotAnalytics())->addAddUser($chat);
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	public function addDeleteUser(Chat|int $chat): void
	{
		if ($this->isSingleUserEventsBlocked($chat))
		{
			return;
		}

		$this->async(function () use ($chat)
		{
			try
			{
				if (!$chat instanceof Chat)
				{
					$chat = Chat::getInstance($chat);
				}

				$this
					->createChatEvent('delete_user', $chat, false)
					->send()
				;
				(new CopilotAnalytics())->addDeleteUser($chat);
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	public function addFollowComments(Chat $chat, bool $flag): void
	{
		$this->async(function () use ($chat, $flag)
		{
			try
			{
				$this
					->createChatEvent($flag ? 'follow_comments' : 'unfollow_comments', $chat)
					->send()
				;
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	protected function getTool(): string
	{
		return 'im';
	}

	protected function addChatEditEvent(Chat|int $chat, string $eventName): void
	{
		$this->async(function () use ($chat, $eventName)
		{
			try
			{
				if (!$chat instanceof Chat)
				{
					$chat = Chat::getInstance($chat);
				}

				$this
					->createChatEvent($eventName, $chat, false)
					->send()
				;
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	public function addAddDepartment(Chat|int $chat): void
	{
		$this->addChatEditEvent($chat, 'add_department');
	}

	public function addDeleteDepartment(Chat|int $chat): void
	{
		$this->addChatEditEvent($chat, 'delete_department');
	}

	public function addEditPermissions(Chat|int $chat): void
	{
		if ($this->isFirstTimeEvent($chat, 'edit_permissions'))
		{
			$this->addChatEditEvent($chat, 'edit_permissions');
		}
	}

	public function addEditDescription(Chat|int $chat): void
	{
		$this->addChatEditEvent($chat, 'edit_description');
	}

	public function addSetType(Chat|int $chat): void
	{
		$this->addChatEditEvent($chat, 'set_type');
	}
}