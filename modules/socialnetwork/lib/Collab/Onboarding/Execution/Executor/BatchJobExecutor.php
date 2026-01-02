<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Execution\Executor;

use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\BatchJobResult;

class BatchJobExecutor
{
	public function execute(JobCollection $jobList): BatchJobResult
	{
		$result = new BatchJobResult();

		foreach ($jobList as $job)
		{
			$executor = new JobExecutor();

			$executor($job, $result);
		}

		return $result;
	}
}
