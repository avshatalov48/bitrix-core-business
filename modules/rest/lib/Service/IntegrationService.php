<?php

declare(strict_types=1);

namespace Bitrix\Rest\Service;

use Bitrix\Main\ModuleManager;
use Bitrix\Rest\Contract;
use Bitrix\Rest\Entity\Collection\IntegrationCollection;

class IntegrationService implements Contract\Service\IntegrationService
{
	public function __construct(
		private readonly Contract\Repository\IntegrationRepository $integrationRepository,
	)
	{}

	public function getPaidIntegrations(): IntegrationCollection
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$collection = $this->integrationRepository->getCloudPaidIntegrations();
		}
		else
		{
			$collection = $this->integrationRepository->getBoxedPaidIntegrations();
		}

		return $collection;
	}

	public function hasPaidIntegrations(): bool
	{
		return ModuleManager::isModuleInstalled('bitrix24')
			? $this->integrationRepository->hasUserIntegrations()
			: $this->integrationRepository->hasNotInWebhookUserIntegrations();
	}
}
