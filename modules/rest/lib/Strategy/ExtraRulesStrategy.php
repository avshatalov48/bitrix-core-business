<?php

declare(strict_types = 1);

namespace Bitrix\Rest\Strategy;

use Bitrix\Rest\Contract\Strategy\AvailabilityToolCompatible;
use Bitrix\Rest\Engine\Access;
use Bitrix\Rest\Entity\Integration;

class ExtraRulesStrategy implements AvailabilityToolCompatible
{
	public function isApproved(Integration $integration): bool
	{
		if ($integration->getPasswordId())
		{
			return Access::isAvailableAPAuthByPasswordId($integration->getPasswordId());
		}

		return false;
	}
}
