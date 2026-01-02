<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Orm\Mapper;

use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobFactory;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobModel;

class JobMapper
{
	public static function convertToOrm(AbstractJob $job): JobModel
	{
		$jobModel = (new JobModel())
			->setUserId($job->getUserId())
			->setCollabId($job->getCollabId())
			->setType($job->getType()->value)
			->setNextExecution($job->getNextExecution())
		;

		$jobId = $job->getId();
		if ($jobId !== null)
		{
			$jobModel->setId($jobId);
		}

		return $jobModel;
	}

	public static function convertFromOrm(JobModel $jobModel): ?AbstractJob
	{
		$job = JobFactory::create(
			$jobModel->getCollabId(),
			$jobModel->getUserId(),
			$jobModel->getType(),
		);

		$job
			?->setId($jobModel->getId())
			->setCreatedDate($jobModel->getCreatedDate())
			->setNextExecution($jobModel->getNextExecution())
		;

		return $job;
	}
}