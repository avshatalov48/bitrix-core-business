<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync;

use Bitrix\HumanResources\Contract\Repository\NodeRelationRepository;
use Bitrix\HumanResources\Contract\Service\NodeMemberService;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Item\NodeRelation;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Service\NodeRelationService;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\V2\Common\PeriodAgentTrait;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\EntityType;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncDirection;
use Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Base;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class SyncService
{
	use PeriodAgentTrait;

	protected const AGENT_SHORT_PERIOD = 5;
	protected const AGENT_LONG_PERIOD = 300;

	protected NodeMemberService $memberService;
	protected NodeRelationService $relationService;
	protected NodeRelationRepository $relationRepository;
	protected SyncProcessor $syncProcessor;
	protected EntityType $entityType;

	public function __construct(
		EntityType $entityType,
		?NodeMemberService $memberService = null,
		?NodeRelationService $nodeRelationService = null,
		?NodeRelationRepository $relationRepository = null
	)
	{
		Loader::requireModule('humanresources');

		$this->entityType = $entityType;
		$this->syncProcessor = Base::getInstance($entityType);
		$this->memberService = $memberService ?? Container::getNodeMemberService();
		$this->relationService = $nodeRelationService ?? Container::getNodeRelationService();
		$this->relationRepository = $relationRepository ?? Container::getNodeRelationRepository();
	}

	protected static function getAgentNameByEntityType(Item\EntityType $entityType): string
	{
		$agentNameByType = [
			Item\EntityType::CHAT->value => '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncRelationAgent();',
			Item\EntityType::USER->value => '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncMemberAgent();',
		];

		return $agentNameByType[$entityType->value];
	}

	public static function onMemberAdded(Event $event): void
	{
		/** @var NodeMember $member */
		$member = $event->getParameter('member');

		(new static(EntityType::USER))
			->startSync(Item\SyncInfo::createFromNodeMember($member, Item\SyncDirection::ADD))
		;
	}

	public static function onMemberDeleted(Event $event): void
	{
		/** @var NodeMember $member */
		$member = $event->getParameter('member');

		(new static(EntityType::USER))
			->startSync(Item\SyncInfo::createFromNodeMember($member, Item\SyncDirection::DELETE))
		;
	}

	public static function onMemberUpdated(Event $event): void
	{
		/** @var NodeMember $member */
		$member = $event->getParameter('member');
		/** @var NodeMember|null $previousMember */
		$previousMember = $event->getParameter('previousMember');

		if ($previousMember === null)
		{
			return;
		}

		if ($member->nodeId === $previousMember->nodeId)
		{
			return;
		}

		(new static(EntityType::USER))
			->startSync(Item\SyncInfo::createFromNodeMember($member, Item\SyncDirection::ADD))
		;
		(new static(EntityType::USER))
			->startSync(Item\SyncInfo::createFromNodeMember($previousMember, Item\SyncDirection::DELETE))
		;
	}

	public static function onRelationAdded(Event $event): void
	{
		/** @var NodeRelation $relation */
		$relation = $event->getParameter('relation');
		if ($relation->entityType !== RelationEntityType::CHAT)
		{
			return;
		}

		(new static(EntityType::CHAT))
			->startSync(Item\SyncInfo::createFromNodeRelation($relation, Item\SyncDirection::ADD))
		;
	}

	public static function onRelationDeleted(Event $event): void
	{
		/** @var NodeRelation $relation */
		$relation = $event->getParameter('relation');
		if ($relation->entityType !== RelationEntityType::CHAT)
		{
			return;
		}

		(new static(EntityType::CHAT))
			->startSync(Item\SyncInfo::createFromNodeRelation($relation, Item\SyncDirection::DELETE))
		;
	}

	public static function syncRelationAgent(): string
	{
		if (!Loader::includeModule('humanresources'))
		{
			return self::getAgentNameByEntityType(Item\EntityType::CHAT);
		}

		(new static(EntityType::CHAT))->sync();

		return self::getAgentNameByEntityType(Item\EntityType::CHAT);
	}

	public static function syncMemberAgent(): string
	{
		if (!Loader::includeModule('humanresources'))
		{
			return self::getAgentNameByEntityType(Item\EntityType::USER);
		}

		(new static(EntityType::USER))->sync();

		return self::getAgentNameByEntityType(Item\EntityType::USER);
	}

	protected function startSync(Item\SyncInfo $syncInfo): Result
	{
		$itemResult = $this->syncProcessor->getOrCreateWithLock($syncInfo);
		if ($itemResult->skip())
		{
			$this->determinePeriod(false);

			return new Result();
		}

		$item = $itemResult->getResult();
		try
		{
			$firstIterationResult = $this->syncProcessor->makeIteration($item);
			if (!$firstIterationResult->hasMore())
			{
				$this->syncProcessor->finalizeSync($item);
			}
			$this->determinePeriod(false);

			return $firstIterationResult;
		}
		catch (\Throwable $exception)
		{
			$item->setErrorStatus();

			return new Result();
		}
		finally
		{
			$item->unlock();
		}
	}

	protected function sync(): Result
	{
		$result = $this->syncNext();
		$this->determinePeriod(true);

		return $result;
	}

	protected function syncNext(): Result
	{
		$result = new Result();
		$syncInfo = $this->syncProcessor->dequeue();

		if ($syncInfo === null)
		{
			return $result;
		}

		$item = $this->syncProcessor->tryGetWithLock($syncInfo);
		if ($item === null)
		{
			return $result;
		}
		try
		{
			$iterationResult = $this->syncProcessor->makeIteration($item);
			if ($iterationResult->hasMore())
			{
				$item->unlock();
			}
			else
			{
				$this->syncProcessor->finalizeSync($item);
			}
		}
		catch (\Throwable $exception)
		{
			$item->unlock();
			$item->setErrorStatus();
		}

		return $result;
	}

	protected static function isAgentPeriodShort(int $newPeriod): bool
	{
		return $newPeriod === self::AGENT_SHORT_PERIOD;
	}

	protected function getPeriodGetter(): callable
	{
		return fn () => $this->syncProcessor->hasItemsInQueue() ? self::AGENT_SHORT_PERIOD : self::AGENT_LONG_PERIOD;
	}

	protected function determinePeriod(bool $fromAgent): void
	{
		self::setPeriodByName($fromAgent, self::getAgentNameByEntityType($this->entityType), $this->getPeriodGetter());
	}
}
