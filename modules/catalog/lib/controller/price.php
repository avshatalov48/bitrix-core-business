<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Price extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['PRICE'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		return new Page('PRICES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['PRICE'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function modifyAction($fields)
	{
		$r = $this->modifyBefore($fields);

		if($r->isSuccess())
		{
			$r = $this->modifyValidate($fields);
			if($r->isSuccess())
			{
				$r = $this->modify($fields);
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			$ids = $r->getData()[0];
			$entityTable = $this->getEntityTable();

			return ['PRICES'=>$entityTable::getList(['filter'=>['ID'=>$ids]])->fetchAll()];
		}
	}

	public function deleteAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			$r = $this->deleteValidate($id);
			if($r->isSuccess())
			{
				$r = \Bitrix\Catalog\Model\Price::delete($id);
			}
		}

		if($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	protected function modify($fields)
	{
		$ids = [];
		$productId = $fields['PRODUCT']['ID'];
		$prices = $fields['PRODUCT']['PRICES'];

		$r = $this->checkPermissionIBlockElementPriceModify($productId);
		if($r->isSuccess())
		{
			foreach ($prices as $price)
			{
				if(isset($price['ID']))
				{
					self::normalizeFields($price);

					$result = \Bitrix\Catalog\Model\Price::update($price['ID'], $price);
					if($result->isSuccess())
					{
						$ids[] = $price['ID'];
					}
				}
				else
				{
					$result = \Bitrix\Catalog\Model\Price::add([
						'PRODUCT_ID' => $productId,
						'CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID'],
						'CURRENCY' => $price['CURRENCY'],
						'PRICE' => $price['PRICE'],
						'QUANTITY_FROM' => isset($price['QUANTITY_FROM']) ? $price['QUANTITY_FROM']:null,
						'QUANTITY_TO' => isset($price['QUANTITY_TO']) ? $price['QUANTITY_TO']:null,
						'EXTRA_ID' => isset($price['EXTRA_ID']) ? $price['EXTRA_ID']:null,
					]);

					if($result->isSuccess())
					{
						$ids[] = $result->getId();
					}
				}

				if($result->isSuccess() == false)
				{
					$r->addErrors($result->getErrors());
				}
			}

			if($r->isSuccess())
				$r->setData([$ids]);
		}

		return $r;
	}

	protected function modifyBefore($fields)
	{
		$productId = $fields['PRODUCT']['ID'];

		$ids = [];
		$prices = $fields['PRODUCT']['PRICES'];
		foreach ($prices as $price)
		{
			$ids[]=$price['ID'];
		}

		$entityTable = $this->getEntityTable();

		$res = $entityTable::getList(['filter'=>['PRODUCT_ID'=>$productId]]);
		while ($item = $res->fetch())
		{
			if(in_array($item['ID'], $ids) == false)
			{
				$entityTable::delete($item['ID']);
			}
		}

		return new Result();
	}

	private static function normalizeFields(array &$fields)
	{
		if (isset($fields['QUANTITY_FROM']))
		{
			if (is_string($fields['QUANTITY_FROM']) && $fields['QUANTITY_FROM'] === '')
				$fields['QUANTITY_FROM'] = null;
			elseif ($fields['QUANTITY_FROM'] === false || $fields['QUANTITY_FROM'] === 0)
				$fields['QUANTITY_FROM'] = null;
		}
		else
		{
			$fields['QUANTITY_FROM'] = null;
		}

		if (isset($fields['QUANTITY_TO']))
		{
			if (is_string($fields['QUANTITY_TO']) && $fields['QUANTITY_TO'] === '')
				$fields['QUANTITY_TO'] = null;
			elseif ($fields['QUANTITY_TO'] === false || $fields['QUANTITY_TO'] === 0)
				$fields['QUANTITY_TO'] = null;
		}
		else
		{
			$fields['QUANTITY_TO'] = null;
		}

		if (isset($fields['EXTRA_ID']))
		{
			if (is_string($fields['EXTRA_ID']) && $fields['EXTRA_ID'] === '')
				$fields['EXTRA_ID'] = null;
			elseif ($fields['EXTRA_ID'] === false)
				$fields['EXTRA_ID'] = null;
		}
		else
		{
			$fields['EXTRA_ID'] = null;
		}
	}

	protected function modifyValidate($fields)
	{
		$r = new Result();

		$extendPrices = false;
		$items = $fields['PRODUCT']['PRICES'];
		foreach ($items as $k=>$fields)
		{
			$extendPrices = (isset($fields['QUANTITY_FROM']) || isset($fields['QUANTITY_TO']));

			if($extendPrices)
				break;
		}

		$allowEmptyRange = \Bitrix\Main\Config\Option::get('catalog', 'save_product_with_empty_price_range') == 'Y';
		$enableQuantityRanges = \Bitrix\Catalog\Config\Feature::isPriceQuantityRangesEnabled();


		if($extendPrices === false && count($items)>1)
		{
			$r->addError(new Error('Validate price error. Prices without ranges. It is possible to create one price.'));
		}
		elseif($extendPrices && $enableQuantityRanges == false)
		{
			$r->addError(new Error('Validate price error. Price quantity ranges disabled'));
		}
		else
		{
			if($extendPrices)
			{
				$count = count($items);

				for ($i = 0; $i < $count - 1; $i++)
				{
					for ($j = $i + 1; $j < $count; $j++)
					{
						if ($items[$i]["QUANTITY_FROM"] > $items[$j]["QUANTITY_FROM"])
						{
							$tmp = $items[$i];
							$items[$i] = $items[$j];
							$items[$j] = $tmp;
						}
					}
				}

				for ($i = 0, $cnt = $count; $i < $cnt; $i++)
				{
					if ($i != 0 && $items[$i]["QUANTITY_FROM"] <= 0
						|| $i == 0 && $items[$i]["QUANTITY_FROM"] < 0)
					{
						$r->addError(new Error('Quantity bounds error: lower bound '.$items[$i]["QUANTITY_FROM"].' must be above zero (for the first range)'));
					}

					if ($i != $cnt-1 && $items[$i]["QUANTITY_TO"] <= 0
						|| $i == $cnt-1 && $items[$i]["QUANTITY_TO"] < 0)
					{
						$r->addError(new Error('Quantity bounds error: higher bound '.$items[$i]["QUANTITY_TO"].' must be above zero (for the last range)'));

					}

					if ($items[$i]["QUANTITY_FROM"] > $items[$i]["QUANTITY_TO"]
						&& ($i != $cnt-1 || $items[$i]["QUANTITY_TO"] > 0))
					{
						$r->addError(new Error('Quantity bounds error: range '.$items[$i]["QUANTITY_FROM"]."-".$items[$i]["QUANTITY_TO"].' is incorrect'));
					}

					if ($i < $cnt-1 && $items[$i]["QUANTITY_TO"] >= $items[$i+1]["QUANTITY_FROM"])
					{
						$r->addError(new Error('Quantity bounds error: ranges '.$items[$i]["QUANTITY_FROM"]."-".$items[$i]["QUANTITY_TO"].' and '.$items[$i+1]["QUANTITY_FROM"]."-".$items[$i+1]["QUANTITY_TO"].' overlap'));
					}

					if ($i < $cnt-1
						&& $items[$i+1]["QUANTITY_FROM"] - $items[$i]["QUANTITY_TO"] > 1
						&& !$allowEmptyRange
					)
					{
						$r->addError(new Error('Invalid quantity range entry: no price is specified for range '.($items[$i]["QUANTITY_TO"] + 1)."-".($items[$i+1]["QUANTITY_FROM"] - 1)));
					}

					if ($i >= $cnt-1
						&& $items[$i]["QUANTITY_TO"] > 0)
					{
						$r->addError(new Error('Invalid quantity range entry: no price is specified for quantity over '.$items[$i]["QUANTITY_TO"]));
					}
				}

			}
		}
		return $r;
	}

	protected function getEntityTable()
	{
		return new PriceTable();
	}

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Price is not exists'));

		return $r;
	}

	protected function deleteValidate($id)
	{
		return new Result();
	}

	//region checkPermissionController
	protected function checkPermissionEntity($name, $arguments=[])
	{
		$name = mb_strtolower($name); //for ajax mode

		if($name == 'modify')
		{
			$r = $this->checkModifyPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}

	protected function checkModifyPermissionEntity()
	{
		$r = $this->checkReadPermissionEntity();
		if($r->isSuccess())
		{
			if(!static::getGlobalUser()->CanDoOperation('catalog_price'))
			{
				$r->addError(new Error('Access Denied', 200040300020));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!static::getGlobalUser()->CanDoOperation('catalog_read') && !static::getGlobalUser()->CanDoOperation('catalog_price') && !static::getGlobalUser()->CanDoOperation('catalog_view'))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
	//endregion

	//region checkPermissionIBlock
	private function checkPermissionIBlockElementPriceModify($productId)
	{
		$r = new Result();

		$iblockId = \CIBlockElement::GetIBlockByID($productId);
		if(!\CIBlockElementRights::UserHasRightTo($iblockId, $productId, self::IBLOCK_ELEMENT_EDIT_PRICE))
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}
		return $r;
	}
	//endregion
}