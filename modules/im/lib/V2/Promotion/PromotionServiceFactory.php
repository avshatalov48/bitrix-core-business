<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion;

use Bitrix\Im\V2\Integration\Socialnetwork\Collab\Onboarding\CollabOnboardingPromotionService;
use Bitrix\Im\V2\Promotion\Service\PromotionServiceInterface;
use Bitrix\Im\V2\Promotion\Service\UIPromotionService;

class PromotionServiceFactory
{
	/** @return PromotionServiceInterface[] */
	public static function getServiceList(): array
	{
		return [
			UIPromotionService::getInstance(),
			CollabOnboardingPromotionService::getInstance(),
		];
	}

	public static function create(string $promotionId): PromotionServiceInterface
	{
		foreach (self::getServiceList() as $service)
		{
			if ($service->isCurrentTypePromotion($promotionId))
			{
				return $service;
			}
		}

		return UIPromotionService::getInstance();
	}
}
