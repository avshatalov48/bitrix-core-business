<?php

namespace Bitrix\Sale\Integration\Report;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\ReportTable;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;

class EventHandler
{
	const BATCH_INTERNET_SHOP = 'sale_internet_shop';
	const REPORT_KEY = 'sale_report_board_';
	const REPORT_VIEW_URL = '/shop/settings/sale_report_view.php';

	/**
	 * @return AnalyticBoardBatch[]
	 */
	public static function onAnalyticPageBatchCollect()
	{
		$batchList = [];
		if(!\CBXFeatures::IsFeatureEnabled('SaleReports'))
		{
			return $batchList;
		}

		$batch = new AnalyticBoardBatch();
		$batch->setKey(static::BATCH_INTERNET_SHOP);
		$batch->setTitle(Loc::getMessage("SALE_REPORT_INTERNET_SHOP_BATCH_TITLE"));
		$batch->setOrder(300);
		$batchList[] = $batch;

		return $batchList;
	}

	/**
	 * @return AnalyticBoard[]
	 */
	public static function onAnalyticPageCollect()
	{
		$analyticPageList = [];
		if(!\CBXFeatures::IsFeatureEnabled('SaleReports'))
		{
			return $analyticPageList;
		}

		\CBaseSaleReportHelper::initOwners();

		$cursor = ReportTable::getList([
			'select' => ['ID', 'TITLE'],
			'filter' => [
				'=CREATED_BY' => static::getCurrentUserId(),
				'=OWNER_ID' => \CBaseSaleReportHelper::getOwners()
			]
		]);

		while ($row = $cursor->fetch())
		{
			$reportPage = new AnalyticBoard();
			$reportPage->setTitle($row['TITLE']);
			$reportPage->setBoardKey(static::REPORT_KEY . $row['ID']);
			$reportPage->setBatchKey(static::BATCH_INTERNET_SHOP);
			$reportPage->setExternal(true);

			$reportViewUrl = static::REPORT_VIEW_URL;
			$reportViewUrl = \CHTTP::urlAddParams($reportViewUrl, [
				'ID' => $row['ID'],
				'publicSidePanel' => 'Y'
			]);
			$reportPage->setExternalUrl($reportViewUrl);

			$analyticPageList[] = $reportPage;
		}

		return $analyticPageList;
	}

	public static function getCurrentUserId()
	{
		global $USER;
		return $USER->getId();
	}
}