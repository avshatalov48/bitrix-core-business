<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\NotifyChat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Counter\CounterOverflowService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CTimeZone;

class CounterService
{
	use ContextCustomer;

	protected const DELAY_DELETION_COUNTERS_OF_FIRED_USER = 604800; // 1 week
	protected const EXPIRY_INTERVAL = '-12 months';

	protected const CACHE_TTL = 86400; // 1 month
	protected const CACHE_NAME = 'counter_v5';
	protected const CACHE_CHATS_COUNTERS_NAME = 'chats_counter_v6';
	protected const CACHE_PATH = '/bx/im/v3/counter/';

	protected const DEFAULT_COUNTERS = [
		'TYPE' => [
			'ALL' => 0,
			'NOTIFY' => 0,
			'CHAT' => 0,
			'LINES' => 0,
			'COPILOT' => 0,
			'COLLAB' => 0,
		],
		'CHAT' => [],
		'COLLAB' => [],
		'CHAT_MUTED' => [],
		'CHAT_UNREAD' => [],
		'COLLAB_UNREAD' => [],
		'LINES' => [],
		'COPILOT' => [],
		'CHANNEL_COMMENT' => [],
	];

	protected static array $staticCounterCache = [];
	protected static array $staticChatsCounterCache = [];
	protected static array $staticSpecificChatsCounterCache = [];

	protected array $counters;
	protected array $countersByChatIds = [];

	public function __construct(?int $userId = null)
	{
		$this->counters = static::DEFAULT_COUNTERS;

		if (isset($userId))
		{
			$context = new Context();
			$context->setUser($userId);
			$this->setContext($context);
		}
	}

	public function getTotal(): int
	{
		$totalUnreadMessages = $this->getTotalCountUnreadMessages();
		$unreadUnmutedChats = $this->getUnreadChats(false);

		return $totalUnreadMessages + count($unreadUnmutedChats);
	}

	public function getByChatForEachUsers(int $chatId, array $userIds): array
	{
		$result = [];
		$overflowService = new CounterOverflowService($chatId);
		$overflowInfo = $overflowService->getOverflowInfo($userIds);

		$countForEachUsers = $this->getCountUnreadMessagesByChatIdForEachUsers($chatId, $overflowInfo->getUsersWithoutOverflow());

		foreach ($countForEachUsers as $countForUser)
		{
			$result[(int)$countForUser['USER_ID']] = (int)$countForUser['COUNT'];
		}

		$overflowService->insertOverflowed($result);

		foreach ($userIds as $userId)
		{
			if ($overflowInfo->hasOverflow($userId))
			{
				$result[$userId] = CounterOverflowService::getOverflowValue();
			}
			if (!isset($result[$userId]))
			{
				$result[$userId] = 0;
			}
		}

		return $result;
	}

	public function getByChat(int $chatId): int
	{
		return $this->getCountUnreadMessagesByChatId($chatId);
	}

	public function get(): array
	{
		$userId = $this->getContext()->getUserId();
		if (isset(self::$staticCounterCache[$userId]))
		{
			return self::$staticCounterCache[$userId];
		}

		$cache = $this->getCacheForPreparedCounters();
		$cachedCounters = $cache->getVars();
		if ($cachedCounters !== false)
		{
			self::$staticCounterCache[$userId] = $cachedCounters;

			return $cachedCounters;
		}

		$this->counters = static::DEFAULT_COUNTERS;
		$this->countersByChatIds = [];

		$this->countUnreadMessages();
		$this->countUnreadChats();

		$this->savePreparedCountersInCache($cache);
		$this->saveChatsCountersInCache($this->getCacheForChatsCounters());

		return $this->counters;
	}

	public function getForEachChat(?array $chatIds = null): array
	{
		$userId = $this->getContext()->getUserId();
		if (isset(self::$staticChatsCounterCache[$userId]))
		{
			return self::$staticChatsCounterCache[$userId];
		}

		if ($chatIds !== null && $this->haveInSpecificChatsCache($chatIds))
		{
			return static::$staticSpecificChatsCounterCache[$userId] ?? [];
		}

		$cache = $this->getCacheForChatsCounters();
		$cachedCounters = $cache->getVars();
		if ($cachedCounters !== false)
		{
			self::$staticChatsCounterCache[$userId] = $cachedCounters;

			return $cachedCounters;
		}

		$this->counters = static::DEFAULT_COUNTERS;
		$this->countersByChatIds = [];

		$this->countUnreadMessages($chatIds);

		if ($chatIds === null)
		{
			$this->saveChatsCountersInCache($cache);
		}
		else
		{
			$this->saveSpecificChatsCountersInCache($chatIds);
		}

		return $this->countersByChatIds;
	}

	public function getForNotifyChat(): int
	{
		$findResult = NotifyChat::find(['TO_USER_ID' => $this->getContext()->getUserId()]);

		if (!$findResult->isSuccess())
		{
			return 0;
		}

		$chatId = (int)$findResult->getResult()['ID'];

		return $this->getByChat($chatId);
	}

	public function getForNotifyChats(array $chatIds): array
	{
		if (empty($chatIds))
		{
			return [];
		}

		$counters = $this->getCountersForEachChat($chatIds, false);
		$countersByChatId = [];

		foreach ($counters as $counter)
		{
			$countersByChatId[$counter['CHAT_ID']] = $counter['COUNT'];
		}

		$result = [];

		foreach ($chatIds as $chatId)
		{
			$result[$chatId] = $countersByChatId[$chatId] ?? 0;
		}

		return $result;
	}

	public function getIdFirstUnreadMessage(int $chatId): ?int
	{
		$result = MessageUnreadTable::query()
			->setSelect(['MIN'])
			->where('CHAT_ID', $chatId)
			->where('USER_ID', $this->getContext()->getUserId())
			->registerRuntimeField('MIN', new ExpressionField('MIN', 'MIN(%s)', ['MESSAGE_ID']))
			->fetch()
		;

		return isset($result['MIN']) ? (int)$result['MIN'] : null;
	}

	public function getIdFirstUnreadMessageForEachChats(array $chatIds): array
	{
		if (empty($chatIds))
		{
			return [];
		}

		$result = MessageUnreadTable::query()
			->setSelect(['CHAT_ID', 'UNREAD_ID' => new ExpressionField('UNREAD_ID', 'MIN(%s)', ['MESSAGE_ID'])])
			->whereIn('CHAT_ID', $chatIds)
			->where('USER_ID', $this->getContext()->getUserId())
			->setGroup(['CHAT_ID'])
			->fetchAll() //todo index (CHAT_ID, USER_ID, MESSAGE_ID)
		;

		$firstUnread = [];

		foreach ($result as $row)
		{
			$firstUnread[(int)$row['CHAT_ID']] = (int)$row['UNREAD_ID'];
		}

		return $firstUnread;
	}

	public function updateIsMuted(int $chatId, string $isMuted): void
	{
		MessageUnreadTable::updateBatch(
			['IS_MUTED' => $isMuted],
			['=CHAT_ID' => $chatId, '=USER_ID' => $this->getContext()->getUserId()]
		);
		static::clearCache($this->getContext()->getUserId());
	}

	public function deleteByChatId(int $chatId): void
	{
		if ($this->getContext()->getUserId() <= 0)
		{
			return;
		}

		MessageUnreadTable::deleteByFilter(['=CHAT_ID' => $chatId, '=USER_ID' => $this->getContext()->getUserId()]);
		static::clearCache($this->getContext()->getUserId());
		(new CounterOverflowService($chatId))->delete($this->getContext()->getUserId());
	}

	public static function deleteByChatIdForAll(int $chatId): void
	{
		MessageUnreadTable::deleteByFilter(['=CHAT_ID' => $chatId]);
		CounterOverflowService::deleteByChatIdForAll($chatId);
	}

	public function deleteByChatIds(array $chatIds): void
	{
		if (empty($chatIds) || $this->getContext()->getUserId() <= 0)
		{
			return;
		}

		MessageUnreadTable::deleteByFilter(['=CHAT_ID' => $chatIds, '=USER_ID' => $this->getContext()->getUserId()]);
		static::clearCache($this->getContext()->getUserId());
		CounterOverflowService::deleteByChatIds($chatIds, $this->getContext()->getUserId());
	}

	/*public function deleteByChatIdForAll(int $chatId): void
	{
		MessageUnreadTable::deleteByFilter(['=CHAT_ID' => $chatId]);
		static::clearCache();
	}*/

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function deleteAll(bool $withNotify = false): void
	{
		Message\CounterService\CounterServiceAgent::deleteAllViaAgent(
			$this->getContext()->getUserId(),
			$withNotify,
		);
	}

	public static function getChildrenWithCounters(Chat $parentChat, ?int $userId = null): array
	{
		$parentId = $parentChat->getId();
		if (!$parentId)
		{
			return [];
		}

		$query = MessageUnreadTable::query()
			->setSelect(['CHAT_ID'])
			->where('PARENT_ID', $parentId)
			->addGroup('CHAT_ID')
		;

		if (isset($userId))
		{
			$query->where('USER_ID', $userId);
		}

		$parentIds = [];
		foreach ($query->fetchAll() as $row)
		{
			$parentIds[] = isset($row['CHAT_ID']) ? (int)$row['CHAT_ID'] : 0;
		}

		return $parentIds;
	}

	public function addForEachUser(Message $message, RelationCollection $relations): void
	{
		$insertFields = [];
		$usersIds = [];

		foreach ($relations as $relation)
		{
			if ($relation->getMessageType() !== \IM_MESSAGE_SYSTEM && $message->getAuthorId() === $relation->getUserId())
			{
				continue;
			}

			$insertFields[] = $this->prepareInsertFields($message, $relation);
			$usersIds[] = $relation->getUserId();
		}

		MessageUnreadTable::multiplyInsertWithoutDuplicate($insertFields);
		foreach ($usersIds as $userId)
		{
			static::clearCache($userId);
		}
	}

	public function addCollection(MessageCollection $messages, Relation $relation): void
	{
		$insertFields = [];

		foreach ($messages as $message)
		{
			if ($relation->getMessageType() !== \IM_MESSAGE_SYSTEM && $message->getAuthorId() === $relation->getUserId())
			{
				continue;
			}

			$insertFields[] = $this->prepareInsertFields($message, $relation);
		}

		MessageUnreadTable::multiplyInsertWithoutDuplicate($insertFields);
		static::clearCache($relation->getUserId());
	}

	public function addStartingFrom(int $messageId, Relation $relation): void
	{
		$query = MessageTable::query()
			->setSelect([
				'ID_CONST' => new ExpressionField('ID_CONST', '0'),
				'USER_ID_CONST' => new ExpressionField('USER_ID_CONST', (string)$this->getContext()->getUserId()),
				'CHAT_ID_CONST' => new ExpressionField('CHAT_ID', (string)$relation->getChatId()),
				'MESSAGE_ID' => 'ID',
				'IS_MUTED' => new ExpressionField('IS_MUTED', $relation->getNotifyBlock() ? "'Y'" : "'N'"),
				'CHAT_TYPE' => new ExpressionField('CHAT_TYPE', "'{$relation->getMessageType()}'"),
				'DATE_CREATE'
			])
			->where('CHAT_ID', $relation->getChatId())
			->where('MESSAGE_ID', '>=', $messageId)
		;
		if ($relation->getMessageType() !== \IM_MESSAGE_SYSTEM)
		{
			$query->whereNot('AUTHOR_ID', $this->getContext()->getUserId());
		}

		MessageUnreadTable::insertSelect($query, ['ID', 'USER_ID', 'CHAT_ID', 'MESSAGE_ID', 'IS_MUTED', 'CHAT_TYPE', 'DATE_CREATE']);

		static::clearCache($this->getContext()->getUserId());
	}

	public function deleteByMessageForAll(Message $message, ?array $invalidateCacheUsers = null): void
	{
		if (empty($message->getId()))
		{
			return;
		}

		MessageUnreadTable::deleteByFilter(['=MESSAGE_ID' => $message->getId()]);
		CounterOverflowService::deleteByChatIdForAll($message->getChatId());

		if (!isset($invalidateCacheUsers))
		{
			static::clearCache();

			return;
		}

		foreach ($invalidateCacheUsers as $user)
		{
			static::clearCache((int)$user);
		}
	}

	public function deleteByMessagesForAll(MessageCollection $messages, ?array $invalidateCacheUsers = null): void
	{
		$messageIds = $messages->getIds();
		$chatIds = [];
		if (empty($messageIds))
		{
			return;
		}

		foreach ($messages as $message)
		{
			$chatId = $message->getChatId();
			$chatIds[$chatId] = $chatId;
		}

		MessageUnreadTable::deleteByFilter(['=MESSAGE_ID' => $messageIds]);
		CounterOverflowService::deleteByChatIds($chatIds);

		if (!isset($invalidateCacheUsers))
		{
			static::clearCache();

			return;
		}

		foreach ($invalidateCacheUsers as $user)
		{
			static::clearCache((int)$user);
		}
	}

	public function deleteTo(Message $message): void
	{
		$userId = $this->getContext()->getUserId();
		if ($userId <= 0)
		{
			return;
		}

		MessageUnreadTable::deleteByFilter([
			'<=MESSAGE_ID' => $message->getMessageId(),
			'=CHAT_ID' => $message->getChatId(),
			'=USER_ID' => $userId
		]);
		(new CounterOverflowService($message->getChatId()))->delete($userId);
		static::clearCache($userId);
	}

	public static function onAfterUserUpdate(array $fields): void
	{
		if (!isset($fields['ACTIVE']))
		{
			return;
		}

		if ($fields['ACTIVE'] === 'N')
		{
			self::onFireUser((int)$fields['ID']);
		}
		else
		{
			self::onHireUser((int)$fields['ID']);
		}
	}

	public static function deleteCountersOfFiredUserAgent(int $userId): string
	{
		$user = User::getInstance($userId);
		if ($user->isExist() && $user->isActive())
		{
			return '';
		}

		$counterService = new self($userId);
		$counterService->deleteAll(true);

		return '';
	}

	public static function deleteExpiredCountersAgent(): string
	{
		$dateExpired = (new DateTime())->add(self::EXPIRY_INTERVAL);
		MessageUnreadTable::deleteByFilter(['<=DATE_CREATE' => $dateExpired]);
		static::clearCache();

		return '\Bitrix\Im\V2\Message\CounterService::deleteExpiredCountersAgent();';
	}

	protected static function onFireUser(int $userId): void
	{
		\CAgent::AddAgent(
			"\Bitrix\Im\V2\Message\CounterService::deleteCountersOfFiredUserAgent({$userId});",
			'im',
			'N',
			self::DELAY_DELETION_COUNTERS_OF_FIRED_USER,
			'',
			'Y',
			ConvertTimeStamp(time()+CTimeZone::GetOffset()+self::DELAY_DELETION_COUNTERS_OF_FIRED_USER, "FULL"),
			existError: false
		);
	}

	protected static function onHireUser(int $userId): void
	{
		\CAgent::RemoveAgent(
			"\Bitrix\Im\V2\Message\CounterService::deleteCountersOfFiredUserAgent({$userId});",
			'im'
		);
	}

	public static function clearCache(?int $userId = null): void
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if (isset($userId))
		{
			unset(self::$staticCounterCache[$userId], self::$staticChatsCounterCache[$userId], self::$staticSpecificChatsCounterCache[$userId]);
			$cache->clean(static::CACHE_NAME.'_'.$userId, self::CACHE_PATH);
			$cache->clean(static::CACHE_NAME.'_'.$userId, CounterServiceLegacy::CACHE_PATH);
			$cache->clean(self::CACHE_CHATS_COUNTERS_NAME.'_'.$userId, self::CACHE_PATH);
		}
		else
		{
			self::$staticCounterCache = [];
			self::$staticChatsCounterCache = [];
			self::$staticSpecificChatsCounterCache = [];
			$cache->cleanDir(self::CACHE_PATH);
			$cache->cleanDir(CounterServiceLegacy::CACHE_PATH);
		}
	}

	protected function getCacheForPreparedCounters(): Cache
	{
		$userId = $this->getContext()->getUserId();
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->initCache(static::CACHE_TTL, static::CACHE_NAME . '_' . $userId, static::CACHE_PATH);

		return $cache;
	}

	protected function getCacheForChatsCounters(): Cache
	{
		$userId = $this->getContext()->getUserId();
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->initCache(self::CACHE_TTL, self::CACHE_CHATS_COUNTERS_NAME . '_' . $userId, self::CACHE_PATH);

		return $cache;
	}

	protected function savePreparedCountersInCache(Cache $cache): void
	{
		$cache->startDataCache();
		$cache->endDataCache($this->counters);
		self::$staticCounterCache[$this->getContext()->getUserId()] = $this->counters;
	}

	protected function saveChatsCountersInCache(Cache $cache): void
	{
		$cache->startDataCache();
		$cache->endDataCache($this->countersByChatIds);
		self::$staticChatsCounterCache[$this->getContext()->getUserId()] = $this->countersByChatIds;
	}

	protected function saveSpecificChatsCountersInCache(array $chatIds): void
	{
		$userId = $this->getContext()->getUserId();

		foreach ($chatIds as $chatId)
		{
			self::$staticSpecificChatsCounterCache[$userId][$chatId] = $this->countersByChatIds[$chatId] ?? 0;
		}
	}

	protected function countUnreadChats(): void
	{
		$unreadChats = $this->getUnreadChats(false);

		foreach ($unreadChats as $unreadChat)
		{
			if ($unreadChat['CHAT_TYPE'] === Chat::IM_TYPE_COLLAB)
			{
				$this->setUnreadCollab((int)$unreadChat['CHAT_ID'], $unreadChat['IS_MUTED'] === 'Y');
			}
			else
			{
				$this->setUnreadChat((int)$unreadChat['CHAT_ID'], $unreadChat['IS_MUTED'] === 'Y');
			}
		}
	}

	protected function countUnreadMessages(?array $chatIds = null): void
	{
		$counters = $this->getCountersForEachChat($chatIds);
		$chatIdToParentIdMap = $this->getMapChatIdToParentId($counters);

		foreach ($counters as $counter)
		{
			$chatId = (int)$counter['CHAT_ID'];
			$count = (int)$counter['COUNT'];
			if ($counter['IS_MUTED'] === 'Y')
			{
				$this->setFromMutedChat($chatId, $count);
			}
			elseif ($counter['CHAT_TYPE'] === Chat::IM_TYPE_COLLAB)
			{
				$this->setFromCollab($chatId, $count);
			}
			else if ($counter['CHAT_TYPE'] === \IM_MESSAGE_SYSTEM)
			{
				$this->setFromNotify($count);
			}
			else if ($counter['CHAT_TYPE'] === \IM_MESSAGE_OPEN_LINE)
			{
				$this->setFromLine($chatId, $count);
			}
			else if ($counter['CHAT_TYPE'] === Chat::IM_TYPE_COPILOT)
			{
				$this->setFromCopilot($chatId, $count);
			}
			else if ($counter['CHAT_TYPE'] === Chat::IM_TYPE_COMMENT)
			{
				$this->setFromComment($chatId, $chatIdToParentIdMap[$chatId] ?? null, $count);
			}
			else
			{
				$this->setFromChat($chatId, $count);
			}
			$this->countersByChatIds[$chatId] = $count;
		}
	}

	protected function setUnreadChat(int $id, bool $isMuted): void
	{
		if (!$isMuted && !isset($this->counters['CHAT'][$id]))
		{
			$this->counters['TYPE']['ALL']++;
			$this->counters['TYPE']['CHAT']++;
		}

		$this->counters['CHAT_UNREAD'][] = $id;
	}

	protected function setUnreadCollab(int $id, bool $isMuted): void
	{
		if (!$isMuted && !isset($this->counters['COLLAB'][$id]))
		{
			$this->counters['TYPE']['ALL']++;
			$this->counters['TYPE']['CHAT']++;
			$this->counters['TYPE']['COLLAB']++;
		}

		$this->counters['COLLAB_UNREAD'][] = $id;
	}

	protected function setFromMutedChat(int $id, int $count): void
	{
		$this->counters['CHAT_MUTED'][$id] = $count;
	}

	protected function setFromNotify(int $count): void
	{
		$this->counters['TYPE']['ALL'] += $count;
		$this->counters['TYPE']['NOTIFY'] += $count;
	}

	protected function setFromLine(int $id, int $count): void
	{
		$this->counters['TYPE']['ALL'] += $count;
		$this->counters['TYPE']['LINES'] += $count;
		$this->counters['LINES'][$id] = $count;
	}

	protected function setFromCopilot(int $id, int $count): void
	{
		$this->counters['TYPE']['COPILOT'] += $count;
		$this->counters['COPILOT'][$id] = $count;
	}

	protected function setFromCollab(int $id, int $count): void
	{
		$this->counters['TYPE']['COLLAB'] += $count;
		$this->counters['COLLAB'][$id] = $count;
	}

	protected function setFromComment(int $id, ?int $parentId, int $count): void
	{
		if ($parentId === null || $parentId === 0)
		{
			return;
		}

		$this->counters['TYPE']['ALL'] += $count;
		$this->counters['CHANNEL_COMMENT'][$parentId][$id] = $count;
	}

	protected function setFromChat(int $id, int $count): void
	{
		$this->counters['TYPE']['ALL'] += $count;
		$this->counters['TYPE']['CHAT'] += $count;
		$this->counters['CHAT'][$id] = $count;
	}

	protected function getMapChatIdToParentId(array $counters): array
	{
		$commentChatIds = $this->getCommentChatIdsOnly($counters);

		if (empty($commentChatIds))
		{
			return [];
		}

		$raw = ChatTable::query()
			->setSelect(['ID', 'PARENT_ID'])
			->whereIn('ID', $commentChatIds)
			->fetchAll()
		;
		$result = [];

		foreach ($raw as $row)
		{
			$chatId = (int)$row['ID'];
			$parentId = (int)($row['PARENT_ID'] ?? 0);
			$result[$chatId] = $parentId;
		}

		return $result;
	}

	protected function getCommentChatIdsOnly(array $counters): array
	{
		$result = [];

		foreach ($counters as $counter)
		{
			if ($counter['CHAT_TYPE'] === Chat::IM_TYPE_COMMENT)
			{
				$result[] = (int)$counter['CHAT_ID'];
			}
		}

		return $result;
	}

	protected function getUnreadChats(?bool $isMuted = null): array
	{
		$query = RecentTable::query()
			->setSelect([
				'CHAT_ID' => 'ITEM_CID',
				'CHAT_TYPE' => 'ITEM_TYPE',
				'IS_MUTED' => 'RELATION.NOTIFY_BLOCK',
			])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('UNREAD', true)
		;
		if (isset($isMuted))
		{
			$query->where('IS_MUTED', $isMuted);
		}

		return $query->fetchAll();
	}

	protected function getCountersForEachChat(?array $chatIds = null, bool $forCurrentUser = true): array
	{
		$additionalCounters = $this->getAdditionalCounters($chatIds, $forCurrentUser);
		$query = MessageUnreadTable::query()
			->setSelect(['CHAT_ID', 'IS_MUTED', 'CHAT_TYPE', 'COUNT'])
			->setGroup(['CHAT_ID', 'CHAT_TYPE', 'IS_MUTED'])
			->registerRuntimeField('COUNT', new ExpressionField('COUNT', 'COUNT(*)'))
		;
		if (isset($chatIds) && !empty($chatIds))
		{
			$query->whereIn('CHAT_ID', $chatIds);
		}
		if ($forCurrentUser)
		{
			$query->where('USER_ID', $this->getContext()->getUserId());
		}

		return array_merge($additionalCounters, $query->fetchAll());
	}

	protected function getAdditionalCounters(?array $chatIds = null, bool $forCurrentUser = true): array
	{
		$result = [];
		$nonAnsweredLines = [];

		if ($forCurrentUser && empty($chatIds) && Loader::includeModule('imopenlines'))
		{
			$nonAnsweredLines = \Bitrix\ImOpenLines\Recent::getNonAnsweredLines($this->getContext()->getUserId());
		}

		foreach ($nonAnsweredLines as $lineId)
		{
			$result[] = [
				'CHAT_ID' => $lineId,
				'IS_MUTED' => 'N',
				'CHAT_TYPE' => Chat::IM_TYPE_OPEN_LINE,
				'COUNT' => 1,
			];
		}

		return $result;
	}

	protected function getTotalCountUnreadMessages(): int
	{
		return (int)MessageUnreadTable::query()
			->setSelect(['COUNT'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('IS_MUTED', false)
			->whereNot('CHAT_TYPE', Chat::IM_TYPE_COPILOT)
			->registerRuntimeField('COUNT', new ExpressionField('COUNT', 'COUNT(*)'))
			->fetch()['COUNT']
		;
	}

	protected function getCountUnreadMessagesByChatIdForEachUsers(int $chatId, ?array $userIds = null): array
	{
		$query = MessageUnreadTable::query()
			->setSelect(['USER_ID', 'COUNT'])
			->where('CHAT_ID', $chatId)
			->setGroup(['USER_ID'])
			->registerRuntimeField('COUNT', new ExpressionField('COUNT', 'COUNT(*)'))
		;
		if (isset($userIds) && !empty($userIds))
		{
			$query->whereIn('USER_ID', $userIds);
		}

		return $query->fetchAll();
	}

	protected function getCountUnreadMessagesByChatId(int $chatId): int
	{
		return MessageUnreadTable::query()
			->setSelect(['COUNT'])
			->where('CHAT_ID', $chatId)
			->where('USER_ID', $this->getContext()->getUserId())
			->registerRuntimeField('COUNT', new ExpressionField('COUNT', 'COUNT(*)'))
			->fetch()['COUNT'] ?? 0
		;
	}

	protected function haveInSpecificChatsCache(array $chatIds): bool
	{
		$userId = $this->getContext()->getUserId();

		foreach ($chatIds as $chatId)
		{
			if ($chatId > 0 && !isset(self::$staticSpecificChatsCounterCache[$userId][$chatId]))
			{
				return false;
			}
		}

		return true;
	}

	private function prepareInsertFields(Message $message, Relation $relation): array
	{
		return [
			'MESSAGE_ID' => $message->getMessageId(),
			'CHAT_ID' => $relation->getChatId(),
			'USER_ID' => $relation->getUserId(),
			'CHAT_TYPE' => $relation->getMessageType(),
			'IS_MUTED' => $relation->getNotifyBlock() ? 'Y' : 'N',
			'PARENT_ID' => $message->getChat()->getParentChatId() ?? 0,
		];
	}
}
