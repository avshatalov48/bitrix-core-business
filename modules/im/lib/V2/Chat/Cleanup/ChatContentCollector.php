<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\Cleanup;

use Bitrix\Disk\File;
use Bitrix\Disk\Folder;
use Bitrix\Disk\SystemUser;
use Bitrix\Im\Model\BlockUserTable;
use Bitrix\Im\Model\BotChatTable;
use Bitrix\Im\Model\ChatIndexTable;
use Bitrix\Im\Model\ChatParamTable;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\LastMessageTable;
use Bitrix\Im\Model\LinkCalendarIndexTable;
use Bitrix\Im\Model\LinkCalendarTable;
use Bitrix\Im\Model\LinkFavoriteTable;
use Bitrix\Im\Model\LinkFileTable;
use Bitrix\Im\Model\LinkPinTable;
use Bitrix\Im\Model\LinkReminderTable;
use Bitrix\Im\Model\LinkTaskTable;
use Bitrix\Im\Model\LinkUrlIndexTable;
use Bitrix\Im\Model\LinkUrlTable;
use Bitrix\Im\Model\MessageDisappearingTable;
use Bitrix\Im\Model\MessageIndexTable;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\MessageUuidTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\Model\NoRelationPermissionDiskTable;
use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Sync\Event;
use Bitrix\Im\V2\Sync\Logger;
use Bitrix\ImConnector\Model\ChatLastMessageTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;
use CPullStack;

class ChatContentCollector
{
	private int $chatId;
	private array $chatMembers;

	public function __construct(Chat|int $chat)
	{
		if ($chat instanceof Chat)
		{
			$this->chatId = $chat->getChatId();
		}
		else
		{
			$this->chatId = $chat;
		}
	}

	/**
	 * @param string $tableClass
	 * @param mixed $columnValue
	 * @param int|null $limit
	 * @param string $columnName
	 * @param string|array $selectColumnName
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function collectByColumn(
		string $tableClass,
		mixed $columnValue,
		int|null $limit = null,
		string $columnName = 'CHAT_ID',
		string|array $selectColumnName = 'ID',
	): array
	{
		if (!is_a($tableClass, DataManager::class, true))
		{
			return [];
		}

		$isMultiColumn = is_array($selectColumnName);
		$indexColumn = $isMultiColumn ? reset($selectColumnName) : $selectColumnName;

		$params = [
			'select' => $isMultiColumn ? $selectColumnName : [$selectColumnName],
			'filter' => [(is_array($columnValue) ? '@' : '=') . $columnName => $columnValue],
		];

		if (null !== $limit)
		{
			$params['limit'] = $limit;
		}

		$result = $tableClass::getList($params);
		$data = [];

		foreach ($result->fetchAll() as $row)
		{
			$data[$row[$indexColumn]] = $isMultiColumn ? $row : $row[$indexColumn];
		}

		return $data;
	}

	/**
	 * @param string $tableClass
	 * @param array $idColumnValues
	 * @param string $idColumnName
	 * @return void
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function deleteByColumn(
		string $tableClass,
		array $idColumnValues,
		string $idColumnName = 'ID',
	): void
	{
		if (0 === count($idColumnValues))
		{
			return;
		}

		if (!is_a($tableClass, DataManager::class, true))
		{
			return;
		}

		$whereSql = Query::buildFilterSql($tableClass::getEntity(), ['@' . $idColumnName => $idColumnValues]);
		$tableName = $tableClass::getTableName();
		$connection = Application::getConnection();
		$connection->queryExecute($sql = "DELETE FROM {$tableName} WHERE {$whereSql}");
	}

	private function getCleanupSequence(): array
	{
		return [
			'cleanupChatAddons',
			'cleanupMessages',
			'cleanupLastMessages',
			'cleanupFavoritesLinks',
			'cleanupUrlsLinks',
			'cleanupTasksLinks',
			'cleanupCalendarLinks',
			'cleanupFilesLinks',
			'cleanupReactions',
			'cleanupReminder',
			'cleanupNoRelationPermDisk',
		];
	}

	private function getCurrentCleanupStep(?string $previousCompletedStep = null): ?string
	{
		$prevStepFound = false;

		foreach ($this->getCleanupSequence() as $step)
		{
			if ((null === $previousCompletedStep) || $prevStepFound)
			{
				return $step;
			}

			if ($step === $previousCompletedStep)
			{
				$prevStepFound = true;
			}
		}

		return null;
	}

	public function processCleanup(int $limit, ?string $previousCompletedStep = null): ?string
	{
		$currentStep = $this->getCurrentCleanupStep($previousCompletedStep);

		if (null === $currentStep)
		{
			return null;
		}

		$isCompleted = !$this->{$currentStep}($limit);

		return $isCompleted ? $currentStep : $previousCompletedStep;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function getNextChatIdToCleanup(): ?int
	{
		$nextChildChat = $this->collectByColumn(
			ChatTable::class,
			$this->chatId,
			1,
			'PARENT_ID',
		);

		if (count($nextChildChat) > 0)
		{
			return array_key_first($nextChildChat);
		}

		return null;
	}

	/**
	 * Does critical cleanup using DELETE queries without loops and only by indexed fields. Must be run on
	 * the same hit when user initiates chat deletion. All other operations will be performed via agents.
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function deleteChat(?int $userId = null): void
	{
		$this->cleanupChatBasics($userId);
		ChatCleanupAgent::register($this->chatId, userId: $userId);
	}

	protected function getChatMembers(): array
	{
		$this->chatMembers ??= $this->collectByColumn(
			RelationTable::class,
			$this->chatId,
			selectColumnName: 'USER_ID',
		);

		return $this->chatMembers;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function writeSyncLog(): void
	{
		$userIds = $this->getChatMembers();
		$chatType = Chat::getInstance($this->chatId)->getType();
		$logger = Logger::getInstance();

		foreach ($userIds as $userId)
		{
			$logger->add(
				new Event(Event::COMPLETE_DELETE_EVENT, Event::CHAT_ENTITY, $this->chatId),
				(int)$userId,
				$chatType,
			);
		}
	}

	/**
	 * @param int|null $userId
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function cleanupChatBasics(?int $userId = null): void
	{
		$chat = Chat::getInstance($this->chatId);
		$arMessage = [
			'module_id' => 'im',
			'command' => 'chatDelete',
			'params' => [
				'chatId' => $this->chatId,
				'parentChatId' => $chat->getParentChatId() ?? 0,
				'dialogId' => $chat->getDialogId(),
				'type' => $chat->getExtendedType(),
				'userId' => $userId,
			],
		];

		$this->writeSyncLog();
		$this->deleteByColumn(RelationTable::class, [$this->chatId], 'CHAT_ID');
		$this->deleteByColumn(ChatTable::class, [$this->chatId]);
		Chat::cleanCache($this->chatId);
		$this->deleteByColumn(RecentTable::class, [$this->chatId], 'ITEM_CID');
		$this->deleteByColumn(MessageUnreadTable::class, [$this->chatId], 'CHAT_ID');
		$this->deleteByColumn(MessageUnreadTable::class, [$this->chatId], 'PARENT_ID');
		$this->cleanupCounterCache();
		$this->deleteByColumn(ChatIndexTable::class, [$this->chatId], 'CHAT_ID');

		CPullStack::AddShared($arMessage);
	}

	protected function cleanupCounterCache(): void
	{
		foreach ($this->getChatMembers() as $userId)
		{
			CounterService::clearCache((int)$userId);
		}
	}

	/**
	 * @param $limit
	 * @return bool
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected function cleanupChatAddons($limit): bool
	{
		$this->deleteByColumn(ChatParamTable::class, [$this->chatId], 'CHAT_ID');
		$this->deleteByColumn(ChatLastMessageTable::class, [$this->chatId], 'EXTERNAL_CHAT_ID');
		$this->deleteByColumn(BotChatTable::class, [$this->chatId], 'CHAT_ID');
		$this->deleteByColumn(BlockUserTable::class, [$this->chatId], 'CHAT_ID');

		return false;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupLastMessages(int $limit): bool
	{
		$ids = $this->collectByColumn(LastMessageTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LastMessageTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupMessages($limit): bool
	{
		$ids = $this->collectByColumn(MessageTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			$folderId = Chat::getInstance($this->chatId)->getDiskFolderId();

			if ($folderId)
			{
				$folderModel = Folder::getById($folderId);
				$folderModel->deleteTree(SystemUser::SYSTEM_USER_ID);
			}

			return false;
		}

		$params = $this->collectByColumn(
			MessageParamTable::class,
			$ids,
			columnName: 'MESSAGE_ID',
			selectColumnName: [
				'ID',
				'PARAM_NAME',
				'PARAM_VALUE',
			],
		);
		$paramIds = array_keys($params);
		$fileIds = [];

		foreach ($params as $row)
		{
			if ($row['PARAM_NAME'] === 'FILE_ID')
			{
				$fileIds = intval($row['PARAM_VALUE']);
			}
		}

		$this->deleteByColumn(MessageTable::class, $ids);
		$this->deleteByColumn(MessageUuidTable::class, $ids, 'MESSAGE_ID');
		$this->deleteByColumn(MessageIndexTable::class, $ids, 'MESSAGE_ID');
		$this->deleteByColumn(MessageViewedTable::class, $ids, 'MESSAGE_ID');
		$this->deleteByColumn(MessageDisappearingTable::class, $ids, 'MESSAGE_ID');
		$this->deleteByColumn(MessageParamTable::class, $paramIds);
		$this->deleteByColumn(LinkPinTable::class, $ids, 'MESSAGE_ID');

		foreach ($fileIds as $fileId)
		{
			File::getById($fileId)->delete(SystemUser::SYSTEM_USER_ID);
		}

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupFavoritesLinks(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkFavoriteTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkFavoriteTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupUrlsLinks(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkUrlTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkUrlIndexTable::class, $ids, 'URL_ID');
		$this->deleteByColumn(LinkUrlTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupTasksLinks(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkTaskTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkTaskTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupCalendarLinks(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkCalendarTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkCalendarIndexTable::class, $ids);
		$this->deleteByColumn(LinkCalendarTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupFilesLinks(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkFileTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkFileTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupReactions(int $limit): bool
	{
		$ids = $this->collectByColumn(ReactionTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(ReactionTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupReminder(int $limit): bool
	{
		$ids = $this->collectByColumn(LinkReminderTable::class, $this->chatId, $limit);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(LinkReminderTable::class, $ids);

		return true;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function cleanupNoRelationPermDisk(int $limit): bool
	{
		$ids = $this->collectByColumn(
			NoRelationPermissionDiskTable::class,
			$this->chatId,
			$limit,
		);

		if (count($ids) === 0)
		{
			return false;
		}

		$this->deleteByColumn(NoRelationPermissionDiskTable::class, $ids);

		return true;
	}
}
