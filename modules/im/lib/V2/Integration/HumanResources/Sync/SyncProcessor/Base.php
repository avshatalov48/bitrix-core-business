<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor;

use Bitrix\HumanResources\Contract\Repository\NodeRelationRepository;
use Bitrix\HumanResources\Contract\Service\NodeMemberService;
use Bitrix\HumanResources\Contract\Service\NodeService;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Service\NodeRelationService;
use Bitrix\Im\Model\HrSyncQueueTable;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\EntityType;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\Status;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncInfo;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\QueueItemResult;
use Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

abstract class Base implements SyncProcessor
{
	protected const LOCK_TTL = 1;
	protected const LOCK_NAME = 'im_hr_sync';

	abstract protected function getEntityType(): EntityType;

	protected NodeMemberService $memberService;
	protected NodeRelationService $relationService;
	protected NodeRelationRepository $relationRepository;
	protected NodeService $nodeService;

	public function __construct(
		?NodeMemberService $memberService = null,
		?NodeRelationService $nodeRelationService = null,
		?NodeRelationRepository $relationRepository = null,
		?NodeService $nodeService = null
	)
	{
		Loader::includeModule('humanresources');

		$this->memberService = $memberService ?? Container::getNodeMemberService();
		$this->relationService = $nodeRelationService ?? Container::getNodeRelationService();
		$this->relationRepository = $relationRepository ?? Container::getNodeRelationRepository();
		$this->nodeService = $nodeService ?? Container::getNodeService();
	}

	public static function getInstance(EntityType $entityType): static
	{
		return match ($entityType)
		{
			EntityType::CHAT => new Chat(),
			EntityType::USER => new User(),
		};
	}

	public function dequeue(): ?SyncInfo
	{
		$row = HrSyncQueueTable::query()
			->setSelect(['*'])
			->where('ENTITY_TYPE', $this->getEntityType()->value)
			->where('IS_LOCKED', false)
			->setOrder(['ID'])
			->setLimit(1)
			->fetch()
		;

		if (!$row)
		{
			return null;
		}

		return SyncInfo::createFromRow($row);
	}

	public function getOrCreateWithLock(SyncInfo $syncInfo): QueueItemResult
	{
		$isLocked = $this->lock($syncInfo);

		if (!$isLocked)
		{
			return (new QueueItemResult())->setSkip(true);
		}

		try
		{
			return $this->getOrCreateWithLockInternal($syncInfo);
		}
		finally
		{
			$this->unlock($syncInfo);
		}
	}

	public function tryGetWithLock(SyncInfo $syncInfo): ?QueueItem
	{
		$isLocked = $this->lock($syncInfo);

		if (!$isLocked)
		{
			return null;
		}

		try
		{
			$item = $this->tryGetItem($syncInfo);
			return $item?->lock();
		}
		finally
		{
			$this->unlock($syncInfo);
		}
	}

	public function finalizeSync(QueueItem $item): Result
	{
		HrSyncQueueTable::delete($item->id);

		return new Result();
	}

	protected function getOrCreateWithLockInternal(SyncInfo $syncInfo): QueueItemResult
	{
		$result = new QueueItemResult();
		$item = $this->tryGetItem($syncInfo);

		if ($item === null)
		{
			return $result->setResult($this->createNewQueueItem($syncInfo));
		}

		if ($item->syncInfo->direction === $syncInfo->direction)
		{
			return $result->setResult($item)->setSkip(true);
		}

		$result->setSkip($item->isLocked);
		$this->finalizeSync($item);

		return $result->setResult($this->createNewQueueItem($syncInfo));
	}

	protected function tryGetItem(SyncInfo $syncInfo): ?QueueItem
	{
		$row = HrSyncQueueTable::query()
			->setSelect(['*'])
			->where('ENTITY_TYPE', $syncInfo->entityType->value)
			->where('ENTITY_ID', $syncInfo->entityId)
			->where('NODE_ID', $syncInfo->nodeId)
			->fetch()
		;

		if (!$row)
		{
			return null;
		}

		return QueueItem::createFromRow($row);
	}

	protected function createNewQueueItem(SyncInfo $syncInfo): QueueItem
	{
		$id = HrSyncQueueTable::add([
			'ENTITY_TYPE' => $syncInfo->entityType->value,
			'ENTITY_ID' => $syncInfo->entityId,
			'NODE_ID' => $syncInfo->nodeId,
			'DIRECTION' => $syncInfo->direction->value,
			'WITH_CHILD_NODES' => $syncInfo->withChildNodes,
			'POINTER' => 0,
			'IS_LOCKED' => true,
			'DATE_CREATE' => new DateTime(),
			'DATE_UPDATE' => new DateTime(),
		])->getId();

		return new QueueItem(
			$id,
			$syncInfo,
			0,
			true,
			Status::NEW,
		);
	}

	public function hasItemsInQueue(): bool
	{
		$row = HrSyncQueueTable::query()
			->setSelect(['ID'])
			->where('ENTITY_TYPE', $this->getEntityType()->value)
			->setLimit(1)
			->fetch()
		;

		if ($row === false)
		{
			return false;
		}

		return true;
	}

	protected static function getLockName(SyncInfo $syncInfo): string
	{
		return static::LOCK_NAME . "{$syncInfo->entityType->value}_{$syncInfo->entityId}_{$syncInfo->nodeId}";
	}

	protected function lock(SyncInfo $syncInfo): bool
	{
		return Application::getConnection(HrSyncQueueTable::getConnectionName())
			->lock(static::getLockName($syncInfo), self::LOCK_TTL)
		;
	}

	protected function unlock(SyncInfo $syncInfo): bool
	{
		return Application::getConnection(HrSyncQueueTable::getConnectionName())
			->unlock(static::getLockName($syncInfo))
		;
	}
}