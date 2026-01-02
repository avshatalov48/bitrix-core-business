<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\QueueTable;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Orm\Mapper\JobCollectionMapper;

class JobRepository implements JobRepositoryInterface
{
	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getAll(DateTime $from = new DateTime(), int $limit = 500): JobCollection
	{
		$jobCollectionModel = QueueTable::query()
			->setSelect(['ID', 'COLLAB_ID', 'USER_ID', 'TYPE', 'NEXT_EXECUTION', 'CREATED_DATE'])
			->where('IS_PROCESSED', false)
			->where('NEXT_EXECUTION', '<=', $from)
			->setLimit($limit)
			->exec()
			->fetchCollection()
		;

		if ($jobCollectionModel === null)
		{
			return new JobCollection();
		}

		return JobCollectionMapper::convertFromOrm($jobCollectionModel);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByCollabId(int $collabId, DateTime $from = new DateTime()): JobCollection
	{
		$jobCollectionModel = QueueTable::query()
			->setSelect(['ID', 'COLLAB_ID', 'USER_ID', 'TYPE', 'NEXT_EXECUTION', 'CREATED_DATE'])
			->where('COLLAB_ID', $collabId)
			->where('IS_PROCESSED', false)
			->where('NEXT_EXECUTION', '<=', $from)
			->exec()
			->fetchCollection()
		;

		if ($jobCollectionModel === null)
		{
			return new JobCollection();
		}

		return JobCollectionMapper::convertFromOrm($jobCollectionModel);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByUserId(int $userId, DateTime $from = new DateTime()): JobCollection
	{
		$jobCollectionModel = QueueTable::query()
			->setSelect(['ID', 'COLLAB_ID', 'USER_ID', 'TYPE', 'NEXT_EXECUTION', 'CREATED_DATE'])
			->where('USER_ID', $userId)
			->where('IS_PROCESSED', false)
			->where('NEXT_EXECUTION', '<=', $from)
			->exec()
			->fetchCollection()
		;

		if ($jobCollectionModel === null)
		{
			return new JobCollection();
		}

		return JobCollectionMapper::convertFromOrm($jobCollectionModel);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByFilter(array $filter, DateTime $from = new DateTime()): JobCollection
	{
		if (empty($filter))
		{
			return new JobCollection();
		}

		$filter = array_merge([
			'IS_PROCESSED' => false,
			'<=NEXT_EXECUTION' => $from,
		], $filter);

		$jobCollectionModel = QueueTable::query()
			->setSelect(['ID', 'COLLAB_ID', 'USER_ID', 'TYPE', 'NEXT_EXECUTION', 'CREATED_DATE'])
			->setFilter($filter)
			->exec()
			->fetchCollection()
		;

		if ($jobCollectionModel === null)
		{
			return new JobCollection();
		}

		return JobCollectionMapper::convertFromOrm($jobCollectionModel);
	}

	public function add(JobCollection $jobCollection): void
	{
		$jobCollectionModel = JobCollectionMapper::convertToOrm($jobCollection);
		$jobCollectionModel->save(true);
	}

	/**
	 * @throws ArgumentException
	 */
	public function deleteByFilter(array $filter): void
	{
		QueueTable::deleteByFilter($filter);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function markAsProcessing(int ...$commandIds): void
	{
		QueueTable::updateMulti($commandIds, ['IS_PROCESSED' => true, 'PROCESSED_DATE' => new DateTime()]);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function unmarkAsProcessing(int ...$commandIds): void
	{
		QueueTable::updateMulti($commandIds, ['IS_PROCESSED' => false]);
	}
}
