<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Service;

use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Im\V2\Promotion\Internals\DeviceType;
use Bitrix\Im\V2\Promotion\Internals\PromotionType;
use Bitrix\Im\V2\Promotion\Entity;
use Bitrix\Main\Result;

interface PromotionServiceInterface
{
	public function getActive(DeviceType $type = DeviceType::ALL): PromotionList;
	public function getPromotionType(): PromotionType;
	public function isCurrentTypePromotion(string $promotionId): bool;

	public function markAsViewed(Entity\Promotion $promotion): Result;
}
