<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Chat\Copilot\CopilotPopupItem;
use Bitrix\Im\V2\Chat\MessagesAutoDelete\MessagesAutoDeleteConfigs;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Sync\Recent\RecentSync;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;

class ChatsSync extends Chat\ChatPopupItem implements PopupDataAggregatable
{
	use ContextCustomer;

	protected RecentSync $recent;

	public function __construct(array $chats, RecentSync $recent)
	{
		$this->recent = $recent;

		parent::__construct($chats);
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];

		Chat::fillSelfRelations($this->chats);
		$this->fillAllForRest();
		$this->fillDialogIds();

		foreach ($this->chats as $chat)
		{
			$restData = $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true]);
			$rest[] = array_merge($restData, $this->getSelfAdditionalParams($chat));
		}

		return $rest;
	}

	protected function getSelfAdditionalParams(Chat $chat): array
	{
		return [
			'counter' => $chat->getUserCounter(),
			'dateCreate' => $chat->getDateCreate()?->format('c'),
			'lastMessageId' => $chat->getLastMessageId(),
			'lastId' => $chat->getLastId(),
			'managerList' => $chat->getManagerList(false),
			'markedId' => $chat->getMarkedId(),
			'messageCount' => $chat->getMessageCount(),
			'public' => $chat->getPublicOption() ?? '',
			'unreadId' => $chat->getUnreadId(),
			'userCounter' => $chat->getUserCount(),
		];
	}

	protected function fillAllForRest(): void
	{
		$chatIds = $this->getChatIds();

		$nonCachedData = $this->fillNonCachedData($chatIds);
		$counters = $this->fillCounters($chatIds);
		$markedIds = $this->fillMarkedIds($chatIds);

		$this->fillData($nonCachedData, $counters, $markedIds);
	}

	protected function fillData(array $nonCachedData, array $counters, array $markedIds): void
	{
		foreach ($nonCachedData as $chatData)
		{
			$chatId = (int)$chatData['ID'];
			$chat = $this->chats[$chatId] ?? null;
			if ($chat === null)
			{
				continue;
			}

			$chat
				->setMessageCount((int)$chatData['MESSAGE_COUNT'])
				->setUserCount((int)$chatData['USER_COUNT'])
				->setLastMessageId((int)$chatData['LAST_MESSAGE_ID'])
				->setMarkedId($markedIds[$chatId] ?? 0)
				->setUserCounter($counters[$chatId] ?? 0)
				->markFilledNonCachedData(true)
			;
		}
	}

	protected function fillNonCachedData(array $chatIds): array
	{
		if (empty($chatIds))
		{
			return [];
		}

		return ChatTable::query()
			->setSelect(array_merge(ChatFactory::NON_CACHED_FIELDS, ['ID']))
			->whereIn('ID', $chatIds)
			->fetchAll()
			;
	}

	protected function fillCounters(array $chatIds): array
	{
		$readService = new ReadService();

		return $readService->getCounterService()->getForEachChat($chatIds);
	}

	protected function fillMarkedIds(array $chatIds): array
	{
		if (!empty($this->recent))
		{
			return $this->fillMarkedIdsByRecent();
		}

		return Recent::getMarkedIdByChatIds($this->getContext()->getUserId(), $chatIds);
	}

	protected function fillMarkedIdsByRecent(): array
	{
		$markedIds = [];
		foreach ($this->recent as $recentItem)
		{
			$markedIds[$recentItem->getChatId()] = $recentItem->getMarkedId();
		}

		return $markedIds;
	}

	protected function getUsers(): array
	{
		$userIds =
			!empty($this->chats)
				? [$this->getContext()->getUserId()]
				: []
		;

		foreach ($this->chats as $chat)
		{
			if ($chat instanceof Chat\PrivateChat)
			{
				$userIds[] = (int)$chat->getDialogId();
			}
		}

		return $userIds;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$this->fillDialogIds();

		return new PopupData([
			CopilotPopupItem::getInstanceByChatIds($this->getChatIds()),
			(new MessagesAutoDeleteConfigs($this->getChatIds()))->showDefaultValues(),
			new UserPopupItem($this->getUsers()),
			$this->recent,
		], $excludedList);
	}
}
