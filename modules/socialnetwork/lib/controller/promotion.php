<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Promotion\AbstractPromotion;
use Bitrix\Socialnetwork\Promotion\PromotionFactory;
use Bitrix\Socialnetwork\Promotion\PromotionType;

class Promotion extends Controller
{
	/**
	 * @throws BinderArgumentException
	 */
	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				AbstractPromotion::class,
				'promotion',
				function($className, $promotion): ?AbstractPromotion {
					$promotionType = PromotionType::tryFrom($promotion);

					if (!$promotionType)
					{
						$this->addError(new Error('Unknown promotion type'));

						return null;
					}

					return (new PromotionFactory())->getByPromotionType($promotionType);
				},
			),
		];
	}

	public function setViewedAction(AbstractPromotion $promotion): bool
	{
		$userId = (int)$this->getCurrentUser()?->getId();

		if ($userId <= 0)
		{
			return false;
		}

		return $promotion->setViewed($userId);
	}
}