<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Sale;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;

class BasketItem extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\BasketItem::class,
			'basketItem',
			function($className, $id) {
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Basket $basketClass */
				$basketClass = $registry->getBasketClassName();

				$r = $basketClass::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					/** @var Sale\Order $orderClass */
					$orderClass = $registry->getOrderClassName();

					$order = $orderClass::load($row['ORDER_ID']);
					$basket = $order->getBasket()->getItemByBasketCode($id);
					if ($basket instanceof \Bitrix\Sale\BasketItem)
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
		$entity = new \Bitrix\Sale\Rest\Entity\BasketItem();
		return ['BASKET_ITEM'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function getFieldsCatalogProductAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\BasketItem();
		return ['BASKET_ITEM'=>$entity->prepareFieldInfos(
			$entity->getFieldsCatalogProduct()
		)];
	}

	public function modifyAction(array $fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityBasket($fields);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		//TODO: return $basket->toArray();
		return ['BASKET_ITEMS'=>$this->toArray($order)['ORDER']['BASKET_ITEMS']];
	}

	public function addAction(array $fields)
	{
		$data = [];

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['BASKET_ITEMS'] = [$fields];

		$r = $this->addValidate($fields);
		if($r->isSuccess())
		{
			$builder = $this->getBuilder(
				new SettingsContainer([
					'deleteBasketItemsIfNotExists' => false
				])
			);
			$builder->buildEntityBasket($data);

			if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
			{
				$this->addErrors($builder->getErrorsContainer()->getErrors());
				return null;
			}

			$order = $builder->getOrder();

			$idx=0;
			$collection = $order->getBasket();
			/** @var \Bitrix\Sale\BasketItem $basketItem */
			foreach($collection as $basketItem)
			{
				if($basketItem->getId() <= 0)
				{
					$idx = $basketItem->getInternalIndex();
					break;
				}
			}

			$r = $order->save();
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		/** @var \Bitrix\Sale\BasketItem $entity */
		$entity = $order->getBasket()->getItemByIndex($idx);
		return new Page('BASKET_ITEM', $this->get($entity), 1);
	}

	public function addCatalogProductAction(array $fields)
	{
		$fields['MODULE'] = 'catalog';
		$fields['PRODUCT_PROVIDER_CLASS'] = '\Bitrix\Catalog\Product\CatalogProvider';

		return $this->addAction($fields);
	}

	public function updateAction(\Bitrix\Sale\BasketItem $basketItem, array $fields)
	{
		$data = [];

		$fields['ID'] = $basketItem->getBasketCode();
		$fields['ORDER_ID'] = $basketItem->getCollection()->getOrderId();

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['BASKET_ITEMS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteBasketItemsIfNotExists' => false
			])
		);

		$builder->buildEntityBasket($data);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		/** @var \Bitrix\Sale\BasketItem $entity */
		$entity = $order->getBasket()->getItemById($basketItem->getId());
		return new Page('BASKET_ITEM', $this->get($entity), 1);
	}

	public function updateCatalogProductAction(\Bitrix\Sale\BasketItem $basketItem, array $fields)
	{
		return $this->updateAction($basketItem, $fields);
	}

	public function getAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		//TODO: $basketItem->toArray();
		return ['BASKET_ITEM' => $this->get($basketItem)];
	}

	public function deleteAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		/** @var \Bitrix\Sale\Basket $basketCollection */
		$basketCollection = $basketItem->getCollection();
		$order = $basketCollection->getOrder();

		$r = $basketItem->delete();
		if($r->isSuccess())
			$r = $order->save();

		if(!$r->isSuccess())
			$this->addErrors($r->getErrors());

		return $r->isSuccess();
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$items = $basketClass::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('BASKET_ITEMS', $items, function() use ($filter)
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			return count(
				$basketClass::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}

	public function canBuyAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->canBuy()?'Y':'N';
	}

	public function getBasePriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getBasePrice();
	}

	public function getBasePriceWithVatAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getBasePriceWithVat();
	}

	public function getCurrencyAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getCurrency();
	}

	public function getDefaultPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getDefaultPrice();
	}

	public function getDiscountPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getDiscountPrice();
	}

	public function getFinalPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getFinalPrice();
	}

	public function getInitialPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getInitialPrice();
	}

	public function getPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getPrice();
	}

	public function getPriceWithVatAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getPriceWithVat();
	}

	public function getProductIdAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getProductId();
	}

	public function getQuantityAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getQuantity();
	}

	public function getReservedQuantityAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getReservedQuantity();
	}

	public function getVatAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getVat();
	}

	public function getVatRateAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getVatRate();
	}

	public function getWeightAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->getWeight();
	}

	public function isBarcodeMultiAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->isBarcodeMulti()? 'Y':'N';
	}

	public function isCustomMultiAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->isCustom()? 'Y':'N';
	}

	public function isCustomPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->isCustomPrice()? 'Y':'N';
	}

	public function isDelayAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->isDelay()? 'Y':'N';
	}

	public function isVatInPriceAction(\Bitrix\Sale\BasketItem $basketItem)
	{
		return $basketItem->isVatInPrice()? 'Y':'N';
	}

	/*public function checkProductBarcodeAction(\Bitrix\Sale\BasketItem $basketItem, array $fields)
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

	protected function get(\Bitrix\Sale\BasketItem $basketItem, array $fields=[])
	{
		$items = $this->toArray($basketItem->getCollection()->getOrder(), $fields)['ORDER']['BASKET_ITEMS'];
		foreach ($items as $item)
		{
			if($item['ID'] == $basketItem->getId())
			{
				return $item;
			}
		}
		return [];
	}

	static public function prepareFields($fields)
	{
		$data = null;
		Loader::includeModule('catalog');

		if(isset($fields['BASKET_ITEMS']))
		{
			$i=0;
			foreach ($fields['BASKET_ITEMS'] as $item)
			{
				if(isset($item['PRODUCT_ID']))
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

				$item['PROPS'] = isset($item['PROPERTIES'])? $item['PROPERTIES']:[];
				unset($item['PROPERTIES']);

				$basketCode = isset($item['ID'])? $item['ID']:'n'.++$i;
				if(isset($item['ID']))
					unset($item['ID']);

				$data[$basketCode] = $item;
			}
			unset($fields['BASKET_ITEMS']);
		}

		return is_array($data)?['PRODUCT'=>$data]:[];
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'canbuy'
			|| $name == 'getbaseprice'
			|| $name == 'getbasepricewithvat'
			|| $name == 'getcurrency'
			|| $name == 'getdefaultprice'
			|| $name == 'getdiscountprice'
			|| $name == 'getfinalprice'
			|| $name == 'getinitialprice'
			|| $name == 'getprice'
			|| $name == 'getpricewithvat'
			|| $name == 'getproductid'
			|| $name == 'getquantity'
			|| $name == 'getreservedquantity'
			|| $name == 'getvat'
			|| $name == 'getvatrate'
			|| $name == 'getweight'
			|| $name == 'isbarcodemulti'
			|| $name == 'iscustommulti'
			|| $name == 'iscustomprice'
			|| $name == 'isdelay'
			|| $name == 'isvatinprice'
			|| $name == 'getfieldscatalogproduct'
		)
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif ($name == 'addcatalogproduct' || $name == 'updatecatalogproduct')
		{
			$r = $this->checkCreatePermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}

		return $r;
	}

	protected function addValidate($fields)
	{
		$result = new Result();

		if(isset($fields['ORDER_ID']) == false || intval($fields['ORDER_ID'])<=0)
		{
			$result->addError(new Error('Required fields: fields[ORDER_ID]'));
		}
		else
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$order = $orderClass::load($fields['ORDER_ID']);
			if($order->getCurrency() <> $fields['CURRENCY'])
			{
				$result->addError(new Error('Currency must be the currency of the order'));
			}
		}

		return $result;

	}
}