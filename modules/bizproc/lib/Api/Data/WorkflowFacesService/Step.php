<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowFacesService;

use Bitrix\Bizproc\Api\Enum\WorkflowFacesService\WorkflowFacesStep;
use Bitrix\Bizproc\Api\Enum\WorkflowFacesService\WorkflowFacesStepStatus;
use Bitrix\Main\Localization\Loc;

final class Step
{
	private WorkflowFacesStep $step;
	private array $avatars = [];
	private int $duration = 0;
	private ?bool $isSuccess = null;
	private int $taskId = 0;

	public function __construct(WorkflowFacesStep $step)
	{
		$this->step = $step;
	}

	public function fillFromData(array $data): self
	{
		if (isset($data['avatars']) && is_array($data['avatars']))
		{
			$this->setAvatars($data['avatars']);
		}

		if (isset($data['duration']) && is_int($data['duration']))
		{
			$this->setDuration($data['duration']);
		}

		if (isset($data['success']) && is_bool($data['success']))
		{
			$this->setSuccess($data['success']);
		}

		if (isset($data['taskId']) && is_int($data['taskId']))
		{
			$this->setTaskId($data['taskId']);
		}

		return $this;
	}

	public function setAvatars(array $avatars): self
	{
		$this->avatars = $avatars;

		return $this;
	}

	public function setDuration(int $duration): self
	{
		$this->duration = $duration;

		return $this;
	}

	public function setSuccess(bool $isSuccess): self
	{
		$this->isSuccess = $isSuccess;

		return $this;
	}

	public function setTaskId(int $taskId): self
	{
		$this->taskId = $taskId;

		return $this;
	}

	public function getData(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'avatars' => $this->getAvatars(),
			'duration' => $this->getDuration(),
			'success' => $this->getSuccess(),
			'status' => $this->getStatus(),
			'taskId' => $this->getTaskId(),
		];
	}

	public function getId(): string
	{
		return $this->step->value;
	}

	public function getName(): string
	{
		return $this->step->getTitle();
	}

	public function getAvatars(): array
	{
		return $this->avatars;
	}

	public function getDuration(): int
	{
		return $this->duration;
	}

	public function getSuccess(): ?bool
	{
		return $this->isSuccess;
	}

	public function getTaskId(): int
	{
		return $this->taskId;
	}

	public function getStatus(): ?WorkflowFacesStepStatus
	{
		return $this->step->getStatus($this->getSuccess());
	}

	public static function getEmptyDurationText(): string
	{
		return Loc::getMessage('BIZPROC_API_DATA_WORKFLOW_FACES_SERVICE_STEP_EMPTY_DURATION_TITLE') ?? '';
	}
}
