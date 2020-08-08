<?php

namespace Bitrix\Sender\Access\Map;

use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Integration\Seo\Ads\MessageBase;

class AdsAction
{
	/**
	 * legacy action map
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			MessageBase::CODE_ADS_YA => ActionDictionary::ACTION_ADS_YANDEX_EDIT,
			MessageBase::CODE_ADS_GA => ActionDictionary::ACTION_ADS_GOOGLE_EDIT,
			MessageBase::CODE_ADS_FB => ActionDictionary::ACTION_ADS_FB_INSTAGRAM_EDIT,
			MessageBase::CODE_ADS_VK => ActionDictionary::ACTION_ADS_VK_EDIT,
			MessageBase::CODE_ADS_LOOKALIKE_FB => ActionDictionary::ACTION_ADS_LOOK_ALIKE_FB_EDIT,
			MessageBase::CODE_ADS_LOOKALIKE_VK => ActionDictionary::ACTION_ADS_LOOK_ALIKE_VK_EDIT,
		];
	}
}