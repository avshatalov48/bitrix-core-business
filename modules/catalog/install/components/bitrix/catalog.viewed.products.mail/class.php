<?
use Bitrix\Iblock;
use Bitrix\Main;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

class CCatalogViewedProductsMailComponent extends CCatalogViewedProductsComponent
{
	/**
	 * Event called from includeComponent before component execution.
	 *
	 * <p>Takes component parameters as argument and should return it formatted as needed.</p>
	 * @param array[string]mixed $arParams
	 * @return array[string]mixed
	 *
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if(isset($params['LIST_ITEM_ID']))
		{
			if(!is_array($params['LIST_ITEM_ID']))
				$params['LIST_ITEM_ID'] = array($params['LIST_ITEM_ID']);
		}
		else
		{
			$params['LIST_ITEM_ID'] = array();
		}

		$params['PROPERTY_VALUE'] = array();
		$params['OFFER_TREE_PROPS'] = array();

		$itemIterator = Iblock\ElementTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID'),
			'filter' => array('ID' => $params['LIST_ITEM_ID'])
		));
		while($item = $itemIterator->fetch())
		{
			$params['SIMPLE_PRODUCT'][$item['ID']] = false;
			$iblockId = (int)$item['IBLOCK_ID'];

			$params['SHOW_PRODUCTS'][$iblockId] = true;

			$sku = CCatalogSKU::getInfoByProductIBlock($iblockId);
			$boolSku = !empty($sku) && is_array($sku);

			if($boolSku)
			{
				$this->prepareItemData($item['ID'], $sku, $params);
			}
			else
			{
				$sku = CCatalogSKU::getInfoByOfferIBlock($iblockId);
				$productList = CCatalogSKU::getProductList($item['ID']);
				if(!empty($productList))
				{
					$productList = current($productList);
					unset($params['LIST_ITEM_ID'][array_search($item['ID'], $params['LIST_ITEM_ID'])]);
					if(!in_array($productList['ID'], $params['LIST_ITEM_ID']))
					{
						$params['LIST_ITEM_ID'][] = $productList['ID'];
						$params['SHOW_PRODUCTS'][$productList['IBLOCK_ID']] = true;
						$this->prepareItemData($productList['ID'], $sku, $params, $item['ID']);
					}
					else
					{
						$this->prepareItemData($productList['ID'], $sku, $params, $item['ID'], true);
					}
				}
			}
		}

		if(!empty($params['LIST_ITEM_ID']))
		{
			$params['PRICE_CODE'] = array();
			$result = CCatalogGroup::getGroupsList(array("GROUP_ID" => 2));
			while ($group = $result->fetch())
			{
				$catGroups = CCatalogGroup::getListEx(array(), array('ID' => $group['CATALOG_GROUP_ID']),
					false, false, array('NAME'));
				if ($catGroup = $catGroups->fetch())
				{
					$params['PRICE_CODE'][$catGroup['NAME']] = $catGroup['NAME'];
				}
			}
		}

		return $params;
	}

	/**
	 * @override
	 * @return integer[]
	 */
	protected function getProductIds()
	{
		return $this->arParams['LIST_ITEM_ID'];
	}

	protected function prepareItemData($itemId, array $sku, &$params, $offerId = 0, $iteration = false)
	{
		$offersTreeProps = array();
		$propertyValue = array();
		$codeList = $this->getPropertyCodeList($sku);
		$offersList = CCatalogSKU::getOffersList($itemId, 0,
			array('ACTIVE' => 'Y'), array(), array('CODE' => $codeList));
		if(!empty($offersList))
		{
			foreach($offersList[$itemId] as $offersId => &$offers)
			{
				if($offerId && $offersId != $offerId)
					continue;

				foreach($offers['PROPERTIES'] as $propertiesCode => $properties)
				{
					if($properties['ID'] == $sku['SKU_PROPERTY_ID'] || empty($properties['VALUE']))
						continue;

					if(!is_array($propertyValue[$propertiesCode]))
						$propertyValue[$propertiesCode] = array();

					if(!in_array($properties['VALUE'],$propertyValue[$propertiesCode]))
						$propertyValue[$propertiesCode][] = $properties['VALUE'];

					$offersTreeProps[] = $propertiesCode;
				}
			}
		}
		else
		{
			$params['SIMPLE_PRODUCT'][$itemId] = true;
		}

		if($iteration)
		{
			$params['PROPERTY_ITERATION_VALUE'][$itemId] = $propertyValue;
		}
		else
		{
			if($offerId)
				$params['OFFER'][$itemId] = true;
			$params['OFFER_TREE_PROPS'][$itemId] = array_unique($offersTreeProps);
			$params['PROPERTY_VALUE'][$itemId] = $propertyValue;
		}
	}

	protected function getPropertyCodeList(array $sku)
	{
		$codeList = array();
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'USER_TYPE'),
			'filter' => array('=IBLOCK_ID' => $sku['IBLOCK_ID'], '=ACTIVE' => 'Y')
		));
		while ($property = $propertyIterator->fetch())
		{
			if($property['MULTIPLE'] == 'Y' || $property['ID'] == $sku['SKU_PROPERTY_ID'])
				continue;

			$property['USER_TYPE'] = (string)$property['USER_TYPE'];
			if (empty($property['CODE']))
				$property['CODE'] = $property['ID'];

			if (
				$property['PROPERTY_TYPE'] == 'L'
				|| $property['PROPERTY_TYPE'] == 'E'
				|| ($property['PROPERTY_TYPE'] == 'S' && $property['USER_TYPE'] == 'directory')
			)
			{
				$codeList[] = $property['CODE'];
			}
		}
		return $codeList;
	}
}