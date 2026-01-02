<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Type\DeleteJobsCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache\JobCacheInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\JobRepositoryInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache\JobCacheProxy;
use Throwable;

class DeleteJobCollectionHandler
{
	private JobRepositoryInterface $repository;
	private JobCacheInterface $cache;

	public function __construct()
	{
		$this->repository = ServiceLocator::getInstance()->get('socialnetwork.onboarding.job.repository');
		$this->cache = JobCacheProxy::getInstance();
	}

	public function __invoke(DeleteJobsCommand $command): Result
	{
		$filter = [];

		if (!empty($command->jobIds))
		{
			$filter['ID'] = $command->jobIds;
		}

		if (!empty($command->jobTypes))
		{
			$filter['TYPE'] = $command->jobTypes;
		}

		if (!empty($command->userIds))
		{
			$filter['USER_ID'] = $command->userIds;
		}

		if (!empty($command->collabIds))
		{
			$filter['COLLAB_ID'] = $command->collabIds;
		}

		$result = new Result();
		if (empty($filter))
		{
			return $result;
		}

		try
		{
			$this->cleanCacheByFilter($filter);
			$this->repository->deleteByFilter($filter);
		}
		catch (Throwable $t)
		{
			$result->addError(Error::createFromThrowable($t));
		}

		return $result;
	}

	private function cleanCacheByFilter(array $filter): void
	{
		$isSingleFilter = count($filter) === 1;

		if ($isSingleFilter && isset($filter['USER_ID']))
		{
			$this->cache->cleanByUserIds(...$filter['USER_ID']);

			return;
		}

		if ($isSingleFilter && isset($filter['COLLAB_ID']))
		{
			$this->cache->cleanByCollabIds(...$filter['COLLAB_ID']);

			return;
		}

		$jobProvider = ServiceLocator::getInstance()->get('socialnetwork.onboarding.queue.provider');
		$jobCollection = $jobProvider->getByFilter($filter);

		if (!$jobCollection->isEmpty())
		{
			$this->cache->cleanByJobCollection($jobCollection->sortByUserId());
		}
	}
}
