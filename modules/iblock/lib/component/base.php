<?php
namespace Bitrix\Iblock\Component;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency;
use Bitrix\Iblock;
use Bitrix\Catalog;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

abstract class Base extends \CBitrixComponent
{
	public const ACTION_BUY = 'BUY';
	public const ACTION_ADD_TO_BASKET = 'ADD2BASKET';
	public const ACTION_SUBSCRIBE = 'SUBSCRIBE_PRODUCT';
	public const ACTION_ADD_TO_COMPARE = 'ADD_TO_COMPARE_LIST';
	public const ACTION_DELETE_FROM_COMPARE = 'DELETE_FROM_COMPARE_LIST';

	public const ERROR_TEXT = 1;
	public const ERROR_404 = 2;

	public const PARAM_TITLE_MASK = '/^[A-Za-z_][A-Za-z01-9_]*$/';
	public const SORT_ORDER_MASK = '/^(asc|desc|nulls)(,asc|,desc|,nulls)?$/i';

	private $action = '';
	private $cacheUsage = true;
	private $extendedMode = true;
	/** @var ErrorCollection */
	protected $errorCollection;

	protected $separateLoading = false;

	protected $selectFields = array();
	protected $filterFields = array();
	protected $sortFields = array();

	/** @var array Array of ids to show directly */
	protected $productIds = array();

	protected $productIdMap = array();
	protected $iblockProducts = array();
	protected $elements = array();
	protected $elementLinks = array();

	protected $productWithOffers = array();
	protected $productWithPrices = array();

	protected $globalFilter = array();
	protected $navParams = false;

	protected $useCatalog = false;
	protected $isIblockCatalog = false;
	protected $useDiscountCache = false;

	/** @var bool Fill old format $arResult and enable deprecated functionality for existing components (catalog.section, catalog.element, etc) */
	protected $compatibleMode = false;

	protected $oldData = array();
	/** @var array Item prices (new format) */
	protected $prices = array();
	protected $calculatePrices = array();

	protected $measures = array();
	protected $ratios = array();
	protected $quantityRanges = array();

	protected $storage = array();
	protected $recommendationIdToProduct = array();

	/**
	 * Base constructor.
	 * @param \CBitrixComponent|null $component		Component object if exists.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	/**
	 * Return current action.
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Action setter.
	 *
	 * @param string $action		Action code.
	 * @return void
	 */
	protected function setAction($action)
	{
		$this->action = $action;
	}

	/**
	 * Return true if errors exist.
	 *
	 * @return bool
	 */
	protected function hasErrors()
	{
		return (bool)count($this->errorCollection);
	}

	/**
	 * Errors processing depending on error codes.
	 *
	 * @return bool
	 */
	protected function processErrors()
	{
		if (!empty($this->errorCollection))
		{
			/** @var Error $error */
			foreach ($this->errorCollection as $error)
			{
				$code = $error->getCode();

				if ($code == self::ERROR_404)
				{
					Tools::process404(
						trim($this->arParams['MESSAGE_404']) ?: $error->getMessage(),
						true,
						$this->arParams['SET_STATUS_404'] === 'Y',
						$this->arParams['SHOW_404'] === 'Y',
						$this->arParams['FILE_404']
					);
				}
				elseif ($code == self::ERROR_TEXT)
				{
					ShowError($error->getMessage());
				}
			}
		}

		return false;
	}

	/**
	 * Cache usage setter. Enable it to ignore cache.
	 *
	 * @param bool $state	Cache usage mode.
	 * @return $this
	 */
	protected function setCacheUsage($state)
	{
		$this->cacheUsage = (bool)$state;

		return $this;
	}

	/**
	 * Check if cache disabled.
	 *
	 * @return bool
	 */
	public function isCacheDisabled()
	{
		return !$this->cacheUsage;
	}

	/**
	 * Extended mode setter.
	 * Enabled - adds result_modifier.php template logic in component class.
	 * In both cases(true or false) result_modifier.php will be included.
	 *
	 * @param bool $state	New extended mode.
	 * @return $this
	 */
	protected function setExtendedMode($state)
	{
		$this->extendedMode = (bool)$state;

		return $this;
	}

	/**
	 * Check if extended mode is enabled.
	 *
	 * @return bool
	 */
	public function isExtendedMode()
	{
		return $this->extendedMode;
	}

	/**
	 * Enable/disable fill old keys in result data and use of outdated settings. Strict use only for catalog.element, .section, .top, etc.
	 *
	 * @param bool $state		Enable/disable state.
	 * @return void
	 */
	protected function setCompatibleMode($state)
	{
		$this->compatibleMode = (bool)$state;
	}

	/**
	 * Return state filling old keys in result data. This method makes no sense for the new components.
	 *
	 * @return bool
	 */
	public function isEnableCompatible()
	{
		return $this->compatibleMode;
	}

	/**
	 * @param $state
	 * @return void
	 */
	protected function setSeparateLoading($state)
	{
		$this->separateLoading = (bool)$state;
	}

	/**
	 * @return bool
	 */
	protected function isSeparateLoading()
	{
		return $this->separateLoading;
	}

	/**
	 * Return settings script path with modified time postfix.
	 *
	 * @param string $componentPath		Path to component.
	 * @param string $settingsName		Settings name.
	 * @return string
	 */
	public static function getSettingsScript($componentPath, $settingsName)
	{
		if ($settingsName === 'filter_conditions')
		{
			if (Loader::includeModule('catalog'))
			{
				\CJSCore::Init(['core_condtree']);
			}
		}
		$path = $componentPath.'/settings/'.$settingsName.'/script.js';
		$file = new Main\IO\File(Main\Application::getDocumentRoot().$path);

		return $path.'?'.$file->getModificationTime();
	}

	/**
	 * Processing of component parameters.
	 *
	 * @param array $params			Raw component parameters values.
	 * @return mixed
	 */
	public function onPrepareComponentParams($params)
	{
		if (!is_array($params))
		{
			$params = [];
		}

		if (!isset($params['CURRENT_BASE_PAGE']))
		{
			$uri = new Main\Web\Uri($this->request->getRequestUri());
			$uri->deleteParams(Main\HttpRequest::getSystemParameters());
			$params['CURRENT_BASE_PAGE'] = $uri->getUri();
		}

		// parent component params for correct template load through ajax
		if (!isset($params['PARENT_NAME']) && $parent = $this->getParent())
		{
			$params['PARENT_NAME'] = $parent->getName();
			$params['PARENT_TEMPLATE_NAME'] = $parent->getTemplateName();
			$params['PARENT_TEMPLATE_PAGE'] = $parent->getTemplatePage();
		}

		// save original parameters for further ajax requests
		$this->arResult['ORIGINAL_PARAMETERS'] = $params;

		if (isset($params['CUSTOM_SITE_ID']) && is_string($params['CUSTOM_SITE_ID']))
		{
			$this->setSiteId($params['CUSTOM_SITE_ID']);
		}

		// for AJAX_MODE set original ajax_id from initial load
		if (isset($params['AJAX_MODE']) && $params['AJAX_MODE'] === 'Y')
		{
			$ajaxId = $this->request->get('AJAX_ID');
			if (!empty($ajaxId))
			{
				$params['AJAX_ID'] = $ajaxId;
			}
			unset($ajaxId);
		}
		$params['AJAX_ID'] = trim((string)($params['AJAX_ID'] ?? ''));

		$params['CACHE_TIME'] = (int)($params['CACHE_TIME'] ?? 36000000);

		$params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
		$params['SECTION_ID'] = (int)($params['SECTION_ID'] ?? 0);

		$params['SECTION_CODE'] = trim((string)($params['SECTION_CODE'] ?? ''));
		$params['SECTION_URL'] = trim((string)($params['SECTION_URL'] ?? ''));
		$params['STRICT_SECTION_CHECK'] = isset($params['STRICT_SECTION_CHECK']) && $params['STRICT_SECTION_CHECK'] === 'Y';

		$params['CHECK_LANDING_PRODUCT_SECTION'] = (
			isset($params['CHECK_LANDING_PRODUCT_SECTION'])
			&& $params['CHECK_LANDING_PRODUCT_SECTION'] === 'Y'
		);

		$params['DETAIL_URL'] = trim((string)($params['DETAIL_URL'] ?? ''));
		$params['BASKET_URL'] = trim((string)($params['BASKET_URL'] ?? ''));
		if ($params['BASKET_URL'] === '')
		{
			$params['BASKET_URL'] = '/personal/basket.php';
		}

		$params['SHOW_SKU_DESCRIPTION'] = $params['SHOW_SKU_DESCRIPTION'] ?? 'N';

		$params['HIDE_DETAIL_URL'] = isset($params['HIDE_DETAIL_URL']) && $params['HIDE_DETAIL_URL'] === 'Y';

		$params['ACTION_VARIABLE'] = trim((string)($params['ACTION_VARIABLE'] ?? ''));
		if ($params['ACTION_VARIABLE'] === '' || !preg_match(self::PARAM_TITLE_MASK, $params['ACTION_VARIABLE']))
		{
			$params['ACTION_VARIABLE'] = 'action';
		}

		$params['PRODUCT_ID_VARIABLE'] = trim((string)($params['PRODUCT_ID_VARIABLE'] ?? ''));
		if (
			$params['PRODUCT_ID_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['PRODUCT_ID_VARIABLE'])
		)
		{
			$params['PRODUCT_ID_VARIABLE'] = 'id';
		}

		$params['ACTION_COMPARE_VARIABLE'] = trim((string)($params['ACTION_COMPARE_VARIABLE'] ?? ''));
		if (
			$params['ACTION_COMPARE_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['ACTION_COMPARE_VARIABLE'])
		)
		{
			$params['ACTION_COMPARE_VARIABLE'] = $params['ACTION_VARIABLE'];
		}

		$params['PRODUCT_QUANTITY_VARIABLE'] = trim((string)($params['PRODUCT_QUANTITY_VARIABLE'] ?? ''));
		if (
			$params['PRODUCT_QUANTITY_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['PRODUCT_QUANTITY_VARIABLE'])
		)
		{
			$params['PRODUCT_QUANTITY_VARIABLE'] = 'quantity';
		}

		$params['PRODUCT_PROPS_VARIABLE'] = trim((string)($params['PRODUCT_PROPS_VARIABLE'] ?? ''));
		if (
			$params['PRODUCT_PROPS_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['PRODUCT_PROPS_VARIABLE'])
		)
		{
			$params['PRODUCT_PROPS_VARIABLE'] = 'prop';
		}

		// landing mode
		if (
			isset($params['ALLOW_SEO_DATA'])
			&& ($params['ALLOW_SEO_DATA'] === 'Y' || $params['ALLOW_SEO_DATA'] === 'N')
		)
		{
			$params['SET_TITLE'] = $params['ALLOW_SEO_DATA'] === 'Y';
			$params['SET_BROWSER_TITLE'] = $params['ALLOW_SEO_DATA'];
			$params['SET_META_KEYWORDS'] = $params['ALLOW_SEO_DATA'];
			$params['SET_META_DESCRIPTION'] = $params['ALLOW_SEO_DATA'];
		}
		else
		{
			$params['SET_TITLE'] = ($params['SET_TITLE'] ?? '') !== 'N';
			$params['SET_BROWSER_TITLE'] = isset($params['SET_BROWSER_TITLE']) && $params['SET_BROWSER_TITLE'] === 'N' ? 'N' : 'Y';
			$params['SET_META_KEYWORDS'] = isset($params['SET_META_KEYWORDS']) && $params['SET_META_KEYWORDS'] === 'N' ? 'N' : 'Y';
			$params['SET_META_DESCRIPTION'] = isset($params['SET_META_DESCRIPTION']) && $params['SET_META_DESCRIPTION'] === 'N' ? 'N' : 'Y';
		}
		$params['SET_LAST_MODIFIED'] = isset($params['SET_LAST_MODIFIED']) && $params['SET_LAST_MODIFIED'] === 'Y';
		$params['ADD_SECTIONS_CHAIN'] = isset($params['ADD_SECTIONS_CHAIN']) && $params['ADD_SECTIONS_CHAIN'] === 'Y';

		$params['DISPLAY_COMPARE'] = isset($params['DISPLAY_COMPARE']) && $params['DISPLAY_COMPARE'] === 'Y';
		$params['COMPARE_PATH'] = trim((string)($params['COMPARE_PATH'] ?? ''));
		$params['COMPARE_NAME'] = trim((string)($params['COMPARE_NAME'] ?? ''));
		if ($params['COMPARE_NAME'] === '')
		{
			$params['COMPARE_NAME'] = 'CATALOG_COMPARE_LIST';
		}
		$params['USE_COMPARE_LIST'] = (isset($params['USE_COMPARE_LIST']) && $params['USE_COMPARE_LIST'] === 'Y' ? 'Y' : 'N');

		$params['USE_PRICE_COUNT'] = isset($params['USE_PRICE_COUNT']) && $params['USE_PRICE_COUNT'] === 'Y';
		$params['SHOW_PRICE_COUNT'] = (int)($params['SHOW_PRICE_COUNT'] ?? 1);
		if ($params['SHOW_PRICE_COUNT'] <= 0)
		{
			$params['SHOW_PRICE_COUNT'] = 1;
		}
		$params['FILL_ITEM_ALL_PRICES'] = isset($params['FILL_ITEM_ALL_PRICES']) && $params['FILL_ITEM_ALL_PRICES'] === 'Y';

		$params['USE_PRODUCT_QUANTITY'] = isset($params['USE_PRODUCT_QUANTITY']) && $params['USE_PRODUCT_QUANTITY'] === 'Y';

		$params['ADD_PROPERTIES_TO_BASKET'] = isset($params['ADD_PROPERTIES_TO_BASKET']) && $params['ADD_PROPERTIES_TO_BASKET'] === 'N' ? 'N' : 'Y';
		if (Iblock\Model\PropertyFeature::isEnabledFeatures())
			$params['ADD_PROPERTIES_TO_BASKET'] = 'Y';
		if ($params['ADD_PROPERTIES_TO_BASKET'] === 'N')
		{
			$params['PRODUCT_PROPERTIES'] = array();
			$params['OFFERS_CART_PROPERTIES'] = array();
		}

		$params['PARTIAL_PRODUCT_PROPERTIES'] = isset($params['PARTIAL_PRODUCT_PROPERTIES']) && $params['PARTIAL_PRODUCT_PROPERTIES'] === 'Y' ? 'Y' : 'N';

		$params['OFFERS_SORT_FIELD'] = trim((string)($params['OFFERS_SORT_FIELD'] ?? ''));
		if ($params['OFFERS_SORT_FIELD'] === '')
		{
			$params['OFFERS_SORT_FIELD'] = 'sort';
		}

		$params['OFFERS_SORT_ORDER'] = trim((string)($params['OFFERS_SORT_ORDER'] ?? ''));
		if (
			$params['OFFERS_SORT_ORDER'] === ''
			|| !preg_match(self::SORT_ORDER_MASK, $params['OFFERS_SORT_ORDER'])
		)
		{
			$params['OFFERS_SORT_ORDER'] = 'asc';
		}

		$params['OFFERS_SORT_FIELD2'] = trim((string)($params['OFFERS_SORT_FIELD2'] ?? ''));
		if ($params['OFFERS_SORT_FIELD2'] === '')
		{
			$params['OFFERS_SORT_FIELD2'] = 'id';
		}

		$params['OFFERS_SORT_ORDER2'] = trim((string)($params['OFFERS_SORT_ORDER2'] ?? ''));
		if (
			$params['OFFERS_SORT_ORDER2'] === ''
			|| !preg_match(self::SORT_ORDER_MASK, $params['OFFERS_SORT_ORDER2'])
		)
		{
			$params['OFFERS_SORT_ORDER2'] = 'desc';
		}

		$params['PRICE_VAT_INCLUDE'] = !(isset($params['PRICE_VAT_INCLUDE']) && $params['PRICE_VAT_INCLUDE'] === 'N');

		$params['CONVERT_CURRENCY'] = isset($params['CONVERT_CURRENCY']) && $params['CONVERT_CURRENCY'] === 'Y' ? 'Y' : 'N';
		$params['CURRENCY_ID'] ??= '';
		if (!is_scalar($params['CURRENCY_ID']))
		{
			$params['CURRENCY_ID'] = '';
		}
		$params['CURRENCY_ID'] = trim((string)$params['CURRENCY_ID']);
		if ($params['CURRENCY_ID'] === '' || $params['CONVERT_CURRENCY'] === 'N')
		{
			$params['CONVERT_CURRENCY'] = 'N';
			$params['CURRENCY_ID'] = '';
		}

		$params['OFFERS_LIMIT'] = (int)($params['OFFERS_LIMIT'] ?? 0);
		if ($params['OFFERS_LIMIT'] < 0)
		{
			$params['OFFERS_LIMIT'] = 0;
		}

		$params['CACHE_GROUPS'] = trim((string)($params['CACHE_GROUPS'] ?? ''));
		if ($params['CACHE_GROUPS'] !== 'N')
		{
			$params['CACHE_GROUPS'] = 'Y';
		}

		if (isset($params['~PRICE_CODE']))
		{
			$params['PRICE_CODE'] = $params['~PRICE_CODE'];
		}
		$params['PRICE_CODE'] ??= [];
		if (!is_array($params['PRICE_CODE']))
		{
			$params['PRICE_CODE'] = [];
		}

		$params['SHOW_FROM_SECTION'] = isset($params['SHOW_FROM_SECTION']) && $params['SHOW_FROM_SECTION'] === 'Y' ? 'Y' : 'N';
		if ($params['SHOW_FROM_SECTION'] === 'Y')
		{
			$params['SECTION_ELEMENT_ID'] = (int)($params['SECTION_ELEMENT_ID'] ?? 0);
			$params['SECTION_ELEMENT_CODE'] = trim((string)($params['SECTION_ELEMENT_CODE'] ?? ''));
			$params['DEPTH'] = (int)($params['DEPTH'] ?? 0);

			if (empty($params['SECTION_ID']))
			{
				if ($params['SECTION_CODE'] !== '')
				{
					$sectionId = $this->getSectionIdByCode($params['SECTION_CODE'], $params['IBLOCK_ID']);
				}
				else
				{
					$sectionId = $this->getSectionIdByElement(
						$params['SECTION_ELEMENT_ID'],
						$params['SECTION_ELEMENT_CODE'],
						$params['IBLOCK_ID']
					);
				}

				$params['SECTION_ID'] = $sectionId;
			}
		}

		$params['FILTER_IDS'] ??= [];
		if (!is_array($params['FILTER_IDS']))
		{
			$params['FILTER_IDS'] = [$params['FILTER_IDS']];
		}

		return $params;
	}

	/**
	 * Check necessary modules for component.
	 *
	 * @return bool
	 */
	protected function checkModules()
	{
		$this->useCatalog = Loader::includeModule('catalog');
		$this->storage['MODULES'] = array(
			'iblock' => true,
			'catalog' => $this->useCatalog,
			'currency' => $this->useCatalog
		);

		return true;
	}

	/**
	 * Fill discount cache before price calculation.
	 *
	 * @return void
	 */
	protected function initCatalogDiscountCache()
	{
		if ($this->useCatalog && $this->useDiscountCache && !empty($this->elementLinks))
		{
			foreach ($this->iblockProducts as $iblock => $products)
			{
				if ($this->storage['USE_SALE_DISCOUNTS'])
				{
					Catalog\Discount\DiscountManager::preloadPriceData($products, $this->storage['PRICES_ALLOW']);
					Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($products, $this->getUserGroups());
				}
				else
				{
					\CCatalogDiscount::SetProductSectionsCache($products);
					\CCatalogDiscount::SetDiscountProductCache($products, array('IBLOCK_ID' => $iblock, 'GET_BY_ID' => 'Y'));
				}
			}
		}
	}

	/**
	 * Clear discount cache.
	 *
	 * @return void
	 */
	protected function clearCatalogDiscountCache()
	{
		if ($this->useCatalog && $this->useDiscountCache)
		{
			\CCatalogDiscount::ClearDiscountCache(array(
				'PRODUCT' => true,
				'SECTIONS' => true,
				'PROPERTIES' => true
			));
		}
	}

	/**
	 * Check the settings for the output price currency.
	 *
	 * @return void
	 */
	protected function initCurrencyConvert()
	{
		$this->storage['CONVERT_CURRENCY'] = array();

		if ($this->arParams['CONVERT_CURRENCY'] === 'Y')
		{
			$correct = false;
			if (Loader::includeModule('currency'))
			{
				$this->storage['MODULES']['currency'] = true;
				$correct = Currency\CurrencyManager::isCurrencyExist($this->arParams['CURRENCY_ID']);
			}
			if ($correct)
			{
				$this->storage['CONVERT_CURRENCY'] = array(
					'CURRENCY_ID' => $this->arParams['CURRENCY_ID']
				);
			}
			else
			{
				$this->arParams['CONVERT_CURRENCY'] = 'N';
				$this->arParams['CURRENCY_ID'] = '';
			}
			unset($correct);
		}
	}

	/**
	 * Check offers iblock.
	 *
	 * @param int $iblockId		Iblock Id.
	 * @return bool
	 */
	protected function offerIblockExist($iblockId)
	{
		if (empty($this->storage['CATALOGS'][$iblockId]))
			return false;

		$catalog = $this->storage['CATALOGS'][$iblockId];

		if (empty($catalog['CATALOG_TYPE']))
			return false;

		return $catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_FULL || $catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_PRODUCT;
	}

	/**
	 * Load used iblocks info to component storage.
	 *
	 * @return void
	 */
	protected function initCatalogInfo()
	{
		$catalogs = array();

		if ($this->useCatalog)
		{
			$this->storage['SHOW_CATALOG_WITH_OFFERS'] = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
			$this->storage['USE_SALE_DISCOUNTS'] = Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y';
			foreach (array_keys($this->iblockProducts) as $iblockId)
			{
				$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
				if (!empty($catalog) && is_array($catalog))
				{
					$this->isIblockCatalog = $this->isIblockCatalog || $catalog['CATALOG_TYPE'] != \CCatalogSku::TYPE_PRODUCT;
					$catalogs[$iblockId] = $catalog;
				}
			}
		}

		$this->storage['CATALOGS'] = $catalogs;
	}

	protected function getProductInfo($productId)
	{
		if (!$this->useCatalog)
			return null;

		$productId = (int)$productId;
		if ($productId <= 0)
			return null;

		$iblockId = (int)\CIBlockElement::GetIBlockByID($productId);
		if ($iblockId <= 0)
			return null;

		$iterator = Catalog\ProductTable::getList([
			'select' => ['ID', 'TYPE'],
			'filter' => ['=ID' => $productId]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (empty($row))
			return null;

		$row['ID'] = (int)$row['ID'];
		$row['TYPE'] = (int)$row['TYPE'];
		if (
			$row['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU
			|| $row['TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER
		)
			return null;

		$row['ELEMENT_IBLOCK_ID'] = $iblockId;
		$row['PRODUCT_IBLOCK_ID'] = 0;

		if (isset($this->storage['CATALOGS'][$iblockId]))
		{
			if ($this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'] == \CCatalogSku::TYPE_CATALOG)
				$row['PRODUCT_IBLOCK_ID'] = $this->storage['CATALOGS'][$iblockId]['IBLOCK_ID'];
			else
				$row['PRODUCT_IBLOCK_ID'] = $this->storage['CATALOGS'][$iblockId]['PRODUCT_IBLOCK_ID'];
			return $row;
		}

		$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($catalog) || !is_array($catalog))
			return null;

		if ($catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_PRODUCT)
			return null;

		if ($catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_OFFERS)
		{
			$iblockId = $catalog['PRODUCT_IBLOCK_ID'];
			$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		}
		if (!isset($this->storage['CATALOGS']))
			$this->storage['CATALOGS'] = [];
		$this->storage['CATALOGS'][$iblockId] = $catalog;
		unset($catalog);

		if ($this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'] == \CCatalogSku::TYPE_CATALOG)
			$row['PRODUCT_IBLOCK_ID'] = $this->storage['CATALOGS'][$iblockId]['IBLOCK_ID'];
		else
			$row['PRODUCT_IBLOCK_ID'] = $this->storage['CATALOGS'][$iblockId]['PRODUCT_IBLOCK_ID'];
		return $row;
	}

	/**
	 * Load catalog prices in component storage.
	 *
	 * @return void
	 */
	protected function initPrices()
	{
		// This function returns array with prices description and access rights
		// in case catalog module n/a prices get values from element properties
		$this->storage['PRICES'] = \CIBlockPriceTools::GetCatalogPrices(
			isset($this->arParams['IBLOCK_ID']) && $this->arParams['IBLOCK_ID'] > 0 ? $this->arParams['IBLOCK_ID'] : false,
			$this->arParams['PRICE_CODE']
		);
		$this->storage['PRICES_ALLOW'] = \CIBlockPriceTools::GetAllowCatalogPrices($this->storage['PRICES']);
		$this->storage['PRICES_CAN_BUY'] = array();
		$this->storage['PRICES_MAP'] = array();
		foreach ($this->storage['PRICES'] as $priceType)
		{
			$this->storage['PRICES_MAP'][$priceType['ID']] = $priceType['CODE'];
			if ($priceType['CAN_BUY'])
				$this->storage['PRICES_CAN_BUY'][$priceType['ID']] = $priceType['ID'];
		}

		$this->storage['PRICE_TYPES'] = array();
		if ($this->useCatalog)
			$this->storage['PRICE_TYPES'] = \CCatalogGroup::GetListArray();

		$this->useDiscountCache = false;
		if ($this->useCatalog)
		{
			if (!empty($this->storage['CATALOGS']) && !empty($this->storage['PRICES_ALLOW']))
				$this->useDiscountCache = true;
		}

		if ($this->useCatalog && $this->useDiscountCache)
		{
			$this->useDiscountCache = \CIBlockPriceTools::SetCatalogDiscountCache(
				$this->storage['PRICES_ALLOW'],
				$this->getUserGroups()
			);
		}

		if ($this->useCatalog)
			Catalog\Product\Price::loadRoundRules($this->storage['PRICES_ALLOW']);
	}

	/**
	 * Load catalog vats in component storage.
	 *
	 * @return void
	 */
	protected function initVats()
	{
		$this->storage['VATS'] = [];
		$this->storage['IBLOCKS_VAT'] = [];
		if ($this->useCatalog)
		{
			$iterator = Catalog\VatTable::getList([
				'select' => ['ID', 'RATE'],
				'order' => ['ID' => 'ASC']
			]);
			while ($row = $iterator->fetch())
				$this->storage['VATS'][(int)$row['ID']] = (float)$row['RATE'];
			unset($row, $iterator);

			if (!empty($this->storage['CATALOGS']))
			{
				foreach ($this->storage['CATALOGS'] as $catalog)
				{
					$this->storage['IBLOCKS_VAT'][$catalog['IBLOCK_ID']] = 0;
					if ($catalog['PRODUCT_IBLOCK_ID'] > 0)
						$this->storage['IBLOCKS_VAT'][$catalog['PRODUCT_IBLOCK_ID']] = 0;
				}
				unset($catalog);

				$iterator = Catalog\CatalogIblockTable::getList([
					'select' => ['IBLOCK_ID', 'VAT_ID'],
					'filter' => ['@IBLOCK_ID' => array_keys($this->storage['IBLOCKS_VAT'])]
				]);
				while ($row = $iterator->fetch())
					$this->storage['IBLOCKS_VAT'][(int)$row['IBLOCK_ID']] = (int)$row['VAT_ID'];
				unset($row, $iterator);
			}
		}
	}

	/**
	 * @return void
	 */
	protected function initIblockPropertyFeatures()
	{

	}

	/**
	 * Initialize and data process of iblock elements.
	 *
	 * @return void
	 */
	protected function initElementList()
	{
		$this->storage['CURRENCY_LIST'] = array();
		$this->storage['DEFAULT_MEASURE'] = $this->getDefaultMeasure();

		$this->initQueryFields();

		foreach ($this->iblockProducts as $iblock => $products)
		{
			$elementIterator = $this->getElementList($iblock, $products);
			$iblockElements = $this->getIblockElements($elementIterator);

			if (!empty($iblockElements) && !$this->hasErrors())
			{
				$this->modifyDisplayProperties($iblock, $iblockElements);
				$this->elements = array_merge($this->elements, array_values($iblockElements));
				$this->iblockProducts[$iblock] = array_keys($iblockElements);
			}

			unset($elementIterator, $iblockElements, $element);
		}
	}

	/**
	 * Return elements.
	 *
	 * @param \CIBlockResult $elementIterator		Iterator.
	 * @return mixed
	 */
	abstract protected function getIblockElements($elementIterator);

	/**
	 * Sort elements by original position (in case when product ids used in GetList).
	 *
	 * @return void
	 */
	protected function sortElementList()
	{
		if (!empty($this->productIdMap) && is_array($this->productIdMap))
		{
			$sortedElements = array();

			foreach (array_keys($this->productIdMap) as $productId)
			{
				$parentId = $this->productIdMap[$productId];

				foreach ($this->elements as $element)
				{
					if ($element['ID'] == $parentId)
					{
						$sortedElements[$productId] = $element;
						break;
					}
				}
			}

			$this->elements = array_values($sortedElements);
		}
	}

	/**
	 * Create link to elements for fast access.
	 *
	 * @return void
	 */
	protected function makeElementLinks()
	{
		if (!empty($this->elements))
		{
			foreach ($this->elements as $index => $element)
			{
				$this->elementLinks[$element['ID']] =& $this->elements[$index];
			}
		}
	}

	/**
	 * Return array of iblock element ids to show for "initialLoad" action.
	 *
	 * @return bool|array
	 */
	protected function getProductIds()
	{
		return false;
	}

	/**
	 * Return array of iblock element ids to show for "bigDataLoad" action.
	 *
	 * @return array
	 */
	protected function getBigDataProductIds()
	{
		$shownIds = $this->request->get('shownIds');
		if (!empty($shownIds) && is_array($shownIds))
		{
			$this->arParams['FILTER_IDS'] += $shownIds;
		}

		$this->arParams['PAGE_ELEMENT_COUNT'] = $this->request->get('count') ?: 20;
		$this->arParams['FILTER'] ??= [];
		$this->arParams['FILTER'] = $this->arParams['FILTER'] ?: ['PAYED'];
		$this->arParams['BY'] ??= '';
		$this->arParams['BY'] = $this->arParams['BY'] ?: 'AMOUNT';
		$this->arParams['PERIOD'] ??= 0;
		$this->arParams['PERIOD'] = (int)$this->arParams['PERIOD'] ?: 30;
		$this->arParams['DEPTH'] ??= 0;
		$this->arParams['DEPTH'] = (int)$this->arParams['DEPTH'] ?: 2;

		// general filter
		$this->filterFields = $this->getFilter();
		$this->filterFields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
		$this->prepareElementQueryFields();

		// try cloud
		$ids = $this->request->get('items') ?: array();
		if (!empty($ids))
		{
			$recommendationId = $this->request->get('rid');
			$ids = $this->filterByParams($ids, $this->arParams['FILTER_IDS']);

			foreach ($ids as $id)
			{
				$this->recommendationIdToProduct[$id] = $recommendationId;
			}
		}

		// try bestsellers
		if (Main\Loader::includeModule('sale') && count($ids) < $this->arParams['PAGE_ELEMENT_COUNT'])
		{
			$ids = $this->getBestSellersRecommendation($ids);
		}

		// try most viewed
		if ($this->useCatalog && count($ids) < $this->arParams['PAGE_ELEMENT_COUNT'])
		{
			$ids = $this->getMostViewedRecommendation($ids);
		}

		// try random
		if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT'])
		{
			$ids = $this->getRandomRecommendation($ids);
		}

		// limit
		return array_slice($ids, 0, $this->arParams['PAGE_ELEMENT_COUNT']);
	}

	/**
	 * Return recommended best seller products ids.
	 *
	 * @param array $ids		Products id.
	 * @return array
	 */
	protected function getBestSellersRecommendation($ids)
	{
		// increase element count
		$this->arParams['PAGE_ELEMENT_COUNT'] = $this->arParams['PAGE_ELEMENT_COUNT'] * 10;
		$bestsellers = $this->getBestSellersProductIds();
		$this->arParams['PAGE_ELEMENT_COUNT'] = $this->arParams['PAGE_ELEMENT_COUNT'] / 10;

		if (!empty($bestsellers))
		{
			$recommendationId = 'bestsellers';
			$bestsellers = Main\Analytics\Catalog::getProductIdsByOfferIds($bestsellers);
			$bestsellers = $this->filterByParams($bestsellers, $this->arParams['FILTER_IDS']);

			foreach ($bestsellers as $id)
			{
				if (!isset($this->recommendationIdToProduct[$id]))
				{
					$this->recommendationIdToProduct[$id] = $recommendationId;
				}
			}

			$ids = array_unique(array_merge($ids, $bestsellers));
		}

		return $ids;
	}

	/**
	 * Return recommended most viewed products ids.
	 *
	 * @param array $ids		Products id.
	 * @return array
	 */
	protected function getMostViewedRecommendation($ids)
	{
		$mostViewed = array();
		$recommendationId = 'mostviewed';

		$result = Catalog\CatalogViewedProductTable::getList(array(
			'select' => array(
				'ELEMENT_ID',
				new Main\Entity\ExpressionField('SUM_HITS', 'SUM(%s)', 'VIEW_COUNT')
			),
			'filter' => array(
				'=SITE_ID' => $this->getSiteId(),
				'>ELEMENT_ID' => 0,
				'>DATE_VISIT' => new Main\Type\DateTime(date('Y-m-d H:i:s', strtotime('-30 days')), 'Y-m-d H:i:s')
			),
			'order' => array('SUM_HITS' => 'DESC'),
			'limit' => $this->arParams['PAGE_ELEMENT_COUNT'] * 10
		));
		while ($row = $result->fetch())
		{
			$mostViewed[] = $row['ELEMENT_ID'];
		}
		unset($row, $result);

		$mostViewed = $this->filterByParams($mostViewed, $this->arParams['FILTER_IDS']);

		foreach ($mostViewed as $id)
		{
			if (!isset($this->recommendationIdToProduct[$id]))
			{
				$this->recommendationIdToProduct[$id] = $recommendationId;
			}
		}

		return array_unique(array_merge($ids, $mostViewed));
	}

	/**
	 * Return random products ids.
	 *
	 * @param array $ids		Products id.
	 * @return array
	 */
	protected function getRandomRecommendation($ids)
	{
		$limit = $this->getRecommendationLimit($ids);

		if ($limit <= 0)
		{
			return $ids;
		}

		$randomIds = array();
		$recommendationId = 'random';
		$filter = $this->filterFields;

		$filterIds = array_merge($ids, $this->arParams['FILTER_IDS']);
		if (!empty($filterIds))
		{
			$filter['!ID'] = $filterIds;
		}

		if ($this->arParams['SHOW_FROM_SECTION'] === 'Y' && !empty($this->arParams['SECTION_ID']))
		{
			$filter['SECTION_ID'] = $this->arParams['SECTION_ID'];
		}

		$elementIterator = \CIBlockElement::GetList(array('RAND' => 'ASC'), $filter, false, array('nTopCount' => $limit), array('ID'));
		while ($element = $elementIterator->Fetch())
		{
			$randomIds[] = $element['ID'];
		}

		if (!empty($randomIds))
		{
			$this->setCacheUsage(false);
		}

		foreach ($randomIds as $id)
		{
			if (!isset($this->recommendationIdToProduct[$id]))
			{
				$this->recommendationIdToProduct[$id] = $recommendationId;
			}
		}

		return array_merge($ids, $randomIds);
	}

	/**
	 * Filter correct product ids.
	 *
	 * @param array $ids				Items ids.
	 * @param array $filterIds			Filtered ids.
	 * @param bool $useSectionFilter	Check filter by section.
	 * @return array
	 */
	protected function filterByParams($ids, $filterIds = array(), $useSectionFilter = true)
	{
		if (empty($ids))
		{
			return array();
		}

		$ids = array_values(array_unique($ids));
		// remove duplicates of already showed items
		if (!empty($filterIds))
		{
			$ids = array_diff($ids, $filterIds);
		}

		if (!empty($ids))
		{
			$filter = $this->filterFields;
			$filter['ID'] = $ids;

			$correctIds = array();
			$elementIterator = \CIBlockElement::GetList(array(), $filter, false, false, array('ID'));
			while ($element = $elementIterator->Fetch())
			{
				$correctIds[] = $element['ID'];
			}

			if ($useSectionFilter && !empty($correctIds) && $this->arParams['SHOW_FROM_SECTION'] === 'Y')
			{
				$correctIds = $this->filterIdBySection(
					$correctIds,
					$this->arParams['IBLOCK_ID'],
					$this->arParams['SECTION_ID'],
					$this->arParams['PAGE_ELEMENT_COUNT'],
					$this->arParams['DEPTH']
				);
			}

			$correctIds = array_flip($correctIds);
			// remove invalid items
			foreach ($ids as $key => $id)
			{
				if (!isset($correctIds[$id]))
				{
					unset($ids[$key]);
				}
			}

			return array_values($ids);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Return section ID by CODE.
	 *
	 * @param string $sectionCode			Iblock section code.
	 * @return int
	 */
	protected function getSectionIdByCode($sectionCode = '', int $iblockId = 0)
	{
		$sectionId = 0;
		$sectionCode = (string)$sectionCode;

		if ($sectionCode === '')
		{
			return $sectionId;
		}

		$sectionFilter = [];
		if ($iblockId > 0)
		{
			$sectionFilter['=IBLOCK_ID'] = $iblockId;
		}
		elseif (!empty($this->arParams['IBLOCK_ID']))
		{
			$sectionFilter['@IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
		}
		if (empty($sectionFilter))
		{
			return $sectionId;
		}

		$sectionFilter['=IBLOCK.ACTIVE'] = 'Y';
		$sectionFilter['=CODE'] = $sectionCode;

		$section = Iblock\SectionTable::getList(array(
			'select' => array('ID'),
			'filter' => $sectionFilter
		))->fetch();
		if (!empty($section))
		{
			$sectionId = (int)$section['ID'];
		}

		return $sectionId;
	}

	/**
	 * Return section ID by element ID.
	 *
	 * @param int $elementId				Iblock element id.
	 * @param string $elementCode			Iblock element code.
	 * @return int
	 */
	protected function getSectionIdByElement($elementId, $elementCode = '', int $iblockId = 0)
	{
		$sectionId = 0;
		$elementId = (int)$elementId;
		$elementCode = (string)$elementCode;
		$filter = [];

		if ($iblockId > 0)
		{
			$filter['=IBLOCK_ID'] = $iblockId;
		}
		elseif (!empty($this->arParams['IBLOCK_ID']))
		{
			$filter['=IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
		}
		if (empty($filter))
		{
			return $sectionId;
		}

		if ($elementId > 0)
		{
			$filter['=ID'] = $elementId;
		}
		elseif ($elementCode !== '')
		{
			$filter['=CODE'] = $elementCode;
		}
		else
		{
			return $sectionId;
		}

		$itemIterator = Iblock\ElementTable::getList(array(
			'select' => array('ID', 'IBLOCK_SECTION_ID'),
			'filter' => $filter
		));
		if ($item = $itemIterator->fetch())
		{
			$sectionId = (int)$item['IBLOCK_SECTION_ID'];
		}

		return $sectionId;
	}

	protected function filterIdBySection($elementIds, $iblockId, $sectionId, $limit, $depth = 0)
	{
		$map = array();

		Main\Type\Collection::normalizeArrayValuesByInt($elementIds);

		if (empty($elementIds))
			return $map;

		$iblockId = (int)$iblockId;
		$sectionId = (int)$sectionId;
		$limit = (int)$limit;
		$depth = (int)$depth;

		if ($iblockId <= 0 ||$depth < 0)
			return $map;

		$subSections = array();
		if ($depth > 0)
		{
			$parentSectionId = Catalog\Product\Viewed::getParentSection($sectionId, $depth);
			if ($parentSectionId !== null)
			{
				$subSections[$parentSectionId] = $parentSectionId;
			}
			unset($parentSectionId);
		}

		if (empty($subSections) && $sectionId <= 0)
		{
			$getListParams = array(
				'select' => array('ID'),
				'filter' => array(
					'@ID' => $elementIds,
					'=IBLOCK_ID' => $iblockId,
					'=WF_STATUS_ID' => 1,
					'=WF_PARENT_ELEMENT_ID' => null
				),
			);
			if ($limit > 0)
			{
				$getListParams['limit'] = $limit;
			}

			$iterator = Iblock\ElementTable::getList($getListParams);
		}
		else
		{
			if (empty($subSections))
			{
				$subSections[$sectionId] = $sectionId;
			}

			$sectionQuery = new Main\Entity\Query(Iblock\SectionTable::getEntity());
			$sectionQuery->setTableAliasPostfix('_parent');
			$sectionQuery->setSelect(array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
			$sectionQuery->setFilter(array('@ID' => $subSections));

			$subSectionQuery = new Main\Entity\Query(Iblock\SectionTable::getEntity());
			$subSectionQuery->setTableAliasPostfix('_sub');
			$subSectionQuery->setSelect(array('ID'));
			$subSectionQuery->setFilter(array('=IBLOCK_ID' => $iblockId));
			$subSectionQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BS',
					Main\Entity\Base::getInstanceByQuery($sectionQuery),
					array('>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN', '<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN'),
					array('join_type' => 'INNER')
				)
			);

			$sectionElementQuery = new Main\Entity\Query(Iblock\SectionElementTable::getEntity());
			$sectionElementQuery->setSelect(array('IBLOCK_ELEMENT_ID'));
			$sectionElementQuery->setGroup(array('IBLOCK_ELEMENT_ID'));
			$sectionElementQuery->setFilter(array('=ADDITIONAL_PROPERTY_ID' => null));
			$sectionElementQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BSUB',
					Main\Entity\Base::getInstanceByQuery($subSectionQuery),
					array('=this.IBLOCK_SECTION_ID' => 'ref.ID'),
					array('join_type' => 'INNER')
				)
			);

			$elementQuery = new Main\Entity\Query(Iblock\ElementTable::getEntity());
			$elementQuery->setSelect(array('ID'));
			$elementQuery->setFilter(array('=IBLOCK_ID' => $iblockId, '=WF_STATUS_ID' => 1, '=WF_PARENT_ELEMENT_ID' => null));
			$elementQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BSE',
					Main\Entity\Base::getInstanceByQuery($sectionElementQuery),
					array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
					array('join_type' => 'INNER')
				)
			);
			if ($limit > 0)
			{
				$elementQuery->setLimit($limit);
			}

			$iterator = $elementQuery->exec();

			unset($elementQuery, $sectionElementQuery, $subSectionQuery, $sectionQuery);
		}

		while ($row = $iterator->fetch())
		{
			$map[] = $row['ID'];
		}
		unset($row, $iterator);

		return $map;
	}

	/**
	 * Return random element ids to fill partially empty space in row when lack of big data elements.
	 * Does not fill rows with no big data elements at all.
	 *
	 * @param array $ids
	 * @return int
	 */
	protected function getRecommendationLimit($ids)
	{
		$limit = 0;
		$idsCount = count($ids);
		$rowsRange = $this->request->get('rowsRange');

		if (!empty($rowsRange))
		{
			foreach ($rowsRange as $range)
			{
				$range = (int)$range;

				if ($range > $idsCount)
				{
					$limit = $range - $idsCount;
					break;
				}
			}
		}
		else
		{
			$limit = $this->arParams['PAGE_ELEMENT_COUNT'] - $idsCount;
		}

		return $limit;
	}

	protected function getBigDataServiceRequestParams($type = '')
	{
		$params = array(
			'uid' => ($_COOKIE['BX_USER_ID'] ?? ''),
			'aid' => Main\Analytics\Counter::getAccountId(),
			'count' => max($this->arParams['PAGE_ELEMENT_COUNT'] * 2, 30)
		);

		// random choices
		if ($type === 'any_similar')
		{
			$possible = array('similar_sell', 'similar_view', 'similar');
			$type = $possible[array_rand($possible)];
		}
		elseif ($type === 'any_personal')
		{
			$possible = array('bestsell', 'personal');
			$type = $possible[array_rand($possible)];
		}
		elseif ($type === 'any')
		{
			$possible = array('similar_sell', 'similar_view', 'similar', 'bestsell', 'personal');
			$type = $possible[array_rand($possible)];
		}

		// configure
		switch ($type)
		{
			case 'bestsell':
				$params['op'] = 'sim_domain_items';
				$params['type'] = 'order';
				$params['domain'] = Main\Context::getCurrent()->getServer()->getHttpHost();
				break;
			case 'personal':
				$params['op'] = 'recommend';
				break;
			case 'similar_sell':
				$params['op'] = 'simitems';
				$params['eid'] = $this->arParams['RCM_PROD_ID'];
				$params['type'] = 'order';
				break;
			case 'similar_view':
				$params['op'] = 'simitems';
				$params['eid'] = $this->arParams['RCM_PROD_ID'];
				$params['type'] = 'view';
				break;
			case 'similar':
				$params['op'] = 'simitems';
				$params['eid'] = $this->arParams['RCM_PROD_ID'];
				break;
			default:
				$params['op'] = 'recommend';
		}

		$iblocks = array();

		if (!empty($this->storage['IBLOCK_PARAMS']))
		{
			$iblocks = array_keys($this->storage['IBLOCK_PARAMS']);
		}
		else
		{
			$iblockList = array();
			/* catalog */
			$iblockIterator = Catalog\CatalogIblockTable::getList(array(
				'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID')
			));
			while ($iblock = $iblockIterator->fetch())
			{
				$iblock['IBLOCK_ID'] = (int)$iblock['IBLOCK_ID'];
				$iblock['PRODUCT_IBLOCK_ID'] = (int)$iblock['PRODUCT_IBLOCK_ID'];
				$iblockList[$iblock['IBLOCK_ID']] = $iblock['IBLOCK_ID'];

				if ($iblock['PRODUCT_IBLOCK_ID'] > 0)
				{
					$iblockList[$iblock['PRODUCT_IBLOCK_ID']] = $iblock['PRODUCT_IBLOCK_ID'];
				}
			}

			/* iblock */
			$iblockIterator = Iblock\IblockSiteTable::getList(array(
				'select' => array('IBLOCK_ID'),
				'filter' => array('@IBLOCK_ID' => $iblockList, '=SITE_ID' => $this->getSiteId())
			));
			while ($iblock = $iblockIterator->fetch())
			{
				$iblocks[] = $iblock['IBLOCK_ID'];
			}
		}

		$params['ib'] = join('.', $iblocks);

		return $params;
	}

	/**
	 * Return best seller product ids.
	 *
	 * @return array
	 */
	protected function getBestSellersProductIds()
	{
		$productIds = array();
		$filter = $this->getBestSellersFilter();

		if (!empty($filter))
		{
			$productIterator = \CSaleProduct::GetBestSellerList(
				$this->arParams['BY'],
				array(),
				$filter,
				$this->arParams['PAGE_ELEMENT_COUNT']
			);
			while($product = $productIterator->fetch())
			{
				$productIds[] = $product['PRODUCT_ID'];
			}
		}

		return $productIds;
	}

	protected function getBestSellersFilter()
	{
		$filter = array();

		if (!empty($this->arParams['FILTER']))
		{
			$filter = array('=LID' => $this->getSiteId());
			$subFilter = array('LOGIC' => 'OR');

			$statuses = array(
				'CANCELED' => true,
				'ALLOW_DELIVERY' => true,
				'PAYED' => true,
				'DEDUCTED' => true
			);

			if ($this->arParams['PERIOD'] > 0)
			{
				$date = ConvertTimeStamp(AddToTimeStamp(array('DD' => '-'.$this->arParams['PERIOD'])));
				if (!empty($date))
				{
					foreach ($this->arParams['FILTER'] as $field)
					{
						if (isset($statuses[$field]))
						{
							$subFilter[] = array(
								'>=DATE_'.$field => $date,
								'='.$field => 'Y'
							);
						}
						else
						{
							if (empty($this->storage['ORDER_STATUS']) || in_array($field, $this->storage['ORDER_STATUS']))
							{
								$subFilter[] = array(
									'=STATUS_ID' => $field,
									'>=DATE_UPDATE' => $date,
								);
							}
						}
					}
					unset($field);
				}
			}
			else
			{
				foreach ($this->arParams['FILTER'] as $field)
				{
					if (isset($statuses[$field]))
					{
						$subFilter[] = array(
							'='.$field => 'Y'
						);
					}
					else
					{
						if (empty($this->storage['ORDER_STATUS']) || in_array($field, $this->storage['ORDER_STATUS']))
						{
							$subFilter[] = array(
								'=STATUS_ID' => $field,
							);
						}
					}
				}
				unset($field);
			}

			$filter[] = $subFilter;
		}

		return $filter;
	}

	/**
	 * Return array of iblock element ids to show for "initialLoad" action.
	 *
	 * @return array
	 */
	protected function getDeferredProductIds()
	{
		return array();
	}

	protected function getProductIdMap($productIds)
	{
		if ($productIds === false)
		{
			return false;
		}

		return $this->useCatalog ? static::getProductsMap($productIds) : $productIds;
	}

	/**
	 * Returns ids map: SKU_PRODUCT_ID => PRODUCT_ID.
	 *
	 * @param array $originalIds			Input products ids.
	 * @return array
	 */
	public static function getProductsMap(array $originalIds = array())
	{
		if (empty($originalIds))
		{
			return array();
		}

		$result = array();
		$productList = \CCatalogSku::getProductList($originalIds);
		if ($productList === false)
		{
			$productList = array();
		}

		foreach ($originalIds as $id)
		{
			$result[$id] = isset($productList[$id]) ? $productList[$id]['ID'] : (int)$id;
		}

		return $result;
	}

	/**
	 * Return array map of iblock products.

	 * 3 following cases to process $productIdMap:
	 * ~ $productIdMap is array with ids	- show elements with presented ids
	 * ~ $productIdMap is empty array		- nothing to show
	 * ~ $productIdMap === false				- show elements via filter(e.g. $arParams['IBLOCK_ID'],  arParams['ELEMENT_ID'])
	 *
	 * @return array
	 */
	protected function getProductsSeparatedByIblock()
	{
		$iblockItems = array();

		if (!empty($this->productIdMap) && is_array($this->productIdMap))
		{
			$itemsIterator = Iblock\ElementTable::getList(array(
				'select' => array('ID', 'IBLOCK_ID'),
				'filter' => array('@ID' => $this->productIdMap)
			));
			while ($item = $itemsIterator->fetch())
			{
				$item['ID'] = (int)$item['ID'];
				$item['IBLOCK_ID'] = (int)$item['IBLOCK_ID'];

				if (!isset($iblockItems[$item['IBLOCK_ID']]))
				{
					$iblockItems[$item['IBLOCK_ID']] = array();
				}

				$iblockItems[$item['IBLOCK_ID']][] = $item['ID'];
			}
			unset($item, $itemsIterator);
		}
		elseif ($this->productIdMap === false)
		{
			$iblockItems[$this->arParams['IBLOCK_ID']] = $this->arParams['ELEMENT_ID'] ?? 0;
		}

		return $iblockItems;
	}

	/**
	 * Return default measure.
	 *
	 * @return array|null
	 */
	protected function getDefaultMeasure()
	{
		$defaultMeasure = array();

		if ($this->useCatalog)
		{
			$defaultMeasure = \CCatalogMeasure::getDefaultMeasure(true, true);
		}

		return $defaultMeasure;
	}

	/**
	 * Return \CIBlockResult iterator for current iblock ID.
	 *
	 * @param int $iblockId
	 * @param array|int $products
	 * @return \CIBlockResult|int
	 */
	protected function getElementList($iblockId, $products)
	{
		$selectFields = $this->getIblockSelectFields($iblockId);

		$filterFields = $this->filterFields;
		if ($iblockId > 0)
		{
			$filterFields['IBLOCK_ID'] = $iblockId;
		}
		if (!empty($products))
		{
			$filterFields['ID'] = $products;
		}

		$globalFilter = [];
		if (!empty($this->globalFilter))
			$globalFilter = $this->convertFilter($this->globalFilter);

		$iteratorParams = [
			'select' => $selectFields,
			'filter' => array_merge($globalFilter, $filterFields),
			'order' => $this->sortFields,
			'navigation' => $this->navParams
		];
		if ($this->isSeparateLoading() && $iblockId > 0)
		{
			$elementIterator = $this->getSeparateList($iteratorParams);
		}
		else
		{
			$elementIterator = $this->getFullIterator($iteratorParams);
		}
		unset($iteratorParams);

		$elementIterator->SetUrlTemplates($this->arParams['DETAIL_URL']);

		return $elementIterator;
	}

	/**
	 * @param array $params
	 * @return \CIBlockResult
	 */
	protected function getSeparateList(array $params)
	{
		$list = [];

		$selectFields = ['ID', 'IBLOCK_ID'];
		if (!empty($params['order']))
		{
			$selectFields = array_unique(array_merge(
				$selectFields,
				array_keys($params['order'])
			));
		}

		$iterator = \CIBlockElement::GetList(
			$params['order'],
			$params['filter'],
			false,
			$params['navigation'],
			$selectFields
		);
		while ($row = $iterator->Fetch())
		{
			$id = (int)$row['ID'];
			$list[$id] = [
				'ID' => $row['ID'],
				'IBLOCK_ID' => $row['IBLOCK_ID'],
			];
		}
		unset($row);

		if (!empty($list))
		{
			$fullIterator = \CIBlockElement::GetList(
				[],
				['IBLOCK_ID' => $params['filter']['IBLOCK_ID'], 'ID' => array_keys($list), 'SITE_ID' => $this->getSiteId()],
				false,
				false,
				$params['select']
			);
			while ($row = $fullIterator->Fetch())
			{
				$id = (int)$row['ID'];
				$list[$id] = $list[$id] + $row;
			}
			unset($row, $fullIterator);

			$iterator->InitFromArray(array_values($list));
		}

		return $iterator;
	}

	/**
	 * @param array $params
	 * @return \CIBlockResult
	 */
	protected function getFullIterator(array $params)
	{
		return \CIBlockElement::GetList(
			$params['order'],
			$params['filter'],
			false,
			$params['navigation'],
			$params['select']
		);
	}

	/**
	 * Initialization of general query fields.
	 *
	 * @return void
	 */
	protected function initQueryFields()
	{
		$this->selectFields = $this->getSelect();
		$this->filterFields = $this->getFilter();
		$this->sortFields = $this->getSort();
		$this->prepareElementQueryFields();
	}

	/**
	 * Return select fields to execute.
	 *
	 * @return array
	 */
	protected function getSelect()
	{
		$result = [
			'ID', 'IBLOCK_ID', 'CODE', 'XML_ID', 'NAME', 'ACTIVE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'SORT',
			'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'DATE_CREATE', 'CREATED_BY', 'TAGS',
			'TIMESTAMP_X', 'MODIFIED_BY', 'IBLOCK_SECTION_ID', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PREVIEW_PICTURE'
		];

		$checkPriceProperties = (
			!$this->useCatalog
			|| (
				isset($this->arParams['IBLOCK_ID'])
				&& $this->arParams['IBLOCK_ID'] > 0
				&& !isset($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
			)
		);

		if ($checkPriceProperties && !empty($this->storage['PRICES']))
		{
			foreach ($this->storage['PRICES'] as $row)
			{
				if (!empty($row['SELECT']))
					$result[] = $row['SELECT'];
			}
		}

		return $result;
	}

	/**
	 * Return filter fields to execute.
	 *
	 * @return array
	 */
	protected function getFilter()
	{
		return array(
			'IBLOCK_LID' => $this->getSiteId(),
			'ACTIVE_DATE' => 'Y',
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R'
		);
	}

	/**
	 * Return sort fields to execute.
	 *
	 * @return array
	 */
	protected function getSort()
	{
		return array();
	}

	/**
	 * Prepare element getList parameters.
	 *
	 * @return void
	 */
	protected function prepareElementQueryFields()
	{
		$result = $this->prepareQueryFields($this->selectFields, $this->filterFields, $this->sortFields);
		$this->selectFields = $result['SELECT'];
		$this->filterFields = $result['FILTER'];
		$this->sortFields = $result['ORDER'];
		if (!empty($this->globalFilter))
		{
			$result = $this->prepareQueryFields([], $this->globalFilter, []);
			$this->globalFilter = $result['FILTER'];
		}
		unset($result);
	}

	/**
	 * Prepare select, filter, order.
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @return array
	 */
	protected function prepareQueryFields(array $select, array $filter, array $order)
	{
		if ($this->useCatalog)
		{
			$select = $this->convertSelect($select);
			$order = $this->convertOrder($order);
			$filter = $this->convertFilter($filter);
			$filter = \CProductQueryBuilder::modifyFilterFromOrder(
				$filter,
				$order,
				['QUANTITY' => $this->arParams['SHOW_PRICE_COUNT']]
			);
		}

		if (!empty($select))
		{
			$select = array_unique($select);
		}

		return [
			'SELECT' => $select,
			'FILTER' => $filter,
			'ORDER' => $order
		];
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Component\Base::prepareElementQueryFields
	 */
	protected function initPricesQuery()
	{
		$this->prepareElementQueryFields();
	}

	/**
	 * Return select product fields to execute.
	 *
	 * @param int $iblockId
	 * @param array $selectFields
	 * @return array
	 */
	protected function getProductSelect($iblockId, array $selectFields)
	{
		if (!$this->useCatalog)
			return $selectFields;

		$additionalFields = $this->getProductFields($iblockId);
		$result = $selectFields;

		if (!empty($additionalFields))
		{
			$result = array_merge($result, $additionalFields);
			$result = array_unique($result);
		}
		unset($additionalFields);

		return $result;
	}

	/**
	 * Returns product fields for iblock.
	 *
	 * @param int $iblockId
	 * @return array
	 */
	protected function getProductFields($iblockId)
	{
		if (!$this->isIblockCatalog && !$this->offerIblockExist($iblockId))
			return [];

		$result = [
			'TYPE', 'AVAILABLE', 'BUNDLE',
			'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'MEASURE',
			'SUBSCRIBE',
			'VAT_ID', 'VAT_INCLUDED',
			'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT',
			'PAYMENT_TYPE', 'RECUR_SCHEME_LENGTH', 'RECUR_SCHEME_TYPE',
			'TRIAL_PRICE_ID'
		];

		if ($this->isEnableCompatible())
		{
			$result = array_merge(
				$result,
				[
					'QUANTITY_TRACE_RAW', 'CAN_BUY_ZERO_RAW', 'SUBSCRIBE_RAW',
					'PURCHASING_PRICE', 'PURCHASING_CURRENCY',
					'BARCODE_MULTI',
					'WITHOUT_ORDER'
				]
			);
		}

		return $result;
	}

	/**
	 * Convert old product selected fields to new.
	 *
	 * @param array $select
	 * @return array
	 */
	protected function convertSelect(array $select)
	{
		if (!$this->useCatalog)
			return $select;
		return \CProductQueryBuilder::convertOldSelect($select);
	}

	/**
	 * Convert old product filter keys to new.
	 *
	 * @param array $filter
	 * @return array
	 */
	protected function convertFilter(array $filter)
	{
		if (!$this->useCatalog)
			return $filter;
		return \CProductQueryBuilder::convertOldFilter($filter);
	}

	/**
	 * Convert old product order keys to new.
	 *
	 * @param array $order
	 * @return array
	 */
	protected function convertOrder(array $order)
	{
		if (!$this->useCatalog)
			return $order;
		return \CProductQueryBuilder::convertOldOrder($order);
	}

	protected function getIblockSelectFields($iblockId)
	{
		if (!$this->useCatalog)
			return $this->selectFields;
		return $this->getProductSelect($iblockId, $this->selectFields);
	}

	/**
	 * Return parsed conditions array.
	 *
	 * @param $condition
	 * @param $params
	 * @return array
	 */
	protected function parseCondition($condition, $params)
	{
		$result = array();

		if (!empty($condition) && is_array($condition))
		{
			if ($condition['CLASS_ID'] === 'CondGroup')
			{
				if (!empty($condition['CHILDREN']))
				{
					foreach ($condition['CHILDREN'] as $child)
					{
						$childResult = $this->parseCondition($child, $params);

						// is group
						if ($child['CLASS_ID'] === 'CondGroup')
						{
							$result[] = $childResult;
						}
						// same property names not overrides each other
						elseif (isset($result[key($childResult)]))
						{
							$fieldName = key($childResult);

							if (!isset($result['LOGIC']))
							{
								$result = array(
									'LOGIC' => $condition['DATA']['All'],
									array($fieldName => $result[$fieldName])
								);
							}

							$result[][$fieldName] = $childResult[$fieldName];
						}
						else
						{
							$result += $childResult;
						}
					}

					if (!empty($result))
					{
						$this->parsePropertyCondition($result, $condition, $params);

						if (count($result) > 1)
						{
							$result['LOGIC'] = $condition['DATA']['All'];
						}
					}
				}
			}
			else
			{
				$result += $this->parseConditionLevel($condition, $params);
			}
		}

		return $result;
	}

	protected function parseConditionLevel($condition, $params)
	{
		$result = array();

		if (!empty($condition) && is_array($condition))
		{
			$name = $this->parseConditionName($condition);
			if (!empty($name))
			{
				$operator = $this->parseConditionOperator($condition);
				$value = $this->parseConditionValue($condition, $name);
				$result[$operator.$name] = $value;

				if ($name === 'SECTION_ID')
				{
					$result['INCLUDE_SUBSECTIONS'] = isset($params['INCLUDE_SUBSECTIONS']) && $params['INCLUDE_SUBSECTIONS'] === 'N' ? 'N' : 'Y';

					if (isset($params['INCLUDE_SUBSECTIONS']) && $params['INCLUDE_SUBSECTIONS'] === 'A')
					{
						$result['SECTION_GLOBAL_ACTIVE'] = 'Y';
					}

					$result = array($result);
				}
			}
		}

		return $result;
	}

	protected function parseConditionName(array $condition)
	{
		$name = '';
		$conditionNameMap = array(
			'CondIBXmlID' => 'XML_ID',
			'CondIBSection' => 'SECTION_ID',
			'CondIBDateActiveFrom' => 'DATE_ACTIVE_FROM',
			'CondIBDateActiveTo' => 'DATE_ACTIVE_TO',
			'CondIBSort' => 'SORT',
			'CondIBDateCreate' => 'DATE_CREATE',
			'CondIBCreatedBy' => 'CREATED_BY',
			'CondIBTimestampX' => 'TIMESTAMP_X',
			'CondIBModifiedBy' => 'MODIFIED_BY',
			'CondIBTags' => 'TAGS',
			'CondCatQuantity' => 'QUANTITY',
			'CondCatWeight' => 'WEIGHT'
		);

		if (isset($conditionNameMap[$condition['CLASS_ID']]))
		{
			$name = $conditionNameMap[$condition['CLASS_ID']];
		}
		elseif (mb_strpos($condition['CLASS_ID'], 'CondIBProp') !== false)
		{
			$name = $condition['CLASS_ID'];
		}

		return $name;
	}

	protected function parseConditionOperator($condition)
	{
		$operator = '';

		switch ($condition['DATA']['logic'])
		{
			case 'Equal':
				$operator = '';
				break;
			case 'Not':
				$operator = '!';
				break;
			case 'Contain':
				$operator = '%';
				break;
			case 'NotCont':
				$operator = '!%';
				break;
			case 'Great':
				$operator = '>';
				break;
			case 'Less':
				$operator = '<';
				break;
			case 'EqGr':
				$operator = '>=';
				break;
			case 'EqLs':
				$operator = '<=';
				break;
		}

		return $operator;
	}

	protected function parseConditionValue($condition, $name)
	{
		$value = $condition['DATA']['value'];

		switch ($name)
		{
			case 'DATE_ACTIVE_FROM':
			case 'DATE_ACTIVE_TO':
			case 'DATE_CREATE':
			case 'TIMESTAMP_X':
				$value = ConvertTimeStamp($value, 'FULL');
				break;
		}

		return $value;
	}

	protected function parsePropertyCondition(array &$result, array $condition, $params)
	{
		if (!empty($result))
		{
			$subFilter = array();

			foreach ($result as $name => $value)
			{
				if (!empty($result[$name]) && is_array($result[$name]))
				{
					$this->parsePropertyCondition($result[$name], $condition, $params);
				}
				else
				{
					if (($ind = mb_strpos($name, 'CondIBProp')) !== false)
					{
						[$prefix, $iblock, $propertyId] = explode(':', $name);
						$operator = $ind > 0? mb_substr($prefix, 0, $ind) : '';

						$catalogInfo = \CCatalogSku::GetInfoByIBlock($iblock);
						if (!empty($catalogInfo))
						{
							if (
								$catalogInfo['CATALOG_TYPE'] != \CCatalogSku::TYPE_CATALOG
								&& $catalogInfo['IBLOCK_ID'] == $iblock
							)
							{
								$subFilter[$operator.'PROPERTY_'.$propertyId] = $value;
							}
							else
							{
								$result[$operator.'PROPERTY_'.$propertyId] = $value;
							}
						}

						unset($result[$name]);
					}
				}
			}

			if (!empty($subFilter) && !empty($catalogInfo))
			{
				$offerPropFilter = array(
					'IBLOCK_ID' => $catalogInfo['IBLOCK_ID'],
					'ACTIVE_DATE' => 'Y',
					'ACTIVE' => 'Y'
				);

				if ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'Y')
				{
					$offerPropFilter['HIDE_NOT_AVAILABLE'] = 'Y';
				}
				elseif ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'L')
				{
					$offerPropFilter[] = array(
						'LOGIC' => 'OR',
						'AVAILABLE' => 'Y',
						'SUBSCRIBE' => 'Y'
					);
				}

				if (count($subFilter) > 1)
				{
					$subFilter['LOGIC'] = $condition['DATA']['All'];
					$subFilter = array($subFilter);
				}

				$result['=ID'] = \CIBlockElement::SubQuery(
					'PROPERTY_'.$catalogInfo['SKU_PROPERTY_ID'],
					$offerPropFilter + $subFilter
				);
			}
		}
	}

	/**
	 * Process element data to set in $arResult.
	 *
	 * @param array &$element
	 * @return void
	 */
	protected function processElement(array &$element)
	{
		$this->modifyElementCommonData($element);
		$this->modifyElementPrices($element);
		$this->setElementPanelButtons($element);
	}

	/**
	 * Fill various common fields for element.
	 *
	 * @param array &$element			Element data.
	 * @return void
	 */
	protected function modifyElementCommonData(array &$element)
	{
		$element['ID'] = (int)$element['ID'];
		$element['IBLOCK_ID'] = (int)$element['IBLOCK_ID'];

		if ($this->arParams['HIDE_DETAIL_URL'])
		{
			$element['DETAIL_PAGE_URL'] = $element['~DETAIL_PAGE_URL'] = '';
		}

		if ($this->isEnableCompatible())
		{
			$element['ACTIVE_FROM'] = ($element['DATE_ACTIVE_FROM'] ?? null);
			$element['ACTIVE_TO'] = ($element['DATE_ACTIVE_TO'] ?? null);
		}

		$ipropValues = new Iblock\InheritedProperty\ElementValues($element['IBLOCK_ID'], $element['ID']);
		$element['IPROPERTY_VALUES'] = $ipropValues->getValues();

		Iblock\Component\Tools::getFieldImageData(
			$element,
			array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
			Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
			'IPROPERTY_VALUES'
		);

		if (isset($element['~TYPE']))
		{
			$productFields = $this->getProductFields($element['IBLOCK_ID']);
			$translateFields = $this->getCompatibleProductFields();

			$element['PRODUCT'] = array(
				'TYPE' => (int)$element['~TYPE'],
				'AVAILABLE' => $element['~AVAILABLE'],
				'BUNDLE' => $element['~BUNDLE'],
				'QUANTITY' => $element['~QUANTITY'],
				'QUANTITY_TRACE' => $element['~QUANTITY_TRACE'],
				'CAN_BUY_ZERO' => $element['~CAN_BUY_ZERO'],
				'MEASURE' => (int)$element['~MEASURE'],
				'SUBSCRIBE' => $element['~SUBSCRIBE'],
				'VAT_ID' => (int)$element['~VAT_ID'],
				'VAT_RATE' => 0,
				'VAT_INCLUDED' => $element['~VAT_INCLUDED'],
				'WEIGHT' => (float)$element['~WEIGHT'],
				'WIDTH' => (float)$element['~WIDTH'],
				'LENGTH' => (float)$element['~LENGTH'],
				'HEIGHT' => (float)$element['~HEIGHT'],
				'PAYMENT_TYPE' => $element['~PAYMENT_TYPE'],
				'RECUR_SCHEME_TYPE' => $element['~RECUR_SCHEME_TYPE'],
				'RECUR_SCHEME_LENGTH' => (int)$element['~RECUR_SCHEME_LENGTH'],
				'TRIAL_PRICE_ID' => (int)$element['~TRIAL_PRICE_ID']
			);

			$vatId = 0;
			$vatRate = 0;
			if ($element['PRODUCT']['VAT_ID'] > 0)
				$vatId = $element['PRODUCT']['VAT_ID'];
			elseif ($this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']] > 0)
				$vatId = $this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']];
			if ($vatId > 0 && isset($this->storage['VATS'][$vatId]))
				$vatRate = $this->storage['VATS'][$vatId];
			$element['PRODUCT']['VAT_RATE'] = $vatRate;
			unset($vatRate, $vatId);
			$element['PRODUCT']['USE_OFFERS'] = $element['PRODUCT']['TYPE'] == Catalog\ProductTable::TYPE_SKU;

			if ($this->isEnableCompatible())
			{
				foreach ($translateFields as $currentKey => $oldKey)
					$element[$oldKey] = $element[$currentKey];
				unset($currentKey, $oldKey);
				$element['~CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
				$element['CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
			}
			else
			{
				// temporary (compatibility custom templates)
				$element['~CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
				$element['CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
				$element['~CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
				$element['CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
				$element['~CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
				$element['CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
				$element['~CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
				$element['CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
				$element['~CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
				$element['CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
			}

			foreach ($productFields as $field)
				unset($element[$field], $element['~'.$field]);
			unset($field);
		}
		else
		{
			$element['PRODUCT'] = array(
				'TYPE' => null,
				'AVAILABLE' => null,
				'USE_OFFERS' => false
			);
		}

		$element['PROPERTIES'] = array();
		$element['DISPLAY_PROPERTIES'] = array();
		$element['PRODUCT_PROPERTIES'] = array();
		$element['PRODUCT_PROPERTIES_FILL'] = array();
		$element['OFFERS'] = array();
		$element['OFFER_ID_SELECTED'] = 0;

		if (!empty($this->storage['CATALOGS'][$element['IBLOCK_ID']]))
			$element['CHECK_QUANTITY'] = $this->isNeedCheckQuantity($element['PRODUCT']);

		if ($this->getAction() === 'bigDataLoad')
		{
			$element['RCM_ID'] = $this->recommendationIdToProduct[$element['ID']];
		}
	}

	/**
	 * Add Hermitage button links for element.
	 *
	 * @param array &$element			Element data.
	 * @return void
	 */
	protected function setElementPanelButtons(&$element)
	{
		$buttons = \CIBlock::GetPanelButtons(
			$element['IBLOCK_ID'],
			$element['ID'],
			$element['IBLOCK_SECTION_ID'],
			array('SECTION_BUTTONS' => false, 'SESSID' => false, 'CATALOG' => true)
		);
		$element['EDIT_LINK'] = ($buttons['edit']['edit_element']['ACTION_URL'] ?? null);
		$element['DELETE_LINK'] = ($buttons['edit']['delete_element']['ACTION_URL'] ?? null);
	}

	/**
	 * Process element display properties by iblock parameters.
	 *
	 * @param int $iblock					Iblock ID.
	 * @param array &$iblockElements		Items.
	 * @return void
	 */
	protected function modifyDisplayProperties($iblock, &$iblockElements)
	{
	}

	protected function getPropertyList($iblock, $propertyCodes)
	{
		$propertyList = array();
		if (empty($propertyCodes))
			return $propertyList;

		$propertyCodes = array_fill_keys($propertyCodes, true);

		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'CODE', 'SORT'),
			'filter' => array('=IBLOCK_ID' => $iblock, '=ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
		));
		while ($property = $propertyIterator->fetch())
		{
			$code = (string)$property['CODE'];

			if ($code == '')
			{
				$code = $property['ID'];
			}

			if (!isset($propertyCodes[$code]))
				continue;

			$propertyList[] = $code;
		}

		return $propertyList;
	}

	/**
	 * Clear products data.
	 *
	 * @return void
	 */
	protected function clearItems()
	{
		$this->prices = array();
		$this->measures = array();
		$this->ratios = array();
		$this->quantityRanges = array();
		$this->oldData = array();
	}

	/**
	 * Load measure ratios for items.
	 *
	 * @param array $itemIds		Items id list.
	 *
	 * @return void
	 */
	protected function loadMeasureRatios(array $itemIds)
	{
		if (empty($itemIds))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($itemIds, true);
		if (empty($itemIds))
			return;
		$emptyRatioIds = array_fill_keys($itemIds, true);

		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('ID', 'RATIO', 'IS_DEFAULT', 'PRODUCT_ID'),
			'filter' => array('@PRODUCT_ID' => $itemIds),
			'order' => array('PRODUCT_ID' => 'ASC')// not add 'RATIO' => 'ASC' - result will be resorted after load prices
		));
		while ($row = $iterator->fetch())
		{
			$ratio = max((float)$row['RATIO'], (int)$row['RATIO']);
			if ($ratio > CATALOG_VALUE_EPSILON)
			{
				$row['RATIO'] = $ratio;
				$row['ID'] = (int)$row['ID'];
				$id = (int)$row['PRODUCT_ID'];
				if (!isset($this->ratios[$id]))
					$this->ratios[$id] = array();
				$this->ratios[$id][$row['ID']] = $row;
				unset($emptyRatioIds[$id]);
				unset($id);
			}
			unset($ratio);
		}
		unset($row, $iterator);
		if (!empty($emptyRatioIds))
		{
			$emptyRatio = $this->getEmptyRatio();
			foreach (array_keys($emptyRatioIds) as $id)
			{
				$this->ratios[$id] = array(
					$emptyRatio['ID'] => $emptyRatio
				);
			}
			unset($id, $emptyRatio);
		}
		unset($emptyRatioIds);
	}

	/**
	 * Return default empty ratio (unexist in database).
	 *
	 * @return array
	 */
	protected function getEmptyRatio()
	{
		return array(
			'ID' => 0,
			'RATIO' => 1,
			'IS_DEFAULT' => 'Y'
		);
	}

	/**
	 * Init measure for items.
	 *
	 * @param array &$items			Items list.
	 * @return void
	 */
	protected function initItemsMeasure(array &$items)
	{
		if (empty($items))
			return;

		foreach (array_keys($items) as $index)
		{
			if (!isset($items[$index]['PRODUCT']['MEASURE']))
				continue;
			if ($items[$index]['PRODUCT']['MEASURE'] > 0)
			{
				$items[$index]['ITEM_MEASURE'] = array(
					'ID' => $items[$index]['PRODUCT']['MEASURE'],
					'TITLE' => '',
					'~TITLE' => ''
				);
			}
			else
			{
				$items[$index]['ITEM_MEASURE'] = array(
					'ID' => null,
					'TITLE' => $this->storage['DEFAULT_MEASURE']['SYMBOL_RUS'],
					'~TITLE' => $this->storage['DEFAULT_MEASURE']['~SYMBOL_RUS']
				);
			}
		}
		unset($index);
	}

	/**
	 * Return measure ids for items.
	 *
	 * @param array $items			Items data.
	 * @return array
	 */
	protected function getMeasureIds(array $items)
	{
		$result = array();

		if (!empty($items))
		{
			foreach (array_keys($items) as $itemId)
			{
				if (!isset($items[$itemId]['ITEM_MEASURE']))
					continue;
				$measureId = (int)$items[$itemId]['ITEM_MEASURE']['ID'];
				if ($measureId > 0)
					$result[$measureId] = $measureId;
				unset($measureId);
			}
			unset($itemId);
		}

		return $result;
	}

	/**
	 * Load measures data.
	 *
	 * @param array $measureIds
	 * @return void
	 */
	protected function loadMeasures(array $measureIds)
	{
		if (empty($measureIds))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($measureIds, true);
		if (empty($measureIds))
			return;

		$measureIterator = \CCatalogMeasure::getList(
			array(),
			array('@ID' => $measureIds),
			false,
			false,
			array('ID', 'SYMBOL_RUS')
		);
		while ($measure = $measureIterator->GetNext())
		{
			$measure['ID'] = (int)$measure['ID'];
			$measure['TITLE'] = $measure['SYMBOL_RUS'];
			$measure['~TITLE'] = $measure['~SYMBOL_RUS'];
			unset($measure['SYMBOL_RUS'], $measure['~SYMBOL_RUS']);
			$this->measures[$measure['ID']] = $measure;
		}
		unset($measure, $measureIterator);
	}

	/**
	 * Load prices for items.
	 *
	 * @param array $itemIds		Item ids.
	 * @return void
	 */
	protected function loadPrices(array $itemIds)
	{
		if (empty($itemIds))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($itemIds, true);
		if (empty($itemIds))
			return;

		$this->loadMeasureRatios($itemIds);

		if (empty($this->storage['PRICES_ALLOW']))
			return;

		$enableCompatible = $this->isEnableCompatible();

		$quantityList = array_fill_keys($itemIds, array());

		$select = array(
			'ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY',
			'QUANTITY_FROM', 'QUANTITY_TO', 'PRICE_SCALE'
		);
		if ($enableCompatible)
			$select[] = 'EXTRA_ID';

		$pagedItemIds = array_chunk($itemIds, 500);
		foreach ($pagedItemIds as $pageIds)
		{
			if (empty($pageIds))
				continue;

			$iterator = Catalog\PriceTable::getList(array(
				'select' => $select,
				'filter' => array('@PRODUCT_ID' => $pageIds, '@CATALOG_GROUP_ID' => $this->storage['PRICES_ALLOW']),
				'order' => array('PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['PRODUCT_ID'];
				unset($row['PRODUCT_ID']);
				if (!isset($this->prices[$id]))
				{
					$this->prices[$id] = array(
						'RATIO' => array(),
						'QUANTITY' => array(),
						'SIMPLE' => array()
					);
				}

				if ($row['QUANTITY_FROM'] !== null || $row['QUANTITY_TO'] !== null)
				{
					$hash = $this->getQuantityRangeHash($row);
					if (!isset($quantityList[$id][$hash]))
					{
						$quantityList[$id][$hash] = array(
							'HASH' => $hash,
							'QUANTITY_FROM' => $row['QUANTITY_FROM'],
							'QUANTITY_TO' => $row['QUANTITY_TO'],
							'SORT_FROM' => (int)$row['QUANTITY_FROM'],
							'SORT_TO' => ($row['QUANTITY_TO'] === null ? INF : (int)$row['QUANTITY_TO'])
						);
					}
					if (!isset($this->prices[$id]['QUANTITY'][$hash]))
					{
						$this->prices[$id]['QUANTITY'][$hash] = array();
					}
					$this->prices[$id]['QUANTITY'][$hash][$row['CATALOG_GROUP_ID']] = $row;
					unset($hash);
				}
				elseif (!isset($row['MEASURE_RATIO_ID']))
				{
					$this->prices[$id]['SIMPLE'][$row['CATALOG_GROUP_ID']] = $row;
				}
				$this->storage['CURRENCY_LIST'][$row['CURRENCY']] = $row['CURRENCY'];

				unset($id);
			}
			unset($row, $iterator);
		}
		unset($pageIds, $pagedItemIds);

		foreach ($itemIds as $id)
		{
			if (isset($this->prices[$id]))
			{
				foreach ($this->prices[$id] as $key => $data)
				{
					if (empty($data))
						unset($this->prices[$id][$key]);
				}
				unset($key, $data);

				if (count($this->prices[$id]) !== 1)
				{
					unset($this->prices[$id]);
				}
				else
				{
					if (!empty($this->prices[$id]['QUANTITY']))
					{
						$productQuantity = $quantityList[$id];
						Main\Type\Collection::sortByColumn(
							$productQuantity,
							array('SORT_FROM' => SORT_ASC, 'SORT_TO' => SORT_ASC),
							'', null, true
						);
						$this->quantityRanges[$id] = $productQuantity;
						unset($productQuantity);

						if (count($this->ratios[$id]) > 1)
							$this->compactItemRatios($id);
					}
					if (!empty($this->prices[$id]['SIMPLE']))
					{
						$range = $this->getFullQuantityRange();
						$this->quantityRanges[$id] = array(
							$range['HASH'] => $range
						);
						unset($range);
						if (count($this->ratios[$id]) > 1)
							$this->compactItemRatios($id);
					}
				}
			}
		}
		unset($id);

		unset($quantityList);

		unset($enableCompatible);
	}

	protected function calculateItemPrices(array &$items)
	{
		if (empty($items))
			return;

		$enableCompatible = $this->isEnableCompatible();

		if ($enableCompatible)
			$this->initCompatibleFields($items);

		foreach (array_keys($items) as $index)
		{
			$id = $items[$index]['ID'];
			if (!isset($this->calculatePrices[$id]))
				continue;
			if (empty($this->prices[$id]))
				continue;
			$productPrices = $this->prices[$id];
			$result = array(
				'ITEM_PRICE_MODE' => null,
				'ITEM_PRICES' => array(),
				'ITEM_PRICES_CAN_BUY' => false
			);
			if ($this->arParams['FILL_ITEM_ALL_PRICES'])
				$result['ITEM_ALL_PRICES'] = array();
			$priceBlockIndex = 0;
			if (!empty($productPrices['QUANTITY']))
			{
				$result['ITEM_PRICE_MODE'] = Catalog\ProductTable::PRICE_MODE_QUANTITY;
				$ratio = current($this->ratios[$id]);
				foreach ($this->quantityRanges[$id] as $range)
				{
					$priceBlock = $this->calculatePriceBlock(
						$items[$index],
						$productPrices['QUANTITY'][$range['HASH']],
						$ratio['RATIO'],
						$this->arParams['USE_PRICE_COUNT'] || $this->checkQuantityRange($range)
					);
					if (!empty($priceBlock))
					{
						$minimalPrice = ($this->arParams['FILL_ITEM_ALL_PRICES']
							? $priceBlock['MINIMAL_PRICE']
							: $priceBlock
						);
						if ($minimalPrice['QUANTITY_FROM'] === null)
						{
							$minimalPrice['MIN_QUANTITY'] = $ratio['RATIO'];
						}
						else
						{
							$minimalPrice['MIN_QUANTITY'] = $ratio['RATIO'] * ((int)($minimalPrice['QUANTITY_FROM']/$ratio['RATIO']));
							if ($minimalPrice['MIN_QUANTITY'] < $minimalPrice['QUANTITY_FROM'])
								$minimalPrice['MIN_QUANTITY'] += $ratio['RATIO'];
						}
						$result['ITEM_PRICES'][$priceBlockIndex] = $minimalPrice;
						if (isset($this->storage['PRICES_CAN_BUY'][$minimalPrice['PRICE_TYPE_ID']]))
							$result['ITEM_PRICES_CAN_BUY'] = true;
						if ($this->arParams['FILL_ITEM_ALL_PRICES'])
						{
							$priceBlock['ALL_PRICES']['MIN_QUANTITY'] = $minimalPrice['MIN_QUANTITY'];
							$result['ITEM_ALL_PRICES'][$priceBlockIndex] = $priceBlock['ALL_PRICES'];
						}
						unset($minimalPrice);
						$priceBlockIndex++;
					}
					unset($priceBlock);
				}
				unset($range);
				unset($ratio);
			}
			if (!empty($productPrices['SIMPLE']))
			{
				$result['ITEM_PRICE_MODE'] = Catalog\ProductTable::PRICE_MODE_SIMPLE;
				$ratio = current($this->ratios[$id]);
				$priceBlock = $this->calculatePriceBlock(
					$items[$index],
					$productPrices['SIMPLE'],
					$ratio['RATIO'],
					true
				);
				if (!empty($priceBlock))
				{
					$minimalPrice = ($this->arParams['FILL_ITEM_ALL_PRICES']
						? $priceBlock['MINIMAL_PRICE']
						: $priceBlock
					);
					$minimalPrice['MIN_QUANTITY'] = $ratio['RATIO'];
					$result['ITEM_PRICES'][$priceBlockIndex] = $minimalPrice;
					if (isset($this->storage['PRICES_CAN_BUY'][$minimalPrice['PRICE_TYPE_ID']]))
						$result['ITEM_PRICES_CAN_BUY'] = true;
					if ($this->arParams['FILL_ITEM_ALL_PRICES'])
					{
						$priceBlock['ALL_PRICES']['MIN_QUANTITY'] = $minimalPrice['MIN_QUANTITY'];
						$result['ITEM_ALL_PRICES'][$priceBlockIndex] = $priceBlock['ALL_PRICES'];
					}
					unset($minimalPrice);
					$priceBlockIndex++;
				}
				unset($priceBlock);
				unset($ratio);
			}
			$this->prices[$id] = $result;

			if (isset($items[$index]['ACTIVE']) && $items[$index]['ACTIVE'] === 'N')
			{
				$items[$index]['CAN_BUY'] = false;
			}
			else
			{
				$items[$index]['CAN_BUY'] = $result['ITEM_PRICES_CAN_BUY'] && $items[$index]['PRODUCT']['AVAILABLE'] === 'Y';
			}

			unset($priceBlockIndex, $result);
			unset($productPrices);

			if ($enableCompatible)
				$this->resortOldPrices($id);
		}
		unset($index);
	}

	protected function transferItems(array &$items)
	{
		if (empty($items))
			return;

		$enableCompatible = $this->isEnableCompatible();
		$urls = $this->storage['URLS'];

		foreach (array_keys($items) as $index)
		{
			$itemId = $items[$index]['ID'];
			// measure
			if (!empty($items[$index]['ITEM_MEASURE']))
			{
				$id = (int)$items[$index]['ITEM_MEASURE']['ID'];
				if (isset($this->measures[$id]))
				{
					$items[$index]['ITEM_MEASURE']['TITLE'] = $this->measures[$id]['TITLE'];
					$items[$index]['ITEM_MEASURE']['~TITLE'] = $this->measures[$id]['~TITLE'];
				}
				unset($id);
			}
			// prices
			$items[$index]['ITEM_MEASURE_RATIOS'] = $this->ratios[$itemId] ?? [];
			$items[$index]['ITEM_MEASURE_RATIO_SELECTED'] = $this->searchItemSelectedRatioId($itemId);
			$items[$index]['ITEM_QUANTITY_RANGES'] = $this->quantityRanges[$itemId] ?? [];
			$items[$index]['ITEM_QUANTITY_RANGE_SELECTED'] = $this->searchItemSelectedQuantityRangeHash($itemId);
			if (!empty($this->prices[$itemId]))
			{
				$items[$index] = array_merge($items[$index], $this->prices[$itemId]);
				if (!empty($items[$index]['ITEM_PRICES']))
				{
					switch ($items[$index]['ITEM_PRICE_MODE'])
					{
						case Catalog\ProductTable::PRICE_MODE_SIMPLE:
							$items[$index]['ITEM_PRICE_SELECTED'] = 0;
							break;
						case Catalog\ProductTable::PRICE_MODE_QUANTITY:
							foreach (array_keys($items[$index]['ITEM_PRICES']) as $priceIndex)
							{
								if ($items[$index]['ITEM_PRICES'][$priceIndex]['QUANTITY_HASH'] == $items[$index]['ITEM_QUANTITY_RANGE_SELECTED'])
								{
									$items[$index]['ITEM_PRICE_SELECTED'] = $priceIndex;
									break;
								}
							}
							break;
						case Catalog\ProductTable::PRICE_MODE_RATIO:
							foreach (array_keys($items[$index]['ITEM_PRICES']) as $priceIndex)
							{
								if ($items[$index]['ITEM_PRICES'][$priceIndex]['MEASURE_RATIO_ID'] == $items[$index]['ITEM_MEASURE_RATIO_SELECTED'])
								{
									$items[$index]['ITEM_PRICE_SELECTED'] = $priceIndex;
									break;
								}
							}
							break;
					}
				}
			}

			// compatibility
			if ($enableCompatible)
			{
				// old links to buy, add to basket, etc
				$id = $items[$index]['ID'];
				$items[$index]['~BUY_URL'] = str_replace('#ID#', $id, $urls['~BUY_URL_TEMPLATE']);
				$items[$index]['BUY_URL'] = str_replace('#ID#', $id, $urls['BUY_URL_TEMPLATE']);
				$items[$index]['~ADD_URL'] = str_replace('#ID#', $id, $urls['~ADD_URL_TEMPLATE']);
				$items[$index]['ADD_URL'] = str_replace('#ID#', $id, $urls['ADD_URL_TEMPLATE']);
				$items[$index]['~SUBSCRIBE_URL'] = str_replace('#ID#', $id, $urls['~SUBSCRIBE_URL_TEMPLATE']);
				$items[$index]['SUBSCRIBE_URL'] = str_replace('#ID#', $id, $urls['SUBSCRIBE_URL_TEMPLATE']);
				if ($this->arParams['DISPLAY_COMPARE'])
				{
					$items[$index]['~COMPARE_URL'] = str_replace('#ID#', $id, $urls['~COMPARE_URL_TEMPLATE']);
					$items[$index]['COMPARE_URL'] = str_replace('#ID#', $id, $urls['COMPARE_URL_TEMPLATE']);
					$items[$index]['~COMPARE_DELETE_URL'] = str_replace('#ID#', $id, $urls['~COMPARE_DELETE_URL_TEMPLATE']);
					$items[$index]['COMPARE_DELETE_URL'] = str_replace('#ID#', $id, $urls['COMPARE_DELETE_URL_TEMPLATE']);
				}
				unset($id);

				// old measure
				$items[$index]['CATALOG_MEASURE_NAME'] = $items[$index]['ITEM_MEASURE']['TITLE'];
				$items[$index]['~CATALOG_MEASURE_NAME'] = $items[$index]['ITEM_MEASURE']['~TITLE'];

				// old measure ratio
				$items[$index]['CATALOG_MEASURE_RATIO'] = $items[$index]['ITEM_MEASURE_RATIOS'][$items[$index]['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'] ?? 1;

				// old fields
				if (!empty($this->oldData[$itemId]))
					$items[$index] = array_merge($this->oldData[$itemId], $items[$index]);
			}
			unset($itemId);
		}
		unset($index);
		unset($urls, $enableCompatible);
	}

	/**
	 * Calculate price block (simple price, quantity range, etc).
	 *
	 * @param array $product            Product data.
	 * @param array $priceBlock         Prices.
	 * @param int|float $ratio          Measure ratio value.
	 * @param bool $defaultBlock        Save result to old keys (PRICES, PRICE_MATRIX, MIN_PRICE).
	 * @return array|null
	 */
	protected function calculatePriceBlock(array $product, array $priceBlock, $ratio, $defaultBlock = false)
	{
		if (empty($product) || empty($priceBlock))
			return null;

		$enableCompatible = $defaultBlock && $this->isEnableCompatible();

		if ($enableCompatible && !$this->arParams['USE_PRICE_COUNT'])
			$this->fillCompatibleRawPriceFields($product['ID'], $priceBlock);

		$userGroups = $this->getUserGroups();

		$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
		/** @var null|array $minimalPrice */
		$minimalPrice = null;
		/** @var null|array $minimalBuyerPrice */
		$minimalBuyerPrice = null;
		$fullPrices = array();

		$currencyConvert = $this->arParams['CONVERT_CURRENCY'] === 'Y';
		$resultCurrency = ($currencyConvert ? $this->storage['CONVERT_CURRENCY']['CURRENCY_ID'] : null);

		$vatRate = (float)$product['PRODUCT']['VAT_RATE'];
		$percentVat = $vatRate * 0.01;
		$percentPriceWithVat = 1 + $percentVat;
		$vatInclude = $product['PRODUCT']['VAT_INCLUDED'] === 'Y';

		$oldPrices = array();
		$oldMinPrice = false;
		$oldMatrix = false;
		if ($enableCompatible && $this->arParams['USE_PRICE_COUNT'])
		{
			$oldMatrix = $this->getCompatibleFieldValue($product['ID'], 'PRICE_MATRIX');
			if (empty($oldMatrix))
			{
				$oldMatrix = $this->getEmptyPriceMatrix();
				$oldMatrix['AVAILABLE'] = $product['PRODUCT']['AVAILABLE'];
			}
		}

		foreach ($priceBlock as $rawPrice)
		{
			$priceType = (int)$rawPrice['CATALOG_GROUP_ID'];
			$price = (float)$rawPrice['PRICE'];
			if (!$vatInclude)
				$price *= $percentPriceWithVat;
			$currency = $rawPrice['CURRENCY'];

			$changeCurrency = $currencyConvert && $currency !== $resultCurrency;
			if ($changeCurrency)
			{
				$price = \CCurrencyRates::ConvertCurrency($price, $currency, $resultCurrency);
				$currency = $resultCurrency;
			}

			$discounts = array();
			if (\CIBlockPriceTools::isEnabledCalculationDiscounts())
			{
				\CCatalogDiscountSave::Disable();
				$discounts = \CCatalogDiscount::GetDiscount(
					$product['ID'],
					$product['IBLOCK_ID'],
					array($priceType),
					$userGroups,
					'N',
					$this->getSiteId(),
					array()
				);
				\CCatalogDiscountSave::Enable();
			}
			$discountPrice = \CCatalogProduct::CountPriceWithDiscount(
				$price,
				$currency,
				$discounts
			);
			if ($discountPrice !== false)
			{
				$priceWithVat = array(
					'UNROUND_BASE_PRICE' => $price,
					'UNROUND_PRICE' => $discountPrice,
					'BASE_PRICE' => Catalog\Product\Price::roundPrice(
						$priceType,
						$price,
						$currency
					),
					'PRICE' => Catalog\Product\Price::roundPrice(
						$priceType,
						$discountPrice,
						$currency
					)
				);

				$price /= $percentPriceWithVat;
				$discountPrice /= $percentPriceWithVat;

				$priceWithoutVat = array(
					'UNROUND_BASE_PRICE' => $price,
					'UNROUND_PRICE' => $discountPrice,
					'BASE_PRICE' => Catalog\Product\Price::roundPrice(
						$priceType,
						$price,
						$currency
					),
					'PRICE' => Catalog\Product\Price::roundPrice(
						$priceType,
						$discountPrice,
						$currency
					)
				);

				if ($this->arParams['PRICE_VAT_INCLUDE'])
					$priceRow = $priceWithVat;
				else
					$priceRow = $priceWithoutVat;
				$priceRow['ID'] = $rawPrice['ID'];
				$priceRow['PRICE_TYPE_ID'] = $rawPrice['CATALOG_GROUP_ID'];
				$priceRow['CURRENCY'] = $currency;

				if (
					empty($discounts)
					|| ($priceRow['BASE_PRICE'] <= $priceRow['PRICE'])
				)
				{
					$priceRow['BASE_PRICE'] = $priceRow['PRICE'];
					$priceRow['DISCOUNT'] = 0;
					$priceRow['PERCENT'] = 0;
				}
				else
				{
					$priceRow['DISCOUNT'] = $priceRow['BASE_PRICE'] - $priceRow['PRICE'];
					$priceRow['PERCENT'] = roundEx(100*$priceRow['DISCOUNT']/$priceRow['BASE_PRICE'], 0);
				}
				if (isset($this->arParams['PRICE_VAT_SHOW_VALUE']) && $this->arParams['PRICE_VAT_SHOW_VALUE'])
					$priceRow['VAT'] = ($vatRate > 0 ? $priceWithVat['PRICE'] - $priceWithoutVat['PRICE'] : 0);

				if ($this->arParams['FILL_ITEM_ALL_PRICES'])
					$fullPrices[$priceType] = $priceRow;

				$priceRow['QUANTITY_FROM'] = $rawPrice['QUANTITY_FROM'];
				$priceRow['QUANTITY_TO'] = $rawPrice['QUANTITY_TO'];
				$priceRow['QUANTITY_HASH'] = $this->getQuantityRangeHash($rawPrice);
				$priceRow['MEASURE_RATIO_ID'] = $rawPrice['MEASURE_RATIO_ID'] ?? null;
				$priceRow['PRICE_SCALE'] = \CCurrencyRates::ConvertCurrency(
					$priceRow['PRICE'],
					$priceRow['CURRENCY'],
					$baseCurrency
				);
				$priceRow['BASE_PRICE_SCALE'] = $rawPrice['PRICE_SCALE'];

				if (
					$minimalPrice === null
					|| $minimalPrice['PRICE_SCALE'] > $priceRow['PRICE_SCALE']
				)
				{
					$minimalPrice = $priceRow;
				}
				elseif (
					$minimalPrice['PRICE_SCALE'] == $priceRow['PRICE_SCALE']
					&& $minimalPrice['BASE_PRICE_SCALE'] > $priceRow['BASE_PRICE_SCALE']
				)
				{
					$minimalPrice = $priceRow;
				}
				if (isset($this->storage['PRICES_CAN_BUY'][$priceRow['PRICE_TYPE_ID']]))
				{
					if (
						$minimalBuyerPrice === null
						|| $minimalBuyerPrice['PRICE_SCALE'] > $priceRow['PRICE_SCALE']
					)
					{
						$minimalBuyerPrice = $priceRow;
					}
					elseif (
						$minimalBuyerPrice['PRICE_SCALE'] == $priceRow['PRICE_SCALE']
						&& $minimalBuyerPrice['BASE_PRICE_SCALE'] > $priceRow['BASE_PRICE_SCALE']
					)
					{
						$minimalBuyerPrice = $priceRow;
					}
				}

				if ($enableCompatible)
				{
					if ($this->arParams['USE_PRICE_COUNT'])
					{
						$rowIndex = $this->getQuantityRangeHash($rawPrice);
						$oldMatrix['ROWS'][$rowIndex] = array(
							'QUANTITY_FROM' => (float)$rawPrice['QUANTITY_FROM'],
							'QUANTITY_TO' => (float)$rawPrice['QUANTITY_TO']
						);
						if (!isset($oldMatrix['MATRIX'][$priceType]))
						{
							$oldMatrix['MATRIX'][$priceType] = array();
							$oldMatrix['COLS'][$priceType] = $this->storage['PRICE_TYPES'][$priceType];
						}
						$oldMatrix['MATRIX'][$priceType][$rowIndex] = array(
							'ID' => $priceRow['ID'],
							'PRICE' => $priceRow['BASE_PRICE'],
							'DISCOUNT_PRICE' => $priceRow['PRICE'],
							'UNROUND_DISCOUNT_PRICE' => $priceRow['UNROUND_PRICE'],
							'CURRENCY' => $priceRow['CURRENCY'],
							'VAT_RATE' => $percentVat
						);
						if ($changeCurrency)
						{
							$oldMatrix['MATRIX'][$priceType][$rowIndex]['ORIG_CURRENCY'] = $rawPrice['CURRENCY'];
							$oldMatrix['MATRIX'][$priceType][$rowIndex]['ORIG_PRICE'] = \CCurrencyRates::ConvertCurrency(
								$priceRow['BASE_PRICE'],
								$priceRow['CURRENCY'],
								$rawPrice['CURRENCY']
							);
							$oldMatrix['MATRIX'][$priceType][$rowIndex]['ORIG_DISCOUNT_PRICE'] = \CCurrencyRates::ConvertCurrency(
								$priceRow['PRICE'],
								$priceRow['CURRENCY'],
								$rawPrice['CURRENCY']
							);
							$oldMatrix['MATRIX'][$priceType][$rowIndex]['ORIG_VAT_RATE'] = $percentVat; // crazy key, but above all the compatibility
						}
					}
					else
					{
						$priceCode = $this->storage['PRICES_MAP'][$priceType];
						$oldPriceRow = array(
							'PRICE_ID' => $priceRow['PRICE_TYPE_ID'],
							'ID' => $priceRow['ID'],
							'CAN_ACCESS' => ($this->storage['PRICES'][$priceCode]['CAN_VIEW'] ? 'Y' : 'N'),
							'CAN_BUY' => ($this->storage['PRICES'][$priceCode]['CAN_BUY'] ? 'Y' : 'N'),
							'MIN_PRICE' => 'N',
							'CURRENCY' => $priceRow['CURRENCY'],
							'VALUE_VAT' => $priceWithVat['UNROUND_BASE_PRICE'],
							'VALUE_NOVAT' => $priceWithoutVat['UNROUND_BASE_PRICE'],
							'DISCOUNT_VALUE_VAT' => $priceWithVat['UNROUND_PRICE'],
							'DISCOUNT_VALUE_NOVAT' => $priceWithoutVat['UNROUND_PRICE'],
							'ROUND_VALUE_VAT' => $priceWithVat['PRICE'],
							'ROUND_VALUE_NOVAT' => $priceWithoutVat['PRICE'],
							'VALUE' => $priceRow['BASE_PRICE'],
							'UNROUND_DISCOUNT_VALUE' => $priceRow['UNROUND_PRICE'],
							'DISCOUNT_VALUE' => $priceRow['PRICE'],
							'DISCOUNT_DIFF' => $priceRow['DISCOUNT'],
							'DISCOUNT_DIFF_PERCENT' => $priceRow['PERCENT']
						);
						$oldPriceRow['VATRATE_VALUE'] = $oldPriceRow['VALUE_VAT'] - $oldPriceRow['VALUE_NOVAT'];
						$oldPriceRow['DISCOUNT_VATRATE_VALUE'] = $oldPriceRow['DISCOUNT_VALUE_VAT'] - $oldPriceRow['DISCOUNT_VALUE_NOVAT'];
						$oldPriceRow['ROUND_VATRATE_VALUE'] = $oldPriceRow['ROUND_VALUE_VAT'] - $oldPriceRow['ROUND_VALUE_NOVAT'];
						if ($changeCurrency)
							$oldPriceRow['ORIG_CURRENCY'] = $rawPrice['CURRENCY'];
						$oldPrices[$priceCode] = $oldPriceRow;
						unset($oldPriceRow);
					}
				}
			}
			unset($discounts);
			unset($priceType);
		}
		unset($price);

		$minimalPriceId = null;
		if (is_array($minimalBuyerPrice))
			$minimalPrice = $minimalBuyerPrice;
		if (is_array($minimalPrice))
		{
			unset($minimalPrice['PRICE_SCALE']);
			unset($minimalPrice['BASE_PRICE_SCALE']);
			$minimalPriceId = $minimalPrice['PRICE_TYPE_ID'];
			$prepareFields = array(
				'BASE_PRICE', 'PRICE', 'DISCOUNT'
			);
			if (isset($this->arParams['PRICE_VAT_SHOW_VALUE']) && $this->arParams['PRICE_VAT_SHOW_VALUE'])
				$prepareFields[] = 'VAT';

			foreach ($prepareFields as $fieldName)
			{
				$minimalPrice['PRINT_'.$fieldName] = \CCurrencyLang::CurrencyFormat(
					$minimalPrice[$fieldName],
					$minimalPrice['CURRENCY'],
					true
				);
				$minimalPrice['RATIO_'.$fieldName] = $minimalPrice[$fieldName]*$ratio;
				$minimalPrice['PRINT_RATIO_'.$fieldName] = \CCurrencyLang::CurrencyFormat(
					$minimalPrice['RATIO_'.$fieldName],
					$minimalPrice['CURRENCY'],
					true
				);
			}
			unset($fieldName);

			if ($this->arParams['FILL_ITEM_ALL_PRICES'])
			{
				foreach (array_keys($fullPrices) as $priceType)
				{
					foreach ($prepareFields as $fieldName)
					{
						$fullPrices[$priceType]['PRINT_'.$fieldName] = \CCurrencyLang::CurrencyFormat(
							$fullPrices[$priceType][$fieldName],
							$fullPrices[$priceType]['CURRENCY'],
							true
						);
						$fullPrices[$priceType]['RATIO_'.$fieldName] = $fullPrices[$priceType][$fieldName]*$ratio;
						$fullPrices[$priceType]['PRINT_RATIO_'.$fieldName] = \CCurrencyLang::CurrencyFormat(
							$minimalPrice['RATIO_'.$fieldName],
							$minimalPrice['CURRENCY'],
							true
						);
					}
					unset($fieldName);
				}
				unset($priceType);
			}

			unset($prepareFields);
		}

		if ($enableCompatible)
		{
			if ($this->arParams['USE_PRICE_COUNT'])
			{
				$oldMatrix['CAN_BUY'] = array_values($this->storage['PRICES_CAN_BUY']);
				$this->oldData[$product['ID']]['PRICE_MATRIX'] = $oldMatrix;
			}
			else
			{
				$convertFields = array(
					'VALUE_NOVAT', 'VALUE_VAT', 'VATRATE_VALUE',
					'DISCOUNT_VALUE_NOVAT', 'DISCOUNT_VALUE_VAT', 'DISCOUNT_VATRATE_VALUE'
				);

				$prepareFields = array(
					'VALUE_NOVAT', 'VALUE_VAT', 'VATRATE_VALUE',
					'DISCOUNT_VALUE_NOVAT', 'DISCOUNT_VALUE_VAT', 'DISCOUNT_VATRATE_VALUE',
					'VALUE', 'DISCOUNT_VALUE', 'DISCOUNT_DIFF'
				);

				if (!empty($oldPrices))
				{
					foreach (array_keys($oldPrices) as $index)
					{
						foreach ($prepareFields as $fieldName)
							$oldPrices[$index]['PRINT_'.$fieldName] = \CCurrencyLang::CurrencyFormat(
								$oldPrices[$index][$fieldName],
								$oldPrices[$index]['CURRENCY'],
								true
							);
						unset($fieldName);
						if (isset($oldPrices[$index]['ORIG_CURRENCY']))
						{
							foreach ($convertFields as $fieldName)
								$oldPrices[$index]['ORIG_' . $fieldName] = \CCurrencyRates::ConvertCurrency(
									$oldPrices[$index][$fieldName],
									$oldPrices[$index]['CURRENCY'],
									$oldPrices[$index]['ORIG_CURRENCY']
								);
							unset($fieldName);
						}
						if ($oldPrices[$index]['PRICE_ID'] === $minimalPriceId)
						{
							$oldPrices[$index]['MIN_PRICE'] = 'Y';
							$oldMinPrice = $oldPrices[$index];
						}
					}
					unset($index);
				}
				unset($prepareFields);

				$this->oldData[$product['ID']]['PRICES'] = $oldPrices;
				$this->oldData[$product['ID']]['MIN_PRICE'] = $oldMinPrice;
			}
		}
		unset($oldMatrix, $oldMinPrice, $oldPrices);

		if (!$this->arParams['FILL_ITEM_ALL_PRICES'])
			return $minimalPrice;

		return array(
			'MINIMAL_PRICE' => $minimalPrice,
			'ALL_PRICES' => array(
				'QUANTITY_FROM' => $minimalPrice['QUANTITY_FROM'],
				'QUANTITY_TO' => $minimalPrice['QUANTITY_TO'],
				'QUANTITY_HASH' => $minimalPrice['QUANTITY_HASH'],
				'MEASURE_RATIO_ID' => $minimalPrice['MEASURE_RATIO_ID'],
				'PRICES' => $fullPrices
			)
		);
	}

	protected function searchItemSelectedRatioId($id)
	{
		if (!isset($this->ratios[$id]))
			return null;

		$minimal = null;
		$minimalRatio = null;
		$result = null;
		foreach ($this->ratios[$id] as $ratio)
		{
			if ($minimalRatio === null || $minimalRatio > $ratio['RATIO'])
			{
				$minimalRatio = $ratio['RATIO'];
				$minimal = $ratio['ID'];
			}
			if ($ratio['IS_DEFAULT'] === 'Y')
			{
				$result = $ratio['ID'];
				break;
			}
		}
		unset($ratio);
		return ($result === null ? $minimal : $result);
	}

	protected function compactItemRatios($id)
	{
		$ratioId = $this->searchItemSelectedRatioId($id);
		if ($ratioId === null)
			return;
		$this->ratios[$id] = array(
			$ratioId => $this->ratios[$id][$ratioId]
		);
	}

	protected function getQuantityRangeHash(array $range)
	{
		return ($range['QUANTITY_FROM'] === null ? 'ZERO' : $range['QUANTITY_FROM']).
			'-'.($range['QUANTITY_TO'] === null ? 'INF' : $range['QUANTITY_TO']);
	}

	protected function getFullQuantityRange()
	{
		return array(
			'HASH' => $this->getQuantityRangeHash(array('QUANTITY_FROM' => null, 'QUANTITY_TO' => null)),
			'QUANTITY_FROM' => null,
			'QUANTITY_TO' => null,
			'SORT_FROM' => 0,
			'SORT_TO' => INF
		);
	}

	protected function searchItemSelectedQuantityRangeHash($id)
	{
		if (empty($this->quantityRanges[$id]))
			return null;
		foreach ($this->quantityRanges[$id] as $range)
		{
			if ($this->checkQuantityRange($range))
				return $range['HASH'];
		}
		reset($this->quantityRanges[$id]);
		$firsrRange = current($this->quantityRanges[$id]);
		return $firsrRange['HASH'];
	}

	/**
	 * Load URLs for different actions to storage.
	 *
	 * @return void
	 */
	protected function initUrlTemplates()
	{
		$actionVar = $this->arParams['ACTION_VARIABLE'];
		$productIdVar = $this->arParams['PRODUCT_ID_VARIABLE'];
		$compareActionVar = $this->arParams['ACTION_COMPARE_VARIABLE'];

		$clearParams = Main\HttpRequest::getSystemParameters();
		$clearParams[] = $actionVar;
		$clearParams[] = $productIdVar;
		$clearParams[] = $compareActionVar;
		$clearParams[] = '';

		if (!empty($this->arParams['CUSTOM_CURRENT_PAGE']))
		{
			$pageUrl = $this->arParams['CUSTOM_CURRENT_PAGE'];
		}
		else
		{
			if ($this->request->isAjaxRequest())
			{
				$pageUrl = $this->arParams['CURRENT_BASE_PAGE'];
			}
			else
			{
				$pageUrl = Main\Application::getInstance()->getContext()->getRequest()->getDecodedUri();

			}
		}
		$currentUri = new Main\Web\Uri($pageUrl);

		if ($this->arParams['USE_COMPARE_LIST'] == 'N' && $this->arParams['COMPARE_PATH'] != '')
		{
			$compareUri = new Main\Web\Uri($this->arParams['COMPARE_PATH']);
		}
		else
		{
			$compareUri = $currentUri;
		}

		$currentUri->deleteParams($clearParams);
		$compareUri->deleteParams($clearParams);

		$urls = [];
		$urls['BUY_URL_TEMPLATE'] = $currentUri->addParams([$actionVar => self::ACTION_BUY, $productIdVar => '#ID#'])->getUri();
		$urls['ADD_URL_TEMPLATE'] = $currentUri->addParams([$actionVar => self::ACTION_ADD_TO_BASKET, $productIdVar => '#ID#'])->getUri();
		$urls['SUBSCRIBE_URL_TEMPLATE'] = $currentUri->addParams([$actionVar => self::ACTION_SUBSCRIBE, $productIdVar => '#ID#'])->getUri();

		$urls['COMPARE_URL_TEMPLATE'] = $compareUri->addParams([$compareActionVar => self::ACTION_ADD_TO_COMPARE, $productIdVar => '#ID#'])->getUri();
		$urls['COMPARE_DELETE_URL_TEMPLATE'] = $compareUri->addParams([$compareActionVar => self::ACTION_DELETE_FROM_COMPARE, $productIdVar => '#ID#'])->getUri();

		unset($compareUri, $currentUri, $clearParams);

		foreach (array_keys($urls) as $index)
		{
			$value = str_replace('%23ID%23', '#ID#', $urls[$index]); // format compatibility
			$urls['~'.$index] = $value;
			$urls[$index] = Main\Text\HtmlFilter::encode($value, ENT_QUOTES);
		}
		unset($index);

		$this->storage['URLS'] = $urls;
	}

	/**
	 * Process element prices.
	 *
	 * @param array &$element		Item data.
	 * @return void
	 */
	protected function modifyElementPrices(&$element)
	{
		$enableCompatible = $this->isEnableCompatible();
		$id = $element['ID'];
		$iblockId = $element['IBLOCK_ID'];
		$catalog = !empty($this->storage['CATALOGS'][$element['IBLOCK_ID']])
			? $this->storage['CATALOGS'][$element['IBLOCK_ID']]
			: array();

		$element['ITEM_PRICE_MODE'] = null;
		$element['ITEM_PRICES'] = array();
		$element['ITEM_QUANTITY_RANGES'] = array();
		$element['ITEM_MEASURE_RATIOS'] = array();
		$element['ITEM_MEASURE'] = array();
		$element['ITEM_MEASURE_RATIO_SELECTED'] = null;
		$element['ITEM_QUANTITY_RANGE_SELECTED'] = null;
		$element['ITEM_PRICE_SELECTED'] = null;

		if (!empty($catalog))
		{
			if (!isset($this->productWithOffers[$iblockId]))
				$this->productWithOffers[$iblockId] = array();
			if ($element['PRODUCT']['TYPE'] == Catalog\ProductTable::TYPE_SKU)
			{
				$this->productWithOffers[$iblockId][$id] = $id;
				if ($this->storage['SHOW_CATALOG_WITH_OFFERS'] && $enableCompatible)
				{
					$this->productWithPrices[$id] = $id;
					$this->calculatePrices[$id] = $id;
				}
			}

			if (in_array(
				$element['PRODUCT']['TYPE'],
				array(
					Catalog\ProductTable::TYPE_PRODUCT,
					Catalog\ProductTable::TYPE_SET,
					Catalog\ProductTable::TYPE_OFFER,
					Catalog\ProductTable::TYPE_SERVICE,
				)
			))
			{
				$this->productWithPrices[$id] = $id;
				$this->calculatePrices[$id] = $id;
			}

			if (isset($this->productWithPrices[$id]))
			{
				if ($element['PRODUCT']['MEASURE'] > 0)
				{
					$element['ITEM_MEASURE'] = array(
						'ID' => $element['PRODUCT']['MEASURE'],
						'TITLE' => '',
						'~TITLE' => ''
					);
				}
				else
				{
					$element['ITEM_MEASURE'] = array(
						'ID' => null,
						'TITLE' => $this->storage['DEFAULT_MEASURE']['SYMBOL_RUS'],
						'~TITLE' => $this->storage['DEFAULT_MEASURE']['~SYMBOL_RUS']
					);
				}
				if ($enableCompatible)
				{
					$element['CATALOG_MEASURE'] = $element['ITEM_MEASURE']['ID'];
					$element['CATALOG_MEASURE_NAME'] = $element['ITEM_MEASURE']['TITLE'];
					$element['~CATALOG_MEASURE_NAME'] = $element['ITEM_MEASURE']['~TITLE'];
				}
			}
		}
		else
		{
			$element['PRICES'] = \CIBlockPriceTools::GetItemPrices(
				$element['IBLOCK_ID'],
				$this->storage['PRICES'],
				$element,
				$this->arParams['PRICE_VAT_INCLUDE'],
				$this->storage['CONVERT_CURRENCY']
			);
			if (!empty($element['PRICES']))
			{
				$element['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($element['PRICES']);
			}

			$element['CAN_BUY'] = !empty($element['PRICES']);
		}
	}

	/**
	 * Load, calculate and fill data (prices, measures, discounts, deprecated fields) for simple products.
	 *
	 * @return void.
	 */
	protected function processProducts()
	{
		$this->initItemsMeasure($this->elements);
		$this->loadMeasures($this->getMeasureIds($this->elements));

		$this->loadPrices($this->productWithPrices);
		$this->calculateItemPrices($this->elements);

		$this->transferItems($this->elements);
	}

	/**
	 * Load, calculate and fill data (prices, measures, discounts, deprecated fields) for offers.
	 * Link offers to products.
	 *
	 * @return void
	 */
	protected function processOffers()
	{
		if ($this->useCatalog && !empty($this->iblockProducts))
		{
			$offers = array();

			$paramStack = array();
			$enableCompatible = $this->isEnableCompatible();
			if ($enableCompatible)
			{
				$paramStack['USE_PRICE_COUNT'] = $this->arParams['USE_PRICE_COUNT'];
				$paramStack['SHOW_PRICE_COUNT'] = $this->arParams['SHOW_PRICE_COUNT'];
				$this->arParams['USE_PRICE_COUNT'] = false;
				$this->arParams['SHOW_PRICE_COUNT'] = 1;
			}

			foreach (array_keys($this->iblockProducts) as $iblock)
			{
				if (!empty($this->productWithOffers[$iblock]))
				{
					$iblockOffers = $this->getIblockOffers($iblock);
					if (!empty($iblockOffers))
					{
						$offersId = array_keys($iblockOffers);
						$this->initItemsMeasure($iblockOffers);
						$this->loadMeasures($this->getMeasureIds($iblockOffers));

						$this->loadPrices($offersId);
						$this->calculateItemPrices($iblockOffers);

						$this->transferItems($iblockOffers);

						$this->modifyOffers($iblockOffers);
						$this->chooseOffer($iblockOffers, $iblock);

						$offers = array_merge($offers, $iblockOffers);
					}
					unset($iblockOffers);
				}
			}
			if ($enableCompatible)
			{
				$this->arParams['USE_PRICE_COUNT'] = $paramStack['USE_PRICE_COUNT'];
				$this->arParams['SHOW_PRICE_COUNT'] = $paramStack['SHOW_PRICE_COUNT'];
			}
			unset($enableCompatible, $paramStack);
		}
	}

	/**
	 * Return offers array for current iblock.
	 *
	 * @param $iblockId
	 * @return array
	 */
	protected function getIblockOffers($iblockId)
	{
		$offers = array();
		$iblockParams = $this->storage['IBLOCK_PARAMS'][$iblockId];

		$enableCompatible = $this->isEnableCompatible();

		if (
			$this->useCatalog
			&& $this->offerIblockExist($iblockId)
			&& !empty($this->productWithOffers[$iblockId])
		)
		{
			$catalog = $this->storage['CATALOGS'][$iblockId];

			$productProperty = 'PROPERTY_'.$catalog['SKU_PROPERTY_ID'];
			$productPropertyValue = $productProperty.'_VALUE';

			$offersFilter = $this->getOffersFilter($catalog['IBLOCK_ID']);
			$offersFilter[$productProperty] = $this->productWithOffers[$iblockId];

			$offersSelect = array(
				'ID' => 1,
				'IBLOCK_ID' => 1,
				'CODE' => 1,
				$productProperty => 1,
				'PREVIEW_PICTURE' => 1,
				'DETAIL_PICTURE' => 1,
			);

			if ($this->arParams['SHOW_SKU_DESCRIPTION'] === 'Y')
			{
				$offersSelect['PREVIEW_TEXT'] = 1;
				$offersSelect['DETAIL_TEXT'] = 1;
				$offersSelect['PREVIEW_TEXT_TYPE'] = 1;
				$offersSelect['DETAIL_TEXT_TYPE'] = 1;
			}

			if (!empty($iblockParams['OFFERS_FIELD_CODE']))
			{
				foreach ($iblockParams['OFFERS_FIELD_CODE'] as $code)
					$offersSelect[$code] = 1;
				unset($code);
			}

			$offersSelect = $this->getProductSelect($iblockId, array_keys($offersSelect));

			$getListParams = $this->prepareQueryFields($offersSelect, $offersFilter, $this->getOffersSort());
			$offersSelect = $getListParams['SELECT'];
			$offersFilter = $getListParams['FILTER'];
			$offersOrder = $getListParams['ORDER'];
			unset($getListParams);

			$checkFields = array();
			foreach (array_keys($offersOrder) as $code)
			{
				$code = mb_strtoupper($code);
				if ($code == 'ID' || $code == 'AVAILABLE')
					continue;
				$checkFields[] = $code;
			}
			unset($code);

			$productFields = $this->getProductFields($iblockId);
			$translateFields = $this->getCompatibleProductFields();

			$offersId = array();
			$offersCount = array();
			$iterator = \CIBlockElement::GetList(
				$offersOrder,
				$offersFilter,
				false,
				false,
				$offersSelect
			);
			while($row = $iterator->GetNext())
			{
				$row['ID'] = (int)$row['ID'];
				$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
				$productId = (int)$row[$productPropertyValue];

				if ($productId <= 0)
					continue;

				if ($enableCompatible && $this->arParams['OFFERS_LIMIT'] > 0)
				{
					$offersCount[$productId]++;
					if($offersCount[$productId] > $this->arParams['OFFERS_LIMIT'])
						continue;
				}

				$row['SORT_HASH'] = 'ID';
				if (!empty($checkFields))
				{
					$checkValues = '';
					foreach ($checkFields as $code)
						$checkValues .= ($row[$code] ?? '').'|';
					unset($code);
					if ($checkValues != '')
						$row['SORT_HASH'] = md5($checkValues);
					unset($checkValues);
				}
				$row['LINK_ELEMENT_ID'] = $productId;
				$row['PROPERTIES'] = array();
				$row['DISPLAY_PROPERTIES'] = array();

				$row['PRODUCT'] = array(
					'TYPE' => (int)$row['~TYPE'],
					'AVAILABLE' => $row['~AVAILABLE'],
					'BUNDLE' => $row['~BUNDLE'],
					'QUANTITY' => $row['~QUANTITY'],
					'QUANTITY_TRACE' => $row['~QUANTITY_TRACE'],
					'CAN_BUY_ZERO' => $row['~CAN_BUY_ZERO'],
					'MEASURE' => (int)$row['~MEASURE'],
					'SUBSCRIBE' => $row['~SUBSCRIBE'],
					'VAT_ID' => (int)$row['~VAT_ID'],
					'VAT_RATE' => 0,
					'VAT_INCLUDED' => $row['~VAT_INCLUDED'],
					'WEIGHT' => (float)$row['~WEIGHT'],
					'WIDTH' => (float)$row['~WIDTH'],
					'LENGTH' => (float)$row['~LENGTH'],
					'HEIGHT' => (float)$row['~HEIGHT'],
					'PAYMENT_TYPE' => $row['~PAYMENT_TYPE'],
					'RECUR_SCHEME_TYPE' => $row['~RECUR_SCHEME_TYPE'],
					'RECUR_SCHEME_LENGTH' => (int)$row['~RECUR_SCHEME_LENGTH'],
					'TRIAL_PRICE_ID' => (int)$row['~TRIAL_PRICE_ID']
				);

				$vatId = 0;
				$vatRate = 0;
				if ($row['PRODUCT']['VAT_ID'] > 0)
					$vatId = $row['PRODUCT']['VAT_ID'];
				elseif ($this->storage['IBLOCKS_VAT'][$catalog['IBLOCK_ID']] > 0)
					$vatId = $this->storage['IBLOCKS_VAT'][$catalog['IBLOCK_ID']];
				if ($vatId > 0 && isset($this->storage['VATS'][$vatId]))
					$vatRate = $this->storage['VATS'][$vatId];
				$row['PRODUCT']['VAT_RATE'] = $vatRate;
				unset($vatRate, $vatId);

				if ($enableCompatible)
				{
					foreach ($translateFields as $currentKey => $oldKey)
						$row[$oldKey] = $row[$currentKey];
					unset($currentKey, $oldKey);
					$row['~CATALOG_VAT'] = $row['PRODUCT']['VAT_RATE'];
					$row['CATALOG_VAT'] = $row['PRODUCT']['VAT_RATE'];
				}
				else
				{
					// temporary (compatibility custom templates)
					$row['~CATALOG_TYPE'] = $row['PRODUCT']['TYPE'];
					$row['CATALOG_TYPE'] = $row['PRODUCT']['TYPE'];
					$row['~CATALOG_QUANTITY'] = $row['PRODUCT']['QUANTITY'];
					$row['CATALOG_QUANTITY'] = $row['PRODUCT']['QUANTITY'];
					$row['~CATALOG_QUANTITY_TRACE'] = $row['PRODUCT']['QUANTITY_TRACE'];
					$row['CATALOG_QUANTITY_TRACE'] = $row['PRODUCT']['QUANTITY_TRACE'];
					$row['~CATALOG_CAN_BUY_ZERO'] = $row['PRODUCT']['CAN_BUY_ZERO'];
					$row['CATALOG_CAN_BUY_ZERO'] = $row['PRODUCT']['CAN_BUY_ZERO'];
					$row['~CATALOG_SUBSCRIBE'] = $row['PRODUCT']['SUBSCRIBE'];
					$row['CATALOG_SUBSCRIBE'] = $row['PRODUCT']['SUBSCRIBE'];
				}

				foreach ($productFields as $field)
					unset($row[$field], $row['~'.$field]);
				unset($field);

				if ($row['PRODUCT']['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
					$this->calculatePrices[$row['ID']] = $row['ID'];

				$row['ITEM_PRICE_MODE'] = null;
				$row['ITEM_PRICES'] = array();
				$row['ITEM_QUANTITY_RANGES'] = array();
				$row['ITEM_MEASURE_RATIOS'] = array();
				$row['ITEM_MEASURE'] = array();
				$row['ITEM_MEASURE_RATIO_SELECTED'] = null;
				$row['ITEM_QUANTITY_RANGE_SELECTED'] = null;
				$row['ITEM_PRICE_SELECTED'] = null;
				$row['CHECK_QUANTITY'] = $this->isNeedCheckQuantity($row['PRODUCT']);

				if ($row['PRODUCT']['MEASURE'] > 0)
				{
					$row['ITEM_MEASURE'] = array(
						'ID' => $row['PRODUCT']['MEASURE'],
						'TITLE' => '',
						'~TITLE' => ''
					);
				}
				else
				{
					$row['ITEM_MEASURE'] = array(
						'ID' => null,
						'TITLE' => $this->storage['DEFAULT_MEASURE']['SYMBOL_RUS'],
						'~TITLE' => $this->storage['DEFAULT_MEASURE']['~SYMBOL_RUS']
					);
				}
				if ($enableCompatible)
				{
					$row['CATALOG_MEASURE'] = $row['ITEM_MEASURE']['ID'];
					$row['CATALOG_MEASURE_NAME'] = $row['ITEM_MEASURE']['TITLE'];
					$row['~CATALOG_MEASURE_NAME'] = $row['ITEM_MEASURE']['~TITLE'];
				}

				$row['PROPERTIES'] = array();
				$row['DISPLAY_PROPERTIES'] = array();

				Iblock\Component\Tools::getFieldImageData(
					$row,
					array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
					Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
					''
				);

				$offersId[$row['ID']] = $row['ID'];
				$offers[$row['ID']] = $row;
			}
			unset($row, $iterator);

			if (!empty($offersId))
			{
				$loadPropertyCodes = ($iblockParams['OFFERS_PROPERTY_CODE'] ?? []);
				if (Iblock\Model\PropertyFeature::isEnabledFeatures())
				{
					$loadPropertyCodes = array_merge($loadPropertyCodes, $iblockParams['OFFERS_TREE_PROPS'] ?? []);
				}

				$propertyList = $this->getPropertyList($catalog['IBLOCK_ID'], $loadPropertyCodes);
				unset($loadPropertyCodes);

				if (!empty($propertyList) || $this->useDiscountCache)
				{
					\CIBlockElement::GetPropertyValuesArray($offers, $catalog['IBLOCK_ID'], $offersFilter);
					foreach ($offers as &$row)
					{
						if ($this->useDiscountCache)
						{
							if ($this->storage['USE_SALE_DISCOUNTS'])
								Catalog\Discount\DiscountManager::setProductPropertiesCache($row['ID'], $row["PROPERTIES"]);
							else
								\CCatalogDiscount::SetProductPropertiesCache($row['ID'], $row["PROPERTIES"]);
						}

						if (!empty($propertyList))
						{
							foreach ($propertyList as $pid)
							{
								if (!isset($row["PROPERTIES"][$pid]))
									continue;
								$prop = &$row["PROPERTIES"][$pid];
								$boolArr = is_array($prop["VALUE"]);
								if (
									($boolArr && !empty($prop["VALUE"])) ||
									(!$boolArr && (string)$prop["VALUE"] !== '')
								)
								{
									$row["DISPLAY_PROPERTIES"][$pid] = \CIBlockFormatProperties::GetDisplayValue($row, $prop);
								}
								unset($boolArr, $prop);
							}
							unset($pid);
						}
					}
					unset($row);
				}
				if (!empty($propertyList))
				{
					\CIBlockFormatProperties::clearCache();
				}

				if ($this->useDiscountCache)
				{
					if ($this->storage['USE_SALE_DISCOUNTS'])
					{
						Catalog\Discount\DiscountManager::preloadPriceData($offersId, $this->storage['PRICES_ALLOW']);
						Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($offersId, $this->getUserGroups());
					}
					else
					{
						\CCatalogDiscount::SetProductSectionsCache($offersId);
						\CCatalogDiscount::SetDiscountProductCache($offersId, array('IBLOCK_ID' => $catalog['IBLOCK_ID'], 'GET_BY_ID' => 'Y'));
					}
				}
			}
			unset($offersId);
		}

		return $offers;
	}

	protected function getOffersFilter($iblockId)
	{
		$offersFilter = array(
			'IBLOCK_ID' => $iblockId,
			'ACTIVE' => 'Y',
			'ACTIVE_DATE' => 'Y',
			'CHECK_PERMISSIONS' => 'N'
		);

		if ($this->arParams['HIDE_NOT_AVAILABLE_OFFERS'] === 'Y')
		{
			$offersFilter['AVAILABLE'] = 'Y';
		}
		elseif ($this->arParams['HIDE_NOT_AVAILABLE_OFFERS'] === 'L')
		{
			$offersFilter['CUSTOM_FILTER'] = array(
				'LOGIC' => 'OR',
				'AVAILABLE' => 'Y',
				'SUBSCRIBE' => 'Y'
			);
		}

		if (!$this->arParams['USE_PRICE_COUNT'])
		{
			$offersFilter['SHOW_PRICE_COUNT'] = $this->arParams['SHOW_PRICE_COUNT'];
		}

		return $offersFilter;
	}

	/**
	 * Return offers sort fields to execute.
	 *
	 * @return array
	 */
	protected function getOffersSort()
	{
		$offersOrder = array(
			mb_strtoupper($this->arParams['OFFERS_SORT_FIELD']) => $this->arParams['OFFERS_SORT_ORDER'],
			mb_strtoupper($this->arParams['OFFERS_SORT_FIELD2']) => $this->arParams['OFFERS_SORT_ORDER2']
		);
		if (!isset($offersOrder['ID']))
			$offersOrder['ID'] = 'DESC';

		return $offersOrder;
	}

	protected function modifyOffers($offers)
	{
		//$urls = $this->storage['URLS'];

		foreach ($offers as &$offer)
		{
			$elementId = $offer['LINK_ELEMENT_ID'];

			if (!isset($this->elementLinks[$elementId]))
				continue;

			$offer['CAN_BUY'] = $this->elementLinks[$elementId]['ACTIVE'] === 'Y' && $offer['CAN_BUY'];

			$this->elementLinks[$elementId]['OFFERS'][] = $offer;

			unset($elementId, $offer);
		}
	}

	abstract protected function chooseOffer($offers, $iblockId);

	protected function initResultCache()
	{
		if (
			$this->arParams['CONVERT_CURRENCY'] === 'Y'
			&& !empty($this->storage['CURRENCY_LIST'])
			&& defined('BX_COMP_MANAGED_CACHE')
		)
		{
			$this->storage['CURRENCY_LIST'][$this->storage['CONVERT_CURRENCY']['CURRENCY_ID']] = $this->storage['CONVERT_CURRENCY']['CURRENCY_ID'];
			$taggedCache = Main\Application::getInstance()->getTaggedCache();
			foreach ($this->storage['CURRENCY_LIST'] as $oneCurrency)
			{
				$taggedCache->registerTag('currency_id_'.$oneCurrency);
			}

			unset($oneCurrency);
			unset($taggedCache);
		}

		unset($this->storage['CURRENCY_LIST']);

		$this->setResultCacheKeys($this->getCacheKeys());
	}

	protected function getCacheKeys()
	{
		return array();
	}

	/**
	 * All iblock/section/element/offer initializations starts here.
	 * If have no errors - result showed in $arResult.
	 */
	protected function processResultData()
	{
		$this->iblockProducts = $this->getProductsSeparatedByIblock();
		$this->checkIblock();

		if ($this->hasErrors())
			return;

		$this->initCurrencyConvert();
		$this->initCatalogInfo();
		$this->initIblockPropertyFeatures();
		$this->initPrices();
		$this->initVats();
		$this->initUrlTemplates();

		$this->initElementList();
		if (!$this->hasErrors())
		{
			$this->sortElementList();
			$this->makeElementLinks();
			$this->prepareData();
			$this->filterPureOffers();
			$this->makeOutputResult();
		}
	}

	/**
	 * Check for correct iblocks.
	 */
	protected function checkIblock()
	{
		if (!empty($this->iblockProducts))
		{
			$iblocks = array();
			$iblockIterator = Iblock\IblockSiteTable::getList(array(
				'select' => array('IBLOCK_ID'),
				'filter' => array(
					'=IBLOCK_ID' => array_keys($this->iblockProducts),
					'=SITE_ID' => $this->getSiteId(),
					'=IBLOCK.ACTIVE' => 'Y'
				)
			));
			while ($iblock = $iblockIterator->fetch())
			{
				$iblocks[$iblock['IBLOCK_ID']] = true;
			}

			foreach ($this->iblockProducts as $iblock => $products)
			{
				if (!isset($iblocks[$iblock]))
				{
					unset($this->iblockProducts[$iblock]);
				}
			}

			if (empty($this->iblockProducts))
			{
				$this->abortResultCache();
				$this->errorCollection->setError(new Error(Loc::getMessage('INVALID_IBLOCK'), self::ERROR_TEXT));
			}
		}
	}

	protected function prepareData()
	{
		$this->clearItems();
		$this->initCatalogDiscountCache();
		$this->processProducts();
		$this->processOffers();
		$this->makeOutputResult();
		$this->clearItems();
	}

	protected function filterPureOffers()
	{
		if (!empty($this->productIds) && is_array($this->productIds))
		{
			foreach ($this->productIds as $productId)
			{
				// check if it's element
				if ($this->productIdMap[$productId] == $productId)
				{
					continue;
				}

				if (isset($this->elementLinks[$this->productIdMap[$productId]]) && !empty($this->elementLinks[$this->productIdMap[$productId]]['OFFERS']))
				{
					// clear all unwanted offers
					foreach ($this->elementLinks[$this->productIdMap[$productId]]['OFFERS'] as $key => $offer)
					{
						if ($offer['ID'] != $productId)
						{
							unset($this->elementLinks[$this->productIdMap[$productId]]['OFFERS'][$key]);
						}
					}
				}
			}
		}
	}

	/**
	 * Set component data from storage to $arResult.
	 */
	protected function makeOutputResult()
	{
		$this->arResult = array_merge($this->arResult, $this->storage['URLS']);
		$this->arResult['CONVERT_CURRENCY'] = $this->storage['CONVERT_CURRENCY'];
		$this->arResult['CATALOGS'] = $this->storage['CATALOGS'];
		$this->arResult['MODULES'] = $this->storage['MODULES'];
		$this->arResult['PRICES_ALLOW'] = $this->storage['PRICES_ALLOW'];

		if ($this->isEnableCompatible())
		{
			if ($this->arParams['IBLOCK_ID'] > 0)
			{
				$this->arResult['CATALOG'] = false;

				if (
					!empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
					&& is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
				)
				{
					$this->arResult['CATALOG'] = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']];
				}
			}
		}
	}

	/**
	 * Process of buy/add-to-basket/etc actions.
	 */
	protected function processLinkAction()
	{
		global $APPLICATION;

		if ($this->request->get($this->arParams['ACTION_VARIABLE'].self::ACTION_BUY) !== null)
		{
			$action = self::ACTION_BUY;
		}
		elseif ($this->request->get($this->arParams['ACTION_VARIABLE'].self::ACTION_ADD_TO_BASKET))
		{
			$action = self::ACTION_ADD_TO_BASKET;
		}
		else
		{
			$action = mb_strtoupper($this->request->get($this->arParams['ACTION_VARIABLE']));
		}

		$productId = (int)$this->request->get($this->arParams['PRODUCT_ID_VARIABLE']);

		if (
			($action == self::ACTION_ADD_TO_BASKET || $action == self::ACTION_BUY || $action == self::ACTION_SUBSCRIBE)
			&& Loader::includeModule('sale')
			&& Loader::includeModule('catalog')
		)
		{
			$addByAjax = $this->request->get('ajax_basket') === 'Y';
			if ($addByAjax)
			{
				$this->request->set(Main\Text\Encoding::convertEncoding($this->request->toArray(), 'UTF-8', SITE_CHARSET));
			}

			[$successfulAdd, $errorMsg] = $this->addProductToBasket($productId, $action);

			if ($addByAjax)
			{
				if ($successfulAdd)
				{
					$addResult = array(
						'STATUS' => 'OK',
						'MESSAGE' => Loc::getMessage('CATALOG_SUCCESSFUL_ADD_TO_BASKET')
					);
				}
				else
				{
					$addResult = array(
						'STATUS' => 'ERROR',
						'MESSAGE' => $errorMsg
					);
				}

				$APPLICATION->RestartBuffer();
				header('Content-Type: application/json');
				\CMain::FinalActions(Main\Web\Json::encode($addResult));
			}
			else
			{
				if ($successfulAdd)
				{
					$pathRedirect = $action == self::ACTION_BUY
						? $this->arParams['BASKET_URL']
						: $APPLICATION->GetCurPageParam('', array(
							$this->arParams['PRODUCT_ID_VARIABLE'],
							$this->arParams['ACTION_VARIABLE'],
							$this->arParams['PRODUCT_QUANTITY_VARIABLE'],
							$this->arParams['PRODUCT_PROPS_VARIABLE']
						));

					LocalRedirect($pathRedirect);
				}
				else
				{
					$this->errorCollection->setError(new Error($errorMsg, self::ERROR_TEXT));
				}
			}
		}
	}

	protected function checkProductSection($productId, $sectionId = 0, $sectionCode = '')
	{
		$successfulAdd = true;
		$errorMsg = '';

		if (!empty($productId) && ($sectionId > 0 || !empty($sectionCode)))
		{
			$productsMap = $this->getProductIdMap([$productId]);

			if (!empty($productsMap[$productId]))
			{
				$sectionId = (int)$sectionId;
				$sectionCode = (string)$sectionCode;

				$filter = ['ID' => $productsMap[$productId]];

				$element = false;
				if ($sectionId > 0)
				{
					$filter['SECTION_ID'] = $sectionId;
					$filter['INCLUDE_SUBSECTIONS'] = 'Y';
					$elementIterator = \CIBlockElement::GetList(array(), $filter, false, false, array('ID'));
					$element = $elementIterator->Fetch();
					unset($elementIterator);
				}
				elseif ($sectionCode != '')
				{
					$iblockId = (int)\CIBlockElement::GetIBlockByID($productsMap[$productId]);
					if ($iblockId > 0)
					{
						$sectionIterator = \CIBlockSection::GetList(
							[],
							['IBLOCK_ID' => $iblockId, '=CODE' => $sectionCode],
							false,
							['ID', 'IBLOCK_ID']
						);
						$section = $sectionIterator->Fetch();
						unset($sectionIterator);
						if (!empty($section))
						{
							$filter['SECTION_ID'] = (int)$section['ID'];
							$filter['INCLUDE_SUBSECTIONS'] = 'Y';
							$elementIterator = \CIBlockElement::GetList(array(), $filter, false, false, array('ID'));
							$element = $elementIterator->Fetch();
							unset($elementIterator);
						}
						unset($section);
					}
					unset($iblockId);
				}

				if (empty($element))
				{
					$successfulAdd = false;
					$errorMsg = Loc::getMessage('CATALOG_PRODUCT_NOT_FOUND');
				}
			}
		}

		return [$successfulAdd, $errorMsg];
	}

	protected function checkProductIblock(array $product): bool
	{
		return true;
	}

	protected function addProductToBasket($productId, $action)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$successfulAdd = true;
		$errorMsg = '';

		$quantity = 0;
		$productProperties = array();

		$productId = (int)$productId;
		if ($productId <= 0)
		{
			$errorMsg = Loc::getMessage('CATALOG_PRODUCT_ID_IS_ABSENT');
			$successfulAdd = false;
		}
		$product = [];
		if ($successfulAdd)
		{
			$product = $this->getProductInfo($productId);
			if (empty($product))
			{
				$errorMsg = Loc::getMessage('CATALOG_PRODUCT_NOT_FOUND');
				$successfulAdd = false;
			}
		}
		if ($successfulAdd)
		{
			if ($this->arParams['CHECK_LANDING_PRODUCT_SECTION'])
			{
				[$successfulAdd, $errorMsg] = $this->checkProductSection(
					$productId, $this->arParams['SECTION_ID'], $this->arParams['SECTION_CODE']
				);
			}
		}
		if ($successfulAdd)
		{
			if (!$this->checkProductIblock($product))
			{
				$errorMsg = Loc::getMessage('CATALOG_PRODUCT_NOT_FOUND');
				$successfulAdd = false;
			}
		}
		if ($successfulAdd)
		{
			if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y')
			{
				$this->initIblockPropertyFeatures();
				$iblockParams = $this->storage['IBLOCK_PARAMS'][$product['PRODUCT_IBLOCK_ID']];
				if ($product['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
				{
					$skuAddProps = $this->request->get('basket_props') ?: '';
					if (!empty($iblockParams['OFFERS_CART_PROPERTIES']) || !empty($skuAddProps))
					{
						$productProperties = \CIBlockPriceTools::GetOfferProperties(
							$productId,
							$product['PRODUCT_IBLOCK_ID'],
							$iblockParams['OFFERS_CART_PROPERTIES'],
							$skuAddProps
						);
					}
					unset($skuAddProps);
				}
				else
				{
					if (!empty($iblockParams['CART_PROPERTIES']))
					{
						$productPropsVar = $this->request->get($this->arParams['PRODUCT_PROPS_VARIABLE']);
						if (is_array($productPropsVar))
						{
							$productProperties = \CIBlockPriceTools::CheckProductProperties(
								$product['PRODUCT_IBLOCK_ID'],
								$productId,
								$iblockParams['CART_PROPERTIES'],
								$productPropsVar,
								$this->arParams['PARTIAL_PRODUCT_PROPERTIES'] === 'Y'
							);
							if (!is_array($productProperties))
							{
								$errorMsg = Loc::getMessage('CATALOG_PARTIAL_BASKET_PROPERTIES_ERROR');
								$successfulAdd = false;
							}
						}
						else
						{
							if ($this->arParams['PARTIAL_PRODUCT_PROPERTIES'] !== 'Y')
							{
								$errorMsg = Loc::getMessage('CATALOG_EMPTY_BASKET_PROPERTIES_ERROR');
								$successfulAdd = false;
							}
						}
						unset($productPropsVar);
					}
				}
				unset($iblockParams);
			}
		}

		if ($successfulAdd)
		{
			if ($this->arParams['USE_PRODUCT_QUANTITY'])
			{
				$quantity = (float)$this->request->get($this->arParams['PRODUCT_QUANTITY_VARIABLE']);
			}

			if ($quantity <= 0)
			{
				$ratioIterator = \CCatalogMeasureRatio::getList(
					array(),
					array('PRODUCT_ID' => $productId),
					false,
					false,
					array('PRODUCT_ID', 'RATIO')
				);
				if ($ratio = $ratioIterator->Fetch())
				{
					$intRatio = (int)$ratio['RATIO'];
					$floatRatio = (float)$ratio['RATIO'];
					$quantity = $floatRatio > $intRatio ? $floatRatio : $intRatio;
				}
			}

			if ($quantity <= 0)
			{
				$quantity = 1;
			}
		}

		if ($successfulAdd)
		{
			$rewriteFields = $this->getRewriteFields($action);
			if (isset($rewriteFields['SUBSCRIBE']) && $rewriteFields['SUBSCRIBE'] == 'Y')
			{
				if (!SubscribeProduct($productId, $rewriteFields, $productProperties))
				{
					if ($ex = $APPLICATION->GetException())
					{
						$errorMsg = $ex->GetString();
					}
					else
					{
						$errorMsg = Loc::getMessage('CATALOG_ERROR2BASKET');
					}

					$successfulAdd = false;
				}
			}
			else
			{
				$product = [
					'PRODUCT_ID' => $productId,
					'QUANTITY' => $quantity
				];
				if (!empty($productProperties))
				{
					$product['PROPS'] = $productProperties;
				}

				$basketResult = Catalog\Product\Basket::addProduct($product, $rewriteFields, [
					'USE_MERGE' => $this->isMergeProductWhenAddedBasket() ? 'Y' : 'N',
				]);
				if (!$basketResult->isSuccess())
				{
					$errorMsg = implode('; ', $basketResult->getErrorMessages());
					$successfulAdd = false;
				}
				unset($basketResult);
			}
		}

		return array($successfulAdd, $errorMsg);
	}

	/**
	 * Should merge products when adding to the basket (increase the quantity of products)?
	 *
	 * If not exists parameter 'USE_MERGE_WHEN_ADD_PRODUCT_TO_BASKET' return true
	 *
	 * @return bool
	 */
	public function isMergeProductWhenAddedBasket()
	{
		return ($this->arParams['USE_MERGE_WHEN_ADD_PRODUCT_TO_BASKET'] ?? 'Y') !== 'N';
	}

	protected function getRewriteFields($action)
	{
		$rewriteFields = [];

		if ($action === self::ACTION_ADD_TO_BASKET || $action === self::ACTION_BUY)
		{
			$rewriteFields['DELAY'] = 'N';
		}

		if ($action == self::ACTION_SUBSCRIBE)
		{
			$notify = unserialize(Main\Config\Option::get('sale', 'subscribe_prod', ''), ['allowed_classes' => false]);
			if (!empty($notify[$this->getSiteId()]) && $notify[$this->getSiteId()]['use'] === 'Y')
			{
				$rewriteFields['SUBSCRIBE'] = 'Y';
				$rewriteFields['CAN_BUY'] = 'N';
			}
		}

		return $rewriteFields;
	}

	/**
	 * This method executes when "deferredLoad" action chosen.
	 * Override getDeferredProductIds method to return needed product ids array.
	 */
	protected function deferredLoadAction()
	{
		$this->productIds = $this->getDeferredProductIds();

		// if no products - show empty response
		if (empty($this->productIds))
		{
			static::sendJsonAnswer();
		}

		$this->productIdMap = $this->getProductIdMap($this->productIds);
		$this->loadData();
	}

	/**
	 * This method executes when "bigDataLoad" action is chosen.
	 */
	protected function bigDataLoadAction()
	{
		$this->initBigDataLastUsage();
		$this->productIds = $this->getBigDataProductIds();

		// if no products - show empty response
		if (empty($this->productIds))
		{
			static::sendJsonAnswer();
		}

		$this->productIdMap = $this->getProductIdMap($this->productIds);
		$this->loadData();
	}

	/**
	 * Mark last usage of BigData.
	 */
	protected function initBigDataLastUsage()
	{
		$lastUsage = Main\Config\Option::get('main', 'rcm_component_usage', 0);

		if ($lastUsage == 0 || (time() - $lastUsage) > 3600)
		{
			Main\Config\Option::set('main', 'rcm_component_usage', time());
		}
	}

	/**
	 * This method executes when "initialLoad" action is chosen.
	 */
	protected function initialLoadAction()
	{
		$this->productIds = $this->getProductIds();
		$this->productIdMap = $this->getProductIdMap($this->productIds);
		$this->loadData();
	}

	/**
	 * Show cached component data or load if outdated.
	 * If extended mode enabled - uses result_modifier.php logic in component (be careful not to duplicate it).
	 */
	protected function loadData()
	{
		if ($this->isCacheDisabled() || $this->startResultCache(false, $this->getAdditionalCacheId(), $this->getComponentCachePath()))
		{
			$this->processResultData();
			if (!$this->hasErrors())
			{
				if ($this->isExtendedMode())
				{
					$this->initComponentTemplate();
					$this->applyTemplateModifications();
				}

				$this->initResultCache();
				$this->includeComponentTemplate();
				$this->clearCatalogDiscountCache();
			}
		}
	}

	/**
	 * Return component cache identifier.
	 *
	 * @return mixed
	 */
	abstract protected function getAdditionalCacheId();

	/**
	 * Return component cache path.
	 *
	 * @return mixed
	 */
	abstract protected function getComponentCachePath();

	public function getTemplateEmptyPreview()
	{
		$emptyPreview = false;
		$documentRoot = Main\Application::getDocumentRoot();
		$emptyPreviewPath = $this->getTemplate()->GetFolder().'/images/no_photo.png';

		$file = new Main\IO\File($documentRoot.$emptyPreviewPath);
		if ($file->isExists())
		{
			$size = getimagesize($documentRoot.$emptyPreviewPath);
			if (!empty($size))
			{
				$emptyPreview = array(
					'ID' => 0,
					'SRC' => $emptyPreviewPath,
					'FILE_NAME' => 'no_photo.png',
					'WIDTH' => (int)$size[0],
					'HEIGHT' => (int)$size[1]
				);
			}
		}

		return $emptyPreview;
	}

	protected function sliceItemsForSlider(&$items)
	{
		$rows = array();

		while (!empty($items))
		{
			$rows[] = array_splice($items, 0, $this->arParams['LINE_ELEMENT_COUNT']);
		}

		$items = $rows;
	}

	protected function getTemplateCurrencies()
	{
		$currencies = array();

		if ($this->arResult['MODULES']['currency'])
		{
			if (isset($this->arResult['CONVERT_CURRENCY']['CURRENCY_ID']))
			{
				$currencyFormat = \CCurrencyLang::GetFormatDescription($this->arResult['CONVERT_CURRENCY']['CURRENCY_ID']);
				$currencies = array(
					array(
						'CURRENCY' => $this->arResult['CONVERT_CURRENCY']['CURRENCY_ID'],
						'FORMAT' => array(
							'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
							'DEC_POINT' => $currencyFormat['DEC_POINT'],
							'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
							'DECIMALS' => $currencyFormat['DECIMALS'],
							'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
							'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
						)
					)
				);
				unset($currencyFormat);
			}
			else
			{
				$currencyIterator = Currency\CurrencyTable::getList(array(
					'select' => array('CURRENCY')
				));
				while ($currency = $currencyIterator->fetch())
				{
					$currencyFormat = \CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
					$currencies[] = array(
						'CURRENCY' => $currency['CURRENCY'],
						'FORMAT' => array(
							'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
							'DEC_POINT' => $currencyFormat['DEC_POINT'],
							'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
							'DECIMALS' => $currencyFormat['DECIMALS'],
							'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
							'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
						)
					);
				}
				unset($currencyFormat, $currency, $currencyIterator);
			}
		}

		return $currencies;
	}

	/**
	 * Send answer for AJAX request.
	 *
	 * @param array $result
	 */
	public static function sendJsonAnswer(array $result = array())
	{
		global $APPLICATION;

		if (!empty($result))
		{
			$result['JS'] = Main\Page\Asset::getInstance()->getJs();
		}

		$APPLICATION->RestartBuffer();

		/* don't change this block, because delayed \CFile::ResizeImageGet is not started in cloud */
		echo Main\Web\Json::encode($result);
		\CMain::FinalActions();
		/* block end */
	}

	/**
	 * Action preparing to execute in doAction method with postfix "Action".
	 * E.g. action "initialLoad" calls "initialLoadAction".
	 *
	 * @return string
	 */
	protected function prepareAction()
	{
		if (
			$this->request->get($this->arParams['ACTION_VARIABLE']) !== null
			&& $this->request->get($this->arParams['PRODUCT_ID_VARIABLE']) !== null
		)
		{
			$action = 'processLink';
		}
		elseif ($this->request->isAjaxRequest() && $this->request->get('action') === 'deferredLoad')
		{
			$action = $this->request->get('bigData') === 'Y' ? 'bigDataLoad' : 'deferredLoad';
		}
		else
		{
			$action = 'initialLoad';
		}

		return $action;
	}

	/**
	 * Action executor.
	 */
	protected function doAction()
	{
		$action = $this->getAction();

		if (is_callable(array($this, $action.'Action')))
		{
			call_user_func(array($this, $action.'Action'));
		}
	}

	/**
	 * @return int|false
	 */
	public function executeComponent()
	{
		$this->checkModules();

		if ($this->hasErrors())
		{
			return $this->processErrors();
		}

		$action = $this->prepareAction();
		$this->setAction($action);
		$this->doAction();

		if ($this->hasErrors())
		{
			return $this->processErrors();
		}

		return $this->arResult['ID'] ?? false;
	}

	public function applyTemplateModifications()
	{
		$this->prepareTemplateParams();
		$this->checkTemplateTheme();
		$this->editTemplateData();

		return $this->arParams;
	}

	protected function prepareTemplateParams()
	{
		$params =& $this->arParams;
		$defaultParams = $this->getTemplateDefaultParams();
		$params = array_merge($defaultParams, $params);

		$params['SHOW_OLD_PRICE'] = $params['SHOW_OLD_PRICE'] === 'Y' ? 'Y' : 'N';
		$params['SHOW_CLOSE_POPUP'] = $params['SHOW_CLOSE_POPUP'] === 'Y' ? 'Y' : 'N';
		$params['SHOW_DISCOUNT_PERCENT'] = $params['SHOW_DISCOUNT_PERCENT'] === 'Y' ? 'Y' : 'N';
		$params['DISCOUNT_PERCENT_POSITION'] = trim($params['DISCOUNT_PERCENT_POSITION']) ?: 'bottom-right';
		$params['LABEL_PROP_POSITION'] = trim($params['LABEL_PROP_POSITION']) ?: 'top-left';
		$params['PRODUCT_SUBSCRIPTION'] = $params['PRODUCT_SUBSCRIPTION'] === 'N' ? 'N' : 'Y';
		$params['MESS_BTN_BUY'] = trim($params['MESS_BTN_BUY']);
		$params['MESS_BTN_ADD_TO_BASKET'] = trim($params['MESS_BTN_ADD_TO_BASKET']);
		$params['MESS_BTN_SUBSCRIBE'] = trim($params['MESS_BTN_SUBSCRIBE']);
		$params['MESS_BTN_DETAIL'] = trim($params['MESS_BTN_DETAIL']);
		$params['MESS_NOT_AVAILABLE'] = trim($params['MESS_NOT_AVAILABLE']);
		$params['MESS_BTN_COMPARE'] = trim($params['MESS_BTN_COMPARE']);
		$params['SHOW_SLIDER'] = $params['SHOW_SLIDER'] === 'N' ? 'N' : 'Y';
		$params['SLIDER_INTERVAL'] = (int)$params['SLIDER_INTERVAL'] ?: 5000;
		$params['SLIDER_PROGRESS'] = $params['SLIDER_PROGRESS'] === 'Y' ? 'Y' : 'N';
		$params['USE_ENHANCED_ECOMMERCE'] = $params['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
		$params['DATA_LAYER_NAME'] = trim($params['DATA_LAYER_NAME']);
		$params['BRAND_PROPERTY'] = $params['BRAND_PROPERTY'] !== '-' ? trim($params['BRAND_PROPERTY']) : '';

		if (!isset($params['SHOW_MAX_QUANTITY']) || !in_array($params['SHOW_MAX_QUANTITY'], array('Y', 'M', 'N')))
		{
			$params['SHOW_MAX_QUANTITY'] = 'N';
		}

		$params['RELATIVE_QUANTITY_FACTOR'] = (int)($params['RELATIVE_QUANTITY_FACTOR'] ?? 0) > 0 ? (int)$params['RELATIVE_QUANTITY_FACTOR'] : 5;
	}

	protected function getTemplateDefaultParams()
	{
		return array(
			'TEMPLATE_THEME' => 'blue',
			'SHOW_MAX_QUANTITY' => 'N',
			'SHOW_OLD_PRICE' => 'N',
			'SHOW_CLOSE_POPUP' => 'N',
			'SHOW_DISCOUNT_PERCENT' => 'N',
			'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
			'LABEL_PROP' => array(),
			'LABEL_PROP_MOBILE' => array(),
			'LABEL_PROP_POSITION' => 'top-left',
			'PRODUCT_SUBSCRIPTION' => 'Y',
			'MESS_BTN_BUY' => '',
			'MESS_BTN_ADD_TO_BASKET' => '',
			'MESS_BTN_SUBSCRIBE' => '',
			'MESS_BTN_DETAIL' => '',
			'MESS_NOT_AVAILABLE' => '',
			'MESS_BTN_COMPARE' => '',
			'SHOW_SLIDER' => 'N',
			'SLIDER_INTERVAL' => 5000,
			'SLIDER_PROGRESS' => 'N',
			'USE_ENHANCED_ECOMMERCE' => 'N',
			'DATA_LAYER_NAME' => 'dataLayer',
			'BRAND_PROPERTY' => ''
		);
	}

	protected function checkTemplateTheme()
	{
		$theme =& $this->arParams['TEMPLATE_THEME'];
		$theme = (string)$theme;

		if ($theme != '')
		{
			$theme = preg_replace('/[^a-zA-Z0-9_\-\(\)\!]/', '', $theme);
			if ($theme === 'site')
			{
				$siteId = $this->getSiteId();
				$templateId = Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', $siteId);
				$templateId = preg_match('/^eshop_adapt/', $templateId) ? 'eshop_adapt' : $templateId;
				$theme = Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', 'blue', $siteId);
			}

			if ($theme != '')
			{
				$documentRoot = Main\Application::getDocumentRoot();
				$templateFolder = $this->getTemplate()->GetFolder();

				$themesFolder = new Main\IO\Directory($documentRoot.$templateFolder.'/themes/');

				if ($themesFolder->isExists())
				{
					$file = new Main\IO\File($documentRoot.$templateFolder.'/themes/'.$theme.'/style.css');

					if (!$file->isExists())
					{
						$theme = '';
					}
				}
			}
		}

		if ($theme == '')
		{
			$theme = 'blue';
		}
	}

	protected function editTemplateData()
	{
		//
	}

	public static function checkEnlargedData(&$item, $propertyCode)
	{
		if (!empty($item) && is_array($item))
		{
			$item['ENLARGED'] = 'N';
			$propertyCode = (string)$propertyCode;

			if ($propertyCode !== '' && isset($item['PROPERTIES'][$propertyCode]))
			{
				$prop = $item['PROPERTIES'][$propertyCode];
				if (!empty($prop['VALUE']))
				{
					$item['ENLARGED'] = 'Y';
				}
			}
		}
	}

	protected function editTemplateProductSlider(&$item, $iblock, $limit = 0, $addDetailToSlider = true, $default = array())
	{
		$propCode = $this->storage['IBLOCK_PARAMS'][$iblock]['ADD_PICT_PROP'];

		$slider = \CIBlockPriceTools::getSliderForItem($item, $propCode, $addDetailToSlider);
		if (empty($slider))
		{
			$slider = $default;
		}

		if ($limit > 0)
		{
			$slider = array_slice($slider, 0, $limit);
		}

		$item['SHOW_SLIDER'] = true;
		$item['MORE_PHOTO'] = $slider;
		$item['MORE_PHOTO_COUNT'] = count($slider);
	}

	protected function editTemplateOfferSlider(&$item, $iblock, $limit = 0, $addDetailToSlider = true, $default = array())
	{
		$propCode = $this->storage['IBLOCK_PARAMS'][$iblock]['OFFERS_ADD_PICT_PROP'];

		$slider = \CIBlockPriceTools::getSliderForItem($item, $propCode, $addDetailToSlider);
		if (empty($slider))
		{
			$slider = $default;
		}

		if ($limit > 0)
		{
			$slider = array_slice($slider, 0, $limit);
		}

		$item['MORE_PHOTO'] = $slider;
		$item['MORE_PHOTO_COUNT'] = count($slider);
	}

	protected function editTemplateCatalogInfo(&$item)
	{
		if ($this->arResult['MODULES']['catalog'])
		{
			$item['CATALOG'] = true;
			if ($this->isEnableCompatible())
				$item['CATALOG_TYPE'] = $item['PRODUCT']['TYPE'];
		}
		else
		{
			if ($this->isEnableCompatible())
				$item['CATALOG_TYPE'] = 0;
			$item['OFFERS'] = array();
		}
	}

	protected function getTemplatePropCell($code, $offer, &$matrixFields, $skuPropList)
	{
		$cell = array(
			'VALUE' => 0,
			'SORT' => PHP_INT_MAX,
			'NA' => true
		);

		if (isset($offer['DISPLAY_PROPERTIES'][$code]))
		{
			$matrixFields[$code] = true;
			$cell['NA'] = false;

			if ($skuPropList[$code]['USER_TYPE'] === 'directory')
			{
				$intValue = $skuPropList[$code]['XML_MAP'][$offer['DISPLAY_PROPERTIES'][$code]['VALUE']];
				$cell['VALUE'] = $intValue;
			}
			elseif ($skuPropList[$code]['PROPERTY_TYPE'] === 'L')
			{
				$cell['VALUE'] = (int)$offer['DISPLAY_PROPERTIES'][$code]['VALUE_ENUM_ID'];
			}
			elseif ($skuPropList[$code]['PROPERTY_TYPE'] === 'E')
			{
				$cell['VALUE'] = (int)$offer['DISPLAY_PROPERTIES'][$code]['VALUE'];
			}

			$cell['SORT'] = $skuPropList[$code]['VALUES'][$cell['VALUE']]['SORT'];
		}

		return $cell;
	}

	protected function getOffersIblockId($iblockId)
	{
		if (!$this->useCatalog)
			return null;
		if (!isset($this->storage['CATALOGS'][$iblockId]))
			return null;
		if (
			$this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'] != \CCatalogSku::TYPE_PRODUCT
			&& $this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'] != \CCatalogSku::TYPE_FULL
		)
			return null;
		return $this->storage['CATALOGS'][$iblockId]['IBLOCK_ID'];
	}

	/**
	 * @param int $iblockId
	 * @return void
	 */
	protected function loadDisplayPropertyCodes($iblockId)
	{

	}

	protected function loadBasketPropertyCodes($iblockId)
	{
		if (!$this->useCatalog)
			return;
		if (!isset($this->storage['CATALOGS'][$iblockId]))
			return;

		switch ($this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'])
		{
			case \CCatalogSku::TYPE_CATALOG:
				$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
					$iblockId,
					['CODE' => 'Y']
				);
				if ($list === null)
					$list = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['CART_PROPERTIES'] = $list;
				unset($list);
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_CART_PROPERTIES'] = [];
				break;
			case \CCatalogSku::TYPE_PRODUCT:
				$this->storage['IBLOCK_PARAMS'][$iblockId]['CART_PROPERTIES'] = [];
				$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
					$this->getOffersIblockId($iblockId),
					['CODE' => 'Y']
				);
				if ($list === null)
					$list = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_CART_PROPERTIES'] = $list;
				unset($list);
				break;
			case \CCatalogSku::TYPE_FULL:
				$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
					$iblockId,
					['CODE' => 'Y']
				);
				if ($list === null)
					$list = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['CART_PROPERTIES'] = $list;
				$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
					$this->getOffersIblockId($iblockId),
					['CODE' => 'Y']
				);
				if ($list === null)
					$list = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_CART_PROPERTIES'] = $list;
				unset($list);
				break;
			case \CCatalogSku::TYPE_OFFERS:
				$this->storage['IBLOCK_PARAMS'][$iblockId]['CART_PROPERTIES'] = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_CART_PROPERTIES'] = [];
				break;
			default:
				break;
		}
	}

	protected function loadOfferTreePropertyCodes($iblockId)
	{
		if (!$this->useCatalog)
			return;
		if (!isset($this->storage['CATALOGS'][$iblockId]))
			return;

		switch ($this->storage['CATALOGS'][$iblockId]['CATALOG_TYPE'])
		{
			case \CCatalogSku::TYPE_CATALOG:
			case \CCatalogSku::TYPE_OFFERS:
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_TREE_PROPS'] = [];
				break;
			case \CCatalogSku::TYPE_PRODUCT:
			case \CCatalogSku::TYPE_FULL:
				$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
					$this->storage['CATALOGS'][$iblockId]['IBLOCK_ID'],
					['CODE' => 'Y']
				);
				if ($list === null)
					$list = [];
				$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_TREE_PROPS'] = $list;
				unset($list);
				break;
			default:
				break;
		}
	}

	/* product tools */

	/**
	 * Return true, if enable quantity trace and disable make out-of-stock items available for purchase.
	 *
	 * @param array $product        Product data.
	 * @return bool
	 */
	protected function isNeedCheckQuantity(array $product)
	{
		return (
			$product['QUANTITY_TRACE'] === Catalog\ProductTable::STATUS_YES
			&& $product['CAN_BUY_ZERO'] === Catalog\ProductTable::STATUS_NO
		);
	}

	/* product tools end */

	/* user tools */

	/**
	 * Return user groups. Now worked only with current user.
	 *
	 * @return array
	 */
	protected function getUserGroups()
	{
		/** @global \CUser $USER */
		global $USER;
		$result = array(2);
		if (isset($USER) && $USER instanceof \CUser)
		{
			$result = $USER->GetUserGroupArray();
			Main\Type\Collection::normalizeArrayValuesByInt($result, true);
		}
		return $result;
	}

	/**
	 * Return user groups string for cache id.
	 *
	 * @return string
	 */
	protected function getUserGroupsCacheId()
	{
		return implode(',', $this->getUserGroups());
	}

	/* user tools end */

	/* compatibility tools */

	/**
	 * Filling deprecated fields of items for compatibility with old templates.
	 * Strict use only for catalog.element, .section, .top, etc in compatible mode.
	 *
	 * @param array $items			Product list.
	 * @return void
	 */
	protected function initCompatibleFields(array $items)
	{
		if (empty($items))
			return;

		$initFields = array(
			'PRICES' => array(),
			'PRICE_MATRIX' => false,
			'MIN_PRICE' => false
		);
		if (!$this->arParams['USE_PRICE_COUNT'] && !empty($this->storage['PRICES']))
		{
			foreach ($this->storage['PRICES'] as $value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;

				$priceType = $value['ID'];
				$initFields['CATALOG_GROUP_ID_'.$priceType] = $priceType;
				$initFields['~CATALOG_GROUP_ID_'.$priceType] = $priceType;
				$initFields['CATALOG_GROUP_NAME_'.$priceType] = $value['TITLE'];
				$initFields['~CATALOG_GROUP_NAME_'.$priceType] = $value['~TITLE'];
				$initFields['CATALOG_CAN_ACCESS_'.$priceType] = ($value['CAN_VIEW'] ? 'Y' : 'N');
				$initFields['~CATALOG_CAN_ACCESS_'.$priceType] = ($value['CAN_VIEW'] ? 'Y' : 'N');
				$initFields['CATALOG_CAN_BUY_'.$priceType] = ($value['CAN_BUY'] ? 'Y' : 'N');
				$initFields['~CATALOG_CAN_BUY_'.$priceType] = ($value['CAN_BUY'] ? 'Y' : 'N');
				$initFields['CATALOG_PRICE_ID_'.$priceType] = null;
				$initFields['~CATALOG_PRICE_ID_'.$priceType] = null;
				$initFields['CATALOG_PRICE_'.$priceType] = null;
				$initFields['~CATALOG_PRICE_'.$priceType] = null;
				$initFields['CATALOG_CURRENCY_'.$priceType] = null;
				$initFields['~CATALOG_CURRENCY_'.$priceType] = null;
				$initFields['CATALOG_QUANTITY_FROM_'.$priceType] = null;
				$initFields['~CATALOG_QUANTITY_FROM_'.$priceType] = null;
				$initFields['CATALOG_QUANTITY_TO_'.$priceType] = null;
				$initFields['~CATALOG_QUANTITY_TO_'.$priceType] = null;
				$initFields['CATALOG_EXTRA_ID_'.$priceType] = null;
				$initFields['~CATALOG_EXTRA_ID_'.$priceType] = null;
				unset($priceType);
			}
			unset($value);
		}

		foreach (array_keys($items) as $index)
			$this->oldData[$items[$index]['ID']] = $initFields;
		unset($index, $initFields);
	}

	/**
	 * Fill deprecated raw price data from database.
	 * Strict use only for catalog.element, .section, .top, etc in compatible mode.
	 *
	 * @param int $id			Item id.
	 * @param array $prices		Price rows from database.
	 * @return void
	 */
	protected function fillCompatibleRawPriceFields($id, array $prices)
	{
		if (!isset($this->oldData[$id]) || empty($prices) || $this->arParams['USE_PRICE_COUNT'])
			return;
		foreach ($prices as $rawPrice)
		{
			$priceType = $rawPrice['CATALOG_GROUP_ID'];
			$this->oldData[$id]['CATALOG_PRICE_ID_'.$priceType] = $rawPrice['ID'];
			$this->oldData[$id]['~CATALOG_PRICE_ID_'.$priceType] = $rawPrice['ID'];
			$this->oldData[$id]['CATALOG_PRICE_'.$priceType] = $rawPrice['PRICE'];
			$this->oldData[$id]['~CATALOG_PRICE_'.$priceType] = $rawPrice['PRICE'];
			$this->oldData[$id]['CATALOG_CURRENCY_'.$priceType] = $rawPrice['CURRENCY'];
			$this->oldData[$id]['~CATALOG_CURRENCY_'.$priceType] = $rawPrice['CURRENCY'];
			$this->oldData[$id]['CATALOG_QUANTITY_FROM_'.$priceType] = $rawPrice['QUANTITY_FROM'];
			$this->oldData[$id]['~CATALOG_QUANTITY_FROM_'.$priceType] = $rawPrice['QUANTITY_FROM'];
			$this->oldData[$id]['CATALOG_QUANTITY_TO_'.$priceType] = $rawPrice['QUANTITY_TO'];
			$this->oldData[$id]['~CATALOG_QUANTITY_TO_'.$priceType] = $rawPrice['QUANTITY_TO'];
			$this->oldData[$id]['CATALOG_EXTRA_ID_'.$priceType] = $rawPrice['EXTRA_ID'];
			$this->oldData[$id]['~CATALOG_EXTRA_ID_'.$priceType] = $rawPrice['EXTRA_ID'];
			unset($priceType);
		}
		unset($rawPrice);
	}

	/**
	 * Return deprecated field value for item.
	 * Strict use only for catalog.element, .section, .top, etc in compatible mode.
	 *
	 * @param int $id				Item id.
	 * @param string $field			Field name.
	 * @return null|mixed
	 */
	protected function getCompatibleFieldValue($id, $field)
	{
		if (!isset($this->oldData[$id]))
			return null;
		return ($this->oldData[$id][$field] ?? null);
	}

	/**
	 * Check quantity range for emulate CATALOG_SHOP_QUANTITY_* filter.
	 * Strict use only for catalog.element, .section, .top, etc in compatible mode.
	 *
	 * @param array $row		Price row from database.
	 * @return bool
	 */
	protected function checkQuantityRange(array $row)
	{
		return (
			($row['QUANTITY_FROM'] === null || $row['QUANTITY_FROM'] <= $this->arParams['SHOW_PRICE_COUNT'])
			&& ($row['QUANTITY_TO'] === null || $row['QUANTITY_TO'] >= $this->arParams['SHOW_PRICE_COUNT'])
		);
	}

	/**
	 * Returns old price result format for product with price ranges. Do not use this method.
	 *
	 * @return array
	 */
	protected function getEmptyPriceMatrix(): array
	{
		return array(
			'ROWS' => array(),
			'COLS' => array(),
			'MATRIX' => array(),
			'CAN_BUY' => array(),
			'AVAILABLE' => 'N',
			'CURRENCY_LIST' => array()
		);
	}

	/**
	 * Resort old price format for compatibility. Do not use this method.
	 * @internl
	 *
	 * @param int $id		Item id.
	 * @return void
	 */
	private function resortOldPrices($id)
	{
		if (empty($this->oldData[$id]['PRICES']) || count($this->oldData[$id]['PRICES']) < 2)
			return;
		foreach (array_keys($this->oldData[$id]['PRICES']) as $priceCode)
			$this->oldData[$id]['PRICES'][$priceCode]['_SORT'] = $this->storage['PRICES'][$priceCode]['SORT'];
		unset($priceCode);
		Main\Type\Collection::sortByColumn(
			$this->oldData[$id]['PRICES'],
			array('_SORT' => SORT_ASC, 'PRICE_ID' => SORT_ASC),
			'', null, true
		);
		foreach (array_keys($this->oldData[$id]['PRICES']) as $priceCode)
			unset($this->oldData[$id]['PRICES'][$priceCode]['_SORT']);
		unset($priceCode);
	}

	/**
	 * Returns old product keys.
	 *
	 * @return array
	 */
	protected function getCompatibleProductFields()
	{
		return [
			'TYPE' => 'CATALOG_TYPE',
			'AVAILABLE' => 'CATALOG_AVAILABLE',
			'BUNDLE' => 'CATALOG_BUNDLE',
			'QUANTITY' => 'CATALOG_QUANTITY',
			'QUANTITY_TRACE' => 'CATALOG_QUANTITY_TRACE',
			'CAN_BUY_ZERO' => 'CATALOG_CAN_BUY_ZERO',
			'MEASURE' => 'CATALOG_MEASURE',
			'SUBSCRIBE' => 'CATALOG_SUBSCRIBE',
			'VAT_ID' => 'CATALOG_VAT_ID',
			'VAT_INCLUDED' => 'CATALOG_VAT_INCLUDED',
			'WEIGHT' => 'CATALOG_WEIGHT',
			'WIDTH' => 'CATALOG_WIDTH',
			'LENGTH' => 'CATALOG_LENGTH',
			'HEIGHT' => 'CATALOG_HEIGHT',
			'PAYMENT_TYPE' => 'CATALOG_PRICE_TYPE',
			'RECUR_SCHEME_LENGTH' => 'CATALOG_RECUR_SCHEME_LENGTH',
			'RECUR_SCHEME_TYPE' => 'CATALOG_RECUR_SCHEME_TYPE',
			'QUANTITY_TRACE_RAW' => 'CATALOG_QUANTITY_TRACE_ORIG',
			'CAN_BUY_ZERO_RAW' => 'CATALOG_CAN_BUY_ZERO_ORIG',
			'SUBSCRIBE_RAW' => 'CATALOG_SUBSCRIBE_ORIG',
			'PURCHASING_PRICE' => 'CATALOG_PURCHASING_PRICE',
			'PURCHASING_CURRENCY' => 'CATALOG_PURCHASING_CURRENCY',
			'BARCODE_MULTI' => 'CATALOG_BARCODE_MULTI',
			'TRIAL_PRICE_ID' => 'CATALOG_TRIAL_PRICE_ID',
			'WITHOUT_ORDER' => 'CATALOG_WITHOUT_ORDER',
			'~TYPE' => '~CATALOG_TYPE',
			'~AVAILABLE' => '~CATALOG_AVAILABLE',
			'~BUNDLE' => '~CATALOG_BUNDLE',
			'~QUANTITY' => '~CATALOG_QUANTITY',
			'~QUANTITY_TRACE' => '~CATALOG_QUANTITY_TRACE',
			'~CAN_BUY_ZERO' => '~CATALOG_CAN_BUY_ZERO',
			'~MEASURE' => '~CATALOG_MEASURE',
			'~SUBSCRIBE' => '~CATALOG_SUBSCRIBE',
			'~VAT_ID' => '~CATALOG_VAT_ID',
			'~VAT_INCLUDED' => '~CATALOG_VAT_INCLUDED',
			'~WEIGHT' => '~CATALOG_WEIGHT',
			'~WIDTH' => '~CATALOG_WIDTH',
			'~LENGTH' => '~CATALOG_LENGTH',
			'~HEIGHT' => '~CATALOG_HEIGHT',
			'~PAYMENT_TYPE' => '~CATALOG_PRICE_TYPE',
			'~RECUR_SCHEME_LENGTH' => '~CATALOG_RECUR_SCHEME_LENGTH',
			'~RECUR_SCHEME_TYPE' => '~CATALOG_RECUR_SCHEME_TYPE',
			'~QUANTITY_TRACE_RAW' => '~CATALOG_QUANTITY_TRACE_ORIG',
			'~CAN_BUY_ZERO_RAW' => '~CATALOG_CAN_BUY_ZERO_ORIG',
			'~SUBSCRIBE_RAW' => '~CATALOG_SUBSCRIBE_ORIG',
			'~PURCHASING_PRICE' => '~CATALOG_PURCHASING_PRICE',
			'~PURCHASING_CURRENCY' => '~CATALOG_PURCHASING_CURRENCY',
			'~BARCODE_MULTI' => '~CATALOG_BARCODE_MULTI',
			'~TRIAL_PRICE_ID' => '~CATALOG_TRIAL_PRICE_ID',
			'~WITHOUT_ORDER' => '~CATALOG_WITHOUT_ORDER'
		];
	}

	/* compatibility tools end */
}
