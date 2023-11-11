<?php

namespace Bitrix\Im\V2\Entity\Department;

use Bitrix\Im\V2\Entity\EntityCollection;

/**
 * @implements \IteratorAggregate<int,Department>
 * @method Department offsetGet($offset)
 * @method Department getById(int $id)
 */
class Departments extends EntityCollection
{
	public function __construct(int ...$departmentIds)
	{
		parent::__construct();

		foreach ($departmentIds as $departmentId)
		{
			$this[] = new Department($departmentId);
		}
	}

	public function filterExist(): self
	{
		$filtered = new static();

		foreach ($this as $department)
		{
			if ($department->isExist())
			{
				$filtered[] = $department;
			}
		}

		return $filtered;
	}

	public function getDeepest(): self
	{
		$maxDepth = 0;

		foreach ($this as $department)
		{
			$maxDepth = max($maxDepth, $department->getDepth());
		}

		$newCollection = new static();

		foreach ($this as $department)
		{
			if ($department->getDepth() === $maxDepth)
			{
				$newCollection[] = $department;
			}
		}

		return $newCollection;
	}

	public static function getRestEntityName(): string
	{
		return 'departments';
	}
}