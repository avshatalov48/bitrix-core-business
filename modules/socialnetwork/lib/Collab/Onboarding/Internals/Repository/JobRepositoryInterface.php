<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

interface JobRepositoryInterface
{
	public function add(JobCollection $jobCollection): void;
	public function deleteByFilter(array $filter): void;

	public function markAsProcessing(int ...$commandIds): void;
	public function unmarkAsProcessing(int ...$commandIds): void;

	public function getAll(DateTime $from = new DateTime(), int $limit = 500): JobCollection;
	public function getByCollabId(int $collabId, DateTime $from = new DateTime()): JobCollection;
	public function getByUserId(int $userId, DateTime $from = new DateTime()): JobCollection;

	public function getByFilter(array $filter, DateTime $from = new DateTime()): JobCollection;
}
