<?php

namespace Bitrix\Sale\Discount;

use Bitrix\Main\ErrorCollection;
use Bitrix\Sale\Internals\DiscountModuleTable;
use Bitrix\Sale\Internals\DiscountTable;

final class Analyzer
{
	/** @var array */
	protected $internalModules = array('sale', 'catalog', 'main');
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  static */
	private static $instance;

	/**
	 * Returns Singleton of Analyzer
	 * @return static
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
		$this->errorCollection = new ErrorCollection();
	}

	private function __clone()
	{
	}

	/**
	 * Returns true if the discount contains action with gift.
	 *
	 * @param array $discount Discount.
	 * @return bool
	 */
	public function isContainGiftAction(array $discount)
	{
		if(isset($discount['ACTIONS']) && is_string($discount['ACTIONS']))
		{
			return strpos($discount['ACTIONS'], \CSaleActionGiftCtrlGroup::getControlID()) !== false;
		}
		elseif(isset($discount['ACTIONS_LIST']['CHILDREN']) && is_array($discount['ACTIONS_LIST']['CHILDREN']))
		{
			foreach($discount['ACTIONS_LIST']['CHILDREN'] as $child)
			{
				if(
					isset($child['CLASS_ID']) && isset($child['DATA']) &&
					$child['CLASS_ID'] === \CSaleActionGiftCtrlGroup::getControlID()
				)
				{
					return true;
				}
			}
			unset($child);
		}

		return false;
	}

	/**
	 * Tells if this discount can calculate separately. It means it doesn't depends on basket and other discounts.
	 * @param array $discount Discount.
	 *
	 * @return bool
	 */
	public function canCalculateSeparately(array $discount)
	{
		if(
			!isset($discount['LAST_DISCOUNT']) ||
			!isset($discount['LAST_LEVEL_DISCOUNT']) ||
			!isset($discount['EXECUTE_MODULE'])
		)
		{
			return false;
		}

		if ($discount['EXECUTE_MODULE'] !== 'all' && $discount['EXECUTE_MODULE'] !== 'catalog')
		{
			return false;
		}

		if ($discount['LAST_DISCOUNT'] === 'Y' || $discount['LAST_LEVEL_DISCOUNT'] === 'Y')
		{
			return false;
		}

		$tryToFindAppliedCondition = $this->tryToFindAppliedCondition($discount);
		if ($tryToFindAppliedCondition === true || $tryToFindAppliedCondition === null)
		{
			return false;
		}

		if (!$this->emptyConditionsList($discount))
		{
			return false;
		}

		if (!$this->isExistOnlySaleDiscountAction($discount))
		{
			return false;
		}

		return true;
	}

	/**
	 * Tells if allowed to calculate discount on basket separately.
	 *
	 * @return bool
	 */
	public function canCalculateSeparatelyAllDiscount()
	{
		$query = DiscountTable::query();
		$query->setSelect(array('ID'));
		$query->setFilter(array(
			'=ACTIVE' => 'Y',
			'!EXECUTE_MODE' => DiscountTable::EXECUTE_MODE_SEPARATELY,
		));
		$query->setLimit(1);

		return !(bool)$query->exec()->fetch() && !$this->isThereCustomDiscountModules();
	}

	private function isThereCustomDiscountModules()
	{
		$query = DiscountModuleTable::query();
		$query->setSelect(array('ID'));
		$query->setFilter(array(
			'!@MODULE_ID' => $this->internalModules,
		));
		$query->setLimit(1);

		return (bool)$query->exec()->fetch();
	}

	private function isExistOnlySaleDiscountAction(array $discount)
	{
		$actionStructure = $discount['ACTIONS_LIST'];
		if (!$actionStructure || !is_array($actionStructure))
		{
			return null;
		}

		if ($actionStructure['CLASS_ID'] != 'CondGroup')
		{
			return false;
		}

		if (count($actionStructure['CHILDREN']) > 1)
		{
			return false;
		}

		$action = reset($actionStructure['CHILDREN']);
		if ($action['CLASS_ID'] != 'ActSaleBsktGrp')
		{
			return false;
		}

		return true;
	}

	private function emptyConditionsList(array $discount)
	{
		if (empty($discount['CONDITIONS_LIST']) || !is_array($discount['CONDITIONS_LIST']))
		{
			return null;
		}

		if (empty($discount['CONDITIONS_LIST']['CHILDREN']))
		{
			return true;
		}

		return false;
	}

	private function tryToFindAppliedCondition(array $discount)
	{
		if (isset($discount['ACTIONS']) && is_string($discount['ACTIONS']))
		{
			return strpos($discount['ACTIONS'], \CSaleActionCondCtrlBasketFields::CONTROL_ID_APPLIED_DISCOUNT) !== false;
		}

		if (isset($discount['ACTIONS_LIST']))
		{
			$asString = serialize($discount['ACTIONS_LIST']);
			if ($asString)
			{
				return strpos($asString, \CSaleActionCondCtrlBasketFields::CONTROL_ID_APPLIED_DISCOUNT) !== false;
			}
		}

		//it means that there are not ACTIONS or ACTIONS_LIST. So, we can't solve this and decide, that we found condition.
		//after that this discount will be marked as EXECUTE_MODE=GENERAL
		return null;
	}
}