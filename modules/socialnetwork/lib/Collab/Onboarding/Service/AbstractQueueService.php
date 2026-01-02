<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Service;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

abstract class AbstractQueueService
{
	abstract public function add(JobCollection $jobCollection): Result;

	abstract public function markAsProcessing(int ...$jobIds): Result;
	abstract public function unmarkAsProcessing(int ...$jobIds): Result;

	/**
	 * @param array $filter
	 * @example:
	 * [
	 *    'JOB_IDS' => [1, 2, 3],
	 *    'USER_IDS' => [1, 2, 3],
	 *    'COLLAB_IDS' => [1, 2, 3],
	 * ]
	 * @return Result
	 */
	abstract public function deleteByFilter(array $filter): Result;
	public function deleteByUserIds(int ...$userIds): Result
	{
		return $this->deleteByFilter(['USER_IDS' => $userIds]);
	}

	public function deleteByJobIds(int ...$jobIds): Result
	{
		return $this->deleteByFilter(['JOB_IDS' => $jobIds]);
	}

	public function deleteByCollabIds(int ...$collabIds): Result
	{
		return $this->deleteByFilter(['COLLAB_IDS' => $collabIds]);
	}
}
