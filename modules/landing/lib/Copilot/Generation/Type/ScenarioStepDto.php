<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

use Bitrix\Landing\Copilot\Generation\Step\IStep;

class ScenarioStepDto
{
	public function __construct(
		public int $stepId,
		public readonly IStep $step,
		public StepStatuses $status,
		public ?int $entityId = null,
	)
	{}
}