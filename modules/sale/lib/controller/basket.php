<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;
use Bitrix\Sale\Provider;

class Basket extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			BasketItem::class,
			'basket',
			function($className, $id) {

				$r = \Bitrix\Sale\Basket::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					$order = \Bitrix\Sale\Order::load($row['ORDER_ID']);
					$basket = $order->getBasket()->getItemByBasketCode($id);
					if($basket instanceof BasketItem)
						return $basket;
				}
				else
				{
					$this->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_BASEKT_NOT_EXISTS', ['#ID#'=>$id]), 'BASEKT_NOT_EXISTS'));
				}
				return null;

			}
		);
	}

	public function getAutoWiredParameters()
	{
		return [
			new \Bitrix\Main\Engine\AutoWire\Parameter(
				BasketItem::class,
				function($className, $id) {

					$r = \Bitrix\Sale\Basket::getList([
						'select'=>['ORDER_ID'],
						'filter'=>['ID'=>$id]
					]);

					if($row = $r->fetch())
					{
						$order = \Bitrix\Sale\Order::load($row['ORDER_ID']);
						$basket = $order->getBasket()->getItemByBasketCode($id);
						if($basket instanceof BasketItem)
							return $basket;
					}
					else
					{
						$this->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_BASEKT_NOT_EXISTS', ['#ID#'=>$id]), 'BASEKT_NOT_EXISTS'));
					}
					return null;
				}
			)
		];
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\Basket();
		return ['ITEM'=>$entity->prepareFieldInfos(
			$entity->getFields()
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
		return ['ITEMS'=>$this->toArray($order)['ORDER']['BASKET']['ITEMS']];
	}

	public function addAction(array $fields)
	{
		if(isset($fields['BASKET_ITEMS']['ID']))
			unset($fields['BASKET_ITEMS']['ID']);

		$data = $fields;
		unset($data['BASKET_ITEMS']);
		$data['BASKET_ITEMS'] = [$fields['BASKET_ITEMS']];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteBaketItemsIfNotExists' => false
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
		/** @var BasketItem $basketItem */
		foreach($collection as $basketItem)
		{
			if($basketItem->getId() <= 0)
			{
				$idx = $basketItem->getInternalIndex();
				break;
			}
		}

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		//TODO: return $basketItem->toArray();
		return new Page('ITEM', $this->get($order->getBasket()->getItemByIndex($idx)), 1);
	}

	public function updateAction(BasketItem $basket, array $fields)
	{
		$data = [
			'ORDER'=>[
				'ID'=>$basket->getCollection()->getOrderId()
			],
			'BASKET'=>[
				'ITEMS'=>[
					array_merge(
						['ID'=>$basket->getBasketCode()],
						$fields
					)
				]
			]
		];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteBaketItemsIfNotExists' => false
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

		//TODO: return $order->toArray();
		return new Page('ITEM', $this->get($basket), 1);
	}

	public function getAction(BasketItem $basket)
	{
		//TODO: $basketItem->toArray();
		return ['ITEM' => $this->get($basket)];
	}

	public function deleteAction(BasketItem $basket)
	{
		/** @var \Bitrix\Sale\Basket $basketCollection */
		$basketCollection = $basket->getCollection();
		$order = $basketCollection->getOrder();

		$r = $basket->delete();
		if($r->isSuccess())
			$r = $order->save();

		if(!$r->isSuccess())
			$this->addErrors($r->getErrors());

		return $r->isSuccess();
	}

	public function listAction($select=[], $filter, $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$items = \Bitrix\Sale\Basket::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('ITEMS', $items, function() use ($filter)
		{
			return count(
				\Bitrix\Sale\Basket::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}

	public function canBuyAction(BasketItem $basket)
	{
		return $basket->canBuy()?'Y':'N';
	}

	public function getBasePriceAction(BasketItem $basket)
	{
		return $basket->getBasketCode();
	}

	public function getBasePriceWithVatAction(BasketItem $basket)
	{
		return $basket->getBasePriceWithVat();
	}

	public function getCurrencyAction(BasketItem $basket)
	{
		return $basket->getCurrency();
	}

	public function getDefaultPriceAction(BasketItem $basket)
	{
		return $basket->getDefaultPrice();
	}

	public function getDiscountPriceAction(BasketItem $basket)
	{
		return $basket->getDiscountPrice();
	}

	public function getFinalPriceAction(BasketItem $basket)
	{
		return $basket->getFinalPrice();
	}

	public function getInitialPriceAction(BasketItem $basket)
	{
		return $basket->getInitialPrice();
	}

	public function getPriceAction(BasketItem $basket)
	{
		return $basket->getPrice();
	}

	public function getPriceWithVatAction(BasketItem $basket)
	{
		return $basket->getPriceWithVat();
	}

	public function getProductIdAction(BasketItem $basket)
	{
		return $basket->getProductId();
	}

	public function getQuantityAction(BasketItem $basket)
	{
		return $basket->getQuantity();
	}

	public function getReservedQuantityAction(BasketItem $basket)
	{
		return $basket->getReservedQuantity();
	}

	public function getVatAction(BasketItem $basket)
	{
		return $basket->getVat();
	}

	public function getVatRateAction(BasketItem $basket)
	{
		return $basket->getVatRate();
	}

	public function getWeightAction(BasketItem $basket)
	{
		return $basket->getWeight();
	}

	public function isBarcodeMultiAction(BasketItem $basket)
	{
		return $basket->isBarcodeMulti();
	}

	public function isCustomMultiAction(BasketItem $basket)
	{
		return $basket->isCustom();
	}

	public function isCustomPriceAction(BasketItem $basket)
	{
		return $basket->isCustomPrice();
	}

	public function isDelayAction(BasketItem $basket)
	{
		return $basket->isDelay();
	}

	public function isVatInPriceAction(BasketItem $basket)
	{
		return $basket->isVatInPrice();
	}

	public function checkProductBarcodeAction(BasketItem $basket, array $fields)
	{
		$r = false;
		if ($basket)
		{
			/*
			 * fields = ['BARCODE'=>..., 'STORE_ID'=>...];
			 * */
			$r = Provider::checkProductBarcode($basket, $fields);
		}

		if ($r)
			return true;
		else
		{
			$this->addError(new Error('','BARCODE_CHECK_FAILED'));
			return null;
		}

	}

	//endregion

	protected function get(BasketItem $basketItem, array $fields=[])
	{
		$items = $this->toArray($basketItem->getCollection()->getOrder(), $fields)['ORDER']['BASKET']['ITEMS'];
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

		if(isset($fields['BASKET']['ITEMS']))
		{
			$i=0;
			foreach ($fields['BASKET']['ITEMS'] as $item)
			{
				$item['OFFER_ID'] = $item['PRODUCT_ID'];
				unset($item['PRODUCT_ID']);

				$item['CUSTOM_PRICE'] = isset($item['PRICE'])? 'Y':'N';

				$item['MODULE'] = isset($item['MODULE'])? $item['MODULE']:null;
				$item['PRODUCT_PROVIDER_CLASS'] = isset($item['PRODUCT_PROVIDER_CLASS'])? $item['PRODUCT_PROVIDER_CLASS']:null;

				if($item['MODULE'] == 'catalog' && $item['PRODUCT_PROVIDER_CLASS'] === null)
				{
					$item['PRODUCT_PROVIDER_CLASS'] = \Bitrix\Catalog\Product\Basket::getDefaultProviderName();
				}

				$properties = isset($item['PROPERTIES'])? $item['PROPERTIES']:[];
				foreach ($properties as &$property)
				{
					if(isset($property['BASKET_ID']))
						unset($property['BASKET_ID']);
				}

				$item['PROPS'] = $properties;
				unset($item['PROPERTIES']);

				$basketCode = isset($item['ID'])? $item['ID']:'n'.++$i;
				if(isset($item['ID']))
					unset($item['ID']);

				$data[$basketCode] = $item;
			}
			unset($fields['BASKET']);
		}

		return ['PRODUCT'=>is_array($data)?$data:[]];
	}

	protected function checkPermission($name)
	{
		if(substr($name,0,6) === 'canbuy')
		{
			$name = 'get';
		}

		parent::checkPermission($name);
	}
}