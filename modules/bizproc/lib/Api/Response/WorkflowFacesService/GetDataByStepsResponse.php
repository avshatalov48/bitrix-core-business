<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowFacesService;

use Bitrix\Bizproc\Api\Data\WorkflowFacesService\ProgressBox;
use Bitrix\Bizproc\Api\Data\WorkflowFacesService\Step;
use Bitrix\Bizproc\Result;
use Bitrix\Main\Type\Collection;

final class GetDataByStepsResponse extends Result
{
	public function setAuthorStep(Step $step): self
	{
		$this->data['authorStep'] = $step;

		return $this;
	}

	public function getAuthorStep(): ?Step
	{
		return $this->data['authorStep'] ?? null;
	}

	public function setProgressBox(ProgressBox $box): self
	{
		$this->data['progressBox'] = $box;

		return $this;
	}

	public function getProgressBox(): ?ProgressBox
	{
		return $this->data['progressBox'] ?? null;
	}

	public function setCompletedStep(Step $step): self
	{
		$this->data['completedStep'] = $step;

		return $this;
	}

	public function getCompletedStep(): ?Step
	{
		return $this->data['completedStep'] ?? null;
	}

	public function setDoneStep(Step $step): self
	{
		$this->data['doneStep'] = $step;

		return $this;
	}

	public function getDoneStep(): ?Step
	{
		return $this->data['doneStep'] ?? null;
	}

	public function setRunningStep(Step $step): self
	{
		$this->data['runningStep'] = $step;

		return $this;
	}

	public function getRunningStep(): ?Step
	{
		return $this->data['runningStep'] ?? null;
	}

	public function setFirstStep(Step $step): self
	{
		$this->data['firstStep'] = $step;

		return $this;
	}

	public function getFirstStep(): ?Step
	{
		return $this->data['firstStep'] ?? null;
	}

	public function setSecondStep(Step $step): self
	{
		$this->data['secondStep'] = $step;

		return $this;
	}

	public function getSecondStep(): ?Step
	{
		return $this->data['secondStep'] ?? null;
	}

	public function setThirdStep(Step $step): self
	{
		$this->data['thirdStep'] = $step;

		return $this;
	}

	public function getThirdStep(): ?Step
	{
		return $this->data['thirdStep'] ?? null;
	}

	/**
	 * @return Step[]|null[]
	 */
	public function getSteps(): array
	{
		return [$this->getFirstStep(), $this->getSecondStep(), $this->getThirdStep()];
	}

	public function setTimeStep(Step $step): self
	{
		$this->data['timeStep'] = $step;

		return $this;
	}

	public function getTimeStep(): ?Step
	{
		return $this->data['timeStep'] ?? null;
	}

	public function getUniqueUserIds(): array
	{
		$steps = $this->getSteps();
		$userIds = [];
		foreach ($steps as $step)
		{
			if ($step?->getAvatars())
			{
				$userIds[] = $step->getAvatars();
			}
		}

		if ($userIds)
		{
			$userIds = array_merge(...$userIds);
			Collection::normalizeArrayValuesByInt($userIds);
		}

		return $userIds;
	}

	public function setIsWorkflowFinished(bool $isFinished): self
	{
		$this->data['isWorkflowFinished'] = $isFinished;

		return $this;
	}

	public function getIsWorkflowFinished(): bool
	{
		$isWorkflowFinished = $this->data['isWorkflowFinished'] ?? false;

		return is_bool($isWorkflowFinished) ? $isWorkflowFinished : false;
	}
}
