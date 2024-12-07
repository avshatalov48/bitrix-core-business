<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Im\Color;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Loader;
use Bitrix\Main\NotSupportedException;

final class EventCategoryService implements EventCategoryServiceInterface
{
	/**
	 * Chat.ENTITY_TYPE property, which should set on channel connected with category
	 * CAUTION!
	 * It used in event subscribers
	 * @see OnChatUserAddEntityTypeCalendarEventCategory IM event
	 * @see OnChatUserDeleteEntityTypeCalendarEventCategory IM event
	 * It stored in every chat entity, connected to category.
	 */
	public const OPEN_EVENT_CATEGORY_IM_ENTITY_TYPE = 'CALENDAR_EVENT_CATEGORY';

	public function __construct()
	{
		if (!Loader::includeModule('im'))
		{
			throw new NotSupportedException('IM module is not installed');
		}
	}

	/**
	 * Get all channels which created by user or user has access to moderate.
	 */
	public function getAvailableChannelsList(int $userId): array
	{
		$channelIdsResult = ChatTable::query()
			->setSelect(['ID'])
			->where('AUTHOR_ID', $userId)
			->whereIn('TYPE', [CHAT::IM_TYPE_CHANNEL, CHAT::IM_TYPE_OPEN_CHANNEL])
			->whereNull('ENTITY_TYPE')
//			->where(
//				Query::filter()
//					->logic(ConditionTree::LOGIC_OR)
//					->whereNull('ENTITY_TYPE')
//					->where('ENTITY_TYPE', self::OPEN_EVENT_CATEGORY_IM_ENTITY_TYPE)
//			)
			->fetchAll()
		;
		$channelIds = array_map('intval', array_column($channelIdsResult, 'ID'));

		$channels = [];
		foreach ($channelIds as $channelId)
		{
			$channel = new Chat\ChannelChat($channelId);
			$hasAccess = $this->hasAccess($userId, $channelId);
			if (!$hasAccess)
			{
				continue;
			}
			$channels[$channelId] = [
				'id' => $channelId,
				'title' => $channel->getDisplayedTitle(),
				'closed' => $channel->getType() === CHAT::IM_TYPE_CHANNEL,
				'avatar' => $channel->getAvatar(),
				'color' => $channel->getColor(),
			];
		}

		return $channels;
	}

	public function createChannel(EventCategory $eventCategory, array $userIds, array $departmentIds = []): int
	{
		if ($eventCategory->getClosed())
		{
			$channelType = Chat::IM_TYPE_CHANNEL;
			$users = $userIds;
		}
		else
		{
			$channelType = Chat::IM_TYPE_OPEN_CHANNEL;
			$users = [];
		}

		$departments = array_map(
			static fn (int|string $departmentId) => ['department', $departmentId],
			$departmentIds,
		);

		$params = [
			'TYPE' => $channelType,
			'AUTHOR_ID' => $eventCategory->getCreatorId(), // get from context otherwise
			'OWNER_ID' => $eventCategory->getCreatorId(), // get from context otherwise
			'USERS' => $users,
			'MEMBER_ENTITIES' => $departments,
			'TITLE' => (new Provider\CategoryProvider())->prepareCategoryName($eventCategory->getName()),
			'ENTITY_TYPE' => self::OPEN_EVENT_CATEGORY_IM_ENTITY_TYPE,
			'SEND_GREETING_MESSAGES' => 'Y',
		];

		$addChannelResult = ChatFactory::getInstance()->addChat($params);

		$chat = $addChannelResult->getResult()['CHAT'];

		if ($eventCategory->getDescription())
		{
			$chat->setDescription($eventCategory->getDescription());
			$chat->save();
		}

		if ($addChannelResult->isSuccess())
		{
			return $chat->getChatId();
		}

		throw new \RuntimeException('channel not created');
	}

	public function updateChannel(EventCategory $eventCategory): void
	{
		$channel = ChatFactory::getInstance()->getChatById($eventCategory->getChannelId());
		if (!in_array($channel->getType(), [Chat::IM_TYPE_CHANNEL, Chat::IM_TYPE_OPEN_CHANNEL], true))
		{
			return;
		}

		$categoryProvider = new Provider\CategoryProvider();

		$channel->setTitle($categoryProvider->prepareCategoryName($eventCategory->getName()));
		$channel->setDescription($categoryProvider->prepareCategoryDescription($eventCategory->getDescription()));

		$channel->save();
	}

	/**
	 * Change channel mute state for user.
	 */
	public function setMuteChannel(int $userId, int $channelId, bool $newMuteState): void
	{
		$channel = ChatFactory::getInstance()->getChatById($channelId);
		$isMuted = $this->isChannelMuted($userId, $channelId);
		if ($isMuted === $newMuteState)
		{
			return;
		}

		$channelType = $channel->getType();
		if (!in_array($channelType, [Chat::IM_TYPE_OPEN_CHANNEL, Chat::IM_TYPE_CHANNEL], true))
		{
			throw new \RuntimeException('unknown channel type');
		}

		$CIMChat = new \CIMChat($userId);
		$CIMChat->muteNotify($channelId, $newMuteState);
	}

	public function getChannelInfo(int $channelId): array
	{
		$channel = ChatFactory::getInstance()->getChatById($channelId);

		return [
			'id' => $channel->getId(),
			'title' => $channel->getTitle(),
			'avatar' => $channel->getAvatar(),
			'color' => Color::getColor($channel->getColor()),
		];
	}

	public function isChannelMuted(int $userId, int $channelId): ?bool
	{
		return ChatFactory::getInstance()
			->getChatById($channelId)
			?->getRelations(['FILTER' => ['USER_ID' => $userId]])
			->getByUserId($userId, $channelId)
			?->getNotifyBlock()
		;
	}

	public function getThreadCommentsCount(int $threadId): ?int
	{
		$threadMessage = new Message($threadId);

		if ($threadMessage->getMessageId() === null)
		{
			return null;
		}

		if (!$threadMessage->hasAccess())
		{
			return null;
		}

		$commentChatResult = CommentChat::get($threadMessage);
		if ($commentChatResult->getErrors())
		{
			return null;
		}

		/** @var CommentChat $commentChat */
		$commentChat = $commentChatResult->getResult();

		return $commentChat->getMessageCount() > 0 ? $commentChat->getMessageCount() - 1 : 0;
	}

	public function includeUserToChannel(int $userId, int $channelId): bool
	{
		$channel = ChatFactory::getInstance()->getChatById($channelId);
//		$channel->addUsers([$userId]);
//		$channel->save();
		$CIMChat = new \CIMChat(0);
		$addResult = $CIMChat->AddUser($channelId, [$userId], false, false);

		return $addResult;
	}

	public function hasAccess(int $userId, int $channelId): bool
	{
		$channel = ChatFactory::getInstance()->getChatById($channelId);

		return $channel->hasAccess($userId);
	}

	public function getChannelUsers(int $channelId): array
	{
		return ChatFactory::getInstance()
			->getChatById($channelId)
			?->getRelations()
			->getUserIds()
		;
	}

	public function isChannelPrivate(int $channelId): bool
	{
		return ChatFactory::getInstance()
			->getChatById($channelId)
			->getType() === Chat::IM_TYPE_CHANNEL
		;
	}

	public function isManagerOfChannel(int $userId, int $channelId): bool
	{
		$channel = ChatFactory::getInstance()->getChatById($channelId);

		return $channel->getAuthorId() === $userId;
	}

	public function connectChannelToCategory(int $channelId): void
	{
		$chat = ChatFactory::getInstance()->getChatById($channelId);

		if ($chat->getEntityType())
		{
			throw new \Exception('can not connect channel with not empty ENTITY_TYPE to category');
		}

		$chat->setEntityType(self::OPEN_EVENT_CATEGORY_IM_ENTITY_TYPE);
		$chat->save();
	}
}
