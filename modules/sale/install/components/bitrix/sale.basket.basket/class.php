<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Catalog;
use Bitrix\Highloadblock as HL;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Basket;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\PriceMaths;

class CBitrixBasketComponent extends CBitrixComponent
{
	const INITIAL_LOAD_ACTION = 'initialLoad';

	const SEARCH_OFFER_BY_PROPERTIES = 'PROPS';
	const SEARCH_OFFER_BY_ID = 'ID';

	const IMAGE_SIZE_STANDARD = 110;
	const IMAGE_SIZE_ADAPTIVE = 320;

	/** @var Sale\Basket\Storage $basketStorage */
	protected $basketStorage;

	protected $action;
	protected $fUserId;
	protected $basketItems = [];
	protected $storage = [];
	/** @var ErrorCollection $errorCollection */
	protected $errorCollection;

	public $arCustomSelectFields = [];
	public $arIblockProps = [];
	public $weightKoef = 0;
	public $weightUnit = 0;
	public $quantityFloat = 'N';
	/** @deprecated deprecated since 14.0.4 */
	public $countDiscount4AllQuantity = 'N';
	public $priceVatShowValue = 'N';
	public $hideCoupon = 'N';
	public $usePrepayment = 'N';
	public $pathToOrder = '/personal/order.php';
	public $columns = [];
	public $offersProps = [];
	protected static $iblockIncluded = null;
	protected static $catalogIncluded = null;
	protected static $highLoadInclude = null;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function addErrors($errors, $code)
	{
		if (!empty($errors) && is_array($errors))
		{
			/** @var Error $error */
			foreach ($errors as $error)
			{
				$message = $this->checkMessageByCode($error);
				$this->errorCollection->setError(new Error($message, $code));
			}
		}
	}

	protected function checkMessageByCode(Error $error)
	{
		$codeToMessageMap = [
			'SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY' => Loc::getMessage('SBB_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'),
			'SALE_BASKET_AVAILABLE_QUANTITY' => Loc::getMessage('SBB_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'),
			'SALE_BASKET_ITEM_WRONG_PRICE' => Loc::getMessage('SBB_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'),
		];

		$code = $error->getCode();

		if (isset($codeToMessageMap[$code]))
		{
			$message = $codeToMessageMap[$code];
		}
		else
		{
			$message = $error->getMessage();
		}

		return $message;
	}

	/**
	 * Return settings script path with modified time postfix.
	 *
	 * @param string $componentPath Path to component.
	 * @param string $settingsName Settings name.
	 * @return string
	 * @throws Main\IO\FileNotFoundException
	 */
	public static function getSettingsScript($componentPath, $settingsName)
	{
		$path = $componentPath.'/settings/'.$settingsName.'/script.js';
		$file = new Main\IO\File(Main\Application::getDocumentRoot().$path);

		return $path.'?'.$file->getModificationTime();
	}

	public function onPrepareComponentParams($params)
	{
		$params['CACHE_GROUPS'] ??= 'N';

		if (isset($params['CUSTOM_SITE_ID']))
		{
			$this->setSiteId($params['CUSTOM_SITE_ID']);
		}

		if (
			isset($params['CONTEXT_SITE_ID']) &&
			$params['CONTEXT_SITE_ID'] > 0 &&
			\Bitrix\Main\Loader::includeModule('landing')
		)
		{
			$componentName = 'bitrix:landing.pub';
			$className = \CBitrixComponent::includeComponentClass($componentName);
			$demoCmp = new $className;
			$demoCmp->onSaleBasketItemBeforeSaved();
		}

		if (!$this->includeModules())
		{
			return $params;
		}

		global $APPLICATION;

		$this->initParametersFromRequest($params);

		if ($this->initComponentTemplate())
		{
			$template = $this->getTemplate();

			if (
				$template instanceof CBitrixComponentTemplate
				&& $template->GetSiteTemplate() == ''
				&& $template->GetName() === '.default'
			)
			{
				if (!isset($params['COMPATIBLE_MODE']))
				{
					$params['COMPATIBLE_MODE'] = 'N';
				}

				if (!isset($params['DEFERRED_REFRESH']))
				{
					$params['DEFERRED_REFRESH'] = 'Y';
				}
			}
			else
			{
				if (!isset($params['COMPATIBLE_MODE']))
				{
					$params['COMPATIBLE_MODE'] = 'Y';
				}

				if (!isset($params['DEFERRED_REFRESH']))
				{
					$params['DEFERRED_REFRESH'] = 'N';
				}
			}
		}

		$params['COMPATIBLE_MODE'] = isset($params['COMPATIBLE_MODE']) && $params['COMPATIBLE_MODE'] === 'N' ? 'N' : 'Y';
		$params['DEFERRED_REFRESH'] = isset($params['DEFERRED_REFRESH']) && $params['DEFERRED_REFRESH'] === 'Y' ? 'Y' : 'N';

		$params['AJAX_PATH'] = !empty($params['AJAX_PATH']) ? trim((string)$params['AJAX_PATH']) : $this->getPath().'/ajax.php';

		if (isset($params['SET_TITLE']) && $params['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle(Loc::getMessage('SBB_TITLE'));
		}

		$params['PATH_TO_ORDER'] = trim((string)$params['PATH_TO_ORDER']);
		if (empty($params['PATH_TO_ORDER']))
		{
			$params['PATH_TO_ORDER'] = '/personal/order/make/';
		}

		if (isset($params['PATH_TO_BASKET']))
		{
			$params['PATH_TO_BASKET'] = trim((string)$params['PATH_TO_BASKET']);
		}
		else
		{
			$params['PATH_TO_BASKET'] = '';
		}

		if (empty($params['PATH_TO_BASKET']))
		{
			$params['PATH_TO_BASKET'] = '/personal/cart/';
		}

		$params['DETAIL_URL'] = trim((string)($params['DETAIL_URL'] ?? ''));
		$params['QUANTITY_FLOAT'] = isset($params['QUANTITY_FLOAT']) && $params['QUANTITY_FLOAT'] === 'N' ? 'N' : 'Y';
		$params['HIDE_COUPON'] = isset($params['HIDE_COUPON']) && $params['HIDE_COUPON'] === 'Y' ? 'Y' : 'N';
		$params['PRICE_VAT_SHOW_VALUE'] = isset($params['PRICE_VAT_SHOW_VALUE']) && $params['PRICE_VAT_SHOW_VALUE'] === 'N' ? 'N' : 'Y';
		$params['USE_PREPAYMENT'] = isset($params['USE_PREPAYMENT']) && $params['USE_PREPAYMENT'] === 'Y' ? 'Y' : 'N';
		$params['AUTO_CALCULATION'] = isset($params['AUTO_CALCULATION']) && $params['AUTO_CALCULATION'] === 'N' ? 'N' : 'Y';

		$params['WEIGHT_KOEF'] = (float)COption::GetOptionString('sale', 'weight_koef', 1, $this->getSiteId());
		if ($params['WEIGHT_KOEF'] === 0.0)
		{
			$params['WEIGHT_KOEF'] = 1;
		}

		$params['WEIGHT_UNIT'] = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', '', $this->getSiteId()));

		// default columns
		if (!isset($params['COLUMNS_LIST_EXT']) || !is_array($params['COLUMNS_LIST_EXT']))
		{
			if (!empty($params['COLUMNS_LIST']))
			{
				$params['COLUMNS_LIST_EXT'] = $params['COLUMNS_LIST'];

				// compatibility
				if (!in_array('PREVIEW_PICTURE', $params['COLUMNS_LIST_EXT']))
				{
					$params['COLUMNS_LIST_EXT'][] = 'PREVIEW_PICTURE';
				}
			}
			else
			{
				$params['COLUMNS_LIST_EXT'] = [
					'PREVIEW_PICTURE', 'DISCOUNT', 'DELETE', 'DELAY', 'TYPE', 'SUM',
				];
			}
		}

		// required columns
		if (!in_array('NAME', $params['COLUMNS_LIST_EXT']))
		{
			$params['COLUMNS_LIST_EXT'] = array_merge(['NAME'], $params['COLUMNS_LIST_EXT']);
		}

		if (!in_array('QUANTITY', $params['COLUMNS_LIST_EXT']))
		{
			$params['COLUMNS_LIST_EXT'][] = 'QUANTITY';
		}

		if (!in_array('PRICE', $params['COLUMNS_LIST_EXT']))
		{
			if (!in_array('SUM', $params['COLUMNS_LIST_EXT']))
			{
				$params['COLUMNS_LIST_EXT'][] = 'PRICE';
			}
			else // make PRICE before SUM
			{
				$index = array_search('SUM', $params['COLUMNS_LIST_EXT']);
				array_splice($params['COLUMNS_LIST_EXT'], $index, 0, 'PRICE');
			}
		}

		$params['COLUMNS_LIST'] = $params['COLUMNS_LIST_EXT'];

		if (static::includeIblock() && Iblock\Model\PropertyFeature::isEnabledFeatures())
		{
			$params['OFFERS_PROPS'] = [];

			$iterator = Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID'],
				'filter' => ['!=PRODUCT_IBLOCK_ID' => 0],
			]);
			while ($row = $iterator->fetch())
			{
				$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
					$row['IBLOCK_ID'],
					['CODE' => 'Y']
				);

				if (!empty($list))
				{
					$params['OFFERS_PROPS'] = array_merge($params['OFFERS_PROPS'], $list);
				}
			}
		}

		if (!isset($params['OFFERS_PROPS']) || !is_array($params['OFFERS_PROPS']))
		{
			$params['OFFERS_PROPS'] = [];
		}

		$params['ACTION_VARIABLE'] = isset($params['ACTION_VARIABLE']) ? trim((string)$params['ACTION_VARIABLE']) : '';

		if (
			empty($params['ACTION_VARIABLE'])
			|| !preg_match('/[a-zA-Z0-9_-~.!*\'(),]/', $params['ACTION_VARIABLE'])
		)
		{
			$params['ACTION_VARIABLE'] = 'basketAction';
		}
		else
		{
			$params['ACTION_VARIABLE'] = trim((string)$params['ACTION_VARIABLE']);
		}

		$params['CORRECT_RATIO'] = isset($params['CORRECT_RATIO']) && $params['CORRECT_RATIO'] === 'N' ? 'N' : 'Y';

		foreach ($params as $k => $v)
		{
			if (mb_strpos($k, 'ADDITIONAL_PICT_PROP_') !== false)
			{
				$iblockId = intval(mb_substr($k, mb_strlen('ADDITIONAL_PICT_PROP_')));

				if ($v !== '-')
				{
					$params['ADDITIONAL_PICT_PROP'][$iblockId] = $v;
				}

				unset($params[$k]);
			}
		}

		// check for direct initialization with ADDITIONAL_PICT_PROP parameter
		if (!empty($params['ADDITIONAL_PICT_PROP']) && is_array($params['ADDITIONAL_PICT_PROP']))
		{
			$pictProp = [];

			foreach ($params['ADDITIONAL_PICT_PROP'] as $iblockId => $property)
			{
				$pictProp[(int)$iblockId] = $property;
			}

			$params['ADDITIONAL_PICT_PROP'] = $pictProp;
		}

		if (!isset($params['BASKET_IMAGES_SCALING']) || !in_array($params['BASKET_IMAGES_SCALING'], ['standard', 'adaptive', 'no_scale']))
		{
			$params['BASKET_IMAGES_SCALING'] = 'adaptive';
		}

		if (!isset($params['LABEL_PROP']) || !is_array($params['LABEL_PROP']))
		{
			$params['LABEL_PROP'] = [];
		}

		if (!isset($params['LABEL_PROP_MOBILE']) || !is_array($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = [];
		}

		if (!empty($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = array_fill_keys($params['LABEL_PROP_MOBILE'], true);
		}

		if (isset($params['LABEL_PROP_POSITION']))
		{
			$params['LABEL_PROP_POSITION'] = trim((string)$params['LABEL_PROP_POSITION']);
		}

		if (empty($params['LABEL_PROP_POSITION']))
		{
			$params['LABEL_PROP_POSITION'] = 'top-left';
		}

		$params['SHOW_DISCOUNT_PERCENT'] = !isset($params['SHOW_DISCOUNT_PERCENT']) || $params['SHOW_DISCOUNT_PERCENT'] === 'Y' ? 'Y' : 'N';

		if (isset($params['DISCOUNT_PERCENT_POSITION']))
		{
			$params['DISCOUNT_PERCENT_POSITION'] = trim((string)$params['DISCOUNT_PERCENT_POSITION']);
		}

		if (empty($params['LABEL_PROP_POSITION']))
		{
			$params['DISCOUNT_PERCENT_POSITION'] = 'bottom-right';
		}

		$params['BASKET_WITH_ORDER_INTEGRATION'] = isset($params['BASKET_WITH_ORDER_INTEGRATION']) && $params['BASKET_WITH_ORDER_INTEGRATION'] === 'Y' ? 'Y' : 'N';
		$params['BASKET_MAX_COUNT_TO_SHOW'] = isset($params['BASKET_MAX_COUNT_TO_SHOW']) ? (int)$params['BASKET_MAX_COUNT_TO_SHOW'] : 5;
		$params['BASKET_HAS_BEEN_REFRESHED'] = isset($params['BASKET_HAS_BEEN_REFRESHED']) && $params['BASKET_HAS_BEEN_REFRESHED'] === 'Y' ? 'Y' : 'N';

		$params['SHOW_RESTORE'] = isset($params['SHOW_RESTORE']) && $params['SHOW_RESTORE'] === 'N' ? 'N' : 'Y';

		// default gifts
		if (empty($params['USE_GIFTS']))
		{
			$params['USE_GIFTS'] = 'Y';
		}

		if (empty($params['GIFTS_PLACE']))
		{
			$params['GIFTS_PLACE'] = 'BOTTOM';
		}

		if (!isset($params['GIFTS_PAGE_ELEMENT_COUNT']))
		{
			$params['GIFTS_PAGE_ELEMENT_COUNT'] = 4;
		}

		if ($params['BASKET_WITH_ORDER_INTEGRATION'] === 'Y')
		{
			$params['USE_GIFTS'] = 'N';
			$params['HIDE_COUPON'] = 'Y';
			$params['USE_PREPAYMENT'] = 'N';
		}

		$this->initializeParameters($params);

		return $params;
	}

	protected function initializeParameters($params)
	{
		$this->weightKoef = $params['WEIGHT_KOEF'];
		$this->weightUnit = $params['WEIGHT_UNIT'];

		$this->columns = $params['COLUMNS_LIST'];
		$this->offersProps = $params['OFFERS_PROPS'];
		$this->offersProps = array_values(array_unique($this->offersProps));

		$this->quantityFloat = $params['QUANTITY_FLOAT'];
		$this->priceVatShowValue = $params['PRICE_VAT_SHOW_VALUE'];
		$this->hideCoupon = $params['HIDE_COUPON'];
		$this->usePrepayment = $params['USE_PREPAYMENT'];

		$this->pathToOrder = $params['PATH_TO_ORDER'];
	}

	public function initParametersFromRequest(&$params)
	{
		if (!$this->request->isPost() || $this->request->get('via_ajax') === 'Y')
		{
			return;
		}

		$params['COMPATIBLE_MODE'] = 'Y';
		$params['DEFERRED_REFRESH'] = 'N';

		if (empty($params['COLUMNS_LIST']))
		{
			$columns = (string)$this->request->get('select_props');

			if (!empty($columns))
			{
				$params['COLUMNS_LIST'] = explode(',', $columns);
			}
		}

		if (empty($params['OFFERS_PROPS']))
		{
			$offerProps = (string)$this->request->get('offers_props');

			if (!empty($offerProps))
			{
				$params['OFFERS_PROPS'] = explode(',', $offerProps);
			}
		}

		if (empty($params['QUANTITY_FLOAT']))
		{
			$params['QUANTITY_FLOAT'] = $this->request->get('quantity_float') === 'Y' ? 'Y' : 'N';
		}

		if (empty($params['PRICE_VAT_SHOW_VALUE']))
		{
			$params['PRICE_VAT_SHOW_VALUE'] = $this->request->get('price_vat_show_value') === 'Y' ? 'Y' : 'N';
		}

		if (empty($params['HIDE_COUPON']))
		{
			$params['HIDE_COUPON'] = $this->request->get('hide_coupon') === 'Y' ? 'Y' : 'N';
		}

		if (empty($params['USE_PREPAYMENT']))
		{
			$params['USE_PREPAYMENT'] = $this->request->get('use_prepayment') === 'Y' ? 'Y' : 'N';
		}

		if (empty($params['ACTION_VARIABLE']))
		{
			$params['ACTION_VARIABLE'] = $this->request->get('action_var');
		}
	}

	protected function isCompatibleMode()
	{
		return $this->arParams['COMPATIBLE_MODE'] === 'Y';
	}

	public function onIncludeComponentLang()
	{
		Loc::loadMessages(__FILE__);
	}

	public static function sendJsonAnswer($result)
	{
		global $APPLICATION;

		$APPLICATION->RestartBuffer();
		header('Content-Type: application/json');

		echo \Bitrix\Main\Web\Json::encode($result);

		CMain::FinalActions();
		die();
	}

	protected function includeModules()
	{
		$success = true;

		if (!Loader::includeModule('sale'))
		{
			$success = false;
			ShowError(Loc::getMessage('SALE_MODULE_NOT_INSTALL'));
		}

		return $success;
	}

	protected static function includeIblock()
	{
		if (!isset(self::$iblockIncluded))
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		return self::$iblockIncluded;
	}

	protected static function includeCatalog()
	{
		if (!isset(self::$catalogIncluded))
		{
			self::$catalogIncluded = Loader::includeModule('catalog');
		}

		return self::$catalogIncluded;
	}

	protected function makeCompatibleArray(&$array)
	{
		if (empty($array) || !is_array($array))
			return;

		$arr = [];
		foreach ($array as $key => $value)
		{
			if (
				is_array($value)
				|| (is_string($value) && preg_match("/[;&<>\"]/", $value))
			)
			{
				$arr[$key] = htmlspecialcharsEx($value);
			}
			else
			{
				$arr[$key] = $value;
			}

			$arr["~{$key}"] = $value;
		}

		$array = $arr;
	}

	// making correct names for actions (camel case without '_')
	protected function getCorrectActionName($action)
	{
		$action = str_replace('_', ' ', trim((string)$action));

		return str_replace(' ', '', lcfirst(ucwords($action)));
	}

	protected function prepareAction()
	{
		$action = (string)$this->request->get($this->arParams['ACTION_VARIABLE']);

		// prepayment actions
		if (empty($action) && !$this->isBasketIntegrated())
		{
			$basketRefresh = $this->request->get('BasketRefresh');
			$basketOrder = $this->request->get('BasketOrder');

			if (!empty($basketRefresh) || !empty($basketOrder))
			{
				$action = 'basketOrder';
			}
		}

		$action = $this->getCorrectActionName($action);
		if (empty($action))
		{
			$action = self::INITIAL_LOAD_ACTION;
		}

		return $action;
	}

	protected function doAction($action)
	{
		$funcName = $action.'Action';

		if (is_callable([$this, $funcName]))
		{
			$this->{$funcName}();
		}
	}

	protected function initialLoadAction()
	{
		$this->arResult = array_merge($this->arResult, $this->getBasketItems());
		$this->arResult['WARNING_MESSAGE'] += $this->getWarningsFromSession();

		CJSCore::Init(['ajax', 'popup']);
		$this->IncludeComponentTemplate();
	}

	// legacy method
	protected function selectItemAction()
	{
		$currentId = (int)$this->request->get('basketItemId');
		$propValues = $this->request->get('props') ?: [];

		$this->changeProductOffer($currentId, self::SEARCH_OFFER_BY_PROPERTIES, $propValues, true);

		$result = [];
		$result['DELETE_ORIGINAL'] = 'Y';
		$result['BASKET_DATA'] = $this->getBasketItems();
		$result['COLUMNS'] = (string)$this->request->get('select_props');
		$result['BASKET_ID'] = $currentId;
		$result['CODE'] = 'SUCCESS';

		$result['PARAMS']['QUANTITY_FLOAT'] = $this->request->get('quantity_float') === 'Y' ? 'Y' : 'N';
		unset($result['BASKET_DATA']['APPLIED_DISCOUNT_LIST'], $result['BASKET_DATA']['FULL_DISCOUNT_LIST']);

		self::sendJsonAnswer($result);
	}

	// legacy method
	protected function recalculateAction()
	{
		$currentId = (int)$this->request->get('basketItemId');
		$propValues = $this->request->get('props') ?: [];

		$this->changeProductOffer($currentId, self::SEARCH_OFFER_BY_PROPERTIES, $propValues, true);

		$result = $this->recalculateBasket($this->request->toArray());

		if (!$this->errorCollection->isEmpty())
		{
			/** @var Error $error */
			foreach ($this->errorCollection as $error)
			{
				$result['WARNING_MESSAGE'][] = $error->getMessage();
			}
		}

		$result['BASKET_DATA'] = $this->getBasketItems();
		$result['COLUMNS'] = (string)$this->request->get('select_props');
		$result['CODE'] = 'SUCCESS';

		if ($this->needToReloadGifts($result))
		{
			$result['BASKET_DATA']['GIFTS_RELOAD'] = true;
		}

		$result['PARAMS']['QUANTITY_FLOAT'] = $this->request->get('quantity_float') === 'Y' ? 'Y' : 'N';
		unset($result['BASKET_DATA']['APPLIED_DISCOUNT_LIST'], $result['BASKET_DATA']['FULL_DISCOUNT_LIST']);

		self::sendJsonAnswer($result);
	}

	// legacy method
	protected function deleteAction()
	{
		global $APPLICATION;

		if (in_array('DELETE', $this->arParams['COLUMNS_LIST']))
		{
			$basket = $this->getBasketStorage()->getBasket();
			if (!$basket->isEmpty())
			{
				$id = (int)$this->request->get('id');
				/** @var Sale\BasketItem $item */
				$item = $basket->getItemByBasketCode($id);
				if ($item)
				{
					$deleteResult = $item->delete();

					if ($deleteResult->isSuccess())
					{
						$saveResult = $basket->save();

						if (!$saveResult->isSuccess())
						{
							$deleteResult->addErrors($saveResult->getErrors());
						}
					}

					if ($deleteResult->isSuccess())
					{
						LocalRedirect($APPLICATION->GetCurPage());
					}
				}
			}
		}
	}

	// legacy method
	protected function delayAction()
	{
		global $APPLICATION;

		if (in_array('DELAY', $this->arParams['COLUMNS_LIST']))
		{
			$basket = $this->getBasketStorage()->getBasket();
			if (!$basket->isEmpty())
			{
				$id = (int)$this->request->get('id');
				/** @var Sale\BasketItem $item */
				$item = $basket->getItemByBasketCode($id);
				if ($item)
				{
					$delayResult = $item->setField('DELAY', 'Y');

					if ($delayResult->isSuccess())
					{
						$saveResult = $basket->save();

						if (!$saveResult->isSuccess())
						{
							$delayResult->addErrors($saveResult->getErrors());
						}

						if ($delayResult->isSuccess())
						{
							LocalRedirect($APPLICATION->GetCurPage());
						}
					}
				}
			}
		}
	}

	// legacy method
	protected function addAction()
	{
		global $APPLICATION;

		if (in_array('DELAY', $this->arParams['COLUMNS_LIST']))
		{
			$basket = $this->getBasketStorage()->getBasket();
			if (!$basket->isEmpty())
			{
				$id = (int)$this->request->get('id');
				/** @var Sale\BasketItem $item */
				$item = $basket->getItemByBasketCode($id);
				if ($item)
				{
					if ($item->isDelay() && $item->canBuy())
					{
						$delayResult = $item->setField('DELAY', 'N');

						if ($delayResult->isSuccess())
						{
							$saveResult = $basket->save();

							if (!$saveResult->isSuccess())
							{
								$delayResult->addErrors($saveResult->getErrors());
							}

							if (!$delayResult->isSuccess())
							{
								$_SESSION['SALE_BASKET_MESSAGE'][] = Loc::getMessage(
									'SBB_PRODUCT_NOT_AVAILABLE',
									['#PRODUCT#' => $item->getField('NAME')]
								);
							}
						}
					}
					else
					{
						$_SESSION['SALE_BASKET_MESSAGE'][] = Loc::getMessage(
							'SBB_PRODUCT_NOT_AVAILABLE',
							['#PRODUCT#' => $item->getField('NAME')]
						);
					}

					LocalRedirect($APPLICATION->GetCurPage());
				}
			}
		}
	}

	// legacy method
	protected function basketOrderAction()
	{
		global $APPLICATION;

		$basketRefresh = (string)$this->request->get('BasketRefresh');
		$basketOrder = (string)$this->request->get('BasketOrder');

		$postList = $this->request->toArray();
		$result = $this->recalculateBasket($postList);
		$this->saveBasket();

		if (!$this->errorCollection->isEmpty())
		{
			/** @var Error $error */
			foreach ($this->errorCollection as $error)
			{
				$result['WARNING_MESSAGE'][] = $error->getMessage();
			}
		}

		if (empty($basketRefresh) && !empty($basketOrder) && empty($result['WARNING_MESSAGE']))
		{
			if (!array_key_exists('paypalbutton_x', $postList) && !array_key_exists('paypalbutton_y', $postList))
			{
				LocalRedirect($this->pathToOrder);
			}
		}
		else
		{
			if (!empty($result['WARNING_MESSAGE']))
			{
				$_SESSION['SALE_BASKET_MESSAGE'] = $result['WARNING_MESSAGE'];
			}

			LocalRedirect($APPLICATION->GetCurPage());
		}

		$this->initialLoadAction();
	}

	protected function applyTemplateMutator(&$result)
	{
		if ($this->initComponentTemplate())
		{
			$template = $this->getTemplate();
			$templateFolder = $template->GetFolder();

			if (!empty($templateFolder))
			{
				$file = new Main\IO\File(Main\Application::getDocumentRoot().$templateFolder.'/mutator.php');

				if ($file->isExists())
				{
					include($file->getPath());
				}
			}
		}
	}

	protected function recalculateAjaxAction()
	{
		$result = $this->recalculateBasket($this->request->get('basket'));

		[$basketRefreshed, $changedBasketItems] = $this->refreshAndCorrectRatio();
		$result['BASKET_REFRESHED'] = $basketRefreshed;
		$result['CHANGED_BASKET_ITEMS'] = array_merge($result['CHANGED_BASKET_ITEMS'], $changedBasketItems);

		$this->saveBasket();
		$this->modifyResultAfterSave($result);

		if (
			!empty($result['APPLIED_DISCOUNT_IDS'])
			|| implode(',', $result['APPLIED_DISCOUNT_IDS']) !== $this->request->get('lastAppliedDiscounts')
			|| $this->request->get('fullRecalculation') === 'Y'
		)
		{
			// reload all items
			$this->loadBasketItems();
		}
		else
		{
			$this->loadBasketItems($result['CHANGED_BASKET_ITEMS']);
		}

		$result['BASKET_DATA'] = $this->getBasketResult();

		if ($this->needToReloadGifts($result))
		{
			$result['GIFTS_RELOAD'] = true;
		}

		self::sendJsonAnswer($result);
	}

	protected function refreshAjaxAction()
	{
		if (
			$this->needBasketRefresh()
			&& ($this->request->get('fullRecalculation') === 'Y' || $this->basketHasItemsToUpdate())
		)
		{
			$this->recalculateAjaxAction();
		}

		$result = $this->getDefaultAjaxAnswer();
		$result['BASKET_REFRESHED'] = true;

		self::sendJsonAnswer($result);
	}

	protected function modifyResultAfterSave(&$result)
	{
		if (!empty($result['RESTORED_BASKET_ITEMS']))
		{
			/** @var Sale\BasketItem $basketItem */
			foreach ($result['RESTORED_BASKET_ITEMS'] as $oldId => $basketItem)
			{
				$newId = $basketItem->getId();

				$result['RESTORED_BASKET_ITEMS'][$oldId] = $newId;
				$result['CHANGED_BASKET_ITEMS'][] = $newId;

				if (($key = array_search($basketItem->getBasketCode(), $result['CHANGED_BASKET_ITEMS'])) !== false)
				{
					unset($result['CHANGED_BASKET_ITEMS'][$key]);
				}
			}
		}

		$orderableBasket = $this->getBasketStorage()->getOrderableBasket();
		$this->initializeBasketOrderIfNotExists($orderableBasket);

		foreach ($orderableBasket as $item)
		{
			if ($item->isChanged())
			{
				$result['CHANGED_BASKET_ITEMS'][] = $item->getBasketCode();
			}
		}

		$result['CHANGED_BASKET_ITEMS'] = array_unique($result['CHANGED_BASKET_ITEMS']);

		$discountList = $orderableBasket->getOrder()->getDiscount()->getApplyResult(true);
		$result['APPLIED_DISCOUNT_IDS'] = array_keys($discountList['FULL_DISCOUNT_LIST']);
	}

	protected function addProductToBasket($fields)
	{
		$basket = $this->getBasketStorage()->getBasket();
		$context = ['SITE_ID' => $this->getSiteId()];

		return Catalog\Product\Basket::addProductToBasketWithPermissions($basket, $fields, $context, false);
	}

	protected function getUserId(): ?int
	{
		global $USER;

		return
			isset($USER) && $USER instanceof \CUser
				? (int)$USER->GetID()
				: null
		;
	}

	protected function needToReloadGifts(array $result)
	{
		$collections = [];

		if ($this->arParams['USE_GIFTS'] === 'Y')
		{
			[$found, $coupon] = $this->getCouponFromRequest($this->request->toArray());

			if ($found && !empty($coupon) && $result['VALID_COUPON'] === true)
			{
				if (!empty($result['BASKET_DATA']['FULL_DISCOUNT_LIST']))
				{
					$giftManager = Sale\Discount\Gift\Manager::getInstance()->setUserId($this->getUserId() ?? 0);

					Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
					$collections = $giftManager->getCollectionsByBasket(
						$this->getBasketStorage()->getBasket(),
						$result['BASKET_DATA']['FULL_DISCOUNT_LIST'],
						$result['BASKET_DATA']['APPLIED_DISCOUNT_LIST']
					);
					Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
				}
			}
		}

		return !empty($collections);
	}

	public function executeComponent()
	{
		if ($this->includeModules())
		{
			DiscountCouponsManager::init();
			$this->setFrameMode(false);

			$this->action = $this->prepareAction();
			Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
			$this->doAction($this->action);
			Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
		}
	}

	protected function getIblockPropertyCodes()
	{
		$propertyCodes = [];
		$this->arCustomSelectFields = [];

		if (!empty($this->columns) && is_array($this->columns))
		{
			foreach ($this->columns as $value)
			{
				if (strncmp($value, 'PROPERTY_', 9) === 0)
				{
					$propCode = mb_strtoupper(mb_substr($value, 9));

					if ($propCode == '')
					{
						continue;
					}

					// array of iblock properties to select
					$this->arCustomSelectFields[] = $value;
					$propertyCodes[] = $propCode;
				}
			}
		}

		return $propertyCodes;
	}

	protected function initializeIblockProperties()
	{
		$propertyCodes = $this->getIblockPropertyCodes();

		if (self::includeIblock() && self::includeCatalog() && !empty($propertyCodes))
		{
			$iblockList = [];
			$catalogIterator = Bitrix\Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SITE_ID' => 'IBLOCK_SITE.SITE_ID'],
				'filter' => ['SITE_ID' => $this->getSiteId()],
				'runtime' => [
					'IBLOCK_SITE' => [
						'data_type' => 'Bitrix\Iblock\IblockSiteTable',
						'reference' => [
							'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
						],
						'join_type' => 'inner',
					],
				],
			]);
			while ($catalog = $catalogIterator->fetch())
			{
				$iblockList[] = $catalog['IBLOCK_ID'];

				if ((int)$catalog['PRODUCT_IBLOCK_ID'] > 0)
				{
					$iblockList[] = $catalog['PRODUCT_IBLOCK_ID'];
				}
			}

			if (!empty($iblockList))
			{
				$propertyIterator = Bitrix\Iblock\PropertyTable::getList([
					'select' => [
						'ID', 'IBLOCK_ID', 'NAME', 'ACTIVE', 'SORT', 'CODE', 'TIMESTAMP_X',
						'DEFAULT_VALUE', 'PROPERTY_TYPE', 'ROW_COUNT', 'COL_COUNT', 'LIST_TYPE',
						'MULTIPLE', 'XML_ID', 'FILE_TYPE', 'MULTIPLE_CNT', 'LINK_IBLOCK_ID', 'WITH_DESCRIPTION',
						'SEARCHABLE', 'FILTRABLE', 'IS_REQUIRED', 'VERSION', 'USER_TYPE', 'USER_TYPE_SETTINGS', 'HINT',
					],
					'filter' => [
						'@IBLOCK_ID' => $iblockList,
						'=ACTIVE' => 'Y',
						'@CODE' => $propertyCodes,
					],
					'order' => [
						'SORT' => 'ASC',
						'ID' => 'ASC',
					],
				]);
				while ($property = $propertyIterator->fetch())
				{
					$this->arIblockProps[$property['IBLOCK_ID']][$property['CODE']] = $property;

					if (!isset($this->storage['PROPERTY_CODES']))
					{
						$this->storage['PROPERTY_CODES'] = [];
					}

					// don't override previous property (compatibility)
					if (!isset($this->storage['PROPERTY_CODES'][$property['CODE']]))
					{
						$this->storage['PROPERTY_CODES'][$property['CODE']] = $property;
					}
				}
			}
		}
	}

	// legacy method
	public function getCustomColumns()
	{
		$result = [];

		// making grid headers array
		if (!empty($this->columns) && is_array($this->columns))
		{
			foreach ($this->columns as $value)
			{
				$name = '';

				if (strncmp($value, 'PROPERTY_', 9) === 0)
				{
					$propCode = mb_substr($value, 9);

					if ($propCode == '')
						continue;

					// array of iblock properties to select
					$this->arCustomSelectFields[] = $value;
					$id = $value.'_VALUE';
					$name = $value;

					if (isset($this->storage['PROPERTY_CODES'][$propCode]))
					{
						$name = $this->storage['PROPERTY_CODES'][$propCode]['NAME'];
					}
				}
				else
				{
					$id = $value;
				}

				$result[] = [
					'id' => $id,
					'name' => $name,
				];
			}
		}

		return $result;
	}

	protected static function getWarningsFromSession()
	{
		$warnings = [];

		if (!empty($_SESSION['SALE_BASKET_MESSAGE']) && is_array($_SESSION['SALE_BASKET_MESSAGE']))
		{
			$warnings = $_SESSION['SALE_BASKET_MESSAGE'];
			unset($_SESSION['SALE_BASKET_MESSAGE']);
		}

		return $warnings;
	}

	protected function initializeBasketOrderIfNotExists(Sale\Basket $basket)
	{
		if (!$basket->getOrder())
		{
			$userId = $this->getUserId() ?? 0;

			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$order = $orderClass::create($this->getSiteId(), $userId);

			$result = $order->appendBasket($basket);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());
			}

			$discounts = $order->getDiscount();
			$showPrices = $discounts->getShowPrices();
			if (!empty($showPrices['BASKET']))
			{
				foreach ($showPrices['BASKET'] as $basketCode => $data)
				{
					$basketItem = $basket->getItemByBasketCode($basketCode);
					if ($basketItem instanceof Sale\BasketItemBase)
					{
						$basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
						$basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
						$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
					}
				}
			}
		}
	}

	protected function getFuserId()
	{
		if ($this->fUserId === null)
		{
			$this->fUserId = Fuser::getId();
		}

		return $this->fUserId;
	}

	protected function getBasketStorage()
	{
		if (!isset($this->basketStorage))
		{
			$this->basketStorage = Sale\Basket\Storage::getInstance($this->getFuserId(), $this->getSiteId());
		}

		return $this->basketStorage;
	}

	protected function refreshAndCorrectRatio()
	{
		$basketRefreshed = false;
		$changedItems = [];

		$basket = $this->getBasketStorage()->getBasket();

		$actualQuantityList = $this->getActualQuantityList($basket);

		if ($this->needBasketRefresh())
		{
			$refreshResult = $this->refreshBasket($basket);
			if ($refreshResult->isSuccess())
			{
				$items = $refreshResult->get('CHANGED_BASKET_ITEMS');
				if (!empty($items))
				{
					$changedItems = array_merge($changedItems, $items);
				}
			}

			$basketRefreshed = true;
		}

		if ($this->arParams['CORRECT_RATIO'] === 'Y')
		{
			$ratioResult = Sale\BasketComponentHelper::correctQuantityRatio($basket);

			$items = $ratioResult->get('CHANGED_BASKET_ITEMS');
			if (!empty($items))
			{
				$changedItems = array_merge($changedItems, $items);
			}
		}

		$this->checkQuantityList($basket, $actualQuantityList);

		return [$basketRefreshed, $changedItems];
	}

	protected function refreshBasket(Sale\Basket $basket)
	{
		$refreshStrategy = Basket\RefreshFactory::create(Basket\RefreshFactory::TYPE_FULL);

		$result = $basket->refresh($refreshStrategy);
		if (!$result->isSuccess())
		{
			$this->errorCollection->add($result->getErrors());
		}

		return $result;
	}

	protected function getActualQuantityList(Sale\Basket $basket)
	{
		$quantityList = [];

		if (!$basket->isEmpty())
		{
			/** @var Sale\BasketItemBase $basketItem */
			foreach ($basket as $basketItem)
			{
				if ($basketItem->canBuy() && !$basketItem->isDelay())
				{
					$quantityList[$basketItem->getBasketCode()] = $basketItem->getQuantity();
				}
			}
		}

		return $quantityList;
	}

	protected function checkQuantityList($basket, $compareList)
	{
		$actualQuantityList = $this->getActualQuantityList($basket);

		foreach ($actualQuantityList as $basketCode => $itemQuantity)
		{
			if (!isset($compareList[$basketCode]) || $itemQuantity != $compareList[$basketCode])
			{
				$this->errorCollection->setError(new Error(Loc::getMessage('SBB_PRODUCT_QUANTITY_CHANGED'), $basketCode));
			}
		}
	}

	protected function loadBasketItems($itemsToLoad = null)
	{
		if ($this->isFastLoadRequest())
		{
			$this->basketItems = $this->getBasketItemsRawArray();
		}
		else
		{
			$this->basketItems = $this->getBasketItemsArray($itemsToLoad);
		}

		if (!empty($this->basketItems))
		{
			$this->loadCatalogInfo();
			$this->loadIblockProperties();

			if (self::includeCatalog())
			{
				$this->basketItems = $this->getSkuPropsData($this->basketItems, $this->storage['PARENTS'], $this->offersProps);
			}
		}
	}

	// ToDo get gifts result via ajax to prevent BasketStorage loading while using fast load
	protected function isFastLoadRequest()
	{
		return $this->action === self::INITIAL_LOAD_ACTION
			&& $this->arParams['DEFERRED_REFRESH'] === 'Y'
			&& $this->arParams['USE_GIFTS'] !== 'Y';
	}

	protected function needBasketRefresh()
	{
		if ($this->arParams['DEFERRED_REFRESH'] === 'Y')
		{
			$refresh = $this->request->isAjaxRequest() && $this->action === 'refreshAjax';
		}
		else
		{
			$refresh = !$this->isBasketIntegrated() || $this->arParams['BASKET_HAS_BEEN_REFRESHED'] !== 'Y';
		}

		return $refresh;
	}

	protected function isBasketIntegrated()
	{
		return $this->arParams['BASKET_WITH_ORDER_INTEGRATION'] === 'Y';
	}

	protected function basketHasItemsToUpdate()
	{
		$hasItemsToUpdate = true;

		$refreshGap = (int)Main\Config\Option::get('sale', 'basket_refresh_gap', 0);
		if ($refreshGap > 0)
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			$basketItem = $basketClass::getList([
				'filter' => [
					'FUSER_ID' => $this->getFuserId(),
					'=LID' => $this->getSiteId(),
					'ORDER_ID' => null,
					'<=DATE_REFRESH' => FormatDate('FULL', time() - $refreshGap, ''),
				],
				'select' => ['ID'],
				'limit' => 1,
			])->fetchAll()
			;

			$hasItemsToUpdate = !empty($basketItem);
		}

		return $hasItemsToUpdate;
	}

	// legacy method
	public function getBasketItems()
	{
		if (!$this->isFastLoadRequest())
		{
			$this->refreshAndCorrectRatio();
			$this->saveBasket();
		}

		$this->loadBasketItems();
		$result = $this->getBasketResult();

		if ($this->isCompatibleMode())
		{
			$this->sortItemsByTabs($result);
		}

		return $result;
	}

	protected function saveBasket()
	{
		$basket = $this->getBasketStorage()->getBasket();

		if ($basket->isChanged())
		{
			$res = $basket->save();
			if (!$res->isSuccess())
			{
				$this->errorCollection->add($res->getErrors());
			}
		}
	}

	protected function getBasketResult()
	{
		$result = [];

		$result['GRID']['HEADERS'] = $this->getGridColumns();

		if ($this->isCompatibleMode())
		{
			$result['GRID']['ROWS'] = $this->getGridRows();
		}

		if (!$this->isBasketIntegrated())
		{
			$result += $this->getBasketTotal();
			$result += $this->getCouponInfo();

			if ($this->usePrepayment === 'Y' && (float)$result['allSum'] > 0)
			{
				$result += $this->getPrepayment();
			}
		}

		$result += $this->getErrors();

		$result['BASKET_ITEMS_COUNT'] = $this->storage['BASKET_ITEMS_COUNT'];
		$result['ORDERABLE_BASKET_ITEMS_COUNT'] = $this->storage['ORDERABLE_BASKET_ITEMS_COUNT'];
		$result['NOT_AVAILABLE_BASKET_ITEMS_COUNT'] = $this->storage['NOT_AVAILABLE_BASKET_ITEMS_COUNT'];
		$result['DELAYED_BASKET_ITEMS_COUNT'] = $this->storage['DELAYED_BASKET_ITEMS_COUNT'];

		$result['BASKET_ITEM_MAX_COUNT_EXCEEDED'] = $this->basketItemsMaxCountExceeded();
		$result['EVENT_ONCHANGE_ON_START'] = $this->isNeedBasketUpdateEvent();
		$result['CURRENCIES'] = $this->getFormatCurrencies();

		$this->applyTemplateMutator($result);

		return $result;
	}

	protected function basketItemsMaxCountExceeded()
	{
		$exceeded = false;

		if ($this->isBasketIntegrated())
		{
			$exceeded = (int)$this->storage['BASKET_ITEMS_COUNT'] > (int)$this->arParams['BASKET_MAX_COUNT_TO_SHOW'];
		}

		return $exceeded;
	}

	protected function getBasketItemsRawArray()
	{
		$basketItems = [];

		$orderableItemsCount = 0;
		$notAvailableItemsCount = 0;
		$delayedItemsCount = 0;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basketItemsResult = $basketClass::getList([
			'filter' => [
				'FUSER_ID' => $this->getFuserId(),
				'=LID' => $this->getSiteId(),
				'ORDER_ID' => null,
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		]);
		while ($basketItem = $basketItemsResult->fetch())
		{
			$basketItem['PROPS'] = [];
			$basketItem['QUANTITY'] = (float)$basketItem['QUANTITY'];

			$basketItem['WEIGHT'] = (float)$basketItem['WEIGHT'];
			$basketItem['WEIGHT_FORMATED'] = roundEx($basketItem['WEIGHT'] / $this->weightKoef, SALE_WEIGHT_PRECISION).' '.$this->weightUnit;

			$basketItem['PRICE'] = PriceMaths::roundPrecision((float)$basketItem['PRICE']);
			$basketItem['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['PRICE'], $basketItem['CURRENCY'], true);

			$basketItem['FULL_PRICE'] = PriceMaths::roundPrecision((float)$basketItem['BASE_PRICE']);
			$basketItem['FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['FULL_PRICE'], $basketItem['CURRENCY'], true);

			$basketItem['DISCOUNT_PRICE'] = PriceMaths::roundPrecision((float)$basketItem['DISCOUNT_PRICE']);
			$basketItem['DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['DISCOUNT_PRICE'], $basketItem['CURRENCY'], true);

			$basketItem['SUM_VALUE'] = $basketItem['PRICE'] * $basketItem['QUANTITY'];
			$basketItem['SUM'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_VALUE'], $basketItem['CURRENCY'], true);

			$basketItem['SUM_FULL_PRICE'] = $basketItem['FULL_PRICE'] * $basketItem['QUANTITY'];
			$basketItem['SUM_FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_FULL_PRICE'], $basketItem['CURRENCY'], true);

			$basketItem['SUM_DISCOUNT_PRICE'] = $basketItem['DISCOUNT_PRICE'] * $basketItem['QUANTITY'];
			$basketItem['SUM_DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_DISCOUNT_PRICE'], $basketItem['CURRENCY'], true);

			$basketItem['VAT_RATE'] = (float)$basketItem['VAT_RATE'];
			$basketItem['PRICE_VAT_VALUE'] = $basketItem['VAT_VALUE']
				= ($basketItem['PRICE'] * $basketItem['QUANTITY'] / ($basketItem['VAT_RATE'] + 1)) * $basketItem['VAT_RATE'] / $basketItem['QUANTITY'];

			$basketItem['DISCOUNT_PRICE_PERCENT'] = 0;
			if ($basketItem['CUSTOM_PRICE'] !== 'Y' && $basketItem['FULL_PRICE'] > 0 && $basketItem['DISCOUNT_PRICE'] > 0)
			{
				$basketItem['DISCOUNT_PRICE_PERCENT'] = Sale\Discount::calculateDiscountPercent(
					$basketItem['FULL_PRICE'],
					$basketItem['DISCOUNT_PRICE']
				);
				if ($basketItem['DISCOUNT_PRICE_PERCENT'] === null)
					$basketItem['DISCOUNT_PRICE_PERCENT'] = 0;
			}

			$basketItem['DISCOUNT_PRICE_PERCENT_FORMATED'] = Sale\BasketItem::formatQuantity($basketItem['DISCOUNT_PRICE_PERCENT']).'%';

			if ($basketItem['DELAY'] === 'Y')
			{
				$delayedItemsCount++;
			}

			if ($basketItem['CAN_BUY'] !== 'Y' && $basketItem['DELAY'] !== 'Y')
			{
				$basketItem['NOT_AVAILABLE'] = true;
				$notAvailableItemsCount++;
			}
			elseif ($basketItem['DELAY'] !== 'Y')
			{
				$orderableItemsCount++;
			}

			$basketItems[$basketItem['ID']] = $basketItem;
		}

		$this->storage['BASKET_ITEMS_COUNT'] = count($basketItems);
		$this->storage['ORDERABLE_BASKET_ITEMS_COUNT'] = $orderableItemsCount;
		$this->storage['NOT_AVAILABLE_BASKET_ITEMS_COUNT'] = $notAvailableItemsCount;
		$this->storage['DELAYED_BASKET_ITEMS_COUNT'] = $delayedItemsCount;

		if ($this->basketItemsMaxCountExceeded())
		{
			return [];
		}

		$propertyResult = Sale\BasketPropertiesCollection::getList(
			[
				'filter' => [
					'=BASKET_ID' => array_keys($basketItems),
					['!CODE' => 'CATALOG.XML_ID'],
					['!CODE' => 'PRODUCT.XML_ID'],
				],
				'order' => [
					'ID' => 'ASC',
					'SORT' => 'ASC',
				],
			]
		);
		while ($property = $propertyResult->fetch())
		{
			$this->makeCompatibleArray($property);
			$basketItems[$property['BASKET_ID']]['PROPS'][] = $property;
		}

		foreach ($basketItems as &$basketItem)
		{
			$basketItem['HASH'] = $this->getBasketItemHash($basketItem);
		}

		return $basketItems;
	}

	protected function getBasketItemsArray($filterItems = null)
	{
		$basketItems = [];

		$notAvailableItemsCount = 0;
		$delayedItemsCount = 0;

		$basketStorage = $this->getBasketStorage();
		$fullBasket = $basketStorage->getBasket();

		if ($this->basketItemsMaxCountExceeded())
		{
			return [];
		}

		$useFilter = is_array($filterItems);
		if ($useFilter)
		{
			$filterItems = array_fill_keys($filterItems, true);
		}

		$this->storage['ORDERABLE_BASKET_ITEMS_COUNT'] = 0;
		if (!$fullBasket->isEmpty())
		{
			$orderableBasket = $basketStorage->getOrderableBasket();
			// in SOA case we already have real order
			$this->initializeBasketOrderIfNotExists($orderableBasket);

			$this->storage['ORDERABLE_BASKET_ITEMS_COUNT'] = $orderableBasket->count();

			/** @var Sale\BasketItem $item */
			foreach ($fullBasket as $item)
			{
				if ($item->isDelay())
				{
					$delayedItemsCount++;
				}

				if (!$item->canBuy() && !$item->isDelay())
				{
					$notAvailableItemsCount++;
				}

				if ($useFilter && !isset($filterItems[$item->getId()]))
				{
					continue;
				}

				// these items need to process on a basket with order for possible discounts
				if ($item->canBuy() && !$item->isDelay())
				{
					$item = $orderableBasket->getItemByBasketCode($item->getBasketCode());
				}

				$basketItems[$item->getId()] = $this->processBasketItem($item);
			}
		}

		$this->storage['BASKET_ITEMS_COUNT'] = $fullBasket->count();
		$this->storage['NOT_AVAILABLE_BASKET_ITEMS_COUNT'] = $notAvailableItemsCount;
		$this->storage['DELAYED_BASKET_ITEMS_COUNT'] = $delayedItemsCount;

		return $basketItems;
	}

	protected function processBasketItem(Sale\BasketItem $item)
	{
		$basketItem = $item->getFieldValues();

		if ($this->isCompatibleMode())
		{
			$this->makeCompatibleArray($basketItem);
		}

		$basketItem['PROPS'] = $this->getBasketItemProperties($item);
		$basketItem['PROPS_ALL'] = $item->getPropertyCollection()->getPropertyValues();
		$basketItem['QUANTITY'] = $item->getQuantity();

		$basketItem['WEIGHT'] = (float)$basketItem['WEIGHT'];
		$basketItem['WEIGHT_FORMATED'] = roundEx($basketItem['WEIGHT'] / $this->weightKoef, SALE_WEIGHT_PRECISION).' '.$this->weightUnit;

		$basketItem['PRICE'] = PriceMaths::roundPrecision($basketItem['PRICE']);
		$basketItem['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['PRICE'], $basketItem['CURRENCY'], true);

		$basketItem['FULL_PRICE'] = PriceMaths::roundPrecision($basketItem['BASE_PRICE']);
		$basketItem['FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['FULL_PRICE'], $basketItem['CURRENCY'], true);

		$basketItem['DISCOUNT_PRICE'] = PriceMaths::roundPrecision($basketItem['DISCOUNT_PRICE']);
		$basketItem['DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['DISCOUNT_PRICE'], $basketItem['CURRENCY'], true);

		$basketItem['SUM_VALUE'] = $basketItem['PRICE'] * $basketItem['QUANTITY'];
		$basketItem['SUM'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_VALUE'], $basketItem['CURRENCY'], true);

		$basketItem['SUM_FULL_PRICE'] = $basketItem['FULL_PRICE'] * $basketItem['QUANTITY'];
		$basketItem['SUM_FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_FULL_PRICE'], $basketItem['CURRENCY'], true);

		$basketItem['SUM_DISCOUNT_PRICE'] = $basketItem['DISCOUNT_PRICE'] * $basketItem['QUANTITY'];
		$basketItem['SUM_DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($basketItem['SUM_DISCOUNT_PRICE'], $basketItem['CURRENCY'], true);

		$basketItem['PRICE_VAT_VALUE'] = $basketItem['VAT_VALUE']
			= ($basketItem['PRICE'] * $basketItem['QUANTITY'] / ($basketItem['VAT_RATE'] + 1)) * $basketItem['VAT_RATE'] / $basketItem['QUANTITY'];

		$basketItem['DISCOUNT_PRICE_PERCENT'] = 0;
		if ($basketItem['CUSTOM_PRICE'] !== 'Y')
		{
			$basketItem['DISCOUNT_PRICE_PERCENT'] = Sale\Discount::calculateDiscountPercent(
				$basketItem['FULL_PRICE'],
				$basketItem['DISCOUNT_PRICE']
			);
			if ($basketItem['DISCOUNT_PRICE_PERCENT'] === null)
				$basketItem['DISCOUNT_PRICE_PERCENT'] = 0;
		}
		$basketItem['DISCOUNT_PRICE_PERCENT_FORMATED'] = $basketItem['DISCOUNT_PRICE_PERCENT'].'%';

		if ($basketItem['CAN_BUY'] !== 'Y' && $basketItem['DELAY'] !== 'Y')
		{
			$basketItem['NOT_AVAILABLE'] = true;
		}

		$basketItem['HASH'] = $this->getBasketItemHash($basketItem);

		return $basketItem;
	}

	protected function getBasketItemHash($basketItem)
	{
		$basketItemProps = [];

		foreach ($basketItem['PROPS'] as $property)
		{
			$basketItemProps[] = [$property['CODE'], $property['VALUE']];
		}

		return md5($basketItem['PRODUCT_ID'].serialize($basketItemProps));
	}

	protected function getBasketItemProperties(Sale\BasketItem $basketItem)
	{
		$properties = [];
		/** @var Sale\BasketPropertiesCollection $propertyCollection */
		$propertyCollection = $basketItem->getPropertyCollection();
		$basketId = $basketItem->getBasketCode();

		foreach ($propertyCollection->getPropertyValues() as $property)
		{
			if ($property['CODE'] == 'CATALOG.XML_ID' || $property['CODE'] == 'PRODUCT.XML_ID' || $property['CODE'] == 'SUM_OF_CHARGE')
				continue;

			$property = array_filter($property, ['CSaleBasketHelper', 'filterFields']);
			$property['BASKET_ID'] = $basketId;
			$this->makeCompatibleArray($property);

			$properties[] = $property;
		}

		return $properties;
	}

	protected function loadCatalogInfo()
	{
		$this->basketItems = getMeasures($this->basketItems);
		$this->basketItems = getRatio($this->basketItems);
		$this->basketItems = $this->getAvailableQuantity($this->basketItems);
	}

	protected function loadOfferToProductRelations()
	{
		$this->storage['ELEMENT_IDS'] = $this->getBasketProductIds();

		if (!empty($this->storage['ELEMENT_IDS']) && self::includeCatalog())
		{
			$this->storage['SKU_TO_PARENT'] = [];
			$this->storage['PARENTS'] = [];

			$productList = CCatalogSku::getProductList($this->storage['ELEMENT_IDS']);
			if (!empty($productList))
			{
				foreach ($productList as $offerId => $offerInfo)
				{
					$offerInfo['PRODUCT_ID'] = $offerInfo['ID'];
					$this->storage['ELEMENT_IDS'][] = $offerInfo['ID'];
					$this->storage['SKU_TO_PARENT'][$offerId] = $offerInfo['ID'];
					$this->storage['PARENTS'][$offerId] = $offerInfo;
				}

				unset($offerInfo, $offerId);
			}

			unset($productList);
			$this->storage['ELEMENT_IDS'] = array_values(array_unique($this->storage['ELEMENT_IDS']));
		}
	}

	protected function getBasketProductIds()
	{
		$ids = [];

		foreach ($this->basketItems as $basketItem)
		{
			$ids[] = $basketItem['PRODUCT_ID'];
		}

		return $ids;
	}

	protected function loadIblockProperties()
	{
		$this->initializeIblockProperties();
		$this->loadOfferToProductRelations();
		$this->fillItemsWithProperties();
	}

	protected function modifyLabels(&$product, $productProperties)
	{
		$product['PROPERTIES'] = $productProperties;

		\CIBlockPriceTools::getLabel($product, $this->arParams['LABEL_PROP']);
		$item['LABEL_PROP_MOBILE'] = $this->arParams['LABEL_PROP_MOBILE'];

		unset($product['PROPERTIES']);
	}

	protected function fillItemsWithProperties()
	{
		$productIndexMap = [];
		$iblockToProductMap = [];
		$productsData = [];

		$elementIterator = CIBlockElement::GetList(
			[],
			['=ID' => $this->storage['ELEMENT_IDS']],
			false,
			false,
			[
				'ID',
				'IBLOCK_ID',
				'PREVIEW_PICTURE',
				'DETAIL_PICTURE',
				'PREVIEW_TEXT',
				'DETAIL_PAGE_URL',
			]
		);
		if ($this->arParams['DETAIL_URL'] !== '')
		{
			$elementIterator->SetUrlTemplates($this->arParams['DETAIL_URL']);
		}
		while ($product = $elementIterator->GetNext(true, false))
		{
			$productIndexMap[$product['ID']] = [];
			$iblockToProductMap[$product['IBLOCK_ID']][] = $product['ID'];
			$productsData[$product['ID']] = $product;
		}

		foreach ($iblockToProductMap as $iblockId => $productIds)
		{
			$codes = [];

			if (!empty($this->arIblockProps[$iblockId]))
			{
				$codes = array_keys($this->arIblockProps[$iblockId]);
			}

			$imageCode = $this->arParams['ADDITIONAL_PICT_PROP'][$iblockId] ?? '';
			if (!empty($imageCode) && !in_array($imageCode, $codes))
			{
				$codes[] = $imageCode;
			}

			if (!empty($this->arParams['LABEL_PROP']))
			{
				$codes = array_merge($codes, $this->arParams['LABEL_PROP']);
			}

			if (!empty($codes))
			{
				CIBlockElement::GetPropertyValuesArray(
					$productIndexMap, $iblockId,
					['ID' => $productIds],
					['CODE' => $codes]
				);
			}
		}

		unset($iblockToProductMap);

		// getting compatible iblock properties and additional images arrays
		$additionalImages = [];
		foreach ($productIndexMap as $productId => $productProperties)
		{
			if (!empty($productProperties) && is_array($productProperties))
			{
				$productIblockId = $productsData[$productId]['IBLOCK_ID'];
				$additionalImage = $this->getAdditionalImageForProduct($productIblockId, $productProperties);
				if ((int)$additionalImage > 0)
				{
					$additionalImages[$productId] = $additionalImage;
				}

				foreach ($productProperties as $code => $property)
				{
					if (
						!empty($this->arIblockProps[$productIblockId])
						&& isset($this->arIblockProps[$productIblockId][$code])
					)
					{
						$temporary = [];

						if ($property['PROPERTY_TYPE'] === 'S' && $property['USER_TYPE'] === 'HTML')
						{
							$temporary['PROPERTY_'.$code.'_VALUE'] = '';

							if (!empty($property['~VALUE']))
							{
								if ($property['MULTIPLE'] === 'N')
								{
									$property['~VALUE'] = [$property['~VALUE']];
								}

								foreach ($property['~VALUE'] as $value)
								{
									if (!empty($temporary['PROPERTY_'.$code.'_VALUE']))
									{
										$temporary['PROPERTY_'.$code.'_VALUE'] .= ', ';
									}

									$temporary['PROPERTY_'.$code.'_VALUE'] .= ($value['TYPE'] === 'HTML'
										? $value['TEXT']
										: htmlspecialcharsbx($value['TEXT'])
									);
								}
							}

							$temporary['PROPERTY_'.$code.'_VALUE_HTML'] = true;
						}
						else
						{
							if (!empty($property['~VALUE']) && is_array($property['~VALUE']))
							{
								$temporary['PROPERTY_'.$code.'_VALUE'] = implode(', ', $property['~VALUE']);
							}
							else
							{
								$temporary['PROPERTY_'.$code.'_VALUE'] = $property['~VALUE'];
							}
						}

						if (!empty($property['PROPERTY_VALUE_ID']) && is_array($property['PROPERTY_VALUE_ID']))
						{
							$temporary['PROPERTY_'.$code.'_VALUE_ID'] = implode(', ', $property['PROPERTY_VALUE_ID']);
						}
						else
						{
							$temporary['PROPERTY_'.$code.'_VALUE_ID'] = $property['PROPERTY_VALUE_ID'];
						}

						if ($property['PROPERTY_TYPE'] === 'L')
						{
							$temporary['PROPERTY_'.$code.'_ENUM_ID'] = $property['VALUE_ENUM_ID'];
						}

						if ($this->isCompatibleMode())
						{
							$this->makeCompatibleArray($temporary);
						}

						$productsData[$productId] += $temporary;
					}
				}

				if (!empty($this->arParams['LABEL_PROP']))
				{
					$this->modifyLabels($productsData[$productId], $productProperties);
				}
			}
		}

		unset($productIndexMap);

		foreach ($this->basketItems as &$item)
		{
			$productId = $item['PRODUCT_ID'];

			if (!empty($productsData[$productId]) && is_array($productsData[$productId]))
			{
				foreach ($productsData[$productId] as $code => $value)
				{
					if ($value === null)
						continue;

					if (
						$code === 'PREVIEW_PICTURE'
						|| $code === 'DETAIL_PICTURE'
						|| $code === 'DETAIL_PAGE_URL'
						|| mb_strpos($code, 'PROPERTY_') !== false
					)
					{
						$item[$code] = $value;
					}
				}
			}

			// if sku element doesn't have value of some property - we'll show parent element value instead
			$parentId = $this->storage['SKU_TO_PARENT'][$productId] ?? 0;
			if ((int)$parentId > 0)
			{
				$parentDetailUrl = $productsData[$parentId]['DETAIL_PAGE_URL'] ?? '';
				if ($parentDetailUrl !== '')
				{
					$item['DETAIL_PAGE_URL'] = $parentDetailUrl;
				}

				foreach ($this->arCustomSelectFields as $field)
				{
					$fieldVal = (mb_substr($field, -6) === '_VALUE' ? $field : $field.'_VALUE');

					// can be array or string
					if (
						(!isset($item[$fieldVal]) || empty($item[$fieldVal]))
						&& (isset($productsData[$parentId][$fieldVal]) && !empty($productsData[$parentId][$fieldVal]))
					)
					{
						$item[$fieldVal] = $productsData[$parentId][$fieldVal];
						$item['~' . $fieldVal] = $productsData[$parentId]['~' . $fieldVal] ?? $item[$fieldVal];
					}
				}
			}

			if (!empty($productsData[$productId]['PREVIEW_TEXT']))
			{
				$item['PREVIEW_TEXT'] = $productsData[$productId]['PREVIEW_TEXT'];
				$item['PREVIEW_TEXT_TYPE'] = $productsData[$productId]['PREVIEW_TEXT_TYPE'];
			}
			elseif (!empty($productsData[$parentId]['PREVIEW_TEXT']))
			{
				$item['PREVIEW_TEXT'] = $productsData[$parentId]['PREVIEW_TEXT'];
				$item['PREVIEW_TEXT_TYPE'] = $productsData[$parentId]['PREVIEW_TEXT_TYPE'];
			}

			if (!empty($productsData[$productId]['PREVIEW_PICTURE']))
			{
				$item['PREVIEW_PICTURE'] = $productsData[$productId]['PREVIEW_PICTURE'];
			}
			elseif (!empty($productsData[$parentId]['PREVIEW_PICTURE']))
			{
				$item['PREVIEW_PICTURE'] = $productsData[$parentId]['PREVIEW_PICTURE'];
			}

			if (!empty($productsData[$productId]['DETAIL_PICTURE']))
			{
				$item['DETAIL_PICTURE'] = $productsData[$productId]['DETAIL_PICTURE'];
			}
			elseif (!empty($productsData[$parentId]['DETAIL_PICTURE']))
			{
				$item['DETAIL_PICTURE'] = $productsData[$parentId]['DETAIL_PICTURE'];
			}

			if (!empty($productsData[$productId]['LABEL_ARRAY_VALUE']))
			{
				$item['LABEL_ARRAY_VALUE'] = $productsData[$productId]['LABEL_ARRAY_VALUE'];
			}
			elseif (!empty($productsData[$parentId]['LABEL_ARRAY_VALUE']))
			{
				$item['LABEL_ARRAY_VALUE'] = $productsData[$parentId]['LABEL_ARRAY_VALUE'];
			}

			// format property values
			foreach ($item as $key => $value)
			{
				if ((mb_strpos($key, 'PROPERTY_', 0) === 0) && (mb_strrpos($key, '_VALUE') == mb_strlen($key) - 6))
				{
					$iblockId = $productsData[$productId]['IBLOCK_ID'];
					$code = mb_strtoupper(str_replace(['PROPERTY_', '_VALUE'], '', $key));

					$propData = $this->arIblockProps[$iblockId][$code]
						??
						$this->arIblockProps[$this->storage['PARENTS'][$productId]['IBLOCK_ID']][$code];

					$prepared = false;

					if ($propData['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_FILE)
					{
						$this->makeFileSources($item, $propData);
					}

					// display linked property type
					if ($propData['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_ELEMENT)
					{
						$this->makeLinkedProperty($item, $propData);
						$prepared = true;
					}

					if ($propData['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_STRING && $propData['USER_TYPE'] === 'directory')
					{
						$this->makeDirectoryProperty($item, $propData);
						$prepared = true;
					}

					if (!$prepared)
					{
						$item[$key] = CSaleHelper::getIblockPropInfo(
							$value,
							$propData,
							['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD]
						);
					}
				}
			}

			// image replace priority (if has SKU):
			// 1. offer 'PREVIEW_PICTURE' or 'DETAIL_PICTURE'
			// 2. offer additional picture from parameters
			// 3. parent product 'PREVIEW_PICTURE' or 'DETAIL_PICTURE'
			// 4. parent product additional picture from parameters
			if (
				empty($productsData[$productId]['PREVIEW_PICTURE'])
				&& empty($productsData[$productId]['DETAIL_PICTURE'])
				&& isset($additionalImages[$productId])
			)
			{
				$item['PREVIEW_PICTURE'] = $additionalImages[$productId];
			}
			elseif (
				empty($item['PREVIEW_PICTURE'])
				&& empty($item['DETAIL_PICTURE'])
				&& isset($additionalImages[$parentId])
			)
			{
				$item['PREVIEW_PICTURE'] = $additionalImages[$parentId];
			}

			$item['PREVIEW_PICTURE_SRC'] = '';
			if (!empty($item['PREVIEW_PICTURE']))
			{
				$image = CFile::GetFileArray($item['PREVIEW_PICTURE']);
				if ($image)
				{
					self::resizeImage($item, 'PREVIEW_PICTURE', $image,
						['width' => self::IMAGE_SIZE_ADAPTIVE, 'height' => self::IMAGE_SIZE_ADAPTIVE],
						['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD],
						$this->arParams['BASKET_IMAGES_SCALING']
					);
				}
			}

			$item['DETAIL_PICTURE_SRC'] = '';
			if (!empty($item['DETAIL_PICTURE']))
			{
				$image = CFile::GetFileArray($item['DETAIL_PICTURE']);
				if ($image)
				{
					self::resizeImage($item, 'DETAIL_PICTURE', $image,
						['width' => self::IMAGE_SIZE_ADAPTIVE, 'height' => self::IMAGE_SIZE_ADAPTIVE],
						['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD],
						$this->arParams['BASKET_IMAGES_SCALING']
					);
				}
			}
		}
		unset($item);
	}

	protected function getAdditionalImageForProduct($iblockId, $properties)
	{
		$imageId = 0;

		if (!empty($iblockId) && !empty($properties) && !empty($this->arParams['ADDITIONAL_PICT_PROP']))
		{
			if (isset($this->arParams['ADDITIONAL_PICT_PROP'][$iblockId]) && isset($properties[$this->arParams['ADDITIONAL_PICT_PROP'][$iblockId]]))
			{
				$property = $properties[$this->arParams['ADDITIONAL_PICT_PROP'][$iblockId]];
				$imageId = is_array($property['VALUE']) ? reset($property['VALUE']) : $property['VALUE'];
			}
		}

		return $imageId;
	}

	protected function makeFileSources(&$item, $property)
	{
		$propertySources = [];

		if (!empty($item['PROPERTY_'.$property['CODE'].'_VALUE']))
		{
			$value = explode(',', $item['PROPERTY_'.$property['CODE'].'_VALUE']);

			foreach ($value as $fileId)
			{
				$fileId = (int)trim((string)$fileId);
				if ($fileId > 0)
				{
					$fileSources = [];

					$image = CFile::GetFileArray($fileId);
					if ($image)
					{
						self::resizeImage($fileSources, 'IMAGE', $image,
							['width' => self::IMAGE_SIZE_ADAPTIVE, 'height' => self::IMAGE_SIZE_ADAPTIVE],
							['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD],
							$this->arParams['BASKET_IMAGES_SCALING']
						);
					}

					$propertySources[] = $fileSources;
				}
			}
		}

		$item['PROPERTY_'.$property['CODE'].'_VALUE_SRC'] = $propertySources;
	}

	protected function makeLinkedProperty(&$item, $property)
	{
		$propertySources = [];

		$valueKey = 'PROPERTY_'.$property['CODE'].'_VALUE';
		if (!empty($valueKey))
		{
			if ($property['MULTIPLE'] === 'Y')
			{
				$property['VALUE'] = explode(',', $item[$valueKey]);
			}
			$property['~VALUE'] ??= $property['VALUE'];

			$formattedProperty = CIBlockFormatProperties::GetDisplayValue($item, $property);
			if (!empty($formattedProperty['DISPLAY_VALUE']))
			{
				$propertySources =
					is_array($formattedProperty['DISPLAY_VALUE'])
						? $formattedProperty['DISPLAY_VALUE']
						: [$formattedProperty['DISPLAY_VALUE']]
				;
			}
		}

		$item[$valueKey . '_LINK'] = $propertySources;
		$item['~' . $valueKey . '_LINK'] = $propertySources;
	}

	protected function makeDirectoryProperty(&$item, $property)
	{
		$propertySources = [];

		$valueKey = 'PROPERTY_'.$property['CODE'].'_VALUE';
		if (!empty($item[$valueKey]))
		{
			if ($property['MULTIPLE'] === 'Y')
			{
				$property['VALUE'] = explode(', ', $item[$valueKey]);
			}
			else
			{
				$property['VALUE'] = $item[$valueKey];
			}

			$property['~VALUE'] ??= $property['VALUE'];

			if (CheckSerializedData($property['USER_TYPE_SETTINGS']))
			{
				$property['USER_TYPE_SETTINGS'] = unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
			}

			$formattedProperty = CIBlockFormatProperties::GetDisplayValue($item, $property);
			if (!empty($formattedProperty['DISPLAY_VALUE']))
			{
				$propertySources =
					is_array($formattedProperty['DISPLAY_VALUE'])
						? $formattedProperty['DISPLAY_VALUE']
						: [$formattedProperty['DISPLAY_VALUE']]
				;
			}
		}

		$item[$valueKey] = implode(', ', $propertySources);
		$item['~' . $valueKey] = $item[$valueKey];
	}

	/**
	 * Resize image depending on scale type
	 *
	 * @param array $item
	 * @param        $imageKey
	 * @param array $arImage
	 * @param array $sizeAdaptive
	 * @param array $sizeStandard
	 * @param string $scale
	 */
	public static function resizeImage(array &$item, $imageKey, array $arImage, array $sizeAdaptive, array $sizeStandard, $scale = '')
	{
		if ($scale == '')
		{
			$scale = 'adaptive';
		}

		if ($scale === 'no_scale')
		{
			$item[$imageKey.'_SRC'] = Iblock\Component\Tools::getImageSrc([
				'SRC' => $arImage['SRC']
			]);
			$item[$imageKey.'_SRC_ORIGINAL'] = $arImage['SRC'];
		}
		elseif ($scale === 'adaptive')
		{
			$arFileTmp = CFile::ResizeImageGet(
				$arImage,
				['width' => $sizeAdaptive['width'] / 2, 'height' => $sizeAdaptive['height'] / 2],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
			$item[$imageKey.'_SRC'] = Iblock\Component\Tools::getImageSrc([
				'SRC' => $arFileTmp['src']
			]);

			$arFileTmp = CFile::ResizeImageGet(
				$arImage,
				$sizeAdaptive,
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
			$item[$imageKey.'_SRC_2X'] = Iblock\Component\Tools::getImageSrc([
				'SRC' => $arFileTmp['src']
			]);

			$item[$imageKey.'_SRC_ORIGINAL'] = $arImage['SRC'];
		}
		else
		{
			$arFileTmp = CFile::ResizeImageGet($arImage, $sizeStandard, BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$item[$imageKey.'_SRC'] = Iblock\Component\Tools::getImageSrc([
				'SRC' => $arFileTmp['src']
			]);

			$item[$imageKey.'_SRC_ORIGINAL'] = $arImage['SRC'];
		}
	}

	protected function getErrors()
	{
		$result = [
			'EMPTY_BASKET' => false,
			'WARNING_MESSAGE' => [],
			'WARNING_MESSAGE_WITH_CODE' => [],
			'ERROR_MESSAGE' => '',
		];

		if (!$this->errorCollection->isEmpty())
		{
			/** @var Error $error */
			foreach ($this->errorCollection as $error)
			{
				$message = $error->getMessage();
				$code = $error->getCode();

				$result['WARNING_MESSAGE'][] = $message;

				if (empty($code))
				{
					$code = 'common';
				}

				if (!isset($result['WARNING_MESSAGE_WITH_CODE'][$code]))
				{
					$result['WARNING_MESSAGE_WITH_CODE'][$code] = [];
				}

				$result['WARNING_MESSAGE_WITH_CODE'][$code][] = $message;
			}
		}

		if (empty($this->basketItems) && !$this->isBasketIntegrated())
		{
			$result['ERROR_MESSAGE'] .= Loc::getMessage('SALE_EMPTY_BASKET');
			$result['EMPTY_BASKET'] = true;

			if (!empty($result['WARNING_MESSAGE']))
			{
				$result['ERROR_MESSAGE'] .= (trim((string)$result['ERROR_MESSAGE']) != '' ? '\n' : '').implode('\n', $result['WARNING_MESSAGE']);
			}
		}

		return $result;
	}

	// fill item arrays for old templates
	protected function sortItemsByTabs(&$result)
	{
		$result['ITEMS'] = [
			'AnDelCanBuy' => [],
			'DelDelCanBuy' => [],
			'nAnCanBuy' => [],
			'ProdSubscribe' => [],
		];

		if (!empty($this->basketItems))
		{
			foreach ($this->basketItems as $item)
			{
				if ($item['CAN_BUY'] === 'Y' && $item['DELAY'] !== 'Y')
				{
					$result['ITEMS']['AnDelCanBuy'][] = $item;
				}
				elseif ($item['CAN_BUY'] === 'Y' && $item['DELAY'] === 'Y')
				{
					$result['ITEMS']['DelDelCanBuy'][] = $item;
				}
				elseif ($item['CAN_BUY'] !== 'Y' && $item['SUBSCRIBE'] === 'Y')
				{
					$result['ITEMS']['ProdSubscribe'][] = $item;
				}
				else
				{
					$result['ITEMS']['nAnCanBuy'][] = $item;
				}
			}
		}

		$result['ShowReady'] = !empty($result['ITEMS']['AnDelCanBuy']) ? 'Y' : 'N';
		$result['ShowDelay'] = !empty($result['ITEMS']['DelDelCanBuy']) ? 'Y' : 'N';
		$result['ShowSubscribe'] = !empty($result['ITEMS']['ProdSubscribe']) ? 'Y' : 'N';
		$result['ShowNotAvail'] = !empty($result['ITEMS']['nAnCanBuy']) ? 'Y' : 'N';
	}

	protected function getGridColumns()
	{
		$headers = [];

		// making grid headers array
		if (!empty($this->columns) && is_array($this->columns))
		{
			foreach ($this->columns as $value)
			{
				$name = '';

				if (strncmp($value, 'PROPERTY_', 9) === 0)
				{
					$propCode = mb_substr($value, 9);

					if ($propCode == '')
						continue;

					$id = $value.'_VALUE';
					$name = $value;

					if (isset($this->storage['PROPERTY_CODES'][$propCode]))
					{
						$name = $this->storage['PROPERTY_CODES'][$propCode]['NAME'];
					}
				}
				else
				{
					$id = $value;
				}

				$headers[] = [
					'id' => $id,
					'name' => $name,
				];
			}
		}

		return $headers;
	}

	// fill grid data (for new templates with custom columns)
	protected function getGridRows()
	{
		$rows = [];

		if (!empty($this->basketItems))
		{
			foreach ($this->basketItems as $item)
			{
				$rows[$item['ID']] = $item;
			}
		}

		return $rows;
	}

	protected function isNeedBasketUpdateEvent()
	{
		$state = 'N';

		if ($this->isFastLoadRequest())
		{
			return $state;
		}

		$basket = $this->getBasketStorage()->getOrderableBasket();
		$fUserId = $this->getFuserId();
		$siteId = $this->getSiteId();

		$basketPrice = 0;
		/** @var Sale\BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->canBuy())
			{
				$basketPrice += $basketItem->getFinalPrice();
			}
		}

		if (
			!Sale\BasketComponentHelper::existsFUserBasketPrice($fUserId, $siteId)
			|| $this->getSessionFUserBasketPrice($fUserId) != $basketPrice
		)
		{
			$state = 'Y';
			$this->setSessionFUserBasketPrice($basketPrice, $fUserId);
		}

		$basketItemQuantity = 0;
		/** @var Sale\BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->canBuy())
			{
				$basketItemQuantity++;
			}
		}

		if (
			!Sale\BasketComponentHelper::existsFUserBasketQuantity($fUserId, $siteId)
			|| $this->getSessionFUserBasketQuantity($fUserId) != $basketItemQuantity
		)
		{
			$state = 'Y';
			$this->setSessionFUserBasketQuantity($basketItemQuantity, $fUserId);
		}

		unset($basket);

		return $state;
	}

	protected function getSessionFUserBasketPrice($fUserId)
	{
		return Sale\BasketComponentHelper::getFUserBasketPrice($fUserId, $this->getSiteId());
	}

	protected function setSessionFUserBasketPrice($price, $fUserId)
	{
		Sale\BasketComponentHelper::setFUserBasketPrice($fUserId, $price, $this->getSiteId());
	}

	protected function getSessionFUserBasketQuantity($fUserId)
	{
		return Sale\BasketComponentHelper::getFUserBasketQuantity($fUserId, $this->getSiteId());
	}

	protected function setSessionFUserBasketQuantity($quantity, $fUserId)
	{
		Sale\BasketComponentHelper::setFUserBasketQuantity($fUserId, $quantity, $this->getSiteId());
	}

	protected function getAffectedReformattedBasketItemsInDiscount(Sale\BasketBase $basket, array $discountData, array $calcResults)
	{
		$items = [];

		foreach ($calcResults['PRICES']['BASKET'] as $basketCode => $priceData)
		{
			if (empty($priceData['DISCOUNT']) || !empty($priceData['PRICE']) || empty($calcResults['RESULT']['BASKET'][$basketCode]))
			{
				continue;
			}

			//we have gift and PRICE equals 0.
			$found = false;

			foreach ($calcResults['RESULT']['BASKET'][$basketCode] as $data)
			{
				if ($data['DISCOUNT_ID'] == $discountData['ID'])
				{
					$found = true;
				}
			}
			unset($data);

			if (!$found)
			{
				continue;
			}

			$basketItem = $basket->getItemByBasketCode($basketCode);
			if (!$basketItem || $basketItem->getField('MODULE') != 'catalog')
			{
				continue;
			}

			$items[] = [
				'PRODUCT_ID' => $basketItem->getProductId(),
				'VALUE_PERCENT' => '100',
				'MODULE' => 'catalog',
			];
		}
		unset($priceData);

		return $items;
	}

	protected function getDiscountData(Sale\BasketBase $basket)
	{
		/** @var Sale\Order $order */
		$order = $basket->getOrder();
		$calcResults = $order->getDiscount()->getApplyResult(true);

		$appliedDiscounts = [];

		foreach ($calcResults['DISCOUNT_LIST'] as $discountData)
		{
			$discountId = $discountData['REAL_DISCOUNT_ID'];
			if (isset($calcResults['FULL_DISCOUNT_LIST'][$discountId]))
			{
				$appliedDiscounts[$discountId] = $calcResults['FULL_DISCOUNT_LIST'][$discountId];

				if (empty($appliedDiscounts[$discountId]['RESULT']['BASKET']))
				{
					$appliedDiscounts[$discountId]['RESULT']['BASKET'] = [];
				}

				$appliedDiscounts[$discountId]['RESULT']['BASKET'] = array_merge(
					$appliedDiscounts[$discountId]['RESULT']['BASKET'],
					$this->getAffectedReformattedBasketItemsInDiscount($basket, $discountData, $calcResults)
				);
			}
		}
		unset($discountId, $discountData);

		return [$calcResults['FULL_DISCOUNT_LIST'], $appliedDiscounts];
	}

	protected function getBasketTotal()
	{
		$result = [];

		if ($this->isFastLoadRequest())
		{
			$basketPrice = 0;
			$basketWeight = 0;
			$basketBasePrice = 0;
			$basketVatSum = 0;

			foreach ($this->basketItems as $basketItem)
			{
				if ($basketItem['CAN_BUY'] === 'Y' && $basketItem['DELAY'] !== 'Y')
				{
					$basketPrice += $basketItem['SUM_VALUE'];
					$basketWeight += $basketItem['WEIGHT'] * $basketItem['QUANTITY'];
					$basketBasePrice += $basketItem['BASE_PRICE'] * $basketItem['QUANTITY'];
					$basketVatSum += $basketItem['VAT_VALUE'] * $basketItem['QUANTITY'];
				}
			}
		}
		else
		{
			$basket = $this->getBasketStorage()->getOrderableBasket();
			$this->initializeBasketOrderIfNotExists($basket);

			$basketPrice = $basket->getPrice();
			$basketWeight = $basket->getWeight();
			$basketBasePrice = $basket->getBasePrice();
			$basketVatSum = $basket->getVatSum();

			[$result['FULL_DISCOUNT_LIST'], $result['APPLIED_DISCOUNT_LIST']] = $this->getDiscountData($basket);
		}

		$siteCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($this->getSiteId());
		$result['CURRENCY'] = $siteCurrency;

		$result['allSum'] = PriceMaths::roundPrecision($basketPrice);
		$result['allSum_FORMATED'] = CCurrencyLang::CurrencyFormat($result['allSum'], $siteCurrency, true);

		$result['allWeight'] = $basketWeight;
		$result['allWeight_FORMATED'] = roundEx($basketWeight / $this->weightKoef, SALE_WEIGHT_PRECISION).' '.$this->weightUnit;

		$result['PRICE_WITHOUT_DISCOUNT'] = CCurrencyLang::CurrencyFormat($basketBasePrice, $siteCurrency, true);
		$result['DISCOUNT_PRICE_ALL'] = PriceMaths::roundPrecision($basketBasePrice - $basketPrice);
		$result['DISCOUNT_PRICE_FORMATED'] = $result['DISCOUNT_PRICE_ALL_FORMATED'] = CCurrencyLang::CurrencyFormat($result['DISCOUNT_PRICE_ALL'], $siteCurrency, true);

		if ($this->priceVatShowValue === 'Y')
		{
			$result['allVATSum'] = PriceMaths::roundPrecision($basketVatSum);
			$result['allVATSum_FORMATED'] = CCurrencyLang::CurrencyFormat($result['allVATSum'], $siteCurrency, true);
			$result['allSum_wVAT_FORMATED'] = CCurrencyLang::CurrencyFormat($result['allSum'] - $result['allVATSum'], $siteCurrency, true);
		}

		return $result;
	}

	protected function getCouponInfo()
	{
		$result = [
			'COUPON' => '',
			'COUPON_LIST' => [],
		];

		if ($this->hideCoupon != 'Y')
		{
			$coupons = DiscountCouponsManager::get(true, [], true, true);
			if (!empty($coupons))
			{
				foreach ($coupons as &$coupon)
				{
					if ($result['COUPON'] == '')
					{
						$result['COUPON'] = $coupon['COUPON'];
					}

					if ($coupon['STATUS'] == DiscountCouponsManager::STATUS_NOT_FOUND || $coupon['STATUS'] == DiscountCouponsManager::STATUS_FREEZE)
					{
						$coupon['JS_STATUS'] = 'BAD';
					}
					elseif ($coupon['STATUS'] == DiscountCouponsManager::STATUS_NOT_APPLYED || $coupon['STATUS'] == DiscountCouponsManager::STATUS_ENTERED)
					{
						$coupon['JS_STATUS'] = 'ENTERED';

						if ($coupon['STATUS'] == DiscountCouponsManager::STATUS_NOT_APPLYED)
						{
							$coupon['STATUS_TEXT'] = DiscountCouponsManager::getCheckCodeMessage(DiscountCouponsManager::COUPON_CHECK_OK);
							$coupon['CHECK_CODE_TEXT'] = [$coupon['STATUS_TEXT']];
						}
					}
					else
					{
						$coupon['JS_STATUS'] = 'APPLYED';
					}

					$coupon['JS_CHECK_CODE'] = '';

					if (isset($coupon['CHECK_CODE_TEXT']))
					{
						$coupon['JS_CHECK_CODE'] = is_array($coupon['CHECK_CODE_TEXT'])
							? implode(', ', $coupon['CHECK_CODE_TEXT'])
							: $coupon['CHECK_CODE_TEXT'];
					}

					$result['COUPON_LIST'][] = $coupon;
				}

				unset($coupon);
			}

			unset($coupons);
		}

		return $result;
	}

	protected function getPrepayment()
	{
		global $APPLICATION;

		$result = [];
		$prePayablePs = [];
		$personTypes = array_keys(Sale\PersonType::load($this->getSiteId()));

		if (!empty($personTypes))
		{
			$paySysActionIterator = Sale\Paysystem\Manager::getList([
				'select' => [
					'ID', 'PAY_SYSTEM_ID', 'PERSON_TYPE_ID', 'NAME', 'ACTION_FILE', 'RESULT_FILE',
					'NEW_WINDOW', 'PARAMS', 'ENCODING', 'LOGOTIP',
				],
				'filter' => [
					'ACTIVE' => 'Y',
					'HAVE_PREPAY' => 'Y',
				],
			]);
			$helper = Main\Application::getConnection()->getSqlHelper();

			while ($paySysAction = $paySysActionIterator->fetch())
			{
				$dbRestriction = Sale\Internals\ServiceRestrictionTable::getList([
					'select' => ['PARAMS'],
					'filter' => [
						'SERVICE_ID' => $paySysAction['ID'],
						'CLASS_NAME' => $helper->forSql('\\'.Sale\Services\PaySystem\Restrictions\PersonType::class),
						'SERVICE_TYPE' => Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
					],
				]);

				if ($restriction = $dbRestriction->fetch())
				{
					if (array_intersect($personTypes, $restriction['PARAMS']['PERSON_TYPE_ID']))
					{
						$prePayablePs = $paySysAction;
						break;
					}
				}
				else
				{
					$prePayablePs = $paySysAction;
					break;
				}
			}

			if ($prePayablePs)
			{
				// compatibility
				CSalePaySystemAction::InitParamArrays(false, false, $paySysAction['PARAMS']);

				$psPreAction = new Sale\PaySystem\Service($prePayablePs);
				if ($psPreAction->isPrePayable())
				{
					$psPreAction->initPrePayment(null, $this->request);

					$basket = $this->getBasketStorage()->getBasket();
					$basketItems = [];
					/** @var Sale\BasketItem $item */
					foreach ($basket as $key => $item)
					{
						if ($item->canBuy() && !$item->isDelay())
						{
							$basketItems[$key]['NAME'] = $item->getField('NAME');
							$basketItems[$key]['PRICE'] = $item->getPrice();
							$basketItems[$key]['QUANTITY'] = $item->getQuantity();
						}
					}

					$orderData = [
						'PATH_TO_ORDER' => $this->pathToOrder,
						'AMOUNT' => $basket->getPrice(),
						'BASKET_ITEMS' => $basketItems,
					];

					if (!$psPreAction->basketButtonAction($orderData))
					{
						if ($e = $APPLICATION->GetException())
						{
							$this->errorCollection->setError(new Error($e->GetString()));
						}
					}

					$psPreAction->setTemplateMode(Sale\PaySystem\BaseServiceHandler::STRING);
					$r = $psPreAction->showTemplate(null, 'prepay_button');
					$result['PREPAY_BUTTON'] = $r->getTemplate();
				}
			}
		}

		return $result;
	}

	// legacy method
	public function getSkuPropsData($basketItems, $parents, $arSkuProps = [])
	{
		$arRes = [];
		$arSkuIblockID = [];

		if (empty($parents) || !is_array($parents))
			return $basketItems;

		if (empty($arSkuProps) || empty($basketItems))
			return $basketItems;

		// load offers
		$itemIndex = [];
		$itemIds = [];
		$productIds = [];

		$oldSkuData = [];

		$updateBasketProps = [];

		foreach ($basketItems as $index => $item)
		{
			if (!isset($item['MODULE']) || $item['MODULE'] != 'catalog')
				continue;

			if (!isset($parents[$item['PRODUCT_ID']]))
				continue;

			$id = $item['PRODUCT_ID'];
			$itemIds[$id] = $id;

			if (!isset($itemIndex[$id]))
			{
				$itemIndex[$id] = [];
			}

			$itemIndex[$id][] = $index;
			$productIds[$parents[$id]['ID']] = $parents[$id]['ID'];

			$needSkuProps = static::getMissingPropertyCodes($item['PROPS'], $arSkuProps);
			if (!empty($needSkuProps))
			{
				if (!isset($updateBasketProps[$id]))
					$updateBasketProps[$id] = [];
				$updateBasketProps[$id][$item['ID']] = $needSkuProps;
			}

			unset($needSkuProps, $id);
		}

		unset($index, $item);

		$offerList = CCatalogSku::getOffersList(
			$productIds,
			0,
			[
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CATALOG_AVAILABLE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			['ID', 'IBLOCK_ID'],
			['CODE' => $arSkuProps]
		);

		if (!empty($offerList))
		{
			foreach (array_keys($offerList) as $index)
			{
				$oldSkuData[$index] = [];
				foreach (array_keys($offerList[$index]) as $offerId)
				{
					unset($itemIds[$offerId]);

					$offer = $offerList[$index][$offerId];
					$offerList[$index][$offerId] = [
						'ID' => $offer['ID'],
						'IBLOCK_ID' => $offer['IBLOCK_ID'],
						'PROPERTIES' => $offer['PROPERTIES'],
						'CAN_SELECTED' => 'Y',
					];

					if (!empty($offer['PROPERTIES']))
					{
						$currentSkuPropValues = [];
						foreach ($offer['PROPERTIES'] as $propName => $property)
						{
							$property['VALUE'] = (string)$property['VALUE'];

							$currentSkuPropValues[$propName] = [
								'~CODE' => $property['~CODE'] ?? $property['CODE'],
								'CODE' => $property['CODE'],
								'~NAME' => $property['~NAME'],
								'NAME' => $property['NAME'],
								'~VALUE' => $property['~VALUE'],
								'VALUE' => $property['VALUE'],
								'~SORT' => $property['~SORT'] ?? $property['SORT'],
								'SORT' => $property['SORT'],
							];
						}
						unset($propName, $property);

						if (isset($updateBasketProps[$offerId]) && !empty($currentSkuPropValues))
						{
							foreach ($updateBasketProps[$offerId] as $basketId => $updateCodes)
							{
								$basketKey = static::getBasketKeyById($basketItems, $basketId);

								if ($basketKey === false)
									continue;

								static::fillMissingProperties($basketItems[$basketKey]['PROPS'], $updateCodes, $currentSkuPropValues);
								unset($basketKey);
							}
							unset($basketId, $updateCodes);
						}
						unset($currentSkuPropValues);
					}
					unset($offer);
				}
				unset($offerId);
			}
			unset($index);
		}

		$absentOffers = [];
		if (!empty($itemIds))
		{
			$absentProducts = [];
			foreach ($itemIds as $id)
			{
				$absentProducts[$parents[$id]['ID']] = $parents[$id]['ID'];
			}
			unset($id);

			$absentOffers = CCatalogSku::getOffersList(
				$absentProducts,
				0,
				['ID' => $itemIds],
				['ID', 'IBLOCK_ID'],
				['CODE' => $arSkuProps]
			);
			if (!empty($absentOffers))
			{
				foreach (array_keys($absentOffers) as $index)
				{
					foreach (array_keys($absentOffers[$index]) as $offerId)
					{
						unset($itemIds[$offerId]);
						$absentOffers[$index][$offerId]['CAN_SELECTED'] = 'N';
					}
				}
				unset($index);
			}
			unset($absentProducts);
		}

		if (!empty($itemIds))
		{
			foreach ($itemIds as $id)
			{
				foreach ($itemIndex[$id] as $index)
				{
					unset($basketItems[$index]);
				}

				unset($index);
			}

			unset($id);
		}

		if (empty($basketItems))
			return $basketItems;

		// load offers end

		$skuPropKeys = (!empty($arSkuProps) ? array_fill_keys($arSkuProps, true) : []);

		foreach ($basketItems as &$item)
		{
			if (!isset($item['MODULE']) || $item['MODULE'] != 'catalog')
				continue;

			if (!isset($parents[$item['PRODUCT_ID']]))
				continue;

			$arSKU = CCatalogSku::GetInfoByProductIBlock($parents[$item['PRODUCT_ID']]['IBLOCK_ID']);
			if (empty($arSKU))
				continue;

			if (!isset($arSkuIblockID[$arSKU['IBLOCK_ID']]))
				$arSkuIblockID[$arSKU['IBLOCK_ID']] = $arSKU;

			$item['IBLOCK_ID'] = $arSKU['IBLOCK_ID'];
			$item['SKU_PROPERTY_ID'] = $arSKU['SKU_PROPERTY_ID'];
		}
		unset($item);

		foreach ($arSkuIblockID as $skuIblockID => $arSKU)
		{
			// possible props values
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['*'],
				'filter' => [
					'=IBLOCK_ID' => $skuIblockID, '=ACTIVE' => 'Y', '=MULTIPLE' => 'N',
					'!=ID' => $arSKU['SKU_PROPERTY_ID'],
					'@PROPERTY_TYPE' => [
						Iblock\PropertyTable::TYPE_ELEMENT,
						Iblock\PropertyTable::TYPE_LIST,
						Iblock\PropertyTable::TYPE_STRING,
					],
				],
				'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
			]);
			while ($arProp = $iterator->fetch())
			{
				$arProp['CODE'] = (string)$arProp['CODE'];
				if ($arProp['CODE'] === '')
					$arProp['CODE'] = $arProp['ID'];
				if (!isset($skuPropKeys[$arProp['CODE']]))
					continue;

				$arValues = [];

				switch ($arProp['PROPERTY_TYPE'])
				{
					case Iblock\PropertyTable::TYPE_LIST:
						$rsPropEnums = CIBlockProperty::GetPropertyEnum($arProp['ID'], ['SORT' => 'ASC', 'VALUE' => 'ASC']);
						while ($arEnum = $rsPropEnums->Fetch())
						{
							$arValues['n'.$arEnum['ID']] = [
								'ID' => $arEnum['ID'],
								'NAME' => $arEnum['VALUE'],
								'SORT' => (int)$arEnum['SORT'],
								'PICT' => false,
							];
						}
						unset($arEnum, $rsPropEnums);
						$arValues['n0'] = [
							'ID' => 0,
							'NAME' => '-',
							'SORT' => PHP_INT_MAX,
							'PICT' => false,
						];
						break;
					case Iblock\PropertyTable::TYPE_ELEMENT:
						$rsPropEnums = CIBlockElement::GetList(
							['SORT' => 'ASC', 'NAME' => 'ASC'],
							['IBLOCK_ID' => $arProp['LINK_IBLOCK_ID'], 'ACTIVE' => 'Y'],
							false,
							false,
							['ID', 'NAME', 'PREVIEW_PICTURE']
						);
						while ($arEnum = $rsPropEnums->Fetch())
						{
							$arValues['n'.$arEnum['ID']] = [
								'ID' => $arEnum['ID'],
								'NAME' => $arEnum['NAME'],
								'SORT' => (int)$arEnum['SORT'],
								'FILE' => $arEnum['PREVIEW_PICTURE'],
								'PICT' => false,
								'XML_ID' => $arEnum['NAME'],
							];
						}
						unset($arEnum, $rsPropEnums);
						$arValues['n0'] = [
							'ID' => 0,
							'NAME' => '-',
							'SORT' => PHP_INT_MAX,
							'FILE' => false,
							'PICT' => false,
							'XML_ID' => '-',
						];
						break;
					case Iblock\PropertyTable::TYPE_STRING:
						$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
						if ($arProp['USER_TYPE'] == 'directory' && $arProp['USER_TYPE_SETTINGS'] !== null)
						{
							if (!is_array($arProp['USER_TYPE_SETTINGS']))
								$arProp['USER_TYPE_SETTINGS'] = unserialize($arProp['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
							if (self::$highLoadInclude === null)
								self::$highLoadInclude = Loader::includeModule('highloadblock');
							if (self::$highLoadInclude)
							{
								$hlblock = HL\HighloadBlockTable::getList([
									'filter' => ['=TABLE_NAME' => $arProp['USER_TYPE_SETTINGS']['TABLE_NAME']],
								])->fetch()
								;
								if ($hlblock)
								{
									$entity = HL\HighloadBlockTable::compileEntity($hlblock);
									$entityDataClass = $entity->getDataClass();
									$fieldsList = $entity->getFields();
									$dataOrder = [];
									if (isset($fieldsList['UF_SORT']))
										$dataOrder['UF_SORT'] = 'ASC';
									$dataOrder['UF_NAME'] = 'ASC';

									$rsData = $entityDataClass::getList([
										'order' => $dataOrder,
									]);
									while ($arData = $rsData->fetch())
									{
										$arValues['n'.$arData['ID']] = [
											'ID' => $arData['ID'],
											'NAME' => $arData['UF_NAME'],
											'SORT' => (int)$arData['UF_SORT'],
											'FILE' => $arData['UF_FILE'],
											'PICT' => false,
											'XML_ID' => $arData['UF_XML_ID'],
										];
									}
									$arValues['n0'] = [
										'ID' => 0,
										'NAME' => '-',
										'SORT' => PHP_INT_MAX,
										'FILE' => false,
										'PICT' => false,
										'XML_ID' => '-',
									];
								}
							}
						}
						break;
				}
				if (!empty($arValues) && is_array($arValues))
				{
					$arRes[$skuIblockID][$arProp['ID']] = [
						'ID' => $arProp['ID'],
						'CODE' => $arProp['CODE'],
						'NAME' => $arProp['NAME'],
						'TYPE' => $arProp['PROPERTY_TYPE'],
						'USER_TYPE' => $arProp['USER_TYPE'],
						'VALUES' => $arValues,
					];
				}
			}
			unset($arProp, $iterator);
		}

		foreach ($basketItems as &$item)
		{
			if (!isset($item['MODULE']) || $item['MODULE'] != 'catalog')
				continue;

			if (isset($item['IBLOCK_ID']) && (int)$item['IBLOCK_ID'] > 0 && isset($arRes[$item['IBLOCK_ID']]))
			{
				$arUsedValues = [];
				$arTmpRes = [];

				$id = $item['PRODUCT_ID'];
				if (!isset($parents[$id]))
					continue;

				$parentId = $parents[$id]['ID'];
				if (empty($offerList[$parentId][$id]) && empty($absentOffers[$parentId][$id]))
					continue;

				$currentItemProperties = (!empty($offerList[$parentId][$id])
					? $offerList[$parentId][$id]['PROPERTIES']
					: $absentOffers[$parentId][$id]['PROPERTIES']
				);

				if (is_array($currentItemProperties))
				{
					foreach ($currentItemProperties as $code => $data)
					{
						$data['VALUE'] = (string)$data['VALUE'];
						if ($data['VALUE'] == '')
						{
							$data['VALUE'] = '-';
						}
						$arUsedValues[$code] = [$data['VALUE']];
					}
				}

				if (!empty($offerList[$parentId]))
				{
					$propertyFilter = [];
					$idList = array_keys($offerList[$parentId]);
					foreach ($arRes[$item['IBLOCK_ID']] as $property)
					{
						$propertyCode = $property['CODE'];
						foreach ($idList as $offerId)
						{
							if ($offerId == $id)
								continue;
							$check = true;
							if (!empty($propertyFilter))
							{
								foreach ($propertyFilter as $code => $value)
								{
									if ($offerList[$parentId][$offerId]['PROPERTIES'][$code]['VALUE'] != $value)
									{
										$check = false;
										break;
									}
								}
								unset($code, $value);
							}
							if (!$check)
								continue;
							$value = (string)$offerList[$parentId][$offerId]['PROPERTIES'][$propertyCode]['VALUE'];
							if ($value == '')
								$value = '-';

							if (!isset($arUsedValues[$propertyCode]))
							{
								$arUsedValues[$propertyCode] = [];
							}

							if (!in_array($value, $arUsedValues[$propertyCode]))
								$arUsedValues[$propertyCode][] = $value;
							unset($value);
						}
						unset($offerId);
						$propertyFilter[$propertyCode] = $currentItemProperties[$propertyCode]['VALUE'];
					}
					unset($property);
					unset($propertyFilter);
				}

				if (!empty($arUsedValues))
				{
					$removeCodes = [];
					$clearValues = [];
					foreach (array_keys($arUsedValues) as $code)
					{
						if (count($arUsedValues[$code]) === 1 && $arUsedValues[$code][0] === '-')
						{
							$removeCodes[$code] = true;
							continue;
						}
						$clearValues[$code] = $arUsedValues[$code];
					}
					$arUsedValues = $clearValues;
					unset($clearValues);
					if (
						!empty($removeCodes)
						&& !empty($item['PROPS'])
						&& is_array($item['PROPS'])
					)
					{
						$updateProps = false;
						foreach (array_keys($item['PROPS']) as $propertyIndex)
						{
							$propertyCode = $item['PROPS'][$propertyIndex]['CODE'] ?? '';
							if (isset($removeCodes[$propertyCode]))
							{
								$updateProps = true;
								unset($item['PROPS'][$propertyIndex]);
							}
							unset($propertyCode);
						}
						unset($propertyIndex);
						if ($updateProps)
						{
							$item['PROPS'] = array_values($item['PROPS']);
						}
						unset($updateProps);
					}
					unset($removeCodes);
				}

				if (!empty($arUsedValues))
				{
					// add only used values to the item SKU_DATA
					foreach ($arRes[$item['IBLOCK_ID']] as $propId => $arProp)
					{
						if (empty($arUsedValues[$arProp['CODE']]))
							continue;

						$propertyData = [
							"ID" => $arProp["ID"],
							"CODE" => $arProp["CODE"],
							"TYPE" => $arProp["TYPE"],
							"USER_TYPE" => $arProp["USER_TYPE"],
							"NAME" => $arProp["NAME"],
							"VALUES" => [],
						];
						foreach ($arProp['VALUES'] as $valId => $arValue)
						{
							// properties of various type have different values in the used values data
							if (
								(
									$arProp['TYPE'] == 'L'
									&& (
										in_array($arValue['NAME'], $arUsedValues[$arProp['CODE']])
										|| in_array(htmlspecialcharsEx($arValue['NAME']), $arUsedValues[$arProp['CODE']])
									)
								)
								|| ($arProp['TYPE'] == 'E' && in_array($arValue['ID'], $arUsedValues[$arProp['CODE']]))
								|| ($arProp['TYPE'] == 'S' && in_array($arValue['XML_ID'], $arUsedValues[$arProp['CODE']]))
							)
							{
								if ($arProp['TYPE'] == 'S' || $arProp['TYPE'] == 'E')
								{
									if (!empty($arValue['FILE']))
									{
										$arTmpFile = CFile::GetFileArray($arValue['FILE']);
										if (!empty($arTmpFile))
										{
											$tmpImg = CFile::ResizeImageGet(
												$arTmpFile,
												['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD],
												BX_RESIZE_IMAGE_PROPORTIONAL, false, false
											);
											if (is_array($tmpImg))
											{
												$arValue['PICT'] = [
													'SRC' => Iblock\Component\Tools::getImageSrc([
														'SRC' => $tmpImg['src']
													]),
												];
											}
										}
									}
								}

								$propertyData['VALUES'][$valId] = $arValue;
							}
						}
						if (!empty($propertyData['VALUES']))
						{
							$arTmpRes['n'.$propId] = $propertyData;
						}
						unset($propertyData);
					}
				}

				$item['SKU_DATA'] = $arTmpRes;
			}
		}
		unset($item);

		return $basketItems;
	}

	// legacy method
	public function getAvailableQuantity($basketItems)
	{
		if (empty($basketItems) || !is_array($basketItems))
		{
			return [];
		}

		if (!self::includeCatalog())
		{
			return $basketItems;
		}

		$elementIds = [];
		$productMap = [];

		foreach ($basketItems as $key => $item)
		{
			$elementIds[$item['PRODUCT_ID']] = $item['PRODUCT_ID'];

			if (!isset($productMap[$item['PRODUCT_ID']]))
			{
				$productMap[$item['PRODUCT_ID']] = [];
			}

			$productMap[$item['PRODUCT_ID']][] = $key;
		}

		unset($key, $item);

		if (!empty($elementIds))
		{
			sort($elementIds);
			$productIterator = Catalog\ProductTable::getList([
				'select' => ['ID', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO'],
				'filter' => ['@ID' => $elementIds],
			]);
			while ($product = $productIterator->fetch())
			{
				if (!isset($productMap[$product['ID']]))
					continue;

				$check = ($product['QUANTITY_TRACE'] == 'Y' && $product['CAN_BUY_ZERO'] == 'N' ? 'Y' : 'N');
				foreach ($productMap[$product['ID']] as $key)
				{
					$basketItems[$key]['AVAILABLE_QUANTITY'] = $product['QUANTITY'];
					$basketItems[$key]['CHECK_MAX_QUANTITY'] = $check;
				}

				unset($key, $check);
			}

			unset($product, $productIterator);
		}

		unset($productMap, $elementIds);

		return $basketItems;
	}

	protected function checkCoupon($postList)
	{
		$couponChanged = false;

		if (empty($postList))
		{
			return $couponChanged;
		}

		if (!empty($postList['delete_coupon']))
		{
			if (!is_array($postList['delete_coupon']))
			{
				$postList['delete_coupon'] = [$postList['delete_coupon']];
			}

			foreach ($postList['delete_coupon'] as $coupon)
			{
				$couponChanged = DiscountCouponsManager::delete($coupon) || $couponChanged;
			}
		}
		else
		{
			[$found, $coupon] = $this->getCouponFromRequest($postList);

			if ($found)
			{
				if (!empty($coupon))
				{
					$couponChanged = DiscountCouponsManager::add($coupon);
				}
				else
				{
					DiscountCouponsManager::clear(true);
				}
			}
		}

		return $couponChanged;
	}

	protected function getCouponFromRequest(array $postList)
	{
		$found = false;
		$coupon = '';

		if (isset($postList['coupon']))
		{
			$found = true;
			$coupon = trim((string)$postList['coupon']);
		}
		elseif (isset($postList['COUPON']))
		{
			$found = true;
			$coupon = trim((string)$postList['COUPON']);
		}

		return [$found, $coupon];
	}

	protected function getDefaultAjaxAnswer()
	{
		return [
			'BASKET_REFRESHED' => false,
			'CHANGED_BASKET_ITEMS' => [],
			'RESTORED_BASKET_ITEMS' => [],
			'DELETED_BASKET_ITEMS' => [],
			'MERGED_BASKET_ITEMS' => [],
		];
	}

	// legacy method
	public function recalculateBasket($postList)
	{
		$result = $this->getDefaultAjaxAnswer();

		if (!empty($postList))
		{
			if ($this->hideCoupon !== 'Y')
			{
				$result['VALID_COUPON'] = $this->checkCoupon($postList);
			}

			$itemsActionData = $this->extractItemsActionData($postList);

			if (!empty($itemsActionData))
			{
				$itemsRatioData = $this->getBasketItemsRatios($itemsActionData);

				foreach ($itemsActionData as $id => $itemActionData)
				{
					if (!empty($itemActionData['POST_RESTORE']) && $this->arParams['SHOW_RESTORE'] === 'Y')
					{
						$this->processRestore($result, $id, $itemActionData['POST_RESTORE']);
					}
					else
					{
						$basket = $this->getBasketStorage()->getBasket();
						$item = $basket->getItemByBasketCode($id);

						if ($item)
						{
							if (!empty($itemActionData['POST_DELETE']) && in_array('DELETE', $this->columns))
							{
								$this->processDelete($result, $item);
							}
							elseif (!empty($itemActionData['POST_OFFER']))
							{
								$this->processChangeOffer($result, $id, $itemActionData['POST_OFFER']);
							}
							elseif ($item->canBuy())
							{
								if (
									isset($itemActionData['POST_QUANTITY'])
									&& !empty($itemsRatioData[$id])
									&& $item->getQuantity() != $itemActionData['POST_QUANTITY']
									&& in_array('QUANTITY', $this->columns)
								)
								{
									$this->processChangeQuantity($result, $itemsRatioData[$id], $itemActionData['POST_QUANTITY']);
								}

								if (
									isset($itemActionData['POST_DELAY'])
									&& $item->getField('DELAY') !== $itemActionData['POST_DELAY']
									&& ($itemActionData['POST_DELAY'] === 'N' || in_array('DELAY', $this->columns))
								)
								{
									$this->processDelay($result, $item, $itemActionData['POST_DELAY']);
								}

								if (!empty($itemActionData['POST_MERGE_OFFER']))
								{
									$this->processMergeOffer($result, $id);
								}
							}
						}
					}
				}

				$result['CHANGED_BASKET_ITEMS'] = array_keys($itemsActionData);
			}
		}

		return $result;
	}

	protected function extractItemsActionData($postList)
	{
		$itemsData = [];

		foreach ($postList as $key => $value)
		{
			if (mb_strpos($key, 'QUANTITY_') !== false)
			{
				$id = (int)mb_substr($key, 9);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_QUANTITY'] = $value;
			}
			elseif (mb_strpos($key, 'DELETE_') !== false)
			{
				$id = (int)mb_substr($key, 7);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_DELETE'] = $value === 'Y';
			}
			elseif (mb_strpos($key, 'RESTORE_') !== false)
			{
				$id = (int)mb_substr($key, 8);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_RESTORE'] = $value;
			}
			elseif (mb_strpos($key, 'DELAY_') !== false)
			{
				$id = (int)mb_substr($key, 6);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_DELAY'] = $value === 'Y' ? 'Y' : 'N';
			}
			elseif (mb_strpos($key, 'MERGE_OFFER_') !== false)
			{
				$id = (int)mb_substr($key, 12);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_MERGE_OFFER'] = $value === 'Y';
			}
			elseif (mb_strpos($key, 'OFFER_') !== false)
			{
				$id = (int)mb_substr($key, 6);

				if (!isset($itemsData[$id]))
				{
					$itemsData[$id] = [];
				}

				$itemsData[$id]['POST_OFFER'] = $value;
			}
		}

		return $itemsData;
	}

	protected function getBasketItemsRatios($actionData)
	{
		$ratioData = [];

		if (!empty($actionData) && is_array($actionData))
		{
			$basket = $this->getBasketStorage()->getBasket();

			foreach ($actionData as $id => $data)
			{
				if (!empty($data['POST_QUANTITY']))
				{
					$basketItem = $basket->getItemByBasketCode($id);
					if ($basketItem)
					{
						$ratioData[$id] = $basketItem->getFieldValues();
					}
				}
			}

			if (!empty($ratioData))
			{
				$ratioData = getRatio($ratioData);
			}
		}

		return $ratioData;
	}

	protected function processRestore(&$result, $id, $restoreFields)
	{
		$res = $this->addProductToBasket($restoreFields);
		if ($res->isSuccess())
		{
			$resultData = $res->getData();
			if (!empty($resultData['BASKET_ITEM']))
			{
				$result['RESTORED_BASKET_ITEMS'][$id] = $resultData['BASKET_ITEM'];
			}
		}
		else
		{
			$this->addErrors($res->getErrors(), $id);
		}
	}

	protected function processDelete(&$result, Sale\BasketItemBase $item)
	{
		$res = $item->delete();
		if ($res->isSuccess())
		{
			$result['DELETE_ORIGINAL'] = 'Y';
			$result['DELETED_BASKET_ITEMS'][] = $item->getId();

			// compatibility
			$userId = $this->getUserId() ?? 0;

			if ($item->getField('SUBSCRIBE') === 'Y' && is_array($_SESSION['NOTIFY_PRODUCT'][$userId]))
			{
				unset($_SESSION['NOTIFY_PRODUCT'][$userId][$item->getProductId()]);
			}
		}
		else
		{
			$this->addErrors($res->getErrors(), $item->getId());
		}
	}

	protected function processChangeQuantity(&$result, $itemRatioData, $quantity)
	{
		$res = $this->checkQuantity($itemRatioData, $quantity);
		if (!empty($res['ERRORS']))
		{
			$this->addErrors($res['ERRORS'], $itemRatioData['ID']);
		}
	}

	protected function processChangeOffer(&$result, $id, $offerProps)
	{
		$res = $this->changeProductOfferWithoutSave($id, self::SEARCH_OFFER_BY_PROPERTIES, $offerProps, false);
		if (!$res->isSuccess())
		{
			$this->addErrors($res->getErrors(), $id);
		}
	}

	protected function processMergeOffer(&$result, $id)
	{
		$res = $this->mergeProductOffers($id);
		if ($res->isSuccess())
		{
			$mergedBasketItems = $res->get('MERGED_BASKET_ITEMS');
			if (!empty($mergedBasketItems))
			{
				$result['MERGED_BASKET_ITEMS'] = array_merge($result['MERGED_BASKET_ITEMS'], $mergedBasketItems);
			}
		}
		else
		{
			$this->addErrors($res->getErrors(), $id);
		}
	}

	protected function processDelay(&$result, Sale\BasketItemBase $item, $delay)
	{
		$res = $item->setField('DELAY', $delay);
		if (!$res->isSuccess())
		{
			$this->addErrors($res->getErrors(), $item->getId());
		}
	}

	public function checkQuantity($basketItemData, $desiredQuantity)
	{
		$result = [];

		if (
			$this->quantityFloat === 'Y'
			|| (
				isset($basketItemData['MEASURE_RATIO'])
				&& (float)$basketItemData['MEASURE_RATIO'] > 0
				&& (float)$basketItemData['MEASURE_RATIO'] != (int)$basketItemData['MEASURE_RATIO']
			)
		)
		{
			$isFloatQuantity = true;
		}
		else
		{
			$isFloatQuantity = false;
		}

		$quantity = $isFloatQuantity ? (float)$desiredQuantity : (int)$desiredQuantity;
		if ($basketItemData['QUANTITY'] != $quantity)
		{
			$basket = $this->getBasketStorage()->getBasket();
			$basketItem = $basket->getItemByBasketCode($basketItemData['ID']);
			$res = $basketItem->setField('QUANTITY', $desiredQuantity);
			if (!$res->isSuccess())
			{
				$errorMessages = $res->getErrorMessages();
				$result['ERROR'] = reset($errorMessages);
				$result['ERRORS'] = $res->getErrors();
			}
		}

		return $result;
	}

	protected function mergeProductOffers($basketItemId)
	{
		$result = new Sale\Result();

		$basketItemId = (int)$basketItemId;
		if ($basketItemId <= 0)
			return $result;

		$basket = $this->getBasketStorage()->getBasket();
		/** @var Sale\BasketItem $currentBasketItem */
		$currentBasketItem = $basket->getItemByBasketCode($basketItemId);
		if (empty($currentBasketItem))
			return $result;

		if ($currentBasketItem->getField('MODULE') !== 'catalog')
			return $result;

		if ($currentBasketItem->isBundleParent() || $currentBasketItem->isBundleChild())
			return $result;

		$currentBasketItemHash = $this->getBasketItemHash(
			$currentBasketItem->getFieldValues() + ['PROPS' => $this->getBasketItemProperties($currentBasketItem)]
		);

		$mergedBasketItems = [];

		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->getField('MODULE') !== 'catalog')
				continue;

			if ($basketItem->isBundleParent() || $basketItem->isBundleChild())
				continue;

			$basketItemHash = $this->getBasketItemHash(
				$basketItem->getFieldValues() + ['PROPS' => $this->getBasketItemProperties($basketItem)]
			);

			if ($basketItemHash === $currentBasketItemHash)
			{
				$mergedBasketItems[] = $basketItem;
			}
		}

		$mergedBasketCodes = [];

		if (!empty($mergedBasketItems))
		{
			$quantity = 0;
			/** @var Sale\BasketItem $basketItem */
			foreach ($mergedBasketItems as $basketItem)
			{
				if ($basketItem === $currentBasketItem)
					continue;

				$mergedBasketCodes[] = $basketItem->getBasketCode();
				$quantity += $basketItem->getQuantity();

				$res = $basketItem->delete();
				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}

			$res = $currentBasketItem->setField('QUANTITY', $currentBasketItem->getQuantity() + $quantity);
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		$result->addData(['MERGED_BASKET_ITEMS' => $mergedBasketCodes]);

		return $result;
	}

	protected function changeProductOfferWithoutSave($basketId, $searchType, $searchData, $useMerge = true)
	{
		$result = new Sale\Result();

		$basketId = (int)$basketId;
		if ($basketId <= 0)
			return $result;

		$searchType = (string)$searchType;
		if ($searchType != self::SEARCH_OFFER_BY_ID && $searchType != self::SEARCH_OFFER_BY_PROPERTIES)
			return $result;

		if (!is_array($searchData))
			return $result;

		if (empty($this->offersProps) || !is_array($this->offersProps))
			return $result;

		$newOfferId = 0;
		$propertyValues = [];

		if ($searchType == self::SEARCH_OFFER_BY_ID)
		{
			if (!isset($searchData['ID']))
				return $result;

			$newOfferId = (int)$searchData['ID'];
			if ($newOfferId <= 0)
				return $result;
		}
		else
		{
			$propertyValues = array_filter($searchData);
			if (empty($propertyValues))
				return $result;
		}

		$basket = $this->getBasketStorage()->getBasket();
		/** @var Sale\BasketItem $currentBasketItem */
		$currentBasketItem = $basket->getItemByBasketCode($basketId);
		if (empty($currentBasketItem))
			return $result;

		if ($currentBasketItem->getField('MODULE') !== 'catalog')
			return $result;

		if ($currentBasketItem->isBundleParent() || $currentBasketItem->isBundleChild())
			return $result;

		$currentOfferId = $currentBasketItem->getProductId();
		$parent = CCatalogSku::getProductList($currentOfferId, 0);

		if (empty($parent[$currentOfferId]))
			return $result;

		$parent = $parent[$currentOfferId];

		$treeProperties = \CIBlockPriceTools::getTreeProperties(
			['IBLOCK_ID' => $parent['OFFER_IBLOCK_ID'], 'SKU_PROPERTY_ID' => $parent['SKU_PROPERTY_ID']],
			$this->offersProps
		);

		if (empty($treeProperties))
			return $result;

		if ($searchType == self::SEARCH_OFFER_BY_PROPERTIES)
		{
			$newProduct = $this->selectOfferByProps($parent['IBLOCK_ID'], $parent['ID'], $currentOfferId, $propertyValues, $treeProperties);
		}
		else
		{
			$newProduct = $this->selectOfferById($parent['IBLOCK_ID'], $parent['ID'], $currentOfferId, $newOfferId, $treeProperties);
		}

		if ($newProduct === null)
			return $result;

		$existBasketItem = null;
		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->getField('MODULE') !== 'catalog')
				continue;

			if ($basketItem->isBundleParent() || $basketItem->isBundleChild())
				continue;

			if ((int)$basketItem->getProductId() == $newProduct['ID'])
			{
				$existBasketItem = $basketItem;
				break;
			}
		}
		unset($basketItem);

		if ($useMerge && $existBasketItem)
		{
			$result = $existBasketItem->setField(
				'QUANTITY',
				$existBasketItem->getQuantity() + $currentBasketItem->getQuantity()
			);
			$currentBasketItem->delete();
		}
		else
		{
			if (mb_strpos($newProduct['XML_ID'], '#') === false)
			{
				$parentData = Iblock\ElementTable::getList([
					'select' => ['ID', 'XML_ID'],
					'filter' => ['ID' => $parent['ID']],
				])->fetch()
				;
				if (!empty($parentData))
					$newProduct['XML_ID'] = $parentData['XML_ID'].'#'.$newProduct['XML_ID'];
				unset($parentData);
			}

			$result = $currentBasketItem->setFields([
				'PRODUCT_ID' => $newProduct['ID'],
				'NAME' => $newProduct['NAME'],
				'PRODUCT_XML_ID' => $newProduct['XML_ID'],
			]);
			if (!$result->isSuccess())
				return $result;

			$result = $basket->refresh(Basket\RefreshFactory::createSingle($currentBasketItem->getBasketCode()));
			if (!$result->isSuccess())
				return $result;

			$newProperties = CIBlockPriceTools::GetOfferProperties(
				$newProduct['ID'],
				$parent['IBLOCK_ID'],
				$this->offersProps
			);

			$offerProperties = [];
			foreach ($newProperties as $row)
			{
				$codeExist = false;
				foreach ($this->offersProps as $code)
				{
					if ($code == $row['CODE'])
					{
						$codeExist = true;
						break;
					}
				}
				unset($code);

				if (!$codeExist)
					continue;

				$offerProperties[$row['CODE']] = [
					'NAME' => $row['NAME'],
					'CODE' => $row['CODE'],
					'VALUE' => $row['VALUE'],
					'SORT' => $row['SORT'],
				];
			}
			unset($row);

			$offerProperties['PRODUCT.XML_ID'] = [
				'NAME' => 'Product XML_ID',
				'CODE' => 'PRODUCT.XML_ID',
				'VALUE' => $currentBasketItem->getField('PRODUCT_XML_ID'),
			];

			$properties = $currentBasketItem->getPropertyCollection();
			$oldProperties = $properties->getPropertyValues();

			if (empty($oldProperties))
			{
				$oldProperties = $offerProperties;
			}
			else
			{
				$oldProperties = $this->updateOffersProperties($oldProperties, $offerProperties, $this->offersProps);
			}

			$properties->setProperty($oldProperties);
			unset($offerProperties);
		}

		return $result;
	}

	public function changeProductOffer($basketId, $searchType, $searchData, $useMerge = true)
	{
		$result = $this->changeProductOfferWithoutSave($basketId, $searchType, $searchData, $useMerge);

		if ($result->isSuccess())
		{
			$basket = $this->getBasketStorage()->getBasket();
			$res = $basket->save();
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	protected function selectOfferByProps($iblockId, $productId, $currentOfferId, array $propertyValues, array $properties)
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return null;

		$currentOfferId = (int)$currentOfferId;
		if ($currentOfferId <= 0)
			return null;

		if (empty($properties))
			return null;

		$codeList = array_keys($properties);
		$clearProperties = [];
		foreach ($codeList as $code)
		{
			if (isset($propertyValues[$code]) && is_string($propertyValues[$code]) && $propertyValues[$code] !== '')
				$clearProperties[$code] = $propertyValues[$code];
			else
				unset($properties[$code]);
			if ($clearProperties[$code] === '-')
			{
				$clearProperties[$code] = '';
			}
		}
		unset($code);
		$propertyValues = $clearProperties;
		unset($clearProperties);
		if (empty($propertyValues))
			return null;
		$codeList = array_keys($properties);

		$offers = CCatalogSku::getOffersList(
			$productId,
			$iblockId,
			[
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CATALOG_AVAILABLE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			['ID', 'IBLOCK_ID', 'XML_ID', 'NAME'],
			['CODE' => $codeList]
		);

		if (empty($offers[$productId]))
			return null;

		$offerList = [];
		foreach (array_keys($offers[$productId]) as $offerId)
		{
			$offerList[$offerId] = [
				'ID' => $offers[$productId][$offerId]['ID'],
				'IBLOCK_ID' => $offers[$productId][$offerId]['IBLOCK_ID'],
				'XML_ID' => $offers[$productId][$offerId]['XML_ID'],
				'PROPERTIES' => $offers[$productId][$offerId]['PROPERTIES'],
			];
		}
		unset($offerId, $offers);

		$result = null;

		$offersIndex = array_keys($offerList);
		foreach ($offersIndex as $offerId)
		{
			$data = $offerList[$offerId]['PROPERTIES'];

			$found = true;
			foreach ($propertyValues as $code => $value)
			{
				if ($data[$code]['~VALUE'] != $value)
				{
					$found = false;
					break;
				}
			}
			unset($code, $value);
			if ($found)
			{
				$result = $offerId;
				break;
			}
			unset($found, $data);
		}
		unset($offerId);
		if ($result === $currentOfferId)
			return null;
		if ($result === null)
		{
			$needValues = [];
			foreach ($codeList as $code)
			{
				$id = $properties[$code]['ID'];
				$needValues[$id] = [];
				foreach ($offersIndex as $offerId)
				{
					$valueId = (
					$offerList[$offerId]['PROPERTIES'][$code]['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST
						? $offerList[$offerId]['PROPERTIES'][$code]['VALUE_ENUM_ID']
						: $offerList[$offerId]['PROPERTIES'][$code]['VALUE']
					);
					if ($valueId == '')
						continue;
					$needValues[$id][$valueId] = $valueId;
					unset($valueId);
				}
				unset($offerId);
			}
			unset($code);

			\CIBlockPriceTools::getTreePropertyValues($properties, $needValues);
			unset($needValues);

			foreach ($codeList as $code)
			{
				if ($properties[$code]['VALUES_COUNT'] < 2)
					continue;
				$currentOffers = [];
				$existValues = [];
				foreach (array_keys($offerList) as $offerId)
				{
					$data = $offerList[$offerId]['PROPERTIES'][$code];
					if ($data['VALUE'] == $propertyValues[$code])
						$currentOffers[$offerId] = $offerList[$offerId];
					$valueId = null;
					switch ($data['PROPERTY_TYPE'])
					{
						case Iblock\PropertyTable::TYPE_ELEMENT:
							$valueId = ($data['VALUE'] == '' ? 0 : (int)$data['VALUE']);
							break;
						case Iblock\PropertyTable::TYPE_LIST:
							$valueId = ($data['VALUE_ENUM_ID'] == '' ? 0 : (int)$data['VALUE_ENUM_ID']);
							break;
						case Iblock\PropertyTable::TYPE_STRING:
							if ($data['USER_TYPE'] == 'directory')
							{
								if ($data['VALUE'] == '')
									$valueId = 0;
								else
									$valueId = $properties[$code]['XML_MAP'][$data['VALUE']];
							}
							break;
					}
					unset($data);
					if ($valueId !== null)
					{
						if (!isset($existValues[$valueId]))
							$existValues[$valueId] = [];
						$existValues[$valueId][] = $offerId;
					}
					unset($valueId);
				}
				if (empty($currentOffers))
				{
					if (empty($existValues))
						continue;
					foreach (array_keys($properties[$code]['VALUES']) as $valueId)
					{
						if (isset($existValues[$valueId]))
						{
							foreach ($existValues[$valueId] as $offerId)
								$currentOffers[$offerId] = $offerList[$offerId];
							unset($offerId);
						}
					}
				}
				$offerList = $currentOffers;
				unset($currentOffers);
			}
			unset($code);
			reset($offerList);
			$result = key($offerList);
			if ($result === $currentOfferId)
				return null;
		}

		if (
			$result
			&&\Bitrix\Main\Loader::includeModule('sale')
		)
		{
			\Bitrix\Sale\Internals\FacebookConversion::onCustomizeProductHandler((int)$result);
		}

		return ($result === null ? null : $offerList[$result]);
	}

	protected function selectOfferById($iblockId, $productId, $currentOfferId, $newOfferId, $propertyCodes)
	{
		return null;
	}

	/**
	 * @param array $itemProperties
	 * @param array $propertyCodes
	 * @return array
	 */
	protected static function getMissingPropertyCodes(array $itemProperties, array $propertyCodes)
	{
		if (empty($propertyCodes))
			return [];
		if (empty($itemProperties))
			return $propertyCodes;
		$result = array_fill_keys($propertyCodes, true);
		foreach ($itemProperties as &$property)
		{
			if (empty($property) || !is_array($property))
				continue;
			if (!isset($property['CODE']))
				continue;
			$code = trim((string)$property['CODE']);
			if ($code == '')
				continue;
			if (isset($result[$code]))
				unset($result[$code]);
		}
		unset($property);

		return (!empty($result) ? array_keys($result) : []);
	}

	/**
	 * @param array $basket
	 * @param int $basketId
	 * @return bool|int|string
	 */
	protected static function getBasketKeyById(array $basket, $basketId)
	{
		$result = false;

		$basketId = (int)$basketId;
		if ($basketId > 0)
		{
			if (!empty($basket) && is_array($basket))
			{
				foreach ($basket as $basketKey => $basketItem)
				{
					if (isset($basketItem['ID']) && $basketItem['ID'] == $basketId)
					{
						$result = $basketKey;
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $itemProperties
	 * @param array $missingCodes
	 * @param array $values
	 * @return void
	 */
	protected static function fillMissingProperties(array &$itemProperties, array $missingCodes, array $values)
	{
		if (empty($missingCodes))
			return;
		if (empty($values))
			return;
		foreach ($missingCodes as &$code)
		{
			if (!isset($values[$code]))
				continue;
			$found = false;
			foreach ($itemProperties as $existValue)
			{
				if (isset($existValue['CODE']) && $existValue['CODE'] == $code)
				{
					$found = true;
					break;
				}
			}
			unset($existValue);
			if (!$found)
				$itemProperties[] = $values[$code];
			unset($found);
		}
		unset($code);
	}

	protected static function updateOffersProperties($oldProps, $newProps, $offersProps)
	{
		if (!is_array($oldProps) || !is_array($newProps))
			return false;

		$result = [];
		if (empty($newProps))
			return $oldProps;
		if (empty($oldProps))
			return $newProps;
		foreach (array_keys($oldProps) as $code)
		{
			$oldValue = $oldProps[$code];
			$found = false;
			$key = false;
			$shortPropId = (string)($oldValue['CODE'] ?? '');
			$propId = (isset($oldValue['CODE']) ? (string)$oldValue['CODE'] : '').':'.$oldValue['NAME'];
			foreach ($newProps as $newKey => $newValue)
			{
				$newId = (isset($newValue['CODE']) ? (string)$newValue['CODE'] : '').':'.$newValue['NAME'];
				if ($newId == $propId)
				{
					$key = $newKey;
					$found = true;
					break;
				}
			}
			if ($found)
			{
				$oldValue['VALUE'] = $newProps[$key]['VALUE'];
				unset($newProps[$key]);
			}
			elseif ($shortPropId !== '' && in_array($shortPropId, $offersProps))
			{
				$oldValue = null;
			}
			if ($oldValue !== null)
			{
				$result[$code] = $oldValue;
			}
			unset($oldValue);
		}
		unset($code, $oldValue);

		if (!empty($newProps))
		{
			foreach (array_keys($newProps) as $code)
				$result[$code] = $newProps[$code];
			unset($code);
		}

		return $result;
	}

	protected function getFormatCurrencies()
	{
		$currencies = [];

		if (Loader::includeModule('currency'))
		{
			$currencyIterator = \Bitrix\Currency\CurrencyTable::getList([
				'select' => ['CURRENCY'],
			]);
			while ($currency = $currencyIterator->fetch())
			{
				$currencyFormat = \CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
				$currencies[] = [
					'CURRENCY' => $currency['CURRENCY'],
					'FORMAT' => [
						'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
						'DEC_POINT' => $currencyFormat['DEC_POINT'],
						'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
						'DECIMALS' => $currencyFormat['DECIMALS'],
						'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
						'HIDE_ZERO' => $currencyFormat['HIDE_ZERO'],
					],
				];
			}
		}

		return $currencies;
	}
}
