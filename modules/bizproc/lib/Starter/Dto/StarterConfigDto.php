<?php

namespace Bitrix\Bizproc\Starter\Dto;

use Bitrix\Bizproc\Starter\Enum\Scenario;

final class StarterConfigDto
{
	public function __construct(
		public readonly Scenario $scenario,
		public bool $checkFeature = true,
		public bool $checkLimits = true,
		public bool $validateParameters = true,
		public bool $checkConstants = true,
		public bool $isDebug = false,
	)
	{}
}
