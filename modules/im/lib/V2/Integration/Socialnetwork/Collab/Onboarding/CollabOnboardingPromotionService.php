<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\Socialnetwork\Collab\Onboarding;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\SingletonTrait;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Promotion\Entity\Promotion;
use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Im\V2\Promotion\Internals\DeviceType;
use Bitrix\Im\V2\Promotion\Internals\PromotionType;
use Bitrix\Im\V2\Promotion\Service\PromotionServiceInterface;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Provider\QueueProviderInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Service\AbstractQueueService;

class CollabOnboardingPromotionService implements PromotionServiceInterface
{
	use SingletonTrait;

	private ?QueueProviderInterface $queueProvider = null;
	private ?AbstractQueueService $queueService = null;

	private function __construct()
	{
		if (!OnboardingFeature::isAvailable())
		{
			return;
		}

		$this->queueProvider = ServiceLocator::getInstance()->get('socialnetwork.onboarding.queue.provider');
		$this->queueService = ServiceLocator::getInstance()->get('socialnetwork.onboarding.queue.service');
	}

	public function getActive(DeviceType $type = DeviceType::ALL): PromotionList
	{
		$promotions = new PromotionList();

		if ($type === DeviceType::MOBILE)
		{
			return $promotions;
		}

		$userId = (int)User::getCurrent()->getId();
		if ($userId <= 0)
		{
			return $promotions;
		}

		if (!$this->queueProvider)
		{
			return $promotions;
		}

		$jobCollection = $this->queueProvider->getNotImmediatedByUserId($userId);

		return $jobCollection->toPromotions() ?? $promotions;
	}

	public function getPromotionType(): PromotionType
	{
		return PromotionType::COLLAB;
	}

	public function isCurrentTypePromotion(string $promotionId): bool
	{
		return (bool)preg_match('/^socialnetwork:\w+-collab$/', $promotionId);
	}

	public function markAsViewed(Promotion $promotion): Result
	{
		$result = new Result();

		if (!$this->queueService || !OnboardingFeature::isAvailable())
		{
			return $result;
		}

		$chat = Chat::getInstance((int)$promotion->getChatId());

		$isCollabChat = $chat->getType() === Chat::IM_TYPE_COLLAB;
		$collabId = (int)$chat->getEntityId();

		$jobType = Type::createFromPromotionId($promotion->getId());

		if (!$isCollabChat || $collabId <= 0 || !$jobType)
		{
			return $result;
		}

		$filter = [
			'USER_IDS' => [(int)User::getCurrent()->getId()],
			'COLLAB_IDS' => [$collabId],
			'JOB_TYPES' => [$jobType->value],
		];

		return $this->queueService->deleteByFilter($filter);
	}
}
