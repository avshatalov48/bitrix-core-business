<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;

class WorkflowTemplateInstancesView implements \JsonSerializable
{
	private int $tplId;
	private int $allCount;
	private array $usersView = [];
	private ?DateTime $lastActivity;

	public function __construct(int $tplId)
	{
		$this->tplId = $tplId;
		$this->loadInstances();
	}

	protected function loadInstances(): void
	{
		$query = WorkflowInstanceTable::query();
		$query->addFilter('WORKFLOW_TEMPLATE_ID', $this->tplId)
			->addSelect('STARTED_BY')
			->addSelect('STARTED')
			->setOrder(['STARTED' => 'ASC'])
			->setLimit(3)
		;
		$result = $query->exec();
		$rows = $result->fetchAll();

		$this->allCount = $result->getSelectedRowsCount() >= 3
			? $this->countFirstHundred()
			: $result->getSelectedRowsCount()
		;

		$userIds = array_column($rows, 'STARTED_BY');
		$this->lastActivity = $rows[0]['STARTED'] ?? null;
		if ($userIds)
		{
			$this->loadUsersView($userIds);
		}
	}

	public function getTplId(): int
	{
		return $this->tplId;
	}

	public function getLastActivity(): ?DateTime
	{
		return $this->lastActivity;
	}

	public function jsonSerialize(): array
	{
		return [
			'tplId' => $this->tplId,
			'allCount' => $this->allCount,
			'users' => $this->usersView,
		];
	}

	private function loadUsersView(array $userIds): void
	{
		$userService = new UserService();
		$response = $userService->getUsersView(new UsersToGet($userIds));

		if (!$response->isSuccess())
		{
			return;
		}

		$userViews = [];
		foreach ($response->getUserViews() as $user)
		{
			$userId = $user->getUserId();
			$userViews[$userId] = [
				'id' => $userId,
				'avatarUrl' => $user->getUserAvatar(),
			];
		}

		$this->usersView = array_map(
			static fn($userId) => $userViews[$userId] ?? ['id' => 0],
			$userIds,
		);
	}

	private function countFirstHundred(): int
	{
		$query = WorkflowInstanceTable::query();
		$query->addSelect(new ExpressionField('CNT', 'COUNT(1)'));

		$subQuery = WorkflowInstanceTable::query();
		$subQuery->addSelect('ID');
		$subQuery->addFilter('WORKFLOW_TEMPLATE_ID', $this->tplId);
		$subQuery->setLimit(100);

		$query->registerRuntimeField('',
			new ReferenceField('M',
				\Bitrix\Main\ORM\Entity::getInstanceByQuery($subQuery),
				['=this.ID' => 'ref.ID'],
				['join_type' => 'INNER']
			)
		);

		$result = $query->exec()->fetch();

		return (int)$result['CNT'];
	}
}
