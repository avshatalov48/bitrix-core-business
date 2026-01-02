<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Execution\Executor;

use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\BatchJobResult;

class JobExecutor
{
	public function __invoke(AbstractJob $job, BatchJobResult $result): void
	{
		$jobResult = $job();

		if ($jobResult->isSuccess())
		{
			$result->addCompletedJob($job);
		}
		else
		{
			$result->addNotCompletedJob($job);

			$result->addErrors($jobResult->getErrors());
		}
	}
}
