<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\UnmarkAsProcessingJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\JobRepositoryInterface;
use Throwable;

class UnmarkAsProcessingJobHandler
{
	private JobRepositoryInterface $repository;

	public function __construct()
	{
		$this->repository = ServiceLocator::getInstance()->get('socialnetwork.onboarding.job.repository');
	}

	public function __invoke(UnmarkAsProcessingJobsCommand $command): Result
	{
		$result = new Result();

		if (empty($command->jobIds))
		{
			return $result;
		}

		try
		{
			$this->repository->unmarkAsProcessing(...$command->jobIds);
		}
		catch (Throwable $t)
		{
			$result->addError(Error::createFromThrowable($t));
		}

		return $result;
	}
}
