<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\V2\Entity\File\FilePopupItem;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Link\Reminder\ReminderPopupItem;
use Bitrix\Im\V2\Message\AdditionalMessagePopupItem;
use Bitrix\Im\V2\Message\Reaction\ReactionMessages;
use Bitrix\Im\V2\Message\Reaction\ReactionPopupItem;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\ViewedService;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\MessageUuidTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Link\Reminder\ReminderCollection;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Im\V2\Message\Params;

/**
 * @method Message next()
 * @method Message current()
 * @method Message offsetGet($offset)
 */
class MessageCollection extends Collection implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	protected bool $isFileFilled = false;
	protected bool $isParamsFilled = false;
	protected bool $isUuidFilled = false;
	protected bool $isLinkAttachmentsFilled = false;
	protected bool $isUnreadFilled = false;
	protected bool $isViewedFilled = false;
	protected bool $isViewedByOthersFilled = false;
	protected bool $isReactionsFilled = false;

	//region Collection

	/**
	 * @inheritdoc
	 * @return string
	 */
	public static function getCollectionElementClass(): string
	{
		return Message::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function find(array $filter, array $order, ?int $limit = null, ?Context $context = null, array $select = []): self
	{
		$context = $context ?? Locator::getContext();

		$query = MessageTable::query();
		$query->setSelect(['ID']);

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		$messageOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$messageOrder['ID'] = $order['ID'];
		}

		$query->setOrder($messageOrder);

		static::processFilters($query, $filter, $messageOrder);
		$messageIds = $query->fetchCollection()->getIdList();

		if (empty($select))
		{
			return new static($messageIds);
		}

		return new static(MessageTable::query()->whereIn('ID', $messageIds)->setSelect($select)->fetchCollection());
	}

	/**
	 * @return int[]
	 */
	public function getIds(): array
	{
		return $this->getPrimaryIds();
	}


	/**
	 * @return array<int, int[]>
	 */
	public function getFileIds(): array
	{
		$this->fillParams();

		$ids = [];
		foreach ($this as $message)
		{
			if ($message->getParams()->isSet(Params::FILE_ID))
			{
				$ids[$message->getId()] = $message->getParams()->get(Params::FILE_ID)->getValue();
			}
		}

		return $ids;
	}

	public function getCommonChatId(): ?int
	{
		$id = null;

		foreach ($this as $message)
		{
			if (isset($id) && $message->getChatId() !== $id)
			{
				return null;
			}

			if (!isset($id))
			{
				$id = $message->getChatId();
			}
		}

		return $id;
	}

	//endregion

	//region Rest

	public function toRestFormat(array $option = []): array
	{
		$this->fillAllForRest();

		$messagesForRest = [];

		foreach ($this as $message)
		{
			$messagesForRest[] = $message->toRestFormat();
		}

		return $messagesForRest;
	}

	public static function getRestEntityName(): string
	{
		return 'messages';
	}

	//endregion

	//region Fillers

	/**
	 * @return self
	 */
	public function fillFiles(): self
	{
		if (!$this->isFileFilled)
		{
			$fileIdsByMessages = $this->getFileIds();

			$fileIds = [];
			foreach ($fileIdsByMessages as $fileIdsByMessage)
			{
				foreach ($fileIdsByMessage as $fileId)
				{
					$fileIds[] = $fileId;
				}
			}

			$files = FileCollection::initByDiskFilesIds($fileIds);

			foreach ($this as $message)
			{
				$messagesFiles = new FileCollection();
				foreach ($fileIdsByMessages[$message->getId()] ?? [] as $fileId)
				{
					$file = $files->getById($fileId);
					if ($file !== null)
					{
						$messagesFiles[] = $file->setChatId($message->getChatId());
					}
				}
				$message->setFiles($messagesFiles);
			}

			$this->isFileFilled = true;
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function fillParams(): self
	{
		$messageIds = $this->getIds();
		if (!$this->isParamsFilled && !empty($messageIds))
		{
			$paramsCollection = MessageParamTable::query()
				->setSelect(['*'])
				->whereIn('MESSAGE_ID', $this->getIds())
				->fetchCollection()
			;

			foreach ($paramsCollection as $paramRow)
			{
				$this[$paramRow->getMessageId()]->getParams(true)->load($paramRow);
			}

			$this->isParamsFilled = true;
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function fillUuid(): self
	{
		$messageIds = $this->getIds();
		if (!$this->isUuidFilled && !empty($messageIds))
		{
			$uuids = MessageUuidTable::query()
				->setSelect(['UUID', 'MESSAGE_ID'])
				->whereIn('MESSAGE_ID', $this->getIds())
				->fetchAll()
			;

			$uuidsByMessageId = [];
			foreach ($uuids as $uuid)
			{
				$uuidsByMessageId[$uuid['MESSAGE_ID']] = $uuid['UUID'];
			}

			foreach ($this as $message)
			{
				$message->setUuid($uuidsByMessageId[$message->getId()] ?? null);
			}

			$this->isUuidFilled = true;
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function fillLinkAttachments(): self
	{
		if ($this->isLinkAttachmentsFilled)
		{
			return $this;
		}

		$this->fillParams();
		$params = [];
		foreach ($this as $message)
		{
			$params[$message->getId()] = $message->getParams()->toRestFormat();
		}

		$attachByMessageId = \CIMMessageLink::prepareShow([], $params);

		foreach ($this as $message)
		{
			$message->setLinkAttachments($attachByMessageId[$message->getId()]['PARAMS']['ATTACH'] ?? []);
		}

		$this->isLinkAttachmentsFilled = true;

		return $this;
	}

	/**
	 * @return self
	 */
	public function fillUnread(): self
	{
		if ($this->isUnreadFilled)
		{
			return $this;
		}

		$readStatuses = (new ReadService())->getReadStatusesByMessageIds($this->getIds());

		foreach ($this as $message)
		{
			$message->setUnread(!($readStatuses[$message->getMessageId()]));
		}

		$this->isUnreadFilled = true;

		return $this;
	}

	public function fillViewed(): self
	{
		if ($this->isViewedFilled)
		{
			return $this;
		}

		$notOwnMessages = [];

		foreach ($this as $message)
		{
			if ($message->getAuthorId() === $this->getContext()->getUserId())
			{
				$message->setViewed(true);

				continue;
			}

			$notOwnMessages[] = $message->getMessageId();
		}

		$viewStatuses = (new ReadService())->getViewStatusesByMessageIds($notOwnMessages);

		foreach ($notOwnMessages as $notOwnMessageId)
		{
			$this[$notOwnMessageId]->setViewed($viewStatuses[$notOwnMessageId]);
		}

		$this->isViewedFilled = true;

		return $this;
	}

	public function fillViewedByOthers(): self
	{
		if ($this->isViewedByOthersFilled)
		{
			return $this;
		}

		$statuses = (new ViewedService())->getMessageStatuses($this->getIds());

		foreach ($this as $message)
		{
			$status = $statuses[$message->getId()] ?? \IM_MESSAGE_STATUS_RECEIVED;
			$message->setViewedByOthers($status === \IM_MESSAGE_STATUS_DELIVERED);
		}

		$this->isViewedByOthersFilled = true;

		return $this;
	}

	public function fillReactions(): self
	{
		if ($this->isReactionsFilled)
		{
			return $this;
		}

		$messageIds = $this->getIds();

		if (empty($messageIds))
		{
			return $this;
		}

		$reactions = new ReactionMessages($messageIds);

		foreach ($this as $message)
		{
			$message->setReactions($reactions->getReactionMessage($message->getMessageId()));
		}

		$this->isReactionsFilled = true;

		return $this;
	}

	/**
	 * @return self
	 */
	public function fillAllForRest(): self
	{
		return $this
			->fillParams()
			->fillLinkAttachments()
			->fillUuid()
			->fillUnread()
			->fillViewed()
			->fillViewedByOthers()
			->fillReactions()
		;
	}

	//endregion

	//region Getters

	/**
	 * @return FileCollection
	 */
	public function getFiles(): FileCollection
	{
		$this->fillFiles();

		$files = new FileCollection();

		foreach ($this as $message)
		{
			$filesFromMessage = $message->getFiles();
			foreach ($filesFromMessage as $fileFromMessage)
			{
				$files[] = $fileFromMessage;
			}
		}

		return $files->getUnique();
	}

	public function getUserIds(): array
	{
		$users = [];

		$this->fillParams();
		foreach ($this as $message)
		{
			$usersFromMessage = $message->getUserIds();

			if ($message->getParams()->isSet(Params::FORWARD_USER_ID))
			{
				$forwardUserId = $message->getParams()->get(Params::FORWARD_USER_ID)->getValue();
				$usersFromMessage[] = $forwardUserId;
			}

			if ($message->getParams()->isSet(Params::CHAT_USER))
			{
				foreach ($message->getParams()->get(Params::CHAT_USER)->getValue() as $chatUser)
				{
					$usersFromMessage[] = $chatUser;
				}
			}

			foreach ($usersFromMessage as $userFromMessage)
			{
				$users[] = $userFromMessage;
			}
		}

		return $users;
	}

	/**
	 * @return ReminderCollection
	 */
	public function getReminders(): ReminderCollection
	{
		return ReminderCollection::getByMessagesAndAuthorId($this, $this->getContext()->getUserId());
	}

	public function getReplayedMessageIds(): array
	{
		$this->fillParams();
		$result = [];
		foreach ($this as $message)
		{
			if ($message->getParams()->isSet(Params::REPLY_ID))
			{
				$result[] = $message->getParams()->get(Params::REPLY_ID)->getValue();
			}
		}

		return $result;
	}

	public function getReactions(): ReactionMessages
	{
		$reactions = new ReactionMessages([]);

		foreach ($this as $message)
		{
			$reactions->addReactionMessage($message->getReactions());
		}

		return $reactions;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$this->fillAllForRest();

		return (new PopupData([
			new UserPopupItem($this->getUserIds()),
			new FilePopupItem($this->getFiles()),
			new ReminderPopupItem($this->getReminders()),
			new AdditionalMessagePopupItem($this->getReplayedMessageIds()),
			new ReactionPopupItem($this->getReactions())
		], $excludedList))->mergeFromEntity($this->getReactions(), $excludedList);
	}

	public function filterByChatId(int $chatId): self
	{
		$filteredCollection = new static();

		foreach ($this as $message)
		{
			if ($message->getChatId() === $chatId)
			{
				$filteredCollection->add($message);
			}
		}

		return $filteredCollection;
	}

	//endregion

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		if (isset($filter['CHAT_ID']))
		{
			$query->where('CHAT_ID', $filter['CHAT_ID']);
		}

		if (isset($filter['SEARCH_MESSAGE']))
		{
			$query->whereLike('MESSAGE', "%{$filter['SEARCH_MESSAGE']}%");
		}

		if (isset($filter['START_ID']) && (int)$filter['START_ID'] > 0)
		{
			$query->where('ID', '>=', $filter['START_ID']);
		}

		if (isset($filter['LAST_ID']))
		{
			$operator = $order['ID'] === 'DESC' ? '<' : '>';
			$query->where('ID', $operator, $filter['LAST_ID']);
		}

		if (isset($filter['DATE_FROM']))
		{
			$query->where('DATE_CREATE', '>=', $filter['DATE_FROM']);
		}

		if (isset($filter['DATE_TO']))
		{
			$query->where('DATE_CREATE', '<=', $filter['DATE_TO']);
		}

		if (isset($filter['DATE']))
		{
			$query->where('DATE_CREATE', '>=', $filter['DATE']);

			$to = clone $filter['DATE'];
			$to->add('1 DAY');

			$query->where('DATE_CREATE', '<=', $to);
		}
	}
}