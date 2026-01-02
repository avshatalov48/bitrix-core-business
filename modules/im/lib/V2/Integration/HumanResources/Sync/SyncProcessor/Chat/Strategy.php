<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Chat;

use Bitrix\HumanResources\Service\NodeMemberService;
use Bitrix\HumanResources\Item\Node;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Service\NodeRelationService;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncDirection;
use Bitrix\Main\Loader;

abstract class Strategy
{
	protected NodeMemberService $memberService;
	protected NodeRelationService $relationService;

	protected function __construct()
	{
		Loader::includeModule('humanresources');

		$this->memberService = Container::getNodeMemberService();
		$this->relationService = Container::getNodeRelationService();
	}

	public static function getInstance(SyncDirection $syncDirection): Strategy
	{
		return match ($syncDirection)
		{
			SyncDirection::ADD => (new AdditionStrategy()),
			SyncDirection::DELETE => (new DeletionStrategy()),
			SyncDirection::NODE_DELETE => (new NodeDeletionStrategy()),
		};
	}

	public function getUserIds(QueueItem $item, int $limit): array
	{
		$members = $this->memberService->getPagedEmployees(
			$item->syncInfo->nodeId,
			$item->syncInfo->withChildNodes,
			$item->pointer,
			$limit
		);
		$userIds = [];

		foreach ($members as $member)
		{
			$userIds[] = $member->entityId;
		}

		return $userIds;
	}

	public function getPointer(int $pointer, int $limit): int
	{
		return $pointer + $limit;
	}

	public abstract function processUsers(Chat $chat, array $userIds): void;

	public abstract function getFinishMessageCode(Chat $chat, ?Node $node): ?string;
}
