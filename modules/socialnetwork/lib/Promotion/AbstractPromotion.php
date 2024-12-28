<?php

namespace Bitrix\Socialnetwork\Promotion;

abstract class AbstractPromotion
{
	abstract public function getPromotionType(): PromotionType;
	abstract public function shouldShow(int $userId): bool;

	public function setViewed(int $userId): bool
	{
		return
			\CUserOptions::SetOption(
				'socialnetwork',
				$this->getOptionName(),
				true,
				false,
				$userId
			);
	}

	protected function getOptionName(): string
	{
		return 'promo_' . $this->getPromotionType()->value . '_is_viewed';
	}

	protected function isViewed(int $userId): bool
	{
		$option = \CUserOptions::GetOption('socialnetwork', $this->getOptionName(), false, $userId);

		return $option === true;
	}
}