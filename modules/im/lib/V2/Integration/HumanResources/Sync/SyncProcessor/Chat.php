<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor;

use Bitrix\Im\V2\Chat\NullChat;
use Bitrix\Im\V2\Chat\OpenChannelChat;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\EntityType;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncDirection;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\IterationResult;
use Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Chat\Strategy;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Chat extends Base
{
	protected const DEFAULT_LIMIT = 200;
	protected const LIMIT_OPTION_NAME = 'hr_sync_user_limit';

	protected static int $countOfAddedUsers = 0;
	protected Strategy $strategy;

	public function makeIteration(QueueItem $item): IterationResult
	{
		$result = new IterationResult();

		if (!Loader::includeModule('humanresources'))
		{
			return $result;
		}

		if (self::$countOfAddedUsers >= self::getLimit())
		{
			return $result;
		}

		$strategy = $this->getStrategy($item->syncInfo->direction);
		$chat = \Bitrix\Im\V2\Chat::getInstance($item->syncInfo->entityId);

		if ($chat instanceof NullChat)
		{
			return $result->setHasMore(false);
		}

		$chat = $chat->withContextUser(0);
		$userIds = $strategy->getUserIds($item, self::getLimit());
		self::$countOfAddedUsers += count($userIds);

		$strategy->processUsers($chat, $userIds);
		$this->updatePointer($item, $strategy);

		return $result->setHasMore(count($userIds) === self::getLimit());
	}

	protected function getStrategy(SyncDirection $syncDirection): Strategy
	{
		$this->strategy ??= Strategy::getInstance($syncDirection);

		return $this->strategy;
	}

	protected function updatePointer(QueueItem $item, Strategy $strategy): void
	{
		$item->updatePointer($strategy->getPointer($item->pointer, self::getLimit()));
	}

	protected static function getLimit(): int
	{
		return Option::get('im', self::LIMIT_OPTION_NAME, self::DEFAULT_LIMIT);
	}

	protected function getEntityType(): EntityType
	{
		return EntityType::CHAT;
	}

	public function finalizeSync(QueueItem $item): Result
	{
		$result = parent::finalizeSync($item);
		$this->sendFinishMessage($item);

		return $result;
	}

	protected function sendFinishMessage(QueueItem $item): void
	{
		$chat = \Bitrix\Im\V2\Chat::getInstance($item->syncInfo->entityId);
		if ($chat instanceof OpenChannelChat)
		{
			return;
		}

		$node = $this->nodeService->getNodeInformation($item->syncInfo->nodeId);
		$messageCode = $this->getStrategy($item->syncInfo->direction)->getFinishMessageCode($chat, $node);

		if (empty($messageCode))
		{
			return;
		}

		\CIMMessenger::Add(
			[
				'MESSAGE' => Loc::getMessage($messageCode, ['#DEPARTMENT_NAME#' => $node->name]),
				'FROM_USER_ID' => 0,
				'TO_CHAT_ID' => $chat->getId(),
				'MESSAGE_TYPE' => $chat->getType(),
				'SYSTEM' => 'Y',
			],
		);
	}
}
