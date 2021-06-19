<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Sale;
use BItrix\Catalog;
use Bitrix\Rest;
use Bitrix\Crm;

Main\Localization\Loc::loadMessages(__DIR__ . '/class.php');

/**
 * Class SaleOrderCheckoutAjaxController
 */
class SaleOrderCheckoutAjaxController extends Main\Engine\Controller
{
	protected $params;
	protected $actionName;
	protected $config;

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
		if (empty($action->getErrors()))
		{
			if (\is_array($result) && Main\Loader::includeModule('rest'))
			{
				$result = Rest\Integration\Externalizer::multiSortKeysArray($result);
			}

			$result = Main\Engine\Response\Converter::toJson()->process($result);
		}

		return $result;
	}

	public function configureActions()
	{
		return [
			'addbasketitem' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'deleteBasketItem' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'updateBasketItem' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'paymentPay' => [
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

	protected function fillBasketItems(array $basketItems): array
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
	 * @deprecated
	 * @see \SaleOrderCheckoutAjaxController::recalculateBasketAction
	 *
	 * @param array $fields
	 * @return array|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'addBasketItem', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function addBasketItemAction(array $fields): ?array
	{
		$action = new Sale\Controller\Action\Entity\AddBasketItemAction($this->actionName, $this, $this->config);

		$fields['FUSER_ID'] = Sale\Fuser::getId();

		$result = $action->run($fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_ADD_BASKET_ITEM')));
			return null;
		}

		$basketItems = [$result];
		$result = $this->fillBasketItems($basketItems);

		return $result[0];
	}

	/**
	 * @deprecated
	 * @see \SaleOrderCheckoutAjaxController::recalculateBasketAction
	 *
	 * @param int $id
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'deleteBasketItem', { mode: 'ajax', data: { id: ""}});
	 */
	public function deleteBasketItemAction(int $id)
	{
		$action = new Sale\Controller\Action\Entity\DeleteBasketItemAction($this->actionName, $this, $this->config);
		$action->run($id);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_DELETE_BASKET_ITEM')));
		}
	}

	/**
	 * @deprecated
	 * @see \SaleOrderCheckoutAjaxController::recalculateBasketAction
	 *
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'updateBasketItem', { mode: 'ajax', data: { id: "", fields: { ... }}});
	 */
	public function updateBasketItemAction(int $id, array $fields): ?array
	{
		$action = new Sale\Controller\Action\Entity\UpdateBasketItemAction($this->actionName, $this, $this->config);

		$result = $action->run($id, $fields);

		$errors = $action->getErrors();
		if ($errors)
		{
			$this->addError(new Main\Error(Main\Localization\Loc::getMessage('SOC_UPDATE_BASKET_ITEM')));
			return null;
		}

		$basketItems = [$result];
		$result = $this->fillBasketItems($basketItems);

		return $result[0];
	}

	/**
	 * @param array $fields
	 * @return Main\Engine\Response\Component|null
	 * @example BX.ajax.runComponentAction('bitrix:sale.order.checkout', 'paymentPay', { mode: 'ajax', data: { fields: { ... }}});
	 */
	public function paymentPayAction(array $fields): ?Main\Engine\Response\Component
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
			return null;
		}

		$params = [
			'ORDER_ID' => (int)$fields['ORDER_ID'],
			'ACCESS_CODE' => $fields['ACCESS_CODE'],
			'RETURN_URL' => $fields['RETURN_URL'] ?? '',
		];

		$result = new Main\Engine\Response\Component('bitrix:salescenter.payment.pay', 'checkout_form', $params);

		$order = $this->loadOrder($fields['ORDER_ID']);
		$this->addTimelineEntryOnPay($order);

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
			$isPropertyError = false;
			foreach ($errors as $error)
			{
				$errorCode = $error->getCode();
				if ($errorCode >= 202250003000 && $errorCode <= 202250003999)
				{
					$isPropertyError = true;

					$this->addError(
						new Main\Error(
							$error->getMessage(),
							'PROPERTIES',
							$error->getCustomData()
						)
					);
				}
			}

			if (!$isPropertyError)
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
				case 'DELETE':
					foreach ($actionValues as $index => $basketItemId)
					{
						$deleteBasketItemResult = $this->deleteBasketItem($basketItemId);
						if ($deleteBasketItemResult->isSuccess())
						{
							$result['ACTIONS'][$index] = [
								'TYPE' => 'delete',
								'FIELDS' => ['ID' => $basketItemId],
							];
						}
						else
						{
							$result['ACTIONS'][$index] = [
								'TYPE' => 'delete',
								'ERRORS' => $deleteBasketItemResult->getErrorMessages(),
							];
						}
					}

					break;

				case 'QUANTITY':
					foreach ($actionValues as $index => $quantity)
					{
						$updateBasketItemQuantityResult = $this->updateBasketItemQuantity($quantity['BASKET_ID'], $quantity['QUANTITY']);
						if ($updateBasketItemQuantityResult->isSuccess())
						{
							$result['ACTIONS'][$index] = [
								'TYPE' => 'quantity',
								'FIELDS' => ['ID' => $quantity['BASKET_ID']],
							];
						}
						else
						{
							$result['ACTIONS'][$index] = [
								'TYPE' => 'quantity',
								'ERRORS' => $updateBasketItemQuantityResult->getErrorMessages(),
							];
						}
					}

					break;

				case 'RESTORE':
					foreach ($actionValues as $index => $basketItemData)
					{
						$restoreBasketItemResult = $this->restoreBasketItem($basketItemData);
						if ($restoreBasketItemResult->isSuccess())
						{
							$restoreBasketItemData = $restoreBasketItemResult->getData();
							/** @var Sale\BasketItem $basketItem */
							$basketItem = $restoreBasketItemData['basketItem'];

							$result['ACTIONS'][$index] = [
								'TYPE' => 'restore',
								'FIELDS' => ['ID' => $basketItem->getId()],
							];
						}
						else
						{
							$result['ACTIONS'][$index] = [
								'TYPE' => 'restore',
								'ERRORS' => $restoreBasketItemResult->getErrorMessages(),
							];
						}
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

		$result['BASKET_ITEMS'] = $basketData['BASKET_ITEMS'];
		$result['ORDER_PRICE_TOTAL'] = $basketData['ORDER_PRICE_TOTAL'];

		$result['NEED_FULL_RECALCULATION'] =
			self::isBasketHashChanged($basketHashBeforeRecalculation, $basketHashAfterRecalculation)
				? 'Y'
				: 'N'
		;

		return $result;
	}

	private function prepareRecalculateBasketActions(array $actions): array
	{
		$result = [
			'DELETE' => [],
			'QUANTITY' => [],
			'RESTORE' => [],
		];

		$actions = $this->sortRecalculateBasketAction($actions);

		$deleteList = [];
		foreach ($actions as $index => $n)
		{
			foreach ($n as $action)
			{
				$actionName = key($action);

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
					$deleteList[] = $data['ID'];

					$value = $data['ID'];
				}
				elseif ($actionName === 'QUANTITY')
				{
					$value = [
						'BASKET_ID' => $data['ID'],
						'QUANTITY' => $data['VALUE'],
					];
				}
				elseif ($actionName === 'RESTORE')
				{
					$value = [
						'PRODUCT_ID' => $data['PRODUCT']['ID'],
						'QUANTITY' => $data['QUANTITY'],
						'MODULE' => $data['MODULE'],
						'PRODUCT_PROVIDER_CLASS' => $data['PRODUCT_PROVIDER_CLASS'],
						'PROPS' => $data['PROPS'],
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
	 * @param Sale\Order $order
	 * @throws Main\ArgumentException
	 */
	private function addTimelineEntryOnPay(Sale\Order $order): void
	{
		if (!Main\Loader::includeModule('crm'))
		{
			return;
		}

		$orderId = $order->getId();

		$isTimelineExists = false;
		$timelineIterator = Crm\Timeline\Entity\TimelineTable::getList([
			'select' => ['ID', 'SETTINGS'],
			'filter' => [
				'=ASSOCIATED_ENTITY_ID' => $orderId ,
				'=ASSOCIATED_ENTITY_TYPE_ID' => Crm\Timeline\TimelineType::ORDER,
				'=TYPE_ID' => Crm\Timeline\TimelineType::ORDER,
			],
		]);
		while ($timelineData = $timelineIterator->fetch())
		{
			$timelineSettings = $timelineData['SETTINGS'];
			$isManualContinuePay = $timelineSettings['FIELDS']['MANUAL_CONTINUE_PAY'] ?? null;
			if ($isManualContinuePay === 'Y')
			{
				$isTimelineExists = true;
				break;
			}
		}

		if ($isTimelineExists)
		{
			return;
		}

		$bindings = [
			[
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Order,
				'ENTITY_ID' => $orderId,
			],
		];

		/** @var Crm\Order\DealBinding $dealBindings */
		if ($dealBindings = $order->getDealBinding())
		{
			$bindings[] = [
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
				'ENTITY_ID' => $dealBindings->getDealId(),
			];
		}

		$timelineParams = [
			'SETTINGS' => [
				'CHANGED_ENTITY' => \CCrmOwnerType::OrderName,
				'FIELDS' => [
					'ORDER_ID' => $orderId,
				],
			],
			'ORDER_FIELDS' => $order->getFieldValues(),
			'BINDINGS' => $bindings,
		];

		Crm\Timeline\OrderController::getInstance()->onManualContinuePay($orderId, $timelineParams);
	}
}
