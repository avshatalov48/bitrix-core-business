<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Provider;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

interface QueueProviderInterface
{
	public function getAll(DateTime $from = new DateTime(), int $limit = 500): JobCollection;
	public function getNotImmediatedByUserId(int $userId, DateTime $from = new DateTime()): JobCollection;
	public function getByCollabId(int $collabId, DateTime $from = new DateTime()): JobCollection;

	public function getByFilter(array $filter, DateTime $from = new DateTime()): JobCollection;
}
