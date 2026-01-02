<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Provider;

use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache\JobCacheInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\JobRepositoryInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator\ArrayOfJobTypesValidator;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator\ArrayOfPositiveNumbersValidator;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache\JobCacheProxy;
use Throwable;

class QueueProvider implements QueueProviderInterface
{
	private JobRepositoryInterface $repository;
	private JobCacheInterface $cache;

	private const AVAILABLE_FILTERS = ['ID', 'USER_ID', 'COLLAB_ID', 'TYPE'];

	public function __construct()
	{
		$this->repository = ServiceLocator::getInstance()->get('socialnetwork.onboarding.job.repository');
		$this->cache = JobCacheProxy::getInstance();
	}

	public function getAll(DateTime $from = new DateTime(), int $limit = 500): JobCollection
	{
		try
		{
			$jobCollection = $this->repository->getAll($from, $limit);
		}
		catch (Throwable $t)
		{
			$jobCollection = new JobCollection();
			$this->logUnknownThrowable($t);
		}

		return $jobCollection;
	}

	public function getByCollabId(int $collabId, DateTime $from = new DateTime()): JobCollection
	{
		if ($collabId <= 0)
		{
			return new JobCollection();
		}

		try
		{
			$jobCollection = $this->repository->getByCollabId($collabId, $from);
		}
		catch (Throwable $t)
		{
			$jobCollection = new JobCollection();
			$this->logUnknownThrowable($t);
		}

		return $jobCollection;
	}

	public function getNotImmediatedByUserId(int $userId, DateTime $from = new DateTime()): JobCollection
	{
		if ($userId <= 0)
		{
			return new JobCollection();
		}

		$jobs = $this->cache->getByUserId($userId);

		if (null === $jobs)
		{
			$jobs = $this->getByUserId($userId, $from)->getNotImmediatelyExecuted();

			$this->cache->save([$userId => $jobs]);
		}

		return $jobs;
	}

	private function getByUserId(int $userId, DateTime $from = new DateTime()): JobCollection
	{
		if ($userId <= 0)
		{
			return new JobCollection();
		}

		try
		{
			$jobCollection = $this->repository->getByUserId($userId, $from);
		}
		catch (Throwable $t)
		{
			$jobCollection = new JobCollection();
			$this->logUnknownThrowable($t);
		}

		return $jobCollection;
	}

	public function getByFilter(array $filter, DateTime $from = new DateTime()): JobCollection
	{
		$filter = $this->prepareFilter($filter);

		if (empty($filter))
		{
			return new JobCollection();
		}

		try
		{
			$jobCollection = $this->repository->getByFilter($filter, $from);
		}
		catch (Throwable $t)
		{
			$jobCollection = new JobCollection();
			$this->logUnknownThrowable($t);
		}

		return $jobCollection;
	}

	private function prepareFilter(array $filter): array
	{
		$availableFilter = [];

		$idsValidator = new ArrayOfPositiveNumbersValidator();
		$typeValidator = new ArrayOfJobTypesValidator();

		foreach (self::AVAILABLE_FILTERS as $key)
		{
			if (empty($filter[$key]))
			{
				continue;
			}

			$isIdsFilter = str_contains($key, 'ID');
			if ($isIdsFilter && !$idsValidator->validate($filter[$key])->isSuccess())
			{
				return [];
			}

			$isTypeFilter = $key === 'TYPE';
			if ($isTypeFilter && !$typeValidator->validate($filter[$key])->isSuccess())
			{
				return [];
			}

			$availableFilter[$key] = $filter[$key];
		}

		return $availableFilter;
	}

	private function logUnknownThrowable(Throwable $t): void
	{
		Application::getInstance()->getExceptionHandler()->writeToLog($t);
	}
}
