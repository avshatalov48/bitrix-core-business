<?php

namespace Bitrix\Catalog\v2\Section;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Main\Result;

/**
 * Class SectionCollection
 *
 * @package Bitrix\Catalog\v2\Section
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SectionCollection extends BaseCollection
{
	/** @var \Bitrix\Catalog\v2\Section\SectionFactory */
	protected $factory;
	/** @var \Bitrix\Catalog\v2\Section\SectionRepositoryContract */
	protected $repository;

	public function __construct(SectionFactory $factory, SectionRepositoryContract $repository)
	{
		$this->factory = $factory;
		$this->repository = $repository;
	}

	/**
	 * @param array|int[] $values
	 * @return $this
	 */
	public function setValues(array $values): self
	{
		// ToDo recalculate already loaded properties on section modifications?
		$currentValues = $this->getValues();
		$filteredValues = $this->filterValues($values);

		$oldValuesToRemove = array_diff($currentValues, $filteredValues);

		if (!empty($oldValuesToRemove))
		{
			$oldSections = [];

			/** @var \Bitrix\Catalog\v2\Section\Section $item */
			foreach ($this->getIterator() as $item)
			{
				if (in_array($item->getValue(), $oldValuesToRemove, true))
				{
					$oldSections[] = $item;
				}
			}

			$this->remove(...$oldSections);
		}

		$newValuesToAdd = array_diff($filteredValues, $currentValues);

		if (!empty($newValuesToAdd))
		{
			$newSections = [];

			foreach ($newValuesToAdd as $value)
			{
				$newSections[] = $this
					->factory
					->createEntity()
					->setValue($value)
				;
			}

			$this->add(...$newSections);
		}

		return $this;
	}

	public function getValues(): array
	{
		$values = [];

		/** @var \Bitrix\Catalog\v2\Section\Section $item */
		foreach ($this->getIterator() as $item)
		{
			$values[] = $item->getValue();
		}

		return $values;
	}

	private function filterValues(array $values): array
	{
		$filteredValues = [];

		foreach ($values as $value)
		{
			if (is_numeric($value))
			{
				$filteredValues[] = (int)$value;
			}
		}

		return array_unique($filteredValues);
	}

	public function saveInternal(): Result
	{
		$result = new Result();

		if ($this->isChanged())
		{
			$res = $this->repository->save(...$this->getIterator());

			if ($res->isSuccess())
			{
				$this->clearChanged();
			}
			else
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}
}