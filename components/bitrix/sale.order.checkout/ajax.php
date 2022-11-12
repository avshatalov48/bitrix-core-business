<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Sale;
use BItrix\Catalog;
use Bitrix\Rest;
use Bitrix\Sale\Helpers\Controller\Action\Entity\OrderPaymentResolver;
use Bitrix\Sale\PaySystem;
use Sale\Handlers\PaySystem\OrderDocumentHandler;

Main\Localization\Loc::loadMessages(__DIR__ . '/class.php');

/**
 * Class SaleOrderCheckoutAjaxController
 */
class SaleOrderCheckoutAjaxController extends Main\Engine\Controller
{
	private $params;
	private $actionName;
	private $config;

	protected function processBeforeAction(Main\Engine\Action $action)
	{
		if (!Main\Loader::includeModule('sale'))
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_MODULE_SALE_NOT_INSTALLED')));
			return false;
		}

		if (!Main\Loader::includeModule('iblock'))
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_MODULE_IBLOCK_NOT_INSTALLED')));
			return false;
		}

		if (!Main\Loader::includeModule('catalog'))
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_MODULE_CATALOG_NOT_INSTALLED')));
			return false;
		}

		$this->params = $this->getUnsignedParameters();
		$this->actionName = $action->getName();
		$this->config = $action->getConfig() ?? [];

		$arguments = $action->getArguments();

		// convert keys
		$converter = new Main\Engine\Response\Converter(
			Main\Engine\Response\Converter::KEYS
			| Main\Engine\Response\Converter::RECURSIVE
			| Main\Engine\Response\Converter::TO_SNAKE
			| Main\Engine\Response\Converter::TO_SNAKE_DIGIT
			| Main\Engine\Response\Converter::TO_UPPER
		);
		$arguments = $converter->process($arguments);

		$action->setArguments($arguments);

		return parent::processBeforeAction($action);
	}

	protected function processAfterAction(Main\Engine\Action $action, $result)
	{
		if (is_array($result) && empty($this->getErrors()))
		{
			$result = self::prepareJsonData($result);

			if (Main\Loader::includeModule('rest'))
			{
				$result = Rest\Integration\Externalizer::multiSortKeysArray($result);
			}
		}

		return $result;
	}

	private static function prepareJsonData(array $result): array
	{
		if (!empty($result['BASKET_ITEMS']))
		{
			$basketItems = $result['BASKET_ITEMS'];

			$sku = [];
			foreach ($basketItems as $index => $item)
			{
				if (empty($item['CATALOG_PRODUCT']['SKU']))
				{
					$sku[$index] = [];
				}
				else
				{
					$sku[$index]['tree'] = $item['CATALOG_PRODUCT']['SKU']['TREE'];
					$sku[$index]['parentProductId'] = $item['CATALOG_PRODUCT']['SKU']['PARENT_PRODUCT_ID'];
				}

				unset($basketItems[$index]['CATALOG_PRODUCT']['SKU']);
			}

			$result['BASKET_ITEMS'] = $basketItems;
		}

		$result = Main\Engine\Response\Converter::toJson()->process($result);

		if (!empty($result['basketItems']) && isset($sku))
		{
			$basketItems = $result['basketItems'];
			foreach ($basketItems as $index => $item)
			{
				$basketItems[$index]['catalogProduct']['sku'] = $sku[$index];
			}

			$result['basketItems'] = $basketItems;
		}

		return $result;
	}

	public function configureActions()
	{
		return [
			'paymentPay' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'initiatePay' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'userConsentRequest' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'getBasket' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'saveOrder' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'recalculateBasket' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
		];
	}

	private function fillBasketItems(array $basketItems): array
	{
		$urlPathToDetailProduct = $this->params['URL_PATH_TO_DETAIL_PRODUCT'] ?? '';
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
				$basketItems[$item]['DETAIL_PAGE_URL'] = $parent->getDetailUrl();
			}
		}

		return $basketItems;
	}

	private function fillOrderProperties(array $properties): array
	{
		foreach ($properties as $id => $property)
		{
			if ($property['IS_PAYER'] === 'Y')
			{
				$properties[$id]['TYPE'] = 'NAME';
			}
			elseif ($property['IS_PHONE'] === 'Y')
			{
				$properties[$id]['TYPE'] = 'PHONE';
			}
			elseif ($property['IS_EMAIL'] === 'Y')
			{
				$properties[$id]['TYPE'] = 'EMAIL';
			}
		}

		return $properties;
	}

	/**
	 * @param array $fields
	 *   orderId
	 *   accessCode
	 * @return void
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'paymentPay', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function paymentPayAction(array $fields): void
	{
		if (empty($fields['ORDER_ID']) || (int)$fields['ORDER_ID'] <= 0)
		{
			$this->addError(new Main\Error('orderId not found'));
		}

		if (empty($fields['ACCESS_CODE']))
		{
			$this->addError(new Main\Error('accessCode not found'));
		}

		if ($this->getErrors())
		{
			return;
		}

		$order = $this->loadOrder($fields['ORDER_ID']);

		if (!$order || $order->getHash() !== $fields['ACCESS_CODE'])
		{
			$this->addError(new Main\Error('access error'));
			return;
		}

		$event = new Main\Event(
			'sale',
			'onComponentSaleOrderCheckoutPaymentPayAction',
			[
				'ORDER' => $order,
			]
		);
		$event->send();
	}

	/**
	 * @param array $fields
	 *   orderId
	 *   paySystemId
	 *   accessCode
	 *   returnUrl
	 * @return ?array
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'initiatePay', {mode: 'ajax', data: { fields: {...} }});
	 */
	public function initiatePayAction(array $fields): ?array
	{
		$orderId = (int)$fields['ORDER_ID'];
		$paySystemId = isset($fields['PAY_SYSTEM_ID']) ? (int)$fields['PAY_SYSTEM_ID'] : null;

		if ($orderId <= 0)
		{
			$this->addError(new Main\Error('orderId not found'));
		}

		$payment = OrderPaymentResolver::findOrCreatePaymentEqualOrderSum($orderId, $paySystemId);

		if (!$payment)
		{
			$this->addError(new Main\Error('payment not found'));
			return null;
		}

		$fields['PAYMENT_ID'] = $payment->getId();
		$fields['PAY_SYSTEM_ID'] = $paySystemId ?: $payment->getPaymentSystemId();

		if ($this->isPaySystemOrderDocument($fields['PAY_SYSTEM_ID']))
		{
			$fields['template'] = 'template_download';
		}

		$action = new Sale\Controller\Action\Entity\InitiatePayAction($this->actionName, $this, $this->config);

		$result = $action->run($fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addError(new Main\Error('initiate pay error'));
			return null;
		}

		if (empty($result['html']) && $payment = $action->getPayment())
		{
			$result['fields'] = [
				'SUM_WITH_CURRENCY' => SaleFormatCurrency($payment->getSum(), $payment->getField('CURRENCY')),
				'PAY_SYSTEM_NAME' => htmlspecialcharsbx($payment->getPaymentSystemName()),
			];
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return Main\Engine\Response\Component|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'userConsentRequest', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function userConsentRequestAction(array $fields): ?Main\Engine\Response\Component
	{
		$action = new Sale\Controller\Action\Entity\UserConsentRequestAction($this->actionName, $this, $this->config);

		$result = $action->run($fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addErrors($errors);
			return null;
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'getBasketAction', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function getBasketAction(array $fields): ?array
	{
		$action = new Sale\Controller\Action\Entity\GetBasketAction($this->actionName, $this, $this->config);

		$fields['FUSER_ID'] = Sale\Fuser::getId();

		$result = $action->run($fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addErrors($errors);
			return null;
		}

		$basketItems = $result['BASKET_ITEMS'];
		$basketItems = $this->fillBasketItems($basketItems);

		$result['BASKET_ITEMS'] = $basketItems;

		return $result;
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'saveOrder', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function saveOrderAction(array $fields): ?array
	{
		$action = new Sale\Controller\Action\Entity\SaveOrderAction($this->actionName, $this, $this->config);

		if ($this->isUserAuthorized())
		{
			$fields['USER_ID'] = $this->getUserId();
		}

		$fields['FUSER_ID'] = Sale\Fuser::getId();

		$result = $action->run($fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			foreach ($errors as $error)
			{
				$errorCode = $error->getCode();

				if ((int)$errorCode === Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_PROPERTIES)
				{
					$this->addError(
						new Main\Error(
							$error->getMessage(),
							'PROPERTIES',
							$error->getCustomData()
						)
					);
				}

				if ((int)$errorCode === Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_USER)
				{
					$this->addError(new Main\Error($error->getMessage()));
				}
			}

			if (empty($this->getErrors()))
			{
				$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_SAVE_ORDER')));
			}

			return null;
		}

		$basketItems = $result['BASKET_ITEMS'];
		$basketItems = $this->fillBasketItems($basketItems);
		$result['BASKET_ITEMS'] = $basketItems;

		$result['PROPERTIES'] = $this->fillOrderProperties($result['PROPERTIES']);

		$this->onSaveOrder($result);

		return $result;
	}

	private function onSaveOrder(array $result): void
	{
		if (isset($result['USER']))
		{
			if ($result['USER']['IS_NEW'])
			{
				$this->authorizeUser($result['USER']['ID']);
			}

			if (!$this->isUserAuthorized())
			{
				$this->saveOrderToSession($result['ORDER']['ACCOUNT_NUMBER']);
			}
		}
	}

	private function loadOrder(int $id): ?Sale\Order
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		return $orderClassName::load($id);
	}

	private function saveOrderToSession($accountNumber): void
	{
		$session = Main\Application::getInstance()->getSession();

		$saleNumberAccountList = $session->get('SALE_ACCOUNT_NUMBER_LIST');
		if (!is_array($saleNumberAccountList))
		{
			$saleNumberAccountList = [];
		}

		$saleNumberAccountList[] = $accountNumber;

		$session->set('SALE_ACCOUNT_NUMBER_LIST', $saleNumberAccountList);
	}

	private function authorizeUser(int $userId): void
	{
		global $USER;
		$USER->Authorize($userId);
	}

	private function isUserAuthorized(): bool
	{
		global $USER;
		return $USER->IsAuthorized();
	}

	private function getUserId()
	{
		global $USER;
		return $USER->getId();
	}

	public function recalculateBasketAction(array $actions): ?array
	{
		$result = [
			'ACTIONS' => [],
			'BASKET_ITEMS' => [],
			'ORDER_PRICE_TOTAL' => [],
			'NEED_FULL_RECALCULATION' => 'N',
		];

		$basketHashBeforeRecalculation = self::getBasketHash(self::getBasket());

		$preparedActions = $this->prepareRecalculateBasketActions($actions);
		foreach ($preparedActions as $actionName => $actionValues)
		{
			switch ($actionName)
			{
				case 'OFFER':
					$offerItems = [];

					foreach ($actionValues as $index => $offerFields)
					{
						$offerItems[] = $offerFields['BASKET_ID'];
						$result['ACTIONS'][$index] = $this->makeRecalculateOfferBasket($offerFields);
					}

					break;

				case 'DELETE':
					foreach ($actionValues as $index => $basketItemId)
					{
						$result['ACTIONS'][$index] = $this->makeRecalculateDeleteBasket($basketItemId);
					}

					break;

				case 'QUANTITY':
					foreach ($actionValues as $index => $quantity)
					{
						$result['ACTIONS'][$index] = $this->makeRecalculateQuantityBasket($quantity['BASKET_ID'], $quantity['QUANTITY']);
					}

					break;

				case 'RESTORE':
					foreach ($actionValues as $index => $basketItemData)
					{
						$result['ACTIONS'][$index] = $this->makeRecalculateRestoreBasket($basketItemData);
					}

					break;

				default:
					$this->errorCollection->setError(new Main\Error('action '.mb_strtolower($actionName).' not found'));
					break;
			}
		}

		$basketData = $this->getBasketAction([
			'FUSER_ID' => Sale\Fuser::getId(),
			'SITE_ID' => Main\Application::getInstance()->getContext()->getSite(),
		]);

		$basketHashAfterRecalculation = self::getBasketHash(self::getBasket());

		if (isset($offerItems))
		{
			foreach ($offerItems as $offerItem)
			{
				unset(
					$basketHashBeforeRecalculation[$offerItem],
					$basketHashAfterRecalculation[$offerItem]
				);
			}
		}

		$result['BASKET_ITEMS'] = $basketData['BASKET_ITEMS'];
		$result['ORDER_PRICE_TOTAL'] = $basketData['ORDER_PRICE_TOTAL'];

		$result['NEED_FULL_RECALCULATION'] =
			self::isBasketHashChanged($basketHashBeforeRecalculation, $basketHashAfterRecalculation) ? 'Y' : 'N';

		return $result;
	}

	private function makeRecalculateOfferBasket(array $offerFields): array
	{
		$changeBasketItemResult = $this->changeBasketItem($offerFields['BASKET_ID'], $offerFields['PRODUCT_ID']);
		if ($changeBasketItemResult->isSuccess())
		{
			return [
				'TYPE' => 'offer',
				'FIELDS' => ['ID' => $offerFields['BASKET_ID']],
			];
		}

		return [
			'TYPE' => 'offer',
			'ERRORS' => $this->getRecalculateErrorStructure($changeBasketItemResult),
		];
	}

	private function makeRecalculateDeleteBasket(int $basketItemId): array
	{
		$deleteBasketItemResult = $this->deleteBasketItem($basketItemId);
		if ($deleteBasketItemResult->isSuccess())
		{
			return [
				'TYPE' => 'delete',
				'FIELDS' => ['ID' => $basketItemId],
			];
		}

		return [
			'TYPE' => 'delete',
			'ERRORS' => $this->getRecalculateErrorStructure($deleteBasketItemResult),
		];
	}

	private function makeRecalculateQuantityBasket(int $basketId, float $quantity): array
	{
		$updateBasketItemQuantityResult = $this->updateBasketItemQuantity($basketId, $quantity);
		if ($updateBasketItemQuantityResult->isSuccess())
		{
			return [
				'TYPE' => 'quantity',
				'FIELDS' => ['ID' => $basketId],
			];
		}

		return [
			'TYPE' => 'quantity',
			'ERRORS' => $this->getRecalculateErrorStructure($updateBasketItemQuantityResult),
		];
	}

	private function makeRecalculateRestoreBasket(array $basketItemData): array
	{
		$result = $this->checkRestoreFields($basketItemData);
		if ($result->isSuccess())
		{
			$result = $this->restoreBasketItem($basketItemData);
			if ($result->isSuccess())
			{
				$restoreBasketItemData = $result->getData();
				/** @var Sale\BasketItem $basketItem */
				$basketItem = $restoreBasketItemData['basketItem'];

				return [
					'TYPE' => 'restore',
					'FIELDS' => ['ID' => $basketItem->getId()],
				];
			}
		}

		return [
			'TYPE' => 'restore',
			'ERRORS' => $this->getRecalculateErrorStructure($result),
		];
	}

	private function prepareRecalculateBasketActions(array $actions): array
	{
		$result = [
			'DELETE' => [],
			'QUANTITY' => [],
			'RESTORE' => [],
			'OFFER' => [],
		];

		$actions = $this->sortRecalculateBasketAction($actions);

		$deleteList = [];
		foreach ($actions as $index => $n)
		{
			foreach ($n as $action)
			{
				$actionName = key($action);
				if (!isset($result[$actionName]))
				{
					continue;
				}

				$data = $action[$actionName]['FIELDS'];
				if (
					$actionName === 'DELETE'
					|| (isset($data['ID']) && \in_array($data['ID'], $deleteList))
				)
				{
					unset($result['QUANTITY'][$index]);

					if ($actionName !== 'DELETE')
					{
						continue;
					}
				}

				$value = null;

				if ($actionName === 'DELETE')
				{
					$deleteList[] = (int)$data['ID'];

					$value = (int)$data['ID'];
				}
				elseif ($actionName === 'QUANTITY')
				{
					$value = [
						'BASKET_ID' => (int)$data['ID'],
						'QUANTITY' => (float)$data['VALUE'],
					];
				}
				elseif ($actionName === 'RESTORE')
				{
					$value = [
						'PRODUCT_ID' => $data['PRODUCT']['ID'] ?? null,
						'QUANTITY' => $data['QUANTITY'] ?? null,
						'MODULE' => $data['MODULE'] ?? null,
						'PRODUCT_PROVIDER_CLASS' => $data['PRODUCT_PROVIDER_CLASS'] ?? null,
						'PROPS' => $data['PROPS'] ?? null,
					];
				}
				elseif ($actionName === 'OFFER')
				{
					$value = [
						'BASKET_ID' => (int)$data['ID'],
						'PRODUCT_ID' => (int)$data['FIELDS']['OFFER_ID'],
					];
				}

				$result[$actionName][$index] = $value;
			}
		}

		return $result;
	}

	private function sortRecalculateBasketAction(array $actions): array
	{
		ksort($actions);

		foreach ($actions as $key => $value)
		{
			ksort($value);
			$actions[$key] = $value;
		}

		return $actions;
	}

	private function checkRestoreFields(array $basketItemData): Sale\Result
	{
		$result = new Sale\Result();

		$restoreRequiredFields = [
			'PRODUCT_ID',
			'QUANTITY',
			'MODULE',
			'PRODUCT_PROVIDER_CLASS',
		];

		foreach ($restoreRequiredFields as $restoreRequiredField)
		{
			if (empty($basketItemData[$restoreRequiredField]))
			{
				$result->addError(new Main\Error("Field {$restoreRequiredField} is required for restore"));
			}
		}

		return $result;
	}

	private function getRecalculateErrorStructure(Sale\Result $result): array
	{
		$errors = [];
		foreach ($result->getErrors() as $error)
		{
			$errors[] = [
				'CODE' => $error->getCode(),
				'MESSAGE' => $error->getMessage(),
			];
		}

		return $errors;
	}

	private function deleteBasketItem(int $id): Sale\Result
	{
		$result = new Sale\Result();

		$action = new Sale\Controller\Action\Entity\DeleteBasketItemAction($this->actionName, $this, $this->config);

		$deleteBasketItemResult = $action->deleteBasketItem($id);
		if ($deleteBasketItemResult->isSuccess())
		{
			$result->setData($deleteBasketItemResult->getData());
		}
		else
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_DELETE_BASKET_ITEM')));
		}

		return $result;
	}

	private function updateBasketItemQuantity(int $id, float $quantity): Sale\Result
	{
		$result = new Sale\Result();

		$action = new Sale\Controller\Action\Entity\UpdateBasketItemAction($this->actionName, $this, $this->config);

		$updateBasketItemResult = $action->updateBasketItem($id, ['QUANTITY' => $quantity]);
		if ($updateBasketItemResult->isSuccess())
		{
			$result->setData($updateBasketItemResult->getData());
		}
		else
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_UPDATE_BASKET_ITEM_QUANTITY')));
		}

		return $result;
	}

	private function restoreBasketItem(array $basketItemData): Sale\Result
	{
		$result = new Sale\Result();

		$action = new Sale\Controller\Action\Entity\AddBasketItemAction($this->actionName, $this, $this->config);

		$fields['PRODUCT'] = $basketItemData;

		$fields['FUSER_ID'] = Sale\Fuser::getId();
		$fields['SITE_ID'] = Main\Application::getInstance()->getContext()->getSite();

		$addBasketItemResult = $action->addBasketItem($fields);
		if ($addBasketItemResult->isSuccess())
		{
			$result->setData($addBasketItemResult->getData());
		}
		else
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_RESTORE_BASKET_ITEM')));
		}

		return $result;
	}

	private function changeBasketItem(int $basketId, int $productId): Sale\Result
	{
		$result = new Sale\Result();

		$action = new Sale\Controller\Action\Entity\ChangeBasketItemAction($this->actionName, $this, $this->config);

		$fields = [
			'BASKET_ID' => $basketId,
			'PRODUCT_ID' => $productId,
			'FUSER_ID' => Sale\Fuser::getId(),
			'SITE_ID' => Main\Application::getInstance()->getContext()->getSite(),
		];

		$changeBasketItemResult = $action->changeBasketItem($fields);
		if ($changeBasketItemResult->isSuccess())
		{
			$result->setData($changeBasketItemResult->getData());
		}
		else
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_CHANGE_BASKET_ITEM')));
		}

		return $result;
	}

	private static function getBasketHash(Sale\BasketBase $basket)
	{
		$basketHash = [];

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\BasketItem $basketItemClassName */
		$basketItemClassName = $registry->getBasketItemClassName();

		$settableFieldsMap = $basketItemClassName::getSettableFieldsMap();
		unset($settableFieldsMap['QUANTITY']);

		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$basketItemFields = $basketItem->getFieldValues();
			$basketItemFilteredFields = array_filter(
				$basketItemFields,
				static function ($fieldName) use ($settableFieldsMap) {
					return array_key_exists($fieldName, $settableFieldsMap);
				},
				ARRAY_FILTER_USE_KEY
			);

			ksort($basketItemFilteredFields);
			$basketHash[$basketItem->getId()] = md5(serialize($basketItemFilteredFields));
		}

		return $basketHash;
	}

	private static function getBasket(): Sale\BasketBase
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		$basket = $basketClassName::loadItemsForFUser(
			Sale\Fuser::getId(),
			Main\Application::getInstance()->getContext()->getSite()
		);

		$order = $basket->getOrder();
		if (!$order)
		{
			/** @var Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();
			$order = $orderClassName::create($basket->getSiteId());
			$order->setBasket($basket);
		}

		if ($order)
		{
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

		return $basket;
	}

	private static function isBasketHashChanged(array $originalHash, array $recalculatedHash): bool
	{
		foreach ($originalHash as $basketItemId => $basketItemHash)
		{
			if (
				isset($recalculatedHash[$basketItemId])
				&& $basketItemHash !== $recalculatedHash[$basketItemId]
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $paySystemId
	 * @return bool
	 */
	private function isPaySystemOrderDocument(int $paySystemId): bool
	{
		$handlerClassName = PaySystem\Manager::getFolderFromClassName(OrderDocumentHandler::class);

		$service = PaySystem\Manager::getObjectById($paySystemId);

		return $service && $handlerClassName === $service->getField('ACTION_FILE');
	}
}
