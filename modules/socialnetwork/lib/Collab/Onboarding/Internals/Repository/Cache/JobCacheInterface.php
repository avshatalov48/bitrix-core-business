<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache;

use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

interface JobCacheInterface
{
	public function getByUserId(int $userId): ?JobCollection;

	/** @param array<int, JobCollection> $sortedByUserJobs */
	public function save(array $sortedByUserJobs): void;

	/** @param array<int, JobCollection> $sortedByUserJobs */
	public function cleanByJobCollection(array $sortedByUserJobs): void;
	public function cleanByUserIds(int ...$userIds): void;
	public function cleanByCollabIds(int ...$collabIds): void;
}
