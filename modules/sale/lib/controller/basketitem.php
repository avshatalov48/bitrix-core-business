<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Catalog;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;

class BasketItem extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\BasketItem::class,
			'basketItem',
			function($className, $id)
			{
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Basket $basketClass */
				$basketClass = $registry->getBasketClassName();

				$iterator = $basketClass::getList([
					'select' => [
						'ORDER_ID',
					],
					'filter'=>[
						'=ID' => (int)$id,
					],
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if ($row)
				{
					/** @var Sale\Order $orderClass */
					$orderClass = $registry->getOrderClassName();

					$order = $orderClass::load((int)$row['ORDER_ID']);
					$basket = $order->getBasket()->getItemByBasketCode($id);
					if ($basket instanceof Sale\BasketItem)
					{
						return $basket;
					}
				}
				else
				{
					$this->addError(new Error('basket item is not exists', 200140400001));
				}

				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new Sale\Rest\Entity\BasketItem();

		return [
			'BASKET_ITEM' => $entity->prepareFieldInfos($entity->getFields()),
		];
	}

	public function getFieldsCatalogProductAction()
	{
		$entity = new Sale\Rest\Entity\BasketItem();

		return [
			'BASKET_ITEM' => $entity->prepareFieldInfos($entity->getFieldsCatalogProduct()),
		];
	}

	public function modifyAction(array $fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityBasket($fields);

		if (!$this->checkBuilderError($builder))
		{
			return null;
		}

		$order = $builder->getOrder();

		$result = $order->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		//TODO: return $basket->toArray();
		$convertedOrder = $this->toArray($order);
		return [
			'BASKET_ITEMS' => $convertedOrder['ORDER']['BASKET_ITEMS'] ?? null,
		];
	}

	public function addAction(array $fields)
	{
		$data = [];

		if (Loader::includeModule('bitrix24'))
		{
			if (isset($fields['PRODUCT_ID']) && (int)$fields['PRODUCT_ID'] === 0)
			{
				$fields['PRODUCT_ID'] = (int)$fields['PRODUCT_ID'];
				$fields['MODULE'] = '';
				$fields['PRODUCT_PROVIDER_CLASS'] = '';
			}
			else
			{
				if (Loader::includeModule('catalog'))
				{
					$fields = array_merge(
						$fields,
						$this->getStandartProviderDescription()
					);
				}
				else
				{
					unset(
						$fields['MODULE'],
						$fields['PRODUCT_PROVIDER_CLASS'],
					);
				}
			}
		}

		if (isset($fields['VAT_RATE']) && $fields['VAT_RATE'] === '')
		{
			$fields['VAT_RATE'] = null;
		}

		$data['ORDER'] = [
			'ID' => $fields['ORDER_ID'] ?? null,
			'BASKET_ITEMS' => [
				$fields,
			],
		];

		$result = $this->addValidate($fields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteBasketItemsIfNotExists' => false
			])
		);
		$builder->buildEntityBasket($data);

		if (!$this->checkBuilderError($builder))
		{
			return null;
		}

		$idx = 0;
		$order = $builder->getOrder();
		$collection = $order->getBasket();
		/** @var Sale\BasketItem $basketItem */
		foreach ($collection as $basketItem)
		{
			if ($basketItem->getId() <= 0)
			{
				$idx = $basketItem->getInternalIndex();
				break;
			}
		}
		unset(
			$basketItem,
			$collection,
		);

		$result = $order->save();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		/** @var Sale\BasketItem $entity */
		$entity = $order->getBasket()->getItemByIndex($idx);
		if ($entity->getId() <= 0)
		{
			$this->addError(new Error('basket item is not saved - bad data', 200140400007));

			return null;
		}

		return new Page('BASKET_ITEM', $this->get($entity), 1);
	}

	public function addCatalogProductAction(array $fields)
	{
		if (!$this->checkModuleCatalog())
		{
			return null;
		}

		$fields = array_merge(
			$fields,
			$this->getStandartProviderDescription()
		);

		return $this->addAction($fields);
	}

	public function updateAction(Sale\BasketItem $basketItem, array $fields)
	{
		$data = [];

		$fields['ID'] = $basketItem->getBasketCode();
		$fields['ORDER_ID'] = $basketItem->getCollection()->getOrderId();

		if (isset($fields['VAT_RATE']) && $fields['VAT_RATE'] === '')
		{
			$fields['VAT_RATE'] = null;
		}

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['BASKET_ITEMS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteBasketItemsIfNotExists' => false
			])
		);

		$builder->buildEntityBasket($data);
		if (!$this->checkBuilderError($builder))
		{
			return null;
		}

		$order = $builder->getOrder();

		$result = $order->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		/** @var Sale\BasketItem $entity */
		$entity = $order->getBasket()->getItemById($basketItem->getId());

		return new Page('BASKET_ITEM', $this->get($entity), 1);
	}

	public function updateCatalogProductAction(Sale\BasketItem $basketItem, array $fields)
	{
		if (!$this->checkModuleCatalog())
		{
			return null;
		}

		return $this->updateAction($basketItem, $fields);
	}

	public function getAction(Sale\BasketItem $basketItem)
	{
		//TODO: $basketItem->toArray();
		return [
			'BASKET_ITEM' => $this->get($basketItem),
		];
	}

	public function deleteAction(Sale\BasketItem $basketItem)
	{
		/** @var Sale\Basket $basketCollection */
		$basketCollection = $basketItem->getCollection();
		$order = $basketCollection->getOrder();

		$result = $basketItem->delete();
		if ($result->isSuccess())
		{
			$result = $order->save();
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return $result->isSuccess();
	}

	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order)? ['ID' => 'ASC'] : $order;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$iterator = $basketClass::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'count_total' => $__calculateTotalCount,
		]);
		$items = $iterator->fetchAll();
		$totalCount = $__calculateTotalCount ? $iterator->getCount() : 0;
		unset($iterator);

		return new Page('BASKET_ITEMS', $items, $totalCount);
	}

	public function canBuyAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->canBuy() ? 'Y' : 'N';
	}

	public function getBasePriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getBasePrice();
	}

	public function getBasePriceWithVatAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getBasePriceWithVat();
	}

	public function getCurrencyAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getCurrency();
	}

	public function getDefaultPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getDefaultPrice();
	}

	public function getDiscountPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getDiscountPrice();
	}

	public function getFinalPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getFinalPrice();
	}

	public function getInitialPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getInitialPrice();
	}

	public function getPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getPrice();
	}

	public function getPriceWithVatAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getPriceWithVat();
	}

	public function getProductIdAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getProductId();
	}

	public function getQuantityAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getQuantity();
	}

	public function getReservedQuantityAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getReservedQuantity();
	}

	public function getVatAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getVat();
	}

	public function getVatRateAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getVatRate();
	}

	public function getWeightAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->getWeight();
	}

	public function isBarcodeMultiAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->isBarcodeMulti()? 'Y':'N';
	}

	public function isCustomMultiAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->isCustom()? 'Y':'N';
	}

	public function isCustomPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->isCustomPrice()? 'Y':'N';
	}

	public function isDelayAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->isDelay()? 'Y':'N';
	}

	public function isVatInPriceAction(Sale\BasketItem $basketItem)
	{
		return $basketItem->isVatInPrice()? 'Y':'N';
	}

	/*public function checkProductBarcodeAction(Sale\BasketItem $basketItem, array $fields)
	{
		$r = false;
		if ($basketItem)
		{
			$r = Provider::checkProductBarcode($basketItem, $fields);
		}

		if ($r)
			return true;
		else
		{
			$this->addError(new Error('barcode check failed',200150000010));
			return null;
		}

	}*/

	//endregion

	protected function get(Sale\BasketItem $basketItem, array $fields=[])
	{
		$convertedOrder = $this->toArray($basketItem->getCollection()->getOrder(), $fields);
		$items = $convertedOrder['ORDER']['BASKET_ITEMS'] ?? [];
		unset($convertedOrder);

		foreach ($items as $item)
		{
			if ($item['ID'] == $basketItem->getId())
			{
				return $item;
			}
		}

		return [];
	}

	public static function prepareFields($fields)
	{
		$data = null;
		Loader::includeModule('catalog');

		if (isset($fields['BASKET_ITEMS']))
		{
			$i = 0;
			foreach ($fields['BASKET_ITEMS'] as $item)
			{
				if (isset($item['PRODUCT_ID']))
				{
					$item['OFFER_ID'] = $item['PRODUCT_ID'];
					unset($item['PRODUCT_ID']); // need for builder
				}

				/*$properties = isset($item['PROPERTIES'])? $item['PROPERTIES']:[];
				foreach ($properties as &$property)
				{
					if(isset($property['BASKET_ID']))
						unset($property['BASKET_ID']);
				}

				$item['PROPS'] = $properties;*/

				$item['PROPS'] = $item['PROPERTIES'] ?? [];
				unset($item['PROPERTIES']);

				$basketCode = $item['ID'] ?? 'n' . ++$i;
				unset($item['ID']);

				$data[$basketCode] = $item;
			}
			unset($fields['BASKET_ITEMS']);
		}

		return is_array($data) ? ['PRODUCT' => $data] : [];
	}

	protected function checkPermissionEntity($name)
	{
		if (
			$name === 'canbuy'
			|| $name === 'getbaseprice'
			|| $name === 'getbasepricewithvat'
			|| $name === 'getcurrency'
			|| $name === 'getdefaultprice'
			|| $name === 'getdiscountprice'
			|| $name === 'getfinalprice'
			|| $name === 'getinitialprice'
			|| $name === 'getprice'
			|| $name === 'getpricewithvat'
			|| $name === 'getproductid'
			|| $name === 'getquantity'
			|| $name === 'getreservedquantity'
			|| $name === 'getvat'
			|| $name === 'getvatrate'
			|| $name === 'getweight'
			|| $name === 'isbarcodemulti'
			|| $name === 'iscustommulti'
			|| $name === 'iscustomprice'
			|| $name === 'isdelay'
			|| $name === 'isvatinprice'
			|| $name === 'getfieldscatalogproduct'
		)
		{
			$result = $this->checkReadPermissionEntity();
		}
		elseif ($name === 'addcatalogproduct' || $name === 'updatecatalogproduct')
		{
			$result = $this->checkCreatePermissionEntity();
		}
		else
		{
			$result = parent::checkPermissionEntity($name);
		}

		return $result;
	}

	protected function addValidate($fields)
	{
		$result = new Result();

		if ((int)($fields['ORDER_ID'] ?? null) <= 0)
		{
			$result->addError(new Error('Required fields: fields[ORDER_ID]', 200140400008));

			return $result;
		}

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$iterator = $orderClass::getList([
			'select' => [
				'ID',
				'CURRENCY',
			],
			'filter' => [
				'=ID' => (int)$fields['ORDER_ID'],
			],
		]);
		$order = $iterator->fetch();
		unset($iterator);
		if (empty($order))
		{
			$result->addError(new Error('Order not found', 200140400009));

			return $result;
		}

		if ($order['CURRENCY'] !== $fields['CURRENCY'])
		{
			$result->addError(new Error('Currency must be the currency of the order', 200140400011));

			return $result;
		}

		return $result;
	}

	private function checkBuilderError(Sale\Helpers\Order\Builder\OrderBuilder $builder): bool
	{
		$errors = $builder->getErrorsContainer();
		if (!$errors->isSuccess())
		{
			$this->addErrors($errors->getErrors());

			return false;
		}

		return true;
	}

	private function getStandartProviderDescription(): array
	{
		return [
			'MODULE' => 'catalog',
			'PRODUCT_PROVIDER_CLASS' => Catalog\Product\Basket::getDefaultProviderName(),
		];
	}

	private function checkModuleCatalog(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->addError(new Error('Module catalog is not exists', 200140400006));

			return false;
		}

		return true;
	}
}
