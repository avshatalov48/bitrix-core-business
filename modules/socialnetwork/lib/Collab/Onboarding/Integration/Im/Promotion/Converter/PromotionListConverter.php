<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Converter;

use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Helper\SingletonTrait;

class PromotionListConverter
{
	use SingletonTrait;

	private PromotionConverter $promoConverter;

	private function __construct()
	{
		$this->promoConverter = PromotionConverter::getInstance();
	}

	public function convertJobCollection(JobCollection $jobCollection): ?PromotionList
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$promoList = new PromotionList();
		foreach ($jobCollection as $job)
		{
			$promo = $this->promoConverter->convertJob($job);

			if (null !== $promo)
			{
				$promoList->add($promo);
			}
		}

		return $promoList;
	}

	public function convertOnboardingTypes(Type ...$typeList): ?PromotionList
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$promoList = new PromotionList();
		foreach ($typeList as $type)
		{
			$promo = $this->promoConverter->convertOnboardingType($type);

			if (null !== $promo)
			{
				$promoList->add($promo);
			}
		}

		return $promoList;
	}
}
