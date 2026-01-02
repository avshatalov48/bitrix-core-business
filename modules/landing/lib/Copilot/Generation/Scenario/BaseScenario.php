<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Scenario;

use Bitrix\Landing\Metrika;

abstract class BaseScenario implements IScenario
{
	/**
	 * Returns the number of the scenario step at which to check request limits.
	 *
	 * @return int
	 */
	abstract public function getQuotaCalculateStep(): int;

	abstract public function getAnalyticCategory(): Metrika\Categories;

	public function getAsyncRelations(): ?array
	{
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getFirstStepId(): ?int
	{
		foreach ($this->getMap() as $id => $step)
		{
			return $id;
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getNextStepId(int $stepId): ?int
	{
		$useNext = false;
		foreach ($this->getMap() as $id => $step)
		{
			if ($useNext)
			{
				return $id;
			}

			if ($id === $stepId)
			{
				$useNext = true;
			}
		}

		return null;
	}

	public function isLastStep(int $stepId): bool
	{
		$ids = array_keys($this->getMap());
		$lastId = array_pop($ids);
		if (!$lastId)
		{
			return false;
		}

		return $lastId === $stepId;
	}

	/**
	 * @inheritdoc
	 */
	public function checkStep(?int $stepId): bool
	{
		if ($stepId === null)
		{
			return true;
		}

		return array_key_exists($stepId, $this->getMap());
	}
}