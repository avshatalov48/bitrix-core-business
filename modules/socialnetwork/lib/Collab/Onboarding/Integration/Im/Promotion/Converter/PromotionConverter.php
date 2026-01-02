<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Converter;

use Bitrix\Im\V2\Promotion\Entity\Promotion;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Helper\SingletonTrait;

class PromotionConverter
{
	use SingletonTrait;

	public function convertJob(AbstractJob $job): ?Promotion
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$chatId = $job->getChatId();
		if (null === $chatId)
		{
			return null;
		}

		$promo = $this->convertOnboardingType($job->getType());

		return $promo?->setChatId($chatId);
	}

	public function convertOnboardingType(Type $type): ?Promotion
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$promoId = "socialnetwork:{$type->value}-collab";

		return new Promotion($promoId);
	}

	public function convertToOnboardingType(string $promoId): ?Type
	{
		preg_match('/^socialnetwork:(\w+)-collab$/', $promoId, $matches);
		$type = $matches[1] ?? '';

		return Type::tryFrom($type);
	}
}
