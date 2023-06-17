<?php

namespace Bitrix\Im\V2\Entity\Task;

use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Type\DateTime;
use Bitrix\Tasks\Internals\TaskObject;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Tasks\Provider\TaskList;
use Bitrix\Tasks\Provider\TaskQuery;

class TaskItem implements RestEntity, PopupDataAggregatable
{
	use ContextCustomer;

	protected int $taskId;
	protected string $title;
	protected int $status;
	protected string $statusTitle;
	protected ?DateTime $deadline;
	protected string $state;
	protected string $color;
	protected int $creatorId;
	protected int $responsibleId;
	protected array $membersIds;

	public function getId(): int
	{
		return $this->getTaskId();
	}

	public static function getRestEntityName(): string
	{
		return 'task';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getTaskId(),
			'title' => $this->getTitle(),
			'creatorId' => $this->getCreatorId(),
			'responsibleId' => $this->getResponsibleId(),
			'status' => $this->getStatus(),
			'statusTitle' => $this->getStatusTitle(),
			'deadline' => $this->getDeadline() ? $this->getDeadline()->format('c') : null,
			'state' => $this->getState(),
			'color' => $this->getColor(),
			'source' => \CTaskNotifications::getNotificationPath(['ID' => $this->getContext()->getUserId()], $this->getTaskId()),
		];
	}

	public static function getById(int $id, ?Context $context = null): ?self
	{
		$context = $context ?? Locator::getContext();
		$taskQuery = new TaskQuery($context->getUserId());
		$taskQuery
			->setSelect(\Bitrix\Im\V2\Link\Task\TaskCollection::SELECT_FIELDS)
			->setWhere(['=ID' => $id])
		;
		$rows = (new TaskList())->getList($taskQuery);

		if (!isset($rows[0]))
		{
			return null;
		}

		return self::initByRow($rows[0]);
	}

	public static function initByRow(array $row): self
	{
		$taskEntity = new static();

		$membersIds = array_merge(
			[(int)$row['CREATED_BY']],
			[(int)$row['RESPONSIBLE_ID']],
			array_map(static fn ($id) => (int)$id, $row['AUDITORS'] ?? []),
			array_map(static fn ($id) => (int)$id, $row['ACCOMPLICES'] ?? [])
		);
		$uniqueMembersIds = array_unique($membersIds);

		$taskEntity
			->setTaskId((int)$row['ID'])
			->setTitle($row['TITLE'])
			->setDeadline(isset($row['DEADLINE']) ? new DateTime($row['DEADLINE']) : null)
			->setStatus((int)$row['REAL_STATUS'])
			->setCreatorId((int)$row['CREATED_BY'])
			->setResponsibleId((int)$row['RESPONSIBLE_ID'])
			->setMembersIds(array_values($uniqueMembersIds))
		;

		return $taskEntity;
	}

	public static function initByTaskObject(TaskObject $taskObject): self
	{
		$taskEntity = new static();

		$taskEntity
			->setTaskId($taskObject->getId())
			->setTitle($taskObject->getTitle())
			->setDeadline($taskObject->getDeadline())
			->setStatus($taskObject->getStatus())
			->setCreatorId($taskObject->getCreatedBy())
			->setResponsibleId($taskObject->getResponsibleId())
			->setMembersIds(array_unique($taskObject->getMemberList()->getUserIdList()))
		;

		return $taskEntity;
	}

	public function getUrl(): string
	{
		return \CTaskNotifications::getNotificationPath(
			['ID' => $this->getContext()->getUserId()],
			$this->getTaskId()
		);
	}

	protected function updateState(): void
	{
		if (isset($this->status))
		{
			$state = (new \Bitrix\Tasks\UI\Task\Deadline())->buildState($this->status, $this->deadline);
			$this->setState($state['state']);
			$this->setColor($state['color']);
		}
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserPopupItem([$this->getCreatorId(), $this->getResponsibleId()])], $excludedList);
	}

	//region Getters & setters

	public function getTaskId(): int
	{
		return $this->taskId;
	}

	public function setTaskId(int $taskId): TaskItem
	{
		$this->taskId = $taskId;
		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): TaskItem
	{
		$this->title = $title;
		return $this;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function setStatus(int $status): TaskItem
	{
		$this->status = $status;

		$statusTitle = \Bitrix\Tasks\UI\Task\Status::getList()[$this->status];
		$this->setStatusTitle($statusTitle);

		$this->updateState();

		return $this;
	}

	public function getStatusTitle(): string
	{
		return $this->statusTitle;
	}

	public function setStatusTitle(string $statusTitle): TaskItem
	{
		$this->statusTitle = $statusTitle;
		return $this;
	}

	public function getDeadline(): ?DateTime
	{
		return $this->deadline;
	}

	public function setDeadline(?DateTime $deadline): TaskItem
	{
		$this->deadline = $deadline;

		$this->updateState();

		return $this;
	}

	public function getState(): string
	{
		return $this->state;
	}

	public function setState(string $state): TaskItem
	{
		$this->state = $state;
		return $this;
	}

	public function getColor(): string
	{
		return $this->color;
	}

	public function setColor(string $color): TaskItem
	{
		$this->color = $color;
		return $this;
	}

	public function getCreatorId(): int
	{
		return $this->creatorId;
	}

	public function setCreatorId(int $creatorId): TaskItem
	{
		$this->creatorId = $creatorId;
		return $this;
	}

	public function getResponsibleId(): int
	{
		return $this->responsibleId;
	}

	public function setResponsibleId(int $responsibleId): TaskItem
	{
		$this->responsibleId = $responsibleId;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getMembersIds(): array
	{
		return $this->membersIds;
	}

	public function setMembersIds(array $membersIds): self
	{
		$this->membersIds = $membersIds;
		return $this;
	}

	//endregion
}