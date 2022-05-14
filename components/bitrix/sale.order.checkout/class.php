<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Cashbox;
use Bitrix\Catalog;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Class SaleOrderCheckout
 */
class SaleOrderCheckout extends \CBitrixComponent
{
	/** @var Sale\Order */
	private $order;

	/** @var Sale\Basket\Storage $basketStorage */
	private $basketStorage;

	/** @var Main\ErrorCollection */
	private $errorCollection;

	public function onPrepareComponentParams($arParams): array
	{
		$this->errorCollection = new Main\ErrorCollection();

		if (empty($arParams['CONTEXT_SITE_ID']))
		{
			$arParams['CONTEXT_SITE_ID'] = null;
		}

		if (!isset($arParams['IS_LANDING_SHOP']))
		{
			if (
				$arParams['CONTEXT_SITE_ID']
				&& Main\ModuleManager::isModuleInstalled('intranet')
				&& Loader::includeModule('crm')
			)
			{
				$arParams['IS_LANDING_SHOP'] = 'Y';
			}
			else
			{
				$arParams['IS_LANDING_SHOP'] = 'N';
			}
		}

		if (!Loader::includeModule('crm'))
		{
			$arParams['IS_LANDING_SHOP'] = 'N';
		}

		$arParams['IS_LANDING_SHOP'] = $arParams['IS_LANDING_SHOP'] === 'Y' ? 'Y' : 'N';

		$arParams['URL_PATH_TO_DETAIL_PRODUCT'] =
			!empty($arParams['URL_PATH_TO_DETAIL_PRODUCT'])
				? $arParams['URL_PATH_TO_DETAIL_PRODUCT']
				: ''
		;

		$arParams['URL_PATH_TO_MAIN_PAGE'] =
			!empty($arParams['URL_PATH_TO_MAIN_PAGE'])
				? $arParams['URL_PATH_TO_MAIN_PAGE']
				: ''
		;

		return parent::onPrepareComponentParams($arParams);
	}

	protected function listKeysSignedParameters(): array
	{
		return [
			'URL_PATH_TO_DETAIL_PRODUCT',
		];
	}

	public function executeComponent(): void
	{
		$this->checkModules();
		if (!$this->isEmptyError())
		{
			$this->prepareErrorResultArray();
			$this->includeComponentTemplate('error');
			return;
		}

		$this->initResult();

		$action = $this->getAction();
		$this->doAction($action);

		$this->prepareResultArray();

		if ($this->isEmptyError())
		{
			if ($this->needShowEmptyBasket($this->order))
			{
				$this->includeComponentTemplate('empty');
			}
			else
			{
				$this->prepareJsonData();
				$this->includeComponentTemplate();
			}
		}
		else
		{
			$this->includeComponentTemplate('error');
		}
	}

	private function checkModules(): void
	{
		if (!Loader::includeModule('sale'))
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_MODULE_SALE_NOT_INSTALLED')));
		}

		if (!Loader::includeModule('iblock'))
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_MODULE_IBLOCK_NOT_INSTALLED')));
		}

		if (!Loader::includeModule('catalog'))
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_MODULE_CATALOG_NOT_INSTALLED')));
		}
	}

	private function getAction(): string
	{
		$action = 'processOrder';

		if (!$this->arResult['IS_NEW_ORDER'])
		{
			$action = 'showOrder';
		}

		return $action;
	}

	private function doAction(string $action): void
	{
		if ($this->actionExists($action))
		{
			$this->{$action.'Action'}();
		}
	}

	private function actionExists(string $action): bool
	{
		return is_callable([$this, $action.'Action']);
	}

	private function processOrderAction(): void
	{
		$userId = $this->getUserId();
		$this->order = $this->createOrder($userId);
	}

	private function showOrderAction(): void
	{
		$accountNumber = $this->arResult['ACCOUNT_NUMBER'];

		$this->loadOrderByAccountNumber($accountNumber);

		if ($this->order)
		{
			$this->checkAccess($this->order);
		}
		else
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_ORDER_NOT_FOUND')));
		}
	}

	private function loadOrderByAccountNumber($accountNumber): void
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$this->order = $orderClassName::loadByAccountNumber($accountNumber);
	}

	private function checkAccess(Sale\Order $order)
	{
		$access = $this->request->get('access');
		if ($access !== $order->getHash())
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_ACCESS_DENIED')));
		}
	}

	private function initResult(): void
	{
		$accountNumber = $this->request->get('accountNumber');
		$mode = $this->request->get('mode');

		$this->arResult = [
			'USER_CONSENT_PROPERTY_DATA' => [],
			'CURRENCY' => Sale\Internals\SiteCurrencyTable::getSiteCurrency($this->getSiteId()),
			'IS_NEW_ORDER' => $accountNumber ? false : true,
			'ACCOUNT_NUMBER' => $accountNumber ?? null,
			'MODE' => $mode,
			'TRADING_PLATFORM_ID' => null,
			'JSON_DATA' => [],
			'ERRORS' => [],
		];
	}

	private function prepareResultArray(): void
	{
		if ($this->arParams['USER_CONSENT'] === 'Y' && $this->order)
		{
			$this->obtainUserConsentInfo($this->order);
		}

		if ($this->arParams['IS_LANDING_SHOP'] === 'Y' && $this->arParams['CONTEXT_SITE_ID'])
		{
			$this->arResult['TRADING_PLATFORM_ID'] = $this->getTradingPlatformId($this->arParams['CONTEXT_SITE_ID']);
		}

		$this->prepareErrorResultArray();
	}

	private function prepareErrorResultArray(): void
	{
		if (!$this->errorCollection->isEmpty())
		{
			/** @var Main\Error $error */
			foreach ($this->errorCollection as $error)
			{
				$this->arResult['ERRORS'][] = $error->getMessage();
			}
		}
	}

	private function createOrder(int $userId): Sale\Order
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::create($this->getSiteId(), $userId);
		$order->isStartField();

		$this->initPersonType($order);

		$this->initBasket($order);

		$contextSiteId = $this->arParams['CONTEXT_SITE_ID'];
		if ($contextSiteId)
		{
			$this->initTradeBinding($order, $contextSiteId);
		}

		$order->doFinalAction(true);

		return $order;
	}

	private function initBasket(Sale\Order $order): void
	{
		$basket = $this->loadBasket();

		$refreshResult = $this->refreshBasket($basket);
		if (!$refreshResult->isSuccess())
		{
			return;
		}

		$this->refreshBasketRatio($basket);

		if ($basket->isChanged())
		{
			$basket->save();
		}

		$availableBasket = $basket->getOrderableItems();
		$order->appendBasket($availableBasket);
	}

	private function loadBasket(): Sale\Basket
	{
		return $this->getBasketStorage()->getBasket();
	}

	private function getBasketStorage(): Sale\Basket\Storage
	{
		if (!isset($this->basketStorage))
		{
			$this->basketStorage = Sale\Basket\Storage::getInstance(Sale\Fuser::getId(), $this->getSiteId());
		}

		return $this->basketStorage;
	}

	private function refreshBasketRatio(Sale\Basket $basket): void
	{
		Sale\BasketComponentHelper::correctQuantityRatio($basket);
	}

	private function refreshBasket(Sale\Basket $basket): Sale\Result
	{
		$refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);

		$result = $basket->refresh($refreshStrategy);
		if (!$result->isSuccess())
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_BASKER_REFRESH_ERROR')));
		}

		return $result;
	}

	private function initPersonType(Sale\Order $order): void
	{
		$personTypeId = $this->getPersonTypeId($this->getSiteId());
		if ($personTypeId)
		{
			$order->setPersonTypeId($personTypeId);
		}
		else
		{
			$this->addError(new Main\Error(Loc::getMessage('SOC_PERSON_TYPE_NOT_FOUND')));
		}
	}

	private function initTradeBinding(Sale\Order $order, $contextSiteId): void
	{
		if (!Loader::includeModule('landing'))
		{
			return;
		}

		$code = Sale\TradingPlatform\Landing\Landing::getCodeBySiteId($contextSiteId);

		$platform = Sale\TradingPlatform\Landing\Landing::getInstanceByCode($code);
		if (!$platform->isInstalled())
		{
			return;
		}

		$collection = $order->getTradeBindingCollection();
		$collection->createItem($platform);
	}

	private function getUserId(): int
	{
		global $USER;

		$userId = null;

		if ($USER->IsAuthorized())
		{
			$userId = $USER->GetID();
		}
		else
		{
			$order = $this->getOrderFromSession();
			if ($order)
			{
				$userId = $this->getUserIdFromOrder($order);
			}
		}

		return (int)$userId;
	}

	private function getUserIdFromOrder(Sale\Order $order): int
	{
		return (int)$order->getUserId();
	}

	private function getOrderFromSession(): ?Sale\Order
	{
		$order = null;

		$session = Main\Application::getInstance()->getSession();

		if ($session->has('SALE_ACCOUNT_NUMBER_LIST'))
		{
			$saleNumberAccountList = $session->get('SALE_ACCOUNT_NUMBER_LIST');
			if (\is_array($saleNumberAccountList))
			{
				$accountNumber = end($saleNumberAccountList);

				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
				/** @var Sale\Order $orderClassName */
				$orderClassName = $registry->getOrderClassName();
				$order = $orderClassName::loadByAccountNumber($accountNumber);
			}
		}

		return $order ?: null;
	}

	private function prepareJsonData(): void
	{
		$aggregateData = Sale\Helpers\Controller\Action\Entity\Order::getAggregateOrder($this->order);
		if ($this->order->isNew() === false)
		{
			$aggregateData += ['HASH' => $this->order->getHash()];
		}

		foreach ($aggregateData['PROPERTIES'] as $id => $property)
		{
			if ($property['IS_PAYER'] === 'Y')
			{
				$aggregateData['PROPERTIES'][$id]['TYPE'] = 'NAME';
			}
			elseif ($property['IS_PHONE'] === 'Y')
			{
				$aggregateData['PROPERTIES'][$id]['TYPE'] = 'PHONE';
			}
			elseif ($property['IS_EMAIL'] === 'Y')
			{
				$aggregateData['PROPERTIES'][$id]['TYPE'] = 'EMAIL';
			}
		}

		if (!empty($aggregateData['BASKET_ITEMS']))
		{
			$aggregateData['BASKET_ITEMS'] = $this->fillBasketItems($aggregateData['BASKET_ITEMS']);
		}

		$model = $this->getModel($aggregateData);
		$jsonData = [
			'MODEL' => $model,
			'SCHEME' => $this->getScheme($aggregateData, $model),
			'PARAMETERS' => $this->getParameters(),
		];

		$jsonData = $this->onComponentSaleOrderCheckoutPrepareJsonDataEvent($jsonData);

		$sku = [];
		$basket = $jsonData['SCHEME']['BASKET'];
		foreach ($basket as $index => $item)
		{
			if (empty($item['SKU']))
			{
				$sku[$index] = [];
			}
			else
			{
				$sku[$index]['tree'] = $item['SKU']['TREE'];
				$sku[$index]['parentProductId'] = $item['SKU']['PARENT_PRODUCT_ID'];
			}

			unset($basket[$index]['SKU']);
		}

		$jsonData['SCHEME']['BASKET'] = $basket;

		foreach ($jsonData as $key => $data)
		{
			$jsonData[$key] = Main\Engine\Response\Converter::toJson()->process($data);
		}

		$basket = $jsonData['SCHEME']['basket'];
		foreach ($sku as $index => $value)
		{
			$basket[$index]['sku'] = $value;
		}

		$jsonData['SCHEME']['basket'] = $basket;

		$this->arResult['JSON_DATA'] = $jsonData;
	}

	private function getModel(array $aggregateData = []): array
	{
		$basketItems = [];

		foreach ($aggregateData['BASKET_ITEMS'] as $item)
		{
			$basketItems[$item['ID']] = [
				'ID' => $item['ID'],
				'PRODUCT_ID' => $item['PRODUCT_ID'],
				'QUANTITY' => $item['QUANTITY'],
			];
		}

		$properties = [];

		foreach ($aggregateData['PROPERTIES'] as $item)
		{
			$values = array_filter($aggregateData['USER_PROFILE_VALUES'], static function ($profileValue) use ($item) {
				return (int)$item['ID'] === (int)$profileValue['ORDER_PROPS_ID'];
			});

			$value = (($item['MULTIPLE'] === 'Y') ? $item['VALUE'] : reset($item['VALUE']));

			if ($values)
			{
				$value = current($values)['VALUE'];
			}

			$properties[$item['ID']] = $value;
		}

		$payments = [];
		if (isset($aggregateData['ORDER']['PAYMENTS']))
		{
			foreach ($aggregateData['ORDER']['PAYMENTS'] as $payment)
			{
				$payments[$payment['ID']] = [
					'ID'=>$payment['ID'],
					'SUM'=>$payment['SUM'],
				];
			}
		}

		return [
			'BASKET_ITEMS' => $basketItems,
			'PROPERTIES' => $properties,
			'PAYMENTS' => $payments,
			'TRADING_PLATFORM_ID' => $this->arResult['TRADING_PLATFORM_ID'],
		];
	}

	private function getScheme(array $aggregateData = [], array $model = []): array
	{
		$currency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($this->getSiteId());

		$scheme = [
			'ORDER' => [
				'HASH' => $aggregateData['HASH'],
				'ID' => $aggregateData['ORDER']['ID'],
				'ACCOUNT_NUMBER' => $aggregateData['ORDER']['ACCOUNT_NUMBER'],
				'PAID' => $aggregateData['ORDER']['PAYED'] === 'Y' ? 'Y' : 'N',
			],
			'USER_ID' => $this->getUserId(),
			'SITE_ID' => $this->getSiteId(),
			'PAY_SYSTEMS' => [],
			'FUSER_ID' => Sale\Fuser::getId(),
			'CURRENCY' => $currency,
			'PERSON_TYPE_ID' => $this->getPersonTypeId($this->getSiteId()),
			'BASKET' => [],
			'PROPERTIES' => [],
			'TOTAL' => [],
			'DISCOUNT' => [],
			'PAYMENTS' => [],
			'CHECK' => []
		];

		if (!empty($aggregateData['PAY_SYSTEMS']))
		{
			$scheme['PAY_SYSTEMS'] = array_values(array_filter($aggregateData['PAY_SYSTEMS'], static function ($paySystem) {
				return ($paySystem['ACTION_FILE'] !== 'inner');
			}));

			array_walk($scheme['PAY_SYSTEMS'], static function(&$item){
				$item['TYPE'] = self::resolvePaySystemTypeByCode($item['IS_CASH']);
			});
		}

		foreach ($aggregateData['BASKET_ITEMS'] as $basketItem)
		{
			$scheme['BASKET'][] = [
				'ID' => $basketItem['ID'],
				'NAME' => $basketItem['NAME'],
				'QUANTITY' => $basketItem['QUANTITY'],
				'MEASURE_TEXT' => $basketItem['MEASURE_TEXT'],
				'PRICE' => $basketItem['PRICE'],
				'BASE_PRICE' => $basketItem['BASE_PRICE'],
				'SUM' => $basketItem['SUM'],
				'BASE_SUM' => $basketItem['SUM_BASE'],
				'CURRENCY' => $basketItem['CURRENCY'],
				'PRODUCT_PROVIDER_CLASS' => $basketItem['PRODUCT_PROVIDER_CLASS'],
				'MODULE' => $basketItem['MODULE'],
				'DISCOUNT' => [
					'SUM' => $basketItem['SUM_DISCOUNT_DIFF'],
					'PRICE' => $basketItem['DISCOUNT_PRICE'],
				],
				'PROPS' => $basketItem['PROPS'],
				'PRODUCT' => [
					'ID' => $basketItem['CATALOG_PRODUCT']['ID'],
					'DETAIL_PAGE_URL' => $basketItem['DETAIL_PAGE_URL'],
					'PICTURE' => $basketItem['CATALOG_PRODUCT']['FRONT_IMAGE']['SRC'],
					'AVAILABLE_QUANTITY' => $basketItem['CATALOG_PRODUCT']['AVAILABLE_QUANTITY'],
					'RATIO' => $basketItem['CATALOG_PRODUCT']['RATIO'],
					'CHECK_MAX_QUANTITY' => $basketItem['CATALOG_PRODUCT']['CHECK_MAX_QUANTITY'],
				],
				'SKU' => $basketItem['CATALOG_PRODUCT']['SKU'],
			];
		}

		foreach ($aggregateData['PROPERTIES'] as $property)
		{
			$scheme['PROPERTIES'][] = [
				'ID' => $property['ID'],
				'NAME' => $property['NAME'],
				'TYPE' => $property['TYPE'],
				'VALUE' => $model['PROPERTIES'][$property['ID']] ?? null,
			];
		}

		$orderPriceTotal = $aggregateData['ORDER_PRICE_TOTAL'];
		$scheme['TOTAL'] = [
			'PRICE' => $orderPriceTotal['ORDER_PRICE'],
			'BASE_PRICE' => $orderPriceTotal['PRICE_WITHOUT_DISCOUNT_VALUE'],
		];

		$scheme['DISCOUNT'] = [
			'SUM' => $orderPriceTotal['BASKET_PRICE_DISCOUNT_DIFF_VALUE'],
		];

		$culture = Main\Context::getCurrent()->getCulture();

		foreach ($aggregateData['PAYMENTS'] as $payment)
		{
			$scheme['PAYMENTS'][] = [
				'ID' => $payment['ID'],
				'SUM' => $payment['SUM'],
				'PAID' => $payment['PAID'],
				'CURRENCY' => $payment['CURRENCY'],
				'PAY_SYSTEM_ID' => $payment['PAY_SYSTEM_ID'],
				'ACCOUNT_NUMBER' => $payment['ACCOUNT_NUMBER'],
				'DATE_BILL_FORMATTED' => \FormatDate($culture->getLongDateFormat(), $payment['DATE_BILL']->getTimestamp()),
			];
		}

		foreach ($aggregateData['CHECKS'] as $check)
		{
			$scheme['CHECK'][] = [
				'ID' => $check['ID'],
				'DATE_FORMATTED' => \FormatDate($culture->getLongDateFormat(), $check['DATE_CREATE']->getTimestamp()),
				'LINK' => $check['LINK'],
				'STATUS' => $check['STATUS'],
				'PAYMENT_ID' => $check['PAYMENT_ID']
			];
		}

		return $scheme;
	}

	private function getParameters(): array
	{
		$parameters = $this->arParams;
		$parameters['PAY_SYSTEM_RETURN_URL'] = (new Sale\PaySystem\Context())->getUrl();
		$parameters['USER_CONSENT_PROPERTY_DATA'] = $this->arResult['USER_CONSENT_PROPERTY_DATA'];

		return $parameters;
	}

	private function obtainUserConsentInfo(Sale\Order $order): void
	{
		$propertyNames = [];

		foreach ($order->getPropertyCollection() as $property)
		{
			$propertyNames[] = $property->getName();
		}

		$this->arResult['USER_CONSENT_PROPERTY_DATA'] = $propertyNames;
	}

	private function fillBasketItems(array $basketItems): array
	{
		$urlPathToDetailProduct = $this->arParams['URL_PATH_TO_DETAIL_PRODUCT'];
		if ($urlPathToDetailProduct)
		{
			$basketItems = $this->setDetailPageUrlForBasketItems($basketItems, $urlPathToDetailProduct);
		}

		return $basketItems;
	}

	private function setDetailPageUrlForBasketItems(array $basketItems, string $pathToDetailProductUrl): array
	{
		$repositoryFacade = Catalog\v2\IoC\ServiceContainer::getRepositoryFacade();
		$repositoryFacade->setDetailUrlTemplate($pathToDetailProductUrl);

		foreach ($basketItems as $item => $basketItem)
		{
			$product = $repositoryFacade->loadVariation($basketItem['PRODUCT_ID']);
			if ($product)
			{
				/** @var Catalog\v2\Product\Product $parent */
				$parent = $product->getParent();
				if ($parent instanceof Catalog\v2\Product\Product)
				{
					$basketItems[$item]['DETAIL_PAGE_URL'] = $parent->getDetailUrl();
				}
				else
				{
					$basketItems[$item]['DETAIL_PAGE_URL'] = '';
				}
			}
		}

		return $basketItems;
	}

	private function addError(Main\Error $error): void
	{
		$this->errorCollection->setError($error);
	}

	private function isEmptyError(): bool
	{
		return $this->errorCollection->isEmpty();
	}

	private function needShowEmptyBasket(Sale\Order $order): bool
	{
		return $order->isNew() && $order->getBasket() && $order->getBasket()->isEmpty();
	}

	private function getTradingPlatformId($contextSiteId): ?int
	{
		if (Main\Loader::includeModule('landing'))
		{
			$code = Sale\TradingPlatform\Landing\Landing::getCodeBySiteId($contextSiteId);
			$platform = Sale\TradingPlatform\Landing\Landing::getInstanceByCode($code);
			if ($platform->isInstalled())
			{
				return (int)$platform->getId();
			}
		}

		return null;
	}

	private function getPersonTypeId(string $siteId): ?int
	{
		static $result;

		if (!empty($result[$siteId]))
		{
			return $result[$siteId];
		}

		$personType = Sale\Internals\BusinessValuePersonDomainTable::getList([
			'select' => ['PERSON_TYPE_ID'],
			'filter' => [
				'=DOMAIN' => Sale\BusinessValue::INDIVIDUAL_DOMAIN,
				'=PERSON_TYPE_REFERENCE.ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER,
				'=PERSON_TYPE_REFERENCE.LID' => $siteId,
				'=PERSON_TYPE_REFERENCE.ACTIVE' => 'Y',
			],
			'order' => [
				'PERSON_TYPE_REFERENCE.SORT' => 'ASC'
			],
			'limit' => 1,
		])->fetch();

		$personTypeId = $personType ? (int)$personType['PERSON_TYPE_ID'] : null;

		$result[$siteId] = $personTypeId;

		return $personTypeId;
	}

	private static function resolvePaySystemTypeByCode($type): string
	{
		switch($type)
		{
			case 'Y':
				$resolveType = 'CASH';
				break;
			case 'N':
				$resolveType = 'CASH_LESS';
				break;
			case 'A':
				$resolveType = 'CARD_TRANSACTION';
				break;
			default;
				$resolveType = 'UNDEFINED';
		}
		return $resolveType;
	}

	private function onComponentSaleOrderCheckoutPrepareJsonDataEvent(array $jsonData): array
	{
		$eventResult = GetModuleEvents('sale', 'onComponentSaleOrderCheckoutPrepareJsonData');
		while ($event = $eventResult->fetch())
		{
			ExecuteModuleEventEx($event, [&$jsonData]);
		}

		return $jsonData;
	}
}