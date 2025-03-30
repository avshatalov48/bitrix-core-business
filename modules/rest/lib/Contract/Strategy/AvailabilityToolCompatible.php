<?php

namespace Bitrix\Rest\Contract\Strategy;

use Bitrix\Rest\Entity\Integration;

interface AvailabilityToolCompatible
{
	public function isApproved(Integration $integration): bool;
}