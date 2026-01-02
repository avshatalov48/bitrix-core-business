<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Orm\Mapper;

use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobCollectionModel;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobModel;

class JobCollectionMapper
{
	public static function convertToOrm(JobCollection $jobCollection): JobCollectionModel
	{
		$jobCollectionModel = new JobCollectionModel();

		/**
		 * @var AbstractJob $job
		 */
		foreach ($jobCollection as $job)
		{
			$jobModel = JobMapper::convertToOrm($job);

			$jobCollectionModel->add($jobModel);
		}

		return $jobCollectionModel;
	}

	public static function convertFromOrm(JobCollectionModel $jobCollectionModel): JobCollection
	{
		$jobCollection = new JobCollection();

		/**
		 * @var JobModel $jobModel
		 */
		foreach ($jobCollectionModel as $jobModel)
		{
			$job = JobMapper::convertFromOrm($jobModel);
			if ($job === null)
			{
				continue;
			}

			$jobCollection->add($job);
		}

		return $jobCollection;
	}
}