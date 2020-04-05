<?php

namespace Bitrix\Sale\Discount\Index;


use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;

final class Manager
{
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  Manager */
	private static $instance;


	/**
	 * Returns Singleton of Manager
	 * @return Manager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	private function __construct()
	{
		$this->errorCollection = new ErrorCollection;
	}

	private function __clone()
	{}

	public function dropIndex($discountId)
	{
		IndexElementTable::deleteByDiscount($discountId);
		IndexSectionTable::deleteByDiscount($discountId);
	}

	public function indexDiscount(array $discount)
	{
		if(empty($discount['ID']))
		{
			return false;
		}

		$condition = $this->getConditionStructure($discount);
		if(!$condition)
		{
			return false;
		}
		
		list($elementIds, $sectionIds) = $this->extractElementsAndSections($condition);

		$this->dropIndex($discount['ID']);

		if(!$elementIds && !$sectionIds)
		{
			return false;
		}

		if($elementIds)
		{
			IndexElementTable::fillByDiscount($discount['ID'], $elementIds);
		}

		if($sectionIds)
		{
			IndexSectionTable::fillByDiscount($discount['ID'], $sectionIds);
		}
		
		return true;
	}

	public function hasDataToIndex(array $discount)
	{
		list($elementIds, $sectionIds) = $this->extractElementsAndSections($discount['CONDITIONS']);

		return !empty($elementIds) || !empty($sectionIds);
	}

	private function getConditionStructure(array $discount)
	{
		if(empty($discount['CONDITIONS']) && empty($discount['CONDITIONS_LIST']))
		{
			return null;
		}

		if(!empty($discount['CONDITIONS_LIST']))
		{
			$conditions = $discount['CONDITIONS_LIST'];
		}
		else
		{
			$conditions = $discount['CONDITIONS'];
		}

		if(is_string($conditions))
		{
			$conditions = unserialize($conditions);
		}

		if(!$conditions || !is_array($conditions))
		{
			return null;
		}

		return $conditions;
	}

	private function extractElementsAndSections(array $condition)
	{
		if(empty($condition['CLASS_ID']))
		{
			return null;
		}

		if($condition['CLASS_ID'] !== 'CondGroup' || empty($condition['DATA']))
		{
			return null;
		}

		if(empty($condition['CHILDREN']))
		{
			return null;
		}

		if(
			!($condition['DATA']['All'] === 'AND' && $condition['DATA']['True'] === 'True') &&
			!($condition['DATA']['All'] === 'OR'  && $condition['DATA']['True'] === 'True')
		)
		{
			return null;
		}

		$elementIds = $sectionIds = array();
		foreach($condition['CHILDREN'] as $child)
		{
			$onlyOneCondition =
				count($child['CHILDREN']) === 1 &&
				$child['DATA']['All'] === 'AND' &&
				$child['DATA']['Found'] === 'Found'
			;

			if(
				$child['CLASS_ID'] === 'CondBsktProductGroup' &&
				(
					($child['DATA']['All'] === 'OR' && $child['DATA']['Found'] === 'Found') ||
					$onlyOneCondition
				)
			)
			{
				foreach($child['CHILDREN'] as $grandchild)
				{
					switch($grandchild['CLASS_ID'])
					{
						case 'CondIBElement':
							if (is_array($grandchild['DATA']['value']))
							{
								$elementIds = array_merge($elementIds, $grandchild['DATA']['value']);
							}
							else
							{
								$elementIds[] = $grandchild['DATA']['value'];
							}
							break;
						case 'CondIBSection':
							if($grandchild['DATA']['logic'] === 'Equal')
							{
								$sectionIds[] = $grandchild['DATA']['value'];
							}
							break;
					}
				}
			}
		}

		$elementIds = $this->convertSkuToMainProducts($elementIds);

		return array($elementIds, $sectionIds);
	}

	private function convertSkuToMainProducts(array $elementIds)
	{
		if (!Loader::includeModule('catalog'))
		{
			return $elementIds;
		}

		$products = \CCatalogSKU::getProductList($elementIds);
		if (empty($products))
		{
			return $elementIds;
		}

		$newElementIds = array_combine($elementIds, $elementIds);
		foreach($products as $offerId => $product)
		{
			if(isset($newElementIds[$offerId]))
			{
				$newElementIds[$product['ID']] = $product['ID'];
				unset($newElementIds[$offerId]);
			}
		}

		return array_values($newElementIds);
	}
}