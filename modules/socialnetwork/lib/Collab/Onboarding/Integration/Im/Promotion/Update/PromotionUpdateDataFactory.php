<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Update;

use Bitrix\Im\V2\Promotion\Event\Update\PromotionUpdateData;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Helper\SingletonTrait;

class PromotionUpdateDataFactory
{
	use SingletonTrait;

	/**
	 * @throws LoaderException
	 */
	public function create(JobCollection $jobs): ?PromotionUpdateData
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$addedPromotions = $jobs->toPromotions();
		if (null === $addedPromotions)
		{
			return null;
		}

		return new PromotionUpdateData($addedPromotions, Type::toPromotions());
	}
}
