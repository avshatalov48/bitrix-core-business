<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity;

use ArrayIterator;
use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Validation\Rule\ElementsType;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Converter\PromotionListConverter;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator\JobDataValidator;
use IteratorAggregate;

final class JobCollection implements IteratorAggregate, Arrayable
{
	#[ElementsType(className: AbstractJob::class)]
	#[Validatable(true)]
	private array $jobs;
	private ?ArrayIterator $iterator = null;

	public function __construct(?AbstractJob ...$jobs)
	{
		$this->jobs = array_filter(
			$jobs,
			static fn($job) => $job !== null
		);
	}

	public function add(AbstractJob $job): self
	{
		$this->jobs[] = $job;

		return $this;
	}

	public function merge(JobCollection $jobCollection): self
	{
		foreach ($jobCollection as $job)
		{
			$this->jobs[] = $job;
		}

		return $this;
	}

	public function cleanIntersectingJobs(self $jobCollection): self
	{
		$filteredIds = array_flip($jobCollection->getIdList());

		$this->jobs = array_filter(
			$this->jobs,
			static fn(AbstractJob $job) => !isset($filteredIds[$job->getId()])
		);

		return $this;
	}

	public function getImmediatelyExecuted(): self
	{
		return $this->getByImmediately(true);
	}

	public function getNotImmediatelyExecuted(): self
	{
		return $this->getByImmediately(false);
	}

	private function getByImmediately(bool $isImmediately): self
	{
		$jobCollection = new self();

		foreach ($this->jobs as $job)
		{
			if ($isImmediately === $job->isImmediatelyExecuted())
			{
				$jobCollection->add($job);
			}
		}

		return $jobCollection;
	}

	/**
	 * @return string[]
	 */
	public function getJobTypeList(): array
	{
		$types = [];
		foreach ($this->jobs as $job)
		{
			$types[] = $job->getType()->value;
		}

		return array_unique($types);
	}

	/**
	 * @return int[]
	 */
	public function getUserIdList(): array
	{
		$ids = [];
		foreach ($this->jobs as $job)
		{
			$ids[] = $job->getUserId();
		}

		Collection::normalizeArrayValuesByInt($ids, false);

		return $ids;
	}

	/**
	 * @return int[]
	 */
	public function getCollabIdList(): array
	{
		$ids = [];
		foreach ($this->jobs as $job)
		{
			$ids[] = $job->getCollabId();
		}

		Collection::normalizeArrayValuesByInt($ids, false);

		return $ids;
	}

	/**
	 * @return int[]
	 */
	public function getIdList(): array
	{
		$ids = [];
		foreach ($this->jobs as $job)
		{
			$ids[] = $job->getId();
		}

		Collection::normalizeArrayValuesByInt($ids, false);

		return $ids;
	}

	public function isEmpty(): bool
	{
		return empty($this->jobs);
	}

	public function getIterator(): ArrayIterator
	{
		if ($this->iterator === null)
		{
			$this->iterator = new ArrayIterator($this->jobs);
		}

		return $this->iterator;
	}

	/** @return array<int, JobCollection> */
	public function sortByUserId(): array
	{
		$sorted = [];
		foreach ($this->jobs as $job)
		{
			$userId = $job->getUserId();

			if (!isset($sorted[$userId]))
			{
				$sorted[$userId] = new JobCollection();
			}

			$sorted[$userId]->add($job);
		}

		return $sorted;
	}

	public static function mapFromArray(array $jobCollectionData): self
	{
		$jobCollection = new self();

		$jobDataValidator = new JobDataValidator();
		foreach ($jobCollectionData as $jobData)
		{
			if (!$jobDataValidator->validate($jobData)->isSuccess())
			{
				continue;
			}

			$job = JobFactory::create($jobData['collabId'], $jobData['userId'], $jobData['type'])
				?->setId($jobData['id'])
				->setCreatedDate($jobData['createdDate'])
				->setNextExecution($jobData['nextExecution'])
			;

			$jobCollection->add($job);
		}

		return $jobCollection;
	}

	public function toArray(): array
	{
		return array_map(
			static fn(AbstractJob $job) => $job->toArray(),
			$this->jobs
		);
	}

	public function toPromotions(): ?PromotionList
	{
		return PromotionListConverter::getInstance()
			->convertJobCollection($this)
		;
	}
}
