<?php

namespace Bitrix\Catalog\Integration\Report\Handler;

use Bitrix\Main\UI\Filter\Options;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Helper\Filter;
use Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider;

abstract class BaseHandler extends BaseReport
{
	protected static function getAnalyticBoardByKey($key): ?AnalyticBoard
	{
		$boardProvider = new AnalyticBoardProvider();
		$boardProvider->addFilter('boardKey', $key);

		return $boardProvider->execute()->getFirstResult();
	}

	protected function getFilter(): Filter
	{
		static $filter;
		if ($filter)
		{
			return $filter;
		}

		$boardKey = $this->getWidgetHandler()->getWidget()->getBoardId();
		$board = self::getAnalyticBoardByKey($boardKey);
		if ($board)
		{
			$filter = $board->getFilter();
		}
		else
		{
			$filter = new Filter($boardKey);
		}

		return $filter;
	}

	protected function getFilterParameters(): array
	{
		static $filterParameters = [];

		$filter = $this->getFilter();
		$filterId = $filter->getFilterParameters()['FILTER_ID'];

		if (!$filterParameters[$filterId])
		{
			$options = new Options($filterId, $filter::getPresetsList());
			$fieldList = $filter::getFieldsList();
			$filterParameters[$filterId] = $options->getFilter($fieldList);
		}

		return $filterParameters[$filterId];
	}
}
