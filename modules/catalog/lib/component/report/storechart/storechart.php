<?php

namespace Bitrix\Catalog\Component\Report\StoreChart;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Currency\CurrencyManager;

abstract class StoreChart extends \CBitrixComponent implements Errorable
{
	use ErrorableImplementation;

	abstract protected function getChartId(): string;
	abstract protected function getInnerChartData(): array;
	abstract protected function fetchAndBuildStoreColumnData(array $stores): array;
	abstract protected function buildLinkToSliderDetails(string $linkContent): string;
	abstract protected function initializeAdditionalData(): void;

	final public function executeComponent()
	{
		$this->errorCollection = new ErrorCollection();

		if (!Loader::includeModule('currency'))
		{
			$this->errorCollection->add([new Error(Loc::getMessage('STORE_CHART_REPORT_NO_CURRENCY_ERROR'))]);
		}
		if (!self::checkDocumentReadRights())
		{
			$this->errorCollection->add([new Error(Loc::getMessage('STORE_CHART_REPORT_NO_READ_RIGHTS_ERROR'))]);
		}

		if (!$this->hasErrors())
		{
			$this->initializeComponent();
		}

		$this->arResult['ERROR_MESSAGES'] = array_map(static function(Error $error) {
			return $error->getMessage();
		}, $this->errorCollection->getValues());

		$this->includeComponentTemplate();
	}

	protected function initializeComponent(): void
	{
		$this->initializeChart();
		$this->initializeAdditionalData();
	}

	protected function initializeChart(): void
	{
		$innerChartData = $this->getInnerChartData();

		$this->arResult['chartData'] = [
			'chartProps' => [
				'id' => $this->getChartId(),
				'detailSliderUrl' => $innerChartData['isCommonChart'] ? $innerChartData['sliderUrl'] : null,
				'stores' => $this->fetchAndBuildStoreColumnData($innerChartData['data'] ?? []),
				'isPopupEnabled' => !$innerChartData['isCommonChart'] || ($innerChartData['storesInfo']['storeCount'] ?? 0) > 0,
				'label' => $this->formChartLabel($innerChartData),
				'isCommonChart' => $innerChartData['isCommonChart'],
				'currency' => [
					'id' => $innerChartData['currency'],
					'symbol' => $this->getCurrencySymbol($innerChartData['currency']),
					'format' => \CCurrencyLang::GetFormatDescription($innerChartData['currency']),
				],
			],
		];
	}

	protected function formChartLabel(array $chartData): string
	{
		if (!$chartData['isCommonChart']  || $chartData['storesInfo']['storeCount'] <= 0)
		{
			return '';
		}

		$storesInfo = $chartData['storesInfo'];
		$storesList = htmlspecialcharsbx($storesInfo['cropStoreNamesList']);

		$totalLinkContent = Loc::getMessage(
			'STORE_CHART_REPORT_STORES_TOTAL',
			['#TOTAL_NUMBER#' => $storesInfo['storeCount']]
		);

		$totalLink = $totalLinkContent;
		if (isset($chartData['sliderUrl']))
		{
			$totalLink = $this->buildLinkToSliderDetails($totalLinkContent);
		}

		return Loc::getMessage(
			'STORE_CHART_REPORT_STORES_LIST_TEMPLATE',
			[
				'#STORES_LIST#' => $storesList,
				'#STORES_TOTAL_LINK#' => $totalLink,
			]
		);
	}


	private function getCurrencySymbol(string $currency): string
	{
		if (CurrencyManager::isCurrencyExist($currency))
		{
			return CurrencyManager::getSymbolList()[$currency];
		}

		$this->errorCollection->add([new Error(Loc::getMessage(
			'STORE_CHART_REPORT_UNDEFINED_CURRENCY_ERROR',
			['#CURRENCY#' => htmlspecialcharsbx($currency)]
		))]);

		return '';
	}

	private static function checkDocumentReadRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}
}