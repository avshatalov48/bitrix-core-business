<?php
namespace Bitrix\Landing\Source;

use \Bitrix\Landing\Internals\FilterEntityTable;

class FilterEntity extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'FilterEntityTable';

	/**
	 * Gets selector object.
	 * @return Selector
	 */
	protected static function getSourceSelector()
	{
		static $sourceList = null;

		if ($sourceList === null)
		{
			$sourceList = new Selector();
		}

		return $sourceList;
	}

	/**
	 * Gets filter row by id.
	 * @param int $filterId Filter id.
	 * @return array
	 */
	public static function getFilter($filterId)
	{
		$filter = [];
		$filterId = intval($filterId);

		if ($filterId)
		{
			$select = ['SOURCE_ID', 'FILTER', 'FILTER_HASH'];
			$res = self::getList([
				'select' => $select,
				'filter' => [
					'ID' => $filterId
				]
			]);
			if ($row = $res->fetch())
			{
				$filter = $row;
			}
			unset($res, $row);
			$filter = array_merge(
				array_fill_keys($select, null),
				$filter
			);
		}

		return $filter;
	}

	/**
	 * Store the filter for the block.
	 * @param int $blockId Block id.
	 * @param array $sourceParams Source params for this block.
	 * @return void
	 */
	public static function setFilter($blockId, array &$sourceParams = [])
	{
		$sourceList = self::getSourceSelector();

		foreach ($sourceParams as $selector => &$item)
		{
			$item['filterId'] = 0;
			if (isset($item['source']))
			{
				$sourceId = trim($item['source']);
				$sourceFilter = isset($item['settings']['source']['filter'])
								? $item['settings']['source']['filter']
								: [];
				// build source by id
				$source = $sourceList->getDataLoader(
					$sourceId,
					[]
				);
				if (!is_object($source))
				{
					return;
				}
				// normalize and hash the filter
				$sourceFilter = $source->normalizeFilter(
					$sourceFilter
				);
				$hashFilter = $source->getFilterHash(
					$sourceFilter
				);
				// add new entity if not exist
				$filterId = 0;
				$res = self::getList([
					'select' => [
						'ID'
					],
					'filter' => [
						'=FILTER_HASH' => $hashFilter
					]
				]);
				if ($row = $res->fetch())
				{
					$filterId = $row['ID'];
				}
				else
				{
					$res = self::add([
						'SOURCE_ID' => $sourceId,
    					'FILTER_HASH' => $hashFilter,
    					'FILTER' => $sourceFilter
				  	]);
					if ($res->isSuccess())
					{
						$filterId = $res->getId();
					}
				}
				if ($filterId)
				{
					FilterEntityTable::applyBlock($filterId, $blockId);
					$item['filterId'] = $filterId;
				}
				unset($sourceFilter, $hashFilter, $res, $row);
			}
		}
		unset($sourceList, $selector, $item);

		if (!$sourceParams)
		{
			self::removeBlock($blockId);
		}
	}

	/**
	 * Remove the block from all filters.
	 * @param int $blockId Block id.
	 * @return void
	 */
	public static function removeBlock($blockId)
	{
		FilterEntityTable::removeBlock($blockId);
	}
}