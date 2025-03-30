<?php

declare(strict_types = 1);

namespace Bitrix\Rest\Infrastructure;

use Bitrix\Rest\Contract\Strategy\AvailabilityToolCompatible;
use Bitrix\Rest\Engine\Access;
use Bitrix\Rest\Entity\Integration;
use Bitrix\Rest\Strategy\ExtraRulesStrategy;

class IntegrationAvailabilityTool
{
	public function __construct(private readonly AvailabilityToolCompatible $availabilityToolCompatible)
	{
	}

	public static function createByDefault(): self
	{
		return new self(
			new ExtraRulesStrategy()
		);
	}

	public function isAvailable(Integration $integration): bool
	{
		if ($this->availabilityToolCompatible->isApproved($integration))
		{
			return true;
		}

		return $this->canUseIntegration();
	}

	public function canUseIntegration(): bool
	{
		return Access::isAvailable();
	}
}
