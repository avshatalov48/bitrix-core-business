<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Sale\BasketItem;

class BasketBuilderExist implements IBasketBuilderDelegate
{
	/** @var BasketBuilder */
	protected  $builder = null;

	public function __construct(BasketBuilder $builder)
	{
		$this->builder = $builder;
	}

	public function getItemFromBasket($basketCode, $productData)
	{
		if(empty($productData['MANUALLY_EDITED']))
		{
			$item = $this->builder->getBasket()->getExistsItem($productData["MODULE"], $productData["OFFER_ID"], $productData["PROPS"]);
		}
		else
		{
			$item = $this->builder->getBasket()->getItemByBasketCode($basketCode);
		}

		//sku was changed
		if($item == null && $basketCode != BasketBuilder::BASKET_CODE_NEW)
		{
			if($item = $this->builder->getBasket()->getItemByBasketCode($basketCode))
			{
				$res = $item->delete();

				if(!$res->isSuccess())
				{
					$this->builder->getErrorsContainer()->addErrors($res->getErrors());
					throw new BuildingException();
				}

				$item = null;
			}
		}

		return $item;
	}

	/**
	 * @param $basketCode
	 * @param BasketItem $item
	 * @param array $productData
	 */
	public function setItemData($basketCode, &$productData, &$item)
	{
		if ($basketCode != $productData["BASKET_CODE"])
			$productData["BASKET_CODE"] = $item->getBasketCode();

		if(isset($productData["OFFER_ID"]) || intval($productData["OFFER_ID"]) >= 0)
			$productData["PRODUCT_ID"] = $productData["OFFER_ID"];

		$itemFields = array_intersect_key($productData, array_flip($item::getAvailableFields()));

		if(isset($itemFields["MEASURE_CODE"]) && strlen($itemFields["MEASURE_CODE"]) > 0)
		{
			$measures = $this->builder->getCatalogMeasures();

			if(isset($measures[$itemFields["MEASURE_CODE"]]) && strlen($measures[$itemFields["MEASURE_CODE"]]) > 0)
				$itemFields["MEASURE_NAME"] = $measures[$itemFields["MEASURE_CODE"]];
		}

		if(!empty($productData["PROVIDER_DATA"]) && !$this->builder->isNeedUpdateNewProductPrice() && CheckSerializedData($productData["PROVIDER_DATA"]))
		{
			$providerData = unserialize($productData["PROVIDER_DATA"]);
		}

		if(is_array($providerData) && !empty($providerData))
			$this->builder->sendProductCachedDataToProvider($item, $this->builder->getOrder(), $providerData);

		if(!empty($productData["SET_ITEMS_DATA"]) && CheckSerializedData($productData["SET_ITEMS_DATA"]))
			$productData["SET_ITEMS"] = unserialize($productData["SET_ITEMS_DATA"]);

		$this->builder->setBasketItemFields($item, $itemFields);
	}

	public function finalActions()
	{
		if($this->builder->isProductAdded())
		{
			$res = $this->builder->getBasket()->refreshData(array('PRICE', 'COUPONS'));

			if (!$res->isSuccess())
			{
				$this->builder->getErrorsContainer()->addErrors($res->getErrors());
			}
		}
	}
}