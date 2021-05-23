<?
use \Bitrix\Main;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\Component\ElementList;
use \Bitrix\Catalog;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogProductsViewedComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(true)->setMultiIblockMode(true)->setPaginationMode(false);
	}

	public function onPrepareComponentParams($params)
	{
		$params['PRODUCT_DISPLAY_MODE'] = isset($params['PRODUCT_DISPLAY_MODE']) && $params['PRODUCT_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';
		$params['IBLOCK_MODE'] = isset($params['IBLOCK_MODE']) && $params['IBLOCK_MODE'] === 'multi' ? 'multi' : 'single';

		if ($params['IBLOCK_MODE'] === 'single' && (int)$params['IBLOCK_ID'] > 0)
		{
			$params['SHOW_PRODUCTS'] = array((int)$params['IBLOCK_ID'] => true);
		}

		$params = parent::onPrepareComponentParams($params);

		if ($params['PAGE_ELEMENT_COUNT'] <= 0)
		{
			$params['PAGE_ELEMENT_COUNT'] = 9;
		}

		return $params;
	}

	protected function checkModules()
	{
		if ($success = parent::checkModules())
		{
			if (!$this->useCatalog)
			{
				$this->abortResultCache();
				$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				$success = false;
			}
		}

		return $success;
	}

	protected function getProductIds()
	{
		if (!Main\Loader::includeModule('sale'))
		{
			return array();
		}

		$skipUserInit = false;
		if (!Catalog\Product\Basket::isNotCrawler())
			$skipUserInit = true;

		$basketUserId = (int)CSaleBasket::GetBasketUserID($skipUserInit);
		if ($basketUserId <= 0)
		{
			return array();
		}

		if ($this->arParams['IBLOCK_MODE'] === 'single')
		{
			$ids = array_values(Catalog\CatalogViewedProductTable::getProductSkuMap(
				$this->arParams['IBLOCK_ID'],
				$this->arParams['SECTION_ID'],
				$basketUserId,
				$this->arParams['SECTION_ELEMENT_ID'],
				$this->arParams['PAGE_ELEMENT_COUNT'],
				$this->arParams['DEPTH']
			));
		}
		else
		{
			$ids = array();
			$filter = array(
				'=FUSER_ID' => $basketUserId,
				'=SITE_ID' => $this->getSiteId()
			);

			if ($this->arParams['SECTION_ELEMENT_ID'] > 0)
			{
				$filter['!=ELEMENT_ID'] = $this->arParams['SECTION_ELEMENT_ID'];
			}

			$viewedIterator = Catalog\CatalogViewedProductTable::getList(array(
				'select' => array('ELEMENT_ID'),
				'filter' => $filter,
				'order' => array('DATE_VISIT' => 'DESC'),
				'limit' => $this->arParams['PAGE_ELEMENT_COUNT'] * 10
			));
			while ($viewedProduct = $viewedIterator->fetch())
			{
				$ids[] = (int)$viewedProduct['ELEMENT_ID'];
			}

			$this->filterFields = $this->getFilter();
			$this->filterFields['IBLOCK_ID'] = array_keys($this->arParams['SHOW_PRODUCTS']);
			$this->prepareElementQueryFields();

			$ids = array_slice($this->filterByParams($ids, array(), false), 0, $this->arParams['PAGE_ELEMENT_COUNT']);
		}

		return $ids;
	}
}