<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Service;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\AddJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\DeleteJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\MarkAsProcessingJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\UnmarkAsProcessingJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

class QueueService extends AbstractQueueService
{
	public function add(JobCollection $jobCollection): Result
	{
		return (new AddJobsCommand($jobCollection))->run();
	}

	public function deleteByFilter(array $filter): Result
	{
		return (new DeleteJobsCommand($filter))->run();
	}

	public function markAsProcessing(int ...$jobIds): Result
	{
		return (new MarkAsProcessingJobsCommand(...$jobIds))->run();
	}

	public function unmarkAsProcessing(int ...$jobIds): Result
	{
		return (new UnmarkAsProcessingJobsCommand(...$jobIds))->run();
	}
}