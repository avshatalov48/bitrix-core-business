<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2021 Bitrix
 */

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\IdentityMap;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;

/**
 * Class helps to handle Query with relations
 *
 * @package    bitrix
 * @subpackage main
 */
class QueryHelper
{
	/**
	 * Decomposition for Queries with 1:N and N:M relations
	 *
	 * @param Query $query
	 * @param bool $fairLimit Option to select only ID first, then other data
	 * @param bool $separateRelations Option to separate 1:N and N:M relations
	 * @return Collection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function decompose(Query $query, $fairLimit = true, $separateRelations = true)
	{
		$entity = $query->getEntity();
		$queryClass = $entity->getDataClass()::getQueryClass();
		$runtimeChains = $query->getRuntimeChains() ?? [];
		$primaryNames = $entity->getPrimaryArray();
		$originalSelect = $query->getSelect();

		if ($fairLimit)
		{
			// select distinct primary
			$query->setSelect($entity->getPrimaryArray());
			$query->setDistinct();

			$rows = $query->fetchAll();

			// return empty result
			if (empty($rows))
			{
				return $query->getEntity()->createCollection();
			}

			// reset query
			$query = new $queryClass($entity);
			$query->setSelect($originalSelect);
			$query->where(static::getPrimaryFilter($primaryNames, $rows));

			foreach ($runtimeChains as $chain)
			{
				$query->registerChain('runtime', $chain);
			}
		}

		// more than one OneToMany or ManyToMany
		if ($separateRelations)
		{
			$commonSelect = [];
			$dividedSelect = [];

			foreach ($originalSelect as $selectItem)
			{
				// init query with select item
				$selQuery = new $queryClass($entity);
				$selQuery->addSelect($selectItem);
				$selQuery->getQuery(true);

				// check for relations
				foreach ($selQuery->getChains() as $chain)
				{
					if ($chain->hasBackReference())
					{
						$dividedSelect[] = $selectItem;
						continue 2;
					}
				}

				$commonSelect[] = $selectItem;
			}

			if (empty($commonSelect))
			{
				$commonSelect = $query->getEntity()->getPrimaryArray();
			}

			// common query
			$query->setSelect($commonSelect);
		}

		/** @var Collection $collection query data */
		$collection = $query->fetchCollection();

		// original sort
		if (!empty($rows))
		{
			$sortedCollection = $query->getEntity()->createCollection();

			foreach ($rows as $row)
			{
				$sortedCollection->add($collection->getByPrimary($row));
			}

			$collection = $sortedCollection;
		}

		if (!empty($dividedSelect) && $collection->count())
		{
			// custom identity map & collect primaries
			$im = new IdentityMap;
			$primaryValues = [];

			foreach ($collection as $object)
			{
				$im->put($object);

				$primaryValues[] = $object->primary;
			}

			$primaryFilter = static::getPrimaryFilter($primaryNames, $primaryValues);

			// select relations
			foreach ($dividedSelect as $selectItem)
			{
				$relQuery = (new $queryClass($entity))
					->addSelect($selectItem)
					->where($primaryFilter);

				foreach ($runtimeChains as $chain)
				{
					$relQuery->registerChain('runtime', $chain);
				}

				$result = $relQuery->exec();

				$result->setIdentityMap($im);
				$result->fetchCollection();
			}
		}

		return $collection;
	}

	/**
	 * @param array $primaryNames
	 * @param array $primaryValues
	 * @return ConditionTree
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getPrimaryFilter($primaryNames, $primaryValues)
	{
		$commonSubFilter = new ConditionTree();

		if (count($primaryNames) === 1)
		{
			$values = [];

			foreach ($primaryValues as $row)
			{
				$values[] = $row[$primaryNames[0]];
			}

			$commonSubFilter->whereIn($primaryNames[0], $values);
		}
		else
		{
			$commonSubFilter->logic('or');

			foreach ($primaryValues as $row)
			{
				$primarySubFilter = new ConditionTree();

				foreach ($primaryNames as $primaryName)
				{
					$primarySubFilter->where($primaryName, $row[$primaryName]);
				}
			}
		}

		return $commonSubFilter;
	}
}
