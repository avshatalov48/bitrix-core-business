<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock\Component\Element,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogElementComponent extends Element
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(false);
	}

	/**
	 * Processing parameters unique to catalog.element component.
	 *
	 * @param array $params		Component parameters.
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		$params['COMPATIBLE_MODE'] = (isset($params['COMPATIBLE_MODE']) && $params['COMPATIBLE_MODE'] === 'N' ? 'N' : 'Y');
		if ($params['COMPATIBLE_MODE'] === 'N')
		{
			$params['SET_VIEWED_IN_COMPONENT'] = 'N';
			$params['DISABLE_INIT_JS_IN_COMPONENT'] = 'Y';
			$params['OFFERS_LIMIT'] = 0;
		}

		$this->setCompatibleMode($params['COMPATIBLE_MODE'] === 'Y');

		$params['SET_VIEWED_IN_COMPONENT'] = isset($params['SET_VIEWED_IN_COMPONENT']) && $params['SET_VIEWED_IN_COMPONENT'] === 'Y' ? 'Y' : 'N';

		$params['DISABLE_INIT_JS_IN_COMPONENT'] = isset($params['DISABLE_INIT_JS_IN_COMPONENT']) && $params['DISABLE_INIT_JS_IN_COMPONENT'] === 'Y' ? 'Y' : 'N';
		if ($params['DISABLE_INIT_JS_IN_COMPONENT'] !== 'Y')
		{
			\CJSCore::Init(array('popup'));
		}

		return $params;
	}

	/**
	 * Fill additional keys for component cache.
	 *
	 * @param array &$resultCacheKeys		Cached result keys.
	 * @return void
	 */
	protected function initAdditionalCacheKeys(&$resultCacheKeys)
	{
		parent::initAdditionalCacheKeys($resultCacheKeys);

		if (
			$this->useCatalog
			&& !empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
			&& is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
		)
		{
			$element =& $this->elements[0];

			// catalog hit stats
			$productTitle = !empty($element['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
				? $element['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
				: $element['NAME'];

			$categoryId = '';
			$categoryPath = array();

			if (isset($element['SECTION']['ID']))
			{
				$categoryId = $element['SECTION']['ID'];
			}

			if (isset($element['SECTION']['PATH']))
			{
				foreach ($element['SECTION']['PATH'] as $cat)
				{
					$categoryPath[$cat['ID']] = $cat['NAME'];
				}
			}

			$this->arResult['CATEGORY_PATH'] = implode('/', $categoryPath);

			$counterData = array(
				'product_id' => $element['ID'],
				'iblock_id' => $this->arParams['IBLOCK_ID'],
				'product_title' => $productTitle,
				'category_id' => $categoryId,
				'category' => $categoryPath
			);

			if (empty($element['OFFERS']))
			{
				$priceProductId = $element['ID'];
			}
			else
			{
				$offer = reset($element['OFFERS']);
				$priceProductId = $offer['ID'];
				unset($offer);
			}

			// price for anonymous
			if ($this->useDiscountCache)
			{
				if ($this->storage['USE_SALE_DISCOUNTS'])
				{
					$priceTypes = array();
					$priceIterator = Catalog\GroupAccessTable::getList(array(
						'select' => array('CATALOG_GROUP_ID'),
						'filter' => array('GROUP_ID' => 2, '=ACCESS' => Catalog\GroupAccessTable::ACCESS_BUY),
						'order' => array('CATALOG_GROUP_ID' => 'ASC')
					));
					while ($priceType = $priceIterator->fetch())
					{
						$priceTypeId = (int)$priceType['CATALOG_GROUP_ID'];
						$priceTypes[$priceTypeId] = $priceTypeId;
						unset($priceTypeId);
					}
					Catalog\Discount\DiscountManager::preloadPriceData(array($priceProductId), $priceTypes);
					Catalog\Product\Price::loadRoundRules($priceTypes);
				}
			}
			$optimalPrice = \CCatalogProduct::GetOptimalPrice($priceProductId, 1, array(2), 'N', array(), $this->getSiteId(), array());
			$counterData['price'] = $optimalPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
			$counterData['currency'] = $optimalPrice['RESULT_PRICE']['CURRENCY'];

			// make sure it is in utf8
			$counterData = Main\Text\Encoding::convertEncoding($counterData, SITE_CHARSET, 'UTF-8');

			// pack value and protocol version
			$rcmLogCookieName = Main\Config\Option::get('main', 'cookie_name', 'BITRIX_SM') . '_' . Main\Analytics\Catalog::getCookieLogName();

			$this->arResult['counterData'] = array(
				'item' => base64_encode(json_encode($counterData)),
				'user_id' => new Main\Text\JsExpression(
					'function(){return BX.message("USER_ID") ? BX.message("USER_ID") : 0;}'
				),
				'recommendation' => new Main\Text\JsExpression(
					'function() {
							var rcmId = "";
							var cookieValue = BX.getCookie("' . $rcmLogCookieName . '");
							var productId = ' . $element["ID"] . ';
							var cItems = [];
							var cItem;

							if (cookieValue)
							{
								cItems = cookieValue.split(".");
							}

							var i = cItems.length;
							while (i--)
							{
								cItem = cItems[i].split("-");
								if (cItem[0] == productId)
								{
									rcmId = cItem[1];
									break;
								}
							}

							return rcmId;
						}'
				),
				'v' => '2'
			);
			$resultCacheKeys[] = 'counterData';

			if ($this->arParams['SET_VIEWED_IN_COMPONENT'] === 'Y')
			{
				$viewedProduct = array(
					'PRODUCT_ID' => $element['ID'],
					'OFFER_ID' => $element['ID']
				);

				if (!empty($element['OFFERS']))
				{
					$viewedProduct['OFFER_ID'] = $element['OFFER_ID_SELECTED'] > 0
						? $element['OFFER_ID_SELECTED']
						: $element['OFFERS'][0]['ID'];
				}

				$this->arResult['VIEWED_PRODUCT'] = $viewedProduct;
				$resultCacheKeys[] = 'VIEWED_PRODUCT';
				unset($viewedProduct);
			}
			unset($element);
		}
	}

	/**
	 * Save compatible viewed product in catalog.element only.
	 *
	 * @return void
	 */
	protected function saveViewedProduct()
	{
		if ($this->isEnableCompatible())
		{
			if ((string)Main\Config\Option::get('sale', 'product_viewed_save') === 'Y')
			{
				if (
					!isset($_SESSION['VIEWED_ENABLE'])
					&& isset($_SESSION['VIEWED_PRODUCT'])
					&& $_SESSION['VIEWED_PRODUCT'] != $this->arResult['ID']
					&& Loader::includeModule('sale')
				)
				{
					$_SESSION['VIEWED_ENABLE'] = 'Y';
					$fields = array(
						'PRODUCT_ID' => (int)$_SESSION['VIEWED_PRODUCT'],
						'MODULE' => 'catalog',
						'LID' => $this->getSiteId()
					);
					\CSaleViewedProduct::Add($fields);
				}

				if (
					isset($_SESSION['VIEWED_ENABLE'])
					&& $_SESSION['VIEWED_ENABLE'] === 'Y'
					&& $_SESSION['VIEWED_PRODUCT'] != $this->arResult['ID']
					&& Loader::includeModule('sale')
				)
				{
					$fields = array(
						'PRODUCT_ID' => $this->arResult['ID'],
						'MODULE' => 'catalog',
						'LID' => $this->getSiteId(),
						'IBLOCK_ID' => $this->arResult['IBLOCK_ID']
					);
					\CSaleViewedProduct::Add($fields);
				}

				$_SESSION['VIEWED_PRODUCT'] = $this->arResult['ID'];
			}

			if ($this->arParams['SET_VIEWED_IN_COMPONENT'] === 'Y' && !empty($this->arResult['VIEWED_PRODUCT']))
			{
				if (Loader::includeModule('catalog') && Loader::includeModule('sale'))
				{
					Catalog\CatalogViewedProductTable::refresh(
						$this->arResult['VIEWED_PRODUCT']['OFFER_ID'],
						\CSaleBasket::GetBasketUserID(),
						$this->getSiteId(),
						$this->arResult['VIEWED_PRODUCT']['PRODUCT_ID']
					);
				}
			}
		}
	}

	/**
	 * Save bigdata analytics for catalog.element only.
	 *
	 * @return void
	 */
	protected function sendCounters()
	{
		parent::sendCounters();
		if (isset($this->arResult['counterData']) && Main\Analytics\Catalog::isOn())
		{
			Main\Analytics\Counter::sendData('ct', $this->arResult['counterData']);
		}
	}
}