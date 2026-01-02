<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Update\PromotionUpdateDataFactory;

class PromotionService
{
	private PromotionUpdateDataFactory $updateDataFactory;

	public function __construct()
	{
		$this->updateDataFactory = PromotionUpdateDataFactory::getInstance();
	}

	/**
	 * @param array<int, JobCollection> $sortedByUserJobs
	 */
	public function onPromotionUpdate(array $sortedByUserJobs): void
	{
		if (empty($sortedByUserJobs))
		{
			return;
		}

		if (!Loader::includeModule('im'))
		{
			return;
		}

		$sortedByUserData = [];
		foreach ($sortedByUserJobs as $userId => $jobs)
		{
			$promoUpdateData = $this->updateDataFactory->create($jobs);

			if (null === $promoUpdateData)
			{
				continue;
			}

			$sortedByUserData[$userId] = $promoUpdateData;
		}

		$promoService = ServiceLocator::getInstance()->get('Im.Services.Promotion');
		$promoService->handlePromotionUpdate($sortedByUserData);
	}
}
