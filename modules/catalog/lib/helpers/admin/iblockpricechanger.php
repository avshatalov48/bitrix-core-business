<?php
namespace Bitrix\Catalog\Helpers\Admin;

use Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Currency;

class IblockPriceChanger
{
	private $iblockId = 0;
	private $userDialogParams = array();

	/**
	 * IblockChangePrice constructor.
	 * 
	 * @param array $userDialogParams
	 * @param int $iblockId
	 */

	public function __construct(array $userDialogParams, $iblockId)
	{
		$this->setUserDialogParams( $userDialogParams );
		$this->iblockId = (int)$iblockId;
	}

	/**
	 * Set of parameters which was set in CAdminDialog
	 *
	 * @param array $userDialogParams		Dialog's parameter.
	 * @return array|bool
	 */
	public function setUserDialogParams(array $userDialogParams)
	{
		if (!isset($userDialogParams['VALUE_CHANGING'])
			||((float)($userDialogParams['VALUE_CHANGING'] == 0)
			||!isset($userDialogParams['PRICE_TYPE'])
			||!(int)($userDialogParams['PRICE_TYPE'])))
		{
			return false;
		}
		else
		{
			$userDialogParams['PRICE_TYPE'] = (int)($userDialogParams['PRICE_TYPE']);
		}

		if (!isset($userDialogParams['DIFFERENCE_VALUE'])||!(float)$userDialogParams['DIFFERENCE_VALUE'])
		{
			$userDialogParams['DIFFERENCE_VALUE'] = 0;
		}

		if (!isset($userDialogParams['RESULT_MASK'])||!(float)$userDialogParams['RESULT_MASK'])
		{
			$userDialogParams['RESULT_MASK'] = 1;
		}

		if (!isset($userDialogParams['UNITS']))
		{
			$userDialogParams['UNITS'] = null;
		}
		else
		{
			if ($userDialogParams['UNITS'] != 'percent' && $userDialogParams['UNITS'] != 'multiple')
			{
				if (Currency\CurrencyManager::isCurrencyExist($userDialogParams['UNITS']))
				{
					$userDialogParams['CURRENCY'] = $userDialogParams['UNITS'];
					$userDialogParams['UNITS'] = 'currency';
				}
				else
				{
					$userDialogParams['UNITS'] = null;
				}
			}
		}

		return $this->userDialogParams = $userDialogParams;
	}

	/**
	 * Get list of all chosen elements
	 *
	 * @param @return array $productsIdList
	 */
	private function collectAllSectionsElements(&$productsIdList)
	{
		$resultAllElementsList = \CIBlockElement::GetList(
			array(),
			array(
				"SECTION_ID"=>$productsIdList['SECTIONS'],
				"IBLOCK_ID" => $this->iblockId,
				"WF_PARENT_ELEMENT_ID" => NULL,
				"INCLUDE_SUBSECTIONS"=>"Y",
				"CHECK_PERMISSIONS" => "Y", 
				"MIN_PERMISSION" => "W"
			),
			false,
			false,
			array('ID'));
		while ($subSectionsResult = $resultAllElementsList->Fetch())
		{
			$productsIdList['ELEMENTS'][] = $subSectionsResult['ID'];
		}
		unset($subSectionsResult, $resultAllElementsList);
		unset( $productsIdList['SECTIONS'] );
	}

	/**
	 * Get list of id's price elements
	 *
	 * @param array $productsIdList
	 * @return array priceElementsIdList
	 */
	private function collectPriceSkuElementsId($productsIdList)
	{
		$sectionElementsIdList = array();

		$skuIdList = \CCatalogSku::getOffersList($productsIdList['ELEMENTS'], $this->iblockId);
		if(is_array($skuIdList))
		{
			foreach ($skuIdList as $skuId => $skuListElements)
			{
				$sectionElementsIdList[] = $skuId;
				foreach ($skuListElements as $skuElement)
				{
					$priceElementsIdList['SKU_ELEMENTS'][] = $skuElement["ID"];
				}
			}
		}

		if (empty($priceElementsIdList))
		{
			$priceElementsIdList['SIMPLE_ELEMENTS'] = $productsIdList['ELEMENTS'];
		}
		elseif ($elementsWithoutSkuIdList = array_diff($productsIdList['ELEMENTS'], $sectionElementsIdList))
		{
			$priceElementsIdList['SIMPLE_ELEMENTS'] = $elementsWithoutSkuIdList;
			unset ($elementsWithoutSkuIdList);
		}

		return $priceElementsIdList;
	}

	/**
	 * Return array of parameters for CPrice::GetList
	 *
	 * @return array $filterList
	 */
	private function initFilterParams()
	{
		$catalogGroups = array();
		if (isset($this->userDialogParams['INITIAL_PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['INITIAL_PRICE_TYPE'];
			if ($id > 0)
				$catalogGroups[$id] = $id;
			unset($id);
		}
		if (isset($this->userDialogParams['PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['PRICE_TYPE'];
			if ($id > 0)
				$catalogGroups[$id] = $id;
			unset($id);
		}

		$filterList = array("@CATALOG_GROUP_ID" => $catalogGroups);

		return $filterList;
	}

	/**
	 * Calculate price element before update by user's params
	 *
	 * @param float $price
	 * @return float $price
	 */
	private function calculateResultPrice($price)
	{
		$userDialogParams = $this->userDialogParams;
		$valueChangingPrice = $this->userDialogParams['VALUE_CHANGING'];

		if ($userDialogParams['UNITS'] === "percent")
		{
			$price = ($price * (100 + $valueChangingPrice) / 100);
		}
		elseif ($userDialogParams['UNITS'] === "multiple")
		{
			if ($valueChangingPrice > 0)
			{
				$price = $price * $valueChangingPrice;
			}
			else
			{
				$price = $price / $valueChangingPrice * (-1);
			}
		}
		else
		{
			$price = $price + $valueChangingPrice;
		}

		switch ($userDialogParams['FORMAT_RESULTS'])
		{
			case "floor":
				$price = floor($price * $userDialogParams['RESULT_MASK']) / $userDialogParams['RESULT_MASK'] - $userDialogParams['DIFFERENCE_VALUE'] ;
				break;
			case "ceil":
				$price = ceil($price * $userDialogParams['RESULT_MASK']) / $userDialogParams['RESULT_MASK'] - $userDialogParams['DIFFERENCE_VALUE'] ;
				break;
			default:
				$price = round($price * $userDialogParams['RESULT_MASK']) / $userDialogParams['RESULT_MASK'] - $userDialogParams['DIFFERENCE_VALUE'] ;
				break;
		}

		return $price;
	}

	/**
	 * Function updates prices of chosen elements by GroupOperations
	 *
	 * @param array $productsIdList		 List of elements's IDs.
	 * @return Main\Result
	 */
	public function updatePrices($productsIdList)
	{
		$result = new Main\Result();

		if ($this->userDialogParams == false)
		{
			$result->addError( 
				new Main\Error("IBLIST_CHPRICE_ERROR_WRONG_INPUT_VALUE", null)
			);
			return  $result;
		}

		if($this->userDialogParams['UNITS'] === null)
		{
			$result->addError(
				new Main\Error("IBLIST_CHPRICE_ERROR_WRONG_CURRENCY")
			);
			return  $result;
		}

		if (!empty( $productsIdList['SECTIONS']) )
		{
			$this->collectAllSectionsElements($productsIdList);
		}

		if (\CCatalogSku::GetInfoByProductIBlock($this->iblockId))
		{
			$priceElementsListSplitedByType = $this->collectPriceSkuElementsId($productsIdList);
		}
		else
		{
			$priceElementsListSplitedByType['SIMPLE_ELEMENTS'] = $productsIdList['ELEMENTS'];
		}
		$parameters = array(
			"select" => array('*', 'ELEMENT_NAME' => 'ELEMENT.NAME', 'ELEMENT_IBLOCK_ID' => 'ELEMENT.IBLOCK_ID'),
			"filter" => $this->initFilterParams(),
			'order' => array('PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC')
		);

		$group = Catalog\GroupTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=BASE'=>'Y')
		))->fetch();
		$basePriceId = (!empty($group) ? (int)$group['ID'] : 0);
		unset($group);

		$initialType = 0;
		if (isset($this->userDialogParams['INITIAL_PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['INITIAL_PRICE_TYPE'];
			if ($id > 0)
				$initialType = $id;
			unset($id);
		}

		$targetType = 0;
		if (isset($this->userDialogParams['PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['PRICE_TYPE'];
			if ($id > 0)
				$targetType = $id;
			unset($id);
		}

		if ($targetType == 0)
			return $result;

		if ($initialType > 0 && $targetType == $initialType)
			return $result;

		foreach ($priceElementsListSplitedByType as $typeElements => $priceElementsIdList)
		{
			$priceElementsIdList = array_chunk($priceElementsIdList, 500);
			foreach ($priceElementsIdList as $productIdList)
			{
				$parameters['filter']['@PRODUCT_ID'] = $productIdList;

				$cpriceResult = Catalog\PriceTable::getList($parameters);

				$elementsCPriceList = array();

				while ($row = $cpriceResult->fetch())
				{
					$row['PRODUCT_TYPE_CODE'] = $typeElements;
					$productId = (int)$row['PRODUCT_ID'];
					if (!isset($elementsCPriceList[$productId]))
						$elementsCPriceList[$productId] = array(
							'QUANTITY' => array(),
							'SIMPLE' => array()
						);
					$priceType = (int)$row['CATALOG_GROUP_ID'];
					if ($row['QUANTITY_FROM'] !== null || $row['QUANTITY_TO'] !== null)
					{
						$hash = ($row['QUANTITY_FROM'] === null ? 'ZERO' : $row['QUANTITY_FROM']).
							'-'.($row['QUANTITY_TO'] === null ? 'INF' : $row['QUANTITY_TO']);
						if (!isset($elementsCPriceList[$productId]['QUANTITY'][$hash]))
							$elementsCPriceList[$productId]['QUANTITY'][$hash] = array();
						$elementsCPriceList[$productId]['QUANTITY'][$hash][$priceType] = $row;
						unset($hash);
					}
					else
					{
						$elementsCPriceList[$productId]['SIMPLE'][$priceType] = $row;
					}
				}

				if (!empty($elementsCPriceList))
				{
					foreach ($elementsCPriceList as $productId => $prices)
					{
						foreach ($prices as $key => $data)
						{
							if (empty($data))
								unset($prices[$key]);
						}
						unset($key, $data);

						if (count($prices) !== 1)
							continue;

						if (!empty($prices['QUANTITY']))
						{
							foreach ($prices['QUANTITY'] as $hash => $rangePrices)
							{
								if (!empty($rangePrices))
									$this->updatePriceBlock($productId, $rangePrices, $basePriceId);
							}
							unset($hash, $rangePrices);
						}

						if (!empty($prices['SIMPLE']))
						{
							$this->updatePriceBlock($productId, $prices['SIMPLE'], $basePriceId);
						}
					}
					unset($productId, $prices);
				}
				unset($elementsCPriceList);
			}
		}
		return $result;
	}

	private function updatePriceBlock($productId, array $prices, $basePriceId)
	{
		$result = new Main\Result();

		$initialType = 0;
		if (isset($this->userDialogParams['INITIAL_PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['INITIAL_PRICE_TYPE'];
			if ($id > 0)
				$initialType = $id;
			unset($id);
		}

		$targetType = 0;
		if (isset($this->userDialogParams['PRICE_TYPE']))
		{
			$id = (int)$this->userDialogParams['PRICE_TYPE'];
			if ($id > 0)
				$targetType = $id;
			unset($id);
		}

		if (!empty($prices))
		{
			$destinationPrice = null;
			if ($initialType > 0)
			{
				if (isset($prices[$initialType]))
				{
					$sourcePrice = $prices[$initialType];
					$destinationPrice = $prices[$initialType];
					unset($destinationPrice['ID']);
					$destinationPrice['EXTRA_ID'] = false;
					$destinationPrice['CATALOG_GROUP_ID'] = $targetType;
					if (isset($prices[$targetType]))
						$destinationPrice = $prices[$targetType];
					if (
						$this->userDialogParams['UNITS'] != 'currency'
						|| (
							$sourcePrice['CURRENCY'] == $this->userDialogParams['CURRENCY']
							&& $destinationPrice['CURRENCY'] == $this->userDialogParams['CURRENCY']
						)
					)
						$destinationPrice['PRICE'] = $this->calculateResultPrice($sourcePrice['PRICE']);
					else
						$destinationPrice = null;
					unset($sourcePrice);
				}
			}
			else
			{
				if (isset($prices[$targetType]))
				{
					$destinationPrice = $prices[$targetType];
					if (
						$this->userDialogParams['UNITS'] != 'currency'
						|| $destinationPrice['CURRENCY'] == $this->userDialogParams['CURRENCY']
					)
						$destinationPrice['PRICE'] = $this->calculateResultPrice($destinationPrice['PRICE']);
					else
						$destinationPrice = null;
				}
			}
			if (!empty($destinationPrice))
			{
				if ($destinationPrice['PRICE'] <= 0)
				{
					$result->addError(
						new Main\Error("IBLIST_CHPRICE_ERROR_WRONG_VALUE_".$destinationPrice['PRODUCT_TYPE_CODE'],
							array(
								'#ID#' => $destinationPrice['PRODUCT_ID'],
								'#NAME#' => $destinationPrice['ELEMENT_NAME'],
							)
						)
					);
				}
				elseif ($destinationPrice['EXTRA_ID'] > 0)
				{
					$result->addError(
						new Main\Error("IBLIST_CHPRICE_ERROR_PRICE_WITH_EXTRA_".$destinationPrice['PRODUCT_TYPE_CODE'],
							array(
								'#ID#' => $destinationPrice['PRODUCT_ID'],
								'#NAME#' => $destinationPrice['ELEMENT_NAME'],
							)
						)
					);
				}
				else
				{
					if (!empty($destinationPrice['ID']))
					{
						$priceResult = \CPrice::Update(
							$destinationPrice['ID'],
							array(
								'PRODUCT_ID' => $productId,
								'CATALOG_GROUP_ID' => $destinationPrice['CATALOG_GROUP_ID'],
								'PRICE' => $destinationPrice['PRICE'],
								'CURRENCY' => $destinationPrice['CURRENCY'],
							),
							$basePriceId == $targetType
						);
					}
					else
					{
						$priceResult = \CPrice::Add(array(
							'PRODUCT_ID' => $productId,
							'CATALOG_GROUP_ID' => $targetType,
							'PRICE' => $destinationPrice['PRICE'],
							'CURRENCY' => $destinationPrice['CURRENCY'],
							'EXTRA_ID' => $destinationPrice['EXTRA_ID'],
							'QUANTITY_FROM' => ($destinationPrice['QUANTITY_FROM'] !== null ? $destinationPrice['QUANTITY_FROM'] : false),
							'QUANTITY_TO' => ($destinationPrice['QUANTITY_TO'] !== null ? $destinationPrice['QUANTITY_TO'] : false)
						));
					}
					if ($priceResult)
					{
						Iblock\PropertyIndex\Manager::updateElementIndex($destinationPrice['ELEMENT_IBLOCK_ID'], $destinationPrice['PRODUCT_ID']);
						$ipropValues = new Iblock\InheritedProperty\ElementValues($destinationPrice['ELEMENT_IBLOCK_ID'], $destinationPrice['PRODUCT_ID']);
						$ipropValues->clearValues();
						unset($ipropValues);
					}
					unset($priceResult);
				}
			}
			unset($destinationPrice);
		}
	}
}