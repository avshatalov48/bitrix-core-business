<?php

namespace Bitrix\Main\Search;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Engine;
use Bitrix\Main\UI\PageNavigation;

abstract class SearchAction extends Engine\Action
{
	final public function run($searchQuery, array $options = null, PageNavigation $pageNavigation = null)
	{
		$searchQuery = $this->prepareSearchQuery($searchQuery);
		if (!$searchQuery)
		{
			return [
				'items' => [],
				'limits' => [],
			];
		}

		$resultItems = $this->provideData($searchQuery, $options, $pageNavigation);
		if (!is_array($resultItems) && !($resultItems instanceof \Traversable))
		{
			throw new ArgumentTypeException('The method ::provideData() has to return iterable data');
		}

		foreach ($resultItems as $item)
		{
			$this->adjustResultItem($item);
		}

		$limits = $this->provideLimits($searchQuery, $options);
		if (!is_array($limits) && !($limits instanceof \Traversable))
		{
			throw new ArgumentTypeException('The method ::provideLimits() has to return iterable data');
		}

		return [
			'items' => $resultItems,
			'limits' => $limits,
		];
	}

	final protected function adjustResultItem(ResultItem $item)
	{
		if (!$item->getModule())
		{
			$item->setModule($this->getController()->getModuleId());
		}

		return $item;
	}

	protected function prepareSearchQuery($searchQuery)
	{
		return trim($searchQuery);
	}

	protected function provideLimits($searchQuery, array $options = null)
	{
		return [];
	}

	/**
	 * Provides search results.
	 *
	 * @param string $searchQuery
	 * @param array|null $options
	 * @param PageNavigation|null $pageNavigation
	 *
	 * @return ResultItem[]
	 */
	abstract function provideData($searchQuery, array $options = null, PageNavigation $pageNavigation = null);
}