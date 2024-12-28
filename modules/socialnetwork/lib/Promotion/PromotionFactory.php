<?php

namespace Bitrix\Socialnetwork\Promotion;

class PromotionFactory
{
	public function getByPromotionType(PromotionType $type): AbstractPromotion
	{
		return match ($type)
		{
			PromotionType::FEED_AI => new FeedAi(),
			PromotionType::CHAT_AI => new ChatAi(),
		};
	}
}