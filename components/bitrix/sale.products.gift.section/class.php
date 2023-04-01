<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity\ReferenceField;
use \Bitrix\Iblock\Component\ElementList;
use \Bitrix\Sale\Discount\Gift\RelatedDataTable;
use \Bitrix\Sale\Internals\DiscountTable;

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('SPGS_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class SaleProductsGiftSectionComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(true)->setMultiIblockMode(false)->setPaginationMode(false);
	}

	public function onPrepareComponentParams($params)
	{
		if (empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}

		if (empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		if (!isset($params['PAGE_ELEMENT_COUNT']))
		{
			$params['PAGE_ELEMENT_COUNT'] = 4;
		}

		return parent::onPrepareComponentParams($params);
	}

	protected function checkModules()
	{
		if ($success = parent::checkModules())
		{
			if (!$this->useCatalog || !Main\Loader::includeModule('sale'))
			{
				$success = false;
				$this->abortResultCache();

				if (!$this->useCatalog)
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPGS_CATALOG_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}

				if (!Main\Loader::includeModule('sale'))
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPGS_SALE_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}
			}
		}

		return $success;
	}

	private function getSectionId()
	{
		$sectionId = null;

		$sectionSearch = $this->arParams['SECTION_ID'] > 0 || $this->arParams['SECTION_CODE'] !== '';
		$sectionByItemSearch =
			(isset($this->arParams['SECTION_ELEMENT_ID']) && $this->arParams['SECTION_ELEMENT_ID'] > 0)
			|| (isset($this->arParams['SECTION_ELEMENT_CODE']) && $this->arParams['SECTION_ELEMENT_CODE'] !== '')
		;

		if ($sectionSearch || $sectionByItemSearch)
		{
			if ($sectionSearch)
			{
				$sectionId = $this->arParams['SECTION_ID'] > 0
					? $this->arParams['SECTION_ID']
					: $this->getSectionIdByCode($this->arParams['SECTION_CODE']);
			}
			else
			{
				$sectionId = $this->getSectionIdByElement($this->arParams['SECTION_ELEMENT_ID'], $this->arParams['SECTION_ELEMENT_CODE']);
			}
		}

		return $sectionId;
	}

	/**
	 * Returns list of product ids which will be showed.
	 * @return array
	 */
	protected function getProductIds()
	{
		list($elementIds, $sectionIds) = $this->getGiftData();

		$this->productIds = array_unique(array_merge($elementIds, $this->getElementIdsFromSection(reset($sectionIds))));

		return $this->productIds;
	}

	private function getGiftData()
	{
		$elementIds = array();
		$sectionIds = array();
		$sectionId = $this->getSectionId();

		if (empty($sectionId))
		{
			return array($elementIds, $sectionIds);
		}

		$query = new \Bitrix\Main\Entity\Query(RelatedDataTable::getEntity());
		$query->addFilter('MAIN_PRODUCT_SECTION_ID', $sectionId);

		global $USER;
		$query->addFilter('=DISCOUNT_GROUP.ACTIVE', 'Y');
		$query->addFilter('DISCOUNT_GROUP.GROUP_ID', $USER->getUserGroupArray());

		$referenceField2 = new ReferenceField(
			'D',
			DiscountTable::getEntity(),
			array('=this.DISCOUNT_ID' => 'ref.ID'),
			array('join_type' => 'INNER')
		);
		$query->registerRuntimeField('', $referenceField2);

		$query->addSelect('D.ID', 'ID2');
		$query->addSelect('D.XML_ID', 'XML_ID');
		$query->addSelect('D.LID', 'LID');
		$query->addSelect('D.NAME', 'NAME');
		$query->addSelect('D.PRICE_FROM', 'PRICE_FROM');
		$query->addSelect('D.PRICE_TO', 'PRICE_TO');
		$query->addSelect('D.CURRENCY', 'CURRENCY');
		$query->addSelect('D.DISCOUNT_VALUE', 'DISCOUNT_VALUE');
		$query->addSelect('D.DISCOUNT_TYPE', 'DISCOUNT_TYPE');
		$query->addSelect('D.ACTIVE', 'ACTIVE');
		$query->addSelect('D.SORT', 'SORT');
		$query->addSelect('D.ACTIVE_FROM', 'ACTIVE_FROM');
		$query->addSelect('D.ACTIVE_TO', 'ACTIVE_TO');
		$query->addSelect('D.TIMESTAMP_X', 'TIMESTAMP_X');
		$query->addSelect('D.MODIFIED_BY', 'MODIFIED_BY');
		$query->addSelect('D.DATE_CREATE', 'DATE_CREATE');
		$query->addSelect('D.CREATED_BY', 'CREATED_BY');
		$query->addSelect('D.PRIORITY', 'PRIORITY');
		$query->addSelect('D.LAST_DISCOUNT', 'LAST_DISCOUNT');
		$query->addSelect('D.VERSION', 'VERSION');
		$query->addSelect('D.CONDITIONS_LIST', 'CONDITIONS_LIST');
		$query->addSelect('D.CONDITIONS', 'CONDITIONS');
		$query->addSelect('D.UNPACK', 'UNPACK');
		$query->addSelect('D.ACTIONS_LIST', 'ACTIONS_LIST');
		$query->addSelect('D.ACTIONS', 'ACTIONS');
		$query->addSelect('D.APPLICATION', 'APPLICATION');
		$query->addSelect('D.USE_COUPONS', 'USE_COUPONS');
		$query->addSelect('D.EXECUTE_MODULE', 'EXECUTE_MODULE');

		$discounts = array();
		$dbResult = $query->exec();

		while ($row = $dbResult->fetch())
		{
			$row['ID'] = $row['ID2'];
			unset($row['ID2']);
			$discounts[$row['ID']] = $row;

			list($productElementIds, $productSectionIds) = Bitrix\Sale\Discount\Gift\RelatedDataTable::getGiftsData($discounts[$row['ID']]);
			$elementIds = array_merge($elementIds, $productElementIds);
			$sectionIds = array_merge($sectionIds, $productSectionIds);
		}

		return array(array_unique($elementIds), array_unique($sectionIds));
	}

	private function getElementIdsFromSection($sectionId)
	{
		if (empty($sectionId))
		{
			return array();
		}

		$ids = array();
		$query = CIBlockElement::getList(
			array(),
			array(
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'SECTION_ID' => $sectionId,
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			),
			false,
			array('nTopCount' => $this->arParams['PAGE_ELEMENT_COUNT']),
			array('ID')
		);
		while ($row = $query->fetch())
		{
			$ids[] = $row['ID'];
		}

		return $ids;
	}

	protected function processProducts()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processProducts();

		foreach ($this->elementLinks as &$element)
		{
			if (!empty($element['ITEM_PRICES']))
			{
				$this->setGiftDiscountToMinPrice($element);
			}
		}
		unset($element);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	protected function processOffers()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processOffers();

		foreach ($this->elementLinks as &$item)
		{
			if (!isset($item['OFFERS']))
			{
				continue;
			}

			foreach ($item['OFFERS'] as &$offer)
			{
				if (!empty($offer['ITEM_PRICES']))
				{
					$this->setGiftDiscountToMinPrice($offer);
				}
			}
			unset($offer);
		}
		unset($item);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * @param array $item
	 */
	protected function setGiftDiscountToMinPrice(array &$item)
	{
		$selectedPrice =& $item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED']];

		$selectedPrice['PRICE'] = $selectedPrice['DISCOUNT'];
		$selectedPrice['PRINT_PRICE'] = $selectedPrice['PRINT_DISCOUNT'];
		$selectedPrice['DISCOUNT'] = $selectedPrice['BASE_PRICE'];
		$selectedPrice['PRINT_DISCOUNT'] = $selectedPrice['PRINT_BASE_PRICE'];
		$selectedPrice['RATIO_PRICE'] = $selectedPrice['RATIO_DISCOUNT'];
		$selectedPrice['PRINT_RATIO_PRICE'] = $selectedPrice['PRINT_RATIO_DISCOUNT'];
		$selectedPrice['RATIO_DISCOUNT'] = $selectedPrice['RATIO_BASE_PRICE'];
		$selectedPrice['PRINT_RATIO_DISCOUNT'] = $selectedPrice['PRINT_RATIO_BASE_PRICE'];
		$selectedPrice['PERCENT'] = 100;
	}
}