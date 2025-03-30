<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Service;

use Bitrix\Rest\Entity\Collection\IntegrationCollection;

interface IntegrationService
{
	public function getPaidIntegrations(): IntegrationCollection;
	public function hasPaidIntegrations(): bool;
}
