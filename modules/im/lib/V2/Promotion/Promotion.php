<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion;

use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Im\V2\Promotion\Event\Update\PromotionUpdateData;
use Bitrix\Im\V2\Promotion\Event\Update\PromotionUpdateEvent;
use Bitrix\Im\V2\Promotion\Internals\DeviceType;
use Bitrix\Im\V2\Promotion\Service\PromotionServiceInterface;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationService;

class Promotion
{
	/** @var PromotionServiceInterface[] */
	private array $serviceList;
	private ValidationService $vavidation;

	public function __construct()
	{
		$this->serviceList = PromotionServiceFactory::getServiceList();
		$this->vavidation = ServiceLocator::getInstance()->get('main.validation.service');
	}

	public function getActive(DeviceType $type = DeviceType::ALL): PromotionList
	{
		$promotionList = new PromotionList();

		if (self::isUnavailable())
		{
			return $promotionList;
		}

		foreach ($this->serviceList as $service)
		{
			$promotionList->merge($service->getActive($type));
		}

		return $promotionList;
	}

	public function markAsViewed(Entity\Promotion $promotion): Result
	{
		$validationResult = $this->vavidation->validate($promotion);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		return PromotionServiceFactory::create($promotion->getId())->markAsViewed($promotion);
	}

	/** @param array<int, PromotionUpdateData> $sortedByUserData */
	public function handlePromotionUpdate(array $sortedByUserData): void
	{
		if (empty($sortedByUserData))
		{
			return;
		}

		$event = new PromotionUpdateEvent($sortedByUserData);
		$event->send();
	}

	public static function isUnavailable(): bool
	{
		return Option::get('im', 'promo_disabled', 'N') === 'Y';
	}
}
