<?php

namespace Bitrix\Catalog\Integration\Report\View\StoreProfit;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Handler\StoreProfit\GraphHandler;
use Bitrix\Catalog\Integration\Report\View\ViewRenderable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\LinearGraph;

class StoreProfitGraph extends LinearGraph implements ViewRenderable
{
	public const ENABLE_SORTING = false;
	public const VIEW_KEY = 'store_profit_graph';
	public const MAX_RENDER_REPORT_COUNT = 1;
	public const USE_IN_VISUAL_CONSTRUCTOR = false;

	public function __construct()
	{
		parent::__construct();

		$this->setLabel(Loc::getMessage('STORE_PROFIT_CHART_LABEL'));
		$this->setDraggable(false);
		Extension::load(["catalog.store-chart"]);
	}

	/**
	 * @inheritDoc
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		$result = parent::handlerFinallyBeforePassToView($dataFromReport);

		if (is_array($dataFromReport) && isset($result['valueAxes']))
		{
			foreach ($dataFromReport as $data)
			{
				if (empty($data['items']))
				{
					continue;
				}

				foreach ($data['items'] as $item)
				{
					if ($item['value'] < 0 && $result['valueAxes'][0]['minimum'] > $item['value'])
					{
						$result['valueAxes'][0]['minimum'] = $item['value'];
					}
				}
			}
		}

		if (is_array($result['dataProvider']))
		{
			$result['categoryAxis']['autoGridCount'] = true;
			$result['categoryAxis']['minHorizontalGap'] = 0;
			$result['categoryAxis']['labelFrequency'] = ceil(count($result['dataProvider']) / 10);

			foreach ($result['dataProvider'] as $k => $item)
			{
				if (!isset($result['dataProvider'][$k]['value_1']))
				{
					$result['dataProvider'][$k]['value_1'] = 0;
				}
				if (!isset($result['dataProvider'][$k]['value_2']))
				{
					$result['dataProvider'][$k]['value_2'] = 0;
				}
			}
		}

		if (is_array($result['graphs']))
		{
			$baseCurrency = CurrencyManager::getBaseCurrency();
			foreach ($result['graphs'] as $k => $graph)
			{
				$result['graphs'][$k]["balloonFunction"] = "BX.Catalog.LinearGraphBalloon.renderBalloon";
				$result['graphs'][$k]["balloon"]["borderThickness"] = 0;
				$title = $result['graphs'][$k]['title'];
				$amount = $dataFromReport[$k]['config']['amount'] ?? \CCurrencyLang::CurrencyFormat(0, $baseCurrency);
				$result['graphs'][$k]["title"] = "$title ($amount)";
			}
		}

		$result['legend']['valueText'] = '';
		$result['legend']['align'] = 'center';

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getViewHandler(): BaseHandler
	{
		return new GraphHandler();
	}
}
