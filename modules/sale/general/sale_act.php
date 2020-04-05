<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale;

if (!Loader::includeModule('catalog'))
	return;

Loc::loadMessages(__FILE__);

/**
 * @deprecated deprecated since sale 16.0.10
 * @see \Bitrix\Sale\Discount\Actions
 */
class CSaleDiscountActionApply
{
	const VALUE_TYPE_FIX = Sale\Discount\Actions::VALUE_TYPE_FIX;
	const VALUE_TYPE_PERCENT = Sale\Discount\Actions::VALUE_TYPE_PERCENT;
	const VALUE_TYPE_SUMM = Sale\Discount\Actions::VALUE_TYPE_SUMM;

	const GIFT_SELECT_TYPE_ONE = Sale\Discount\Actions::GIFT_SELECT_TYPE_ONE;
	const GIFT_SELECT_TYPE_ALL = Sale\Discount\Actions::GIFT_SELECT_TYPE_ALL;

	const ORDER_MANUAL_MODE_FIELD = 'ORDER_MANUAL_MODE';
	const BASKET_APPLIED_FIELD = Sale\Discount\Actions::BASKET_APPLIED_FIELD;

	const EPS = Sale\Discount\Actions::VALUE_EPS;

	protected static $getPercentFromBasePrice = null;

	/**
	 * Check discount calculate mode field for order.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::isManualMode
	 *
	 * @param array $order			Order data.
	 * @return bool
	 */
	public static function isManualMode(/** @noinspection PhpUnusedParameterInspection */$order)
	{
		return Sale\Discount\Actions::isManualMode();
	}

	/**
	 * Set discount calculate mode field for order.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::setUseMode
	 *
	 * @param array &$order			Order data.
	 * @return void
	 */
	public static function setManualMode(&$order)
	{
		if (empty($order) || empty($order['ID']))
			return;
		Sale\Discount\Actions::setUseMode(
			Sale\Discount\Actions::MODE_MANUAL,
			array(
				'USE_BASE_PRICE' => $order['USE_BASE_PRICE'],
				'SITE_ID' => $order['SITE_ID'],
				'CURRENCY' => $order['CURRENCY']
			)
		);
	}

	/**
	 * Erase discount calculate mode field for order.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::setUseMode
	 *
	 * @param array &$order			Order data.
	 * @return void
	 */
	public static function clearManualMode(&$order)
	{
		if (empty($order) || !is_array($order))
			return;
		Sale\Discount\Actions::setUseMode(Sale\Discount\Actions::MODE_CALCULATE);
	}

	/**
	 * Return true, if discount already applied by basket item.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 *
	 * @param array $row			Basket row.
	 * @return bool
	 */
	public static function filterApplied($row)
	{
		/* @noinspection PhpDeprecationInspection */
		return (isset($row[self::BASKET_APPLIED_FIELD]));
	}

	/**
	 * Fill basket applied information.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 *
	 * @param array &$order			Order data.
	 * @param array $basket			Applied information (key - BASKET_ID, value - Y/N).
	 * @return void
	 */
	public static function fillBasketApplied(&$order, $basket)
	{
		if (empty($order) || empty($order['ID']) || empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
			return;
		if (empty($basket) || !is_array($basket))
			return;
		$founded = false;
		foreach ($basket as $itemId => $value)
		{
			foreach ($order['BASKET_ITEMS'] as &$basketRow)
			{
				if (isset($basketRow['ID']) && $basketRow['ID'] == $itemId)
				{
					$founded = true;
					/* @noinspection PhpDeprecationInspection */
					$basketRow[self::BASKET_APPLIED_FIELD] = $value;
					break;
				}
			}
			unset($basketRow);
		}
		unset($value, $itemId);
		if ($founded)
			/* @noinspection PhpDeprecationInspection */
			self::setManualMode($order);
	}

	/**
	 * Clear basket applied information.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 *
	 * @param array &$order				Order data.
	 * @return void
	 */
	public static function clearBasketApplied(&$order)
	{
		if (empty($order) || empty($order['ID']) || empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
			return;
		foreach ($order['BASKET_ITEMS'] as &$basketRow)
		{
			/* @noinspection PhpDeprecationInspection */
			if (array_key_exists(self::BASKET_APPLIED_FIELD, $basketRow))
				/* @noinspection PhpDeprecationInspection */
				unset($basketRow[self::BASKET_APPLIED_FIELD]);
		}
		unset($basketRow);
	}

	/**
	 * Filter for undiscount basket items.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::filterBasketForAction
	 *
	 * @param array $row		Basket item.
	 * @return bool
	 */
	public static function ClearBasket($row)
	{
		return Sale\Discount\Actions::filterBasketForAction($row);
	}

	/**
	 * Apply discount to delivery price.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::applyToDelivery
	 *
	 * @param array &$order				Order data.
	 * @param float $value				Discount value.
	 * @param string $unit				Value unit.
	 * @param bool $extMode				Apply mode percent discount.
	 * @return void
	 */
	public static function ApplyDelivery(&$order, $value, $unit, $extMode = false)
	{
		$extMode = ($extMode === true);
		$params = array(
			'VALUE' => $value,
			'UNIT' => $unit,
		);
		if ($extMode)
			$params['MAX_BOUND'] = 'Y';
		Sale\Discount\Actions::applyToDelivery(
			$order,
			$params
		);
		unset($params);
	}

	/**
	 * Apply discount to basket.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::applyToBasket
	 *
	 * @param array &$order			Order data.
	 * @param callable $func		Filter function.
	 * @param float $value			Discount value.
	 * @param string $unit			Value unit.
	 * @return void
	 */
	public static function ApplyBasketDiscount(&$order, $func, $value, $unit)
	{
		Sale\Discount\Actions::applyToBasket(
			$order,
			array(
				'VALUE' => $value,
				'UNIT' => $unit
			),
			$func
		);
	}

	/**
	 * Apply simple gift discount.
	 *
	 * @deprecated deprecated since sale 16.0.10
	 * @see \Bitrix\Sale\Discount\Actions::applySimpleGift
	 *
	 * @param array &$order				Order data.
	 * @param callable $callableFilter	Filter function.
	 * @return void
	 */
	public static function ApplyGiftDiscount(&$order, $callableFilter)
	{
		Sale\Discount\Actions::applySimpleGift($order, $callableFilter);
	}
}

class CSaleActionCtrl extends CGlobalCondCtrl
{
	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		if ($arParams['ID'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arParams['ID'],
			'ATOMS' => static::GetAtomsEx(false, true),
		);

		return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		if ($arOneCondition['controlId'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arOneCondition['controlId'],
			'ATOMS' => static::GetAtomsEx(false, true),
		);

		return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
	}
}

class CSaleCumulativeAction extends CGlobalCondCtrl
{
	public static function GetControlID()
	{
		return 'CumulativeAction';
	}

	public static function onBuildDiscountActionInterfaceControls()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			static::getControlDescr() + array('GROUP' => 'Y', 'EXECUTE_MODULE' => 'sale'),
			'sale'
		);
	}

	public static function IsGroup($strControlID = false)
	{
		return 'Y';
	}

	public static function Generate($oneCondition, $params, $control, $subs = false)
	{
		if (empty($oneCondition['ranges']) || !is_array($oneCondition['ranges']))
		{
			return '';
		}

		$filterCode = 'null';
		if ($subs && is_array($subs))
		{
			$filterCode = static::buildSubsCode($subs, $oneCondition);
		}

		$rangesAsString = var_export($oneCondition['ranges'], true);

		static::convertSumConfigurationDateToInt($oneCondition['sum_period_data']);

		$configurationAsString = var_export(
			array(
				'sum' => $oneCondition,
				'apply_if_more_profitable' => $oneCondition['apply_if_more_profitable'],
			),
			true
		);

		/** @see \Bitrix\Sale\Discount\Actions::applyCumulativeToBasket() */
		return "\\Bitrix\\Sale\\Discount\\Actions::applyCumulativeToBasket({$params['ORDER']}, {$rangesAsString}, {$configurationAsString}, {$filterCode})";
	}

	protected static function buildSubsCode(array $subs, array $oneCondition)
	{
		if ($oneCondition['All'] == 'AND')
		{
			$prefix = '';
			$logic = ' && ';
			$itemPrefix = ($oneCondition['True'] == 'True' ? '' : '!');
		}
		else
		{
			$itemPrefix = '';
			if ($oneCondition['True'] == 'True')
			{
				$prefix = '';
				$logic = ' || ';
			}
			else
			{
				$prefix = '!';
				$logic = ' && ';
			}
		}

		$commandLine = $itemPrefix . implode($logic . $itemPrefix, $subs);
		if ($prefix != '')
		{
			$commandLine = $prefix . '(' . $commandLine . ')';
		}

		$code = "function(\$row){
			return ({$commandLine});
		}";

		return $code;
	}

	protected static function convertSumConfigurationDateToInt(&$periodData = array())
	{
		/** @see \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_ALL_TIME */
		/** @see \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_INTERVAL */
		/** @see \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_RELATIVE */

		if (isset($periodData['discount_sum_order_start']))
		{
			static::ConvertDateTime2Int(
				$periodData['discount_sum_order_start'],
				'FULL',
				\CTimeZone::getOffset()
			);
		}

		if (isset($periodData['discount_sum_order_end']))
		{
			static::ConvertDateTime2Int(
				$periodData['discount_sum_order_end'],
				'FULL',
				\CTimeZone::getOffset()
			);
		}
	}
}

class CSaleActionCtrlComplex extends CGlobalCondCtrlComplex
{

}

class CSaleActionCtrlGroup extends CGlobalCondCtrlGroup
{
	public static function GetShowIn($arControls)
	{
		$arControls = array();
		return $arControls;
	}

	public static function GetControlShow($arParams)
	{
		$arResult = array(
			'controlId' => static::GetControlID(),
			'group' => true,
			'label' => '',
			'defaultText' => '',
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_GROUP_GLOBAL_PREFIX')
			)
		);

		return $arResult;
	}

	public static function GetConditionShow($arParams)
	{
		return array(
			'id' => $arParams['COND_NUM'],
			'controlId' => static::GetControlID(),
			'values' => array()
		);
	}

	public static function Parse($arOneCondition)
	{
		return array(
			'All' => 'AND'
		);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		if (!empty($arSubs) && is_array($arSubs))
			return 'function (&'.$arParams['ORDER'].'){'.implode('; ',$arSubs).';};';
		return '';
	}
}

class CSaleActionGiftCtrlGroup extends CSaleActionCtrlGroup
{
	public static function GetShowIn($arControls)
	{
		$arControls = array(
			'CondGroup'
		);
		return $arControls;
	}

	public static function GetControlID()
	{
		return 'GiftCondGroup';
	}

	public static function GetAtoms()
	{
		return static::GetAtomsEx(false, false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = (true === $boolEx ? true : false);
		$arAtomList = array();

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneAtom)
			{
				$arOneAtom = $arOneAtom['JS'];
			}
				if (isset($arOneAtom))
					unset($arOneAtom);
		}

		return $arAtomList;
	}

	public static function GetControlDescr()
	{
		$controlDescr = parent::GetControlDescr();
		$controlDescr['FORCED_SHOW_LIST'] = array(
			'GifterCondIBElement',
			'GifterCondIBSection',
		);
		$controlDescr['SORT'] = 300;

		return $controlDescr;
	}

	public static function GetControlShow($arParams)
	{
		return array(
			'controlId' => static::GetControlID(),
			'group' => true,
			'containsOneAction' => true,
			'label' => Loc::getMessage('BT_SALE_ACT_GIFT_LABEL'),
			'defaultText' => '',
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_GIFT_GROUP_PRODUCT_PREFIX'),
			)
		);
	}

	public static function Parse($arOneCondition)
	{
		return array(
			'All' => 'AND'
		);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		//I have to notice current method can work only with Gifter's. For example, it is CCatalogGifterProduct.
		//Probably in future we'll add another gifter's and create interface or class, which will tell about attitude to CSaleActionGiftCtrlGroup.
		$mxResult = '';
		$boolError = false;

		if (!isset($arSubs) || !is_array($arSubs) || empty($arSubs))
		{
			$boolError = true;
		}
		else
		{
			$mxResult = '\Bitrix\Sale\Discount\Actions::applySimpleGift(' . $arParams['ORDER'] . ', ' . implode('; ',$arSubs) . ');';
		}
		return $mxResult;
	}

	public static function ProvideGiftProductData(array $fields)
	{
		if(isset($fields['ACTIONS']) && is_array($fields['ACTIONS']))
		{
			$fields['ACTIONS_LIST'] = $fields['ACTIONS'];
		}

		if (
				(empty($fields['ACTIONS_LIST']) || !is_array($fields['ACTIONS_LIST']))
				&& CheckSerializedData($fields['ACTIONS']))
		{
			$actions = unserialize($fields['ACTIONS']);
		}
		else
		{
			$actions = $fields['ACTIONS_LIST'];
		}

		if (!is_array($actions) || empty($actions) || empty($actions['CHILDREN']))
		{
			return array();
		}

		$giftCondGroups = array();
		foreach($actions['CHILDREN'] as $child)
		{
			if(isset($child['CLASS_ID']) && isset($child['DATA']) && $child['CLASS_ID'] === static::GetControlID())
			{
				//we know that in GiftCondGroup may be only once child. See 'containsOneAction' option in method GetControlShow().
				$giftCondGroups[] = reset($child['CHILDREN']);
			}
		}
		unset($child);

		$giftsData = array();
		foreach($giftCondGroups as $child)
		{
			//todo so hard, but we can't made abstraction every time.
			if(isset($child['CLASS_ID']) && isset($child['DATA']))
			{
				$gifter = static::getGifter($child);
				if(!$gifter)
				{
					continue;
				}
				$giftsData[] = $gifter->ProvideGiftData($child);
			}
		}
		unset($child);

		return $giftsData;
	}

	protected static function getGifter(array $data)
	{
		if(in_array($data['CLASS_ID'], array('GifterCondIBElement', 'GifterCondIBSection')))
		{
			return new CCatalogGifterProduct;
		}
		return null;
	}

	/**
	 * Extends list of products by base product, if we have SKU in list.
	 *
	 * @param array $productIds
	 * @return array
	 */
	public static function ExtendProductIds(array $productIds)
	{
		return CCatalogGifterProduct::ExtendProductIds($productIds);
	}
}

class CSaleActionCtrlAction extends CGlobalCondCtrlGroup
{
	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		if ($arParams['ID'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arParams['ID'],
			'ATOMS' => static::GetAtomsEx(false, true)
		);

		return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		if ($arOneCondition['controlId'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arOneCondition['controlId'],
			'ATOMS' => static::GetAtomsEx(false, true)
		);

		return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
	}

	public static function GetVisual()
	{
		return array(
			'controls' => array(
				'All'
			),
			'values' => array(
				array(
					'All' => 'AND'
				),
				array(
					'All' => 'OR'
				)
			),
			'logic' => array(
				array(
					'style' => 'condition-logic-and',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_AND')
				),
				array(
					'style' => 'condition-logic-or',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_OR')
				)
			)
		);
	}
}

class CSaleActionCtrlDelivery extends CSaleActionCtrl
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['EXECUTE_MODULE'] = 'sale';
		$description['SORT'] = 200;
		return $description;
	}

	public static function GetControlID()
	{
		return 'ActSaleDelivery';
	}

	public static function GetControlShow($arParams)
	{
		$arAtoms = static::GetAtomsEx(false, false);
		$boolCurrency = false;
		if (static::$boolInit)
		{
			if (isset(static::$arInitParams['CURRENCY']))
			{
				$arAtoms['Unit']['values']['Cur'] = static::$arInitParams['CURRENCY'];
				$boolCurrency = true;
			}
			elseif (isset(static::$arInitParams['SITE_ID']))
			{
				$strCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
				if (!empty($strCurrency))
				{
					$arAtoms['Unit']['values']['Cur'] = $strCurrency;
					$boolCurrency = true;
				}
			}
		}
		if (!$boolCurrency)
		{
			unset($arAtoms['Unit']['values']['Cur']);
		}
		$arResult = array(
			'controlId' => static::GetControlID(),
			'group' => false,
			'label' => Loc::getMessage('BT_SALE_ACT_DELIVERY_LABEL'),
			'defaultText' => Loc::getMessage('BT_SALE_ACT_DELIVERY_DEF_TEXT'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_DELIVERY_GROUP_PRODUCT_PREFIX'),
				$arAtoms['Type'],
				$arAtoms['Value'],
				$arAtoms['Unit']
			)
		);

		return $arResult;
	}

	public static function GetAtoms()
	{
		return static::GetAtomsEx(false, false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = (true === $boolEx ? true : false);
		$arAtomList = array(
			'Type' => array(
				'JS' => array(
					'id' => 'Type',
					'name' => 'extra',
					'type' => 'select',
					'values' => array(
						'Discount' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_TYPE_DISCOUNT'),
						'DiscountZero' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_TYPE_DISCOUNT_ZERO'),
						'Extra' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_TYPE_EXTRA'),
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_TYPE_DEF'),
					'defaultValue' => 'Discount',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Type',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'Value' => array(
				'JS' => array(
					'id' => 'Value',
					'name' => 'extra_size',
					'type' => 'input'
				),
				'ATOM' => array(
					'ID' => 'Value',
					'FIELD_TYPE' => 'double',
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			),
			'Unit' => array(
				'JS' => array(
					'id' => 'Unit',
					'name' => 'extra_unit',
					'type' => 'select',
					'values' => array(
						'Perc' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_PERCENT'),
						'Cur' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_CUR')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_DELIVERY_SELECT_UNIT_DEF'),
					'defaultValue' => 'Perc',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Unit',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			)
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneAtom)
			{
				$arOneAtom = $arOneAtom['JS'];
			}
				if (isset($arOneAtom))
					unset($arOneAtom);
		}

		return $arAtomList;
	}

	public static function GetShowIn($arControls)
	{
		return array(CSaleActionCtrlGroup::GetControlID());
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$mxResult = '';

		if (is_string($arControl))
		{
			if ($arControl == static::GetControlID())
			{
				$arControl = array(
					'ID' => static::GetControlID(),
					'ATOMS' => static::GetAtoms()
				);
			}
		}
		$boolError = !is_array($arControl);

		if (!$boolError)
		{
			$arOneCondition['Value'] = (float)$arOneCondition['Value'];
			$actionParams = array(
				'VALUE' => ($arOneCondition['Type'] == 'Extra' ? $arOneCondition['Value'] : -$arOneCondition['Value']),
				'UNIT' => ($arOneCondition['Unit'] == 'Cur' ? Sale\Discount\Actions::VALUE_TYPE_FIX : Sale\Discount\Actions::VALUE_TYPE_PERCENT)
			);
			if ($arOneCondition['Type'] == 'DiscountZero' && $arOneCondition['Unit'] == 'Cur')
				$actionParams['MAX_BOUND'] = 'Y';

			$mxResult = '\Bitrix\Sale\Discount\Actions::applyToDelivery('.$arParams['ORDER'].', '.var_export($actionParams, true).')';
			unset($actionParams);
		}

		return $mxResult;
	}
}

class CSaleActionGift extends CSaleActionCtrl
{
	public static function GetControlDescr()
	{
		$controlDescr = parent::GetControlDescr();

		$controlDescr['PARENT'] = true;
		$controlDescr['EXIST_HANDLER'] = 'Y';
		$controlDescr['MODULE_ID'] = 'catalog';
		$controlDescr['MODULE_ENTITY'] = 'iblock';
		$controlDescr['ENTITY'] = 'ELEMENT';
		$controlDescr['FIELD'] = 'ID';

		return $controlDescr;
	}

	public static function GetControlID()
	{
		return 'ActSaleGift';
	}

	public static function GetControlShow($arParams)
	{
		$arAtoms = static::GetAtomsEx(false, false);
		if (static::$boolInit)
		{
			//here initialize
		}
		$arResult = array(
			'controlId' => static::GetControlID(),
			'group' => false,
			'label' => Loc::getMessage('BT_SALE_ACT_GIFT_LABEL'),
			'defaultText' => Loc::getMessage('BT_SALE_ACT_GIFT_DEF_TEXT'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_GIFT_GROUP_PRODUCT_PREFIX'),
				$arAtoms['Type'],
				$arAtoms['GiftValue'],
			),
		);

		return $arResult;
	}

	public static function GetAtoms()
	{
		return static::GetAtomsEx(false, false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = (true === $boolEx ? true : false);
		$arAtomList = array(
			'Type' => array(
				'JS' => array(
					'id' => 'Type',
					'name' => 'extra',
					'type' => 'select',
					'values' => array(
						Sale\Discount\Actions::GIFT_SELECT_TYPE_ONE => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ONE'),
						Sale\Discount\Actions::GIFT_SELECT_TYPE_ALL => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ALL'),
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_DEF'),
					'defaultValue' => 'one',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Type',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'GiftValue' => array(
				'JS' => array(
					'id' => 'GiftValue',
					'name' => 'gifts_value',
					'type' => 'multiDialog',
					'popup_url' =>  '/bitrix/tools/sale/product_search_dialog.php',
					'popup_params' => array(
						'lang' => LANGUAGE_ID,
						'caller' => 'discount'
					),
					'param_id' => 'n',
					'show_value' => 'Y'
				),
				'ATOM' => array(
					'ID' => 'GiftValue',

					'PARENT' => true,
					'EXIST_HANDLER' => 'Y',
					'MODULE_ID' => 'catalog',
					'MODULE_ENTITY' => 'iblock',
					'ENTITY' => 'ELEMENT',
					'FIELD' => 'ID',

					'FIELD_TYPE' => 'int',
					'VALIDATE' => 'element',
					'PHP_VALUE' => array(
						'VALIDATE' => 'element'
					)
				)
			),
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneAtom)
			{
				$arOneAtom = $arOneAtom['JS'];
			}
				if (isset($arOneAtom))
					unset($arOneAtom);
		}

		return $arAtomList;
	}

	public static function GetShowIn($arControls)
	{
		return array(CSaleActionCtrlGroup::GetControlID());
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$mxResult = '';
		if (is_string($arControl) && $arControl == static::GetControlID())
		{
			$arControl = array(
				'ID' => static::GetControlID(),
				'ATOMS' => static::GetAtoms()
			);
		}
		$boolError = !is_array($arControl);

		if (!$boolError)
		{
			$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);
			$arValues = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, true);
			$boolError = ($arValues === false);
		}

		if (!$boolError)
		{
			$stringArray = 'array(' . implode(',', array_map('intval', $arOneCondition['GiftValue'])) . ')';
			$type = $arOneCondition['Type'];

			$mxResult = "CSaleDiscountActionApply::ApplyGiftDiscount({$arParams['ORDER']}, $stringArray, '{$type}');";
		}

		return $mxResult;
	}

	public static function getGiftDataByDiscount($fields)
	{
		if (
				(empty($fields['ACTIONS_LIST']) || !is_array($fields['ACTIONS_LIST']))
				&& CheckSerializedData($fields['ACTIONS']))
		{
			$actions = unserialize($fields['ACTIONS']);
		}
		else
		{
			$actions = $fields['ACTIONS_LIST'];
		}

		if (!is_array($actions) || empty($actions) || empty($actions['CHILDREN']))
		{
			return null;
		}

		$result = null;
		foreach($actions['CHILDREN'] as $child)
		{
			if(isset($child['CLASS_ID']) && isset($child['DATA']) && $child['CLASS_ID'] === CSaleActionGift::GetControlID())
			{
				$result[] = $child['DATA'];
			}
		}
		unset($child);

		return $result;
	}
}

class CSaleActionCtrlBasketGroup extends CSaleActionCtrlAction
{
	const ACTION_TYPE_DISCOUNT = 'Discount';
	const ACTION_TYPE_EXTRA = 'Extra';
	const ACTION_TYPE_CLOSEOUT = 'Closeout';

	const VALUE_UNIT_PERCENT = 'Perc';
	const VALUE_UNIT_CURRENCY = 'CurEach';
	const VALUE_UNIT_SUMM = 'CurAll';

	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 100;
		return $description;
	}

	public static function GetControlID()
	{
		return 'ActSaleBsktGrp';
	}

	public static function GetControlShow($arParams)
	{
		$arAtoms = static::GetAtomsEx(false, false);
		$boolCurrency = false;
		if (static::$boolInit)
		{
			if (isset(static::$arInitParams['CURRENCY']))
			{
				$arAtoms['Unit']['values'][self::VALUE_UNIT_CURRENCY] = str_replace(
					'#CUR#',
					static::$arInitParams['CURRENCY'],
					$arAtoms['Unit']['values'][self::VALUE_UNIT_CURRENCY]
				);
				$arAtoms['Unit']['values'][self::VALUE_UNIT_SUMM] = str_replace(
					'#CUR#',
					static::$arInitParams['CURRENCY'],
					$arAtoms['Unit']['values'][self::VALUE_UNIT_SUMM]
				);
				$boolCurrency = true;
			}
			elseif (isset(static::$arInitParams['SITE_ID']))
			{
				$strCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
				if (!empty($strCurrency))
				{
					$arAtoms['Unit']['values'][self::VALUE_UNIT_CURRENCY] = str_replace(
						'#CUR#',
						$strCurrency,
						$arAtoms['Unit']['values'][self::VALUE_UNIT_CURRENCY]
					);
					$arAtoms['Unit']['values'][self::VALUE_UNIT_SUMM] = str_replace(
						'#CUR#',
						$strCurrency,
						$arAtoms['Unit']['values'][self::VALUE_UNIT_SUMM]
					);
					$boolCurrency = true;
				}
			}
		}
		if (!$boolCurrency)
		{
			unset($arAtoms['Unit']['values'][self::VALUE_UNIT_CURRENCY]);
			unset($arAtoms['Unit']['values'][self::VALUE_UNIT_SUMM]);
		}
		return array(
			'controlId' => static::GetControlID(),
			'group' => true,
			'label' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_LABEL'),
			'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_DEF_TEXT'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'visual' => static::GetVisual(),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_PREFIX'),
				$arAtoms['Type'],
				$arAtoms['Value'],
				$arAtoms['Unit'],
				Loc::getMessage('BT_SALE_ACT_MAX_DISCOUNT_GROUP_BASKET_DESCR'),
				$arAtoms['Max'],
				Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_DESCR_EXT'),
				$arAtoms['All'],
				$arAtoms['True']
			),
			'mess' => array(
				'ADD_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_ADD_CONTROL'),
				'SELECT_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_SELECT_CONTROL')
			)
		);
	}

	public static function CheckAtoms($arOneCondition, $arParams, $arControl, $boolShow)
	{
		//TODO: remove this after refactoring control
		if (!isset($arOneCondition['Max']))
		{
			$arOneCondition['Max'] = 0;
		}

		$result = parent::CheckAtoms($arOneCondition, $arParams, $arControl, $boolShow);
		if ($result === false)
			return false;
		if ($boolShow)
		{
			if ($result['values']['Unit'] === self::VALUE_UNIT_SUMM && !empty($result['values']['Max']))
			{
				$result['err_cond'] = 'Y';
				$result['fatal_err_cond'] = 'Y';
				if (!isset($result['err_cond_mess']))
					$result['err_cond_mess'] = Loc::getMessage('BT_SALE_ACT_MAX_DISCOUNT_ON_GROUP_BASKET_ERROR_CONDITION');
				else
					$result['err_cond_mess'] .= '. '.Loc::getMessage('BT_SALE_ACT_MAX_DISCOUNT_ON_GROUP_BASKET_ERROR_CONDITION');
			}
			return $result;
		}
		else
		{
			return ($result['Unit'] === self::VALUE_UNIT_SUMM && !empty($result['Max']) ? false : $result);
		}
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['DATA']['True']))
			$arParams['DATA']['True'] = 'True';

		return parent::GetConditionShow($arParams);
	}

	public static function GetVisual()
	{
		return array(
			'controls' => array(
				'All',
				'True'
			),
			'values' => array(
				array(
					'All' => 'AND',
					'True' => 'True'
				),
				array(
					'All' => 'AND',
					'True' => 'False'
				),
				array(
					'All' => 'OR',
					'True' => 'True'
				),
				array(
					'All' => 'OR',
					'True' => 'False'
				),
			),
			'logic' => array(
				array(
					'style' => 'condition-logic-and',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_AND')
				),
				array(
					'style' => 'condition-logic-and',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_NOT_AND')
				),
				array(
					'style' => 'condition-logic-or',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_OR')
				),
				array(
					'style' => 'condition-logic-or',
					'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_NOT_OR')
				)
			)
		);
	}

	public static function GetShowIn($arControls)
	{
		return array(CSaleActionCtrlGroup::GetControlID());
	}

	public static function GetAtoms()
	{
		return static::GetAtomsEx(false, false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = (true === $boolEx ? true : false);
		$arAtomList = array(
			'Type' => array(
				'JS' => array(
					'id' => 'Type',
					'name' => 'extra',
					'type' => 'select',
					'values' => array(
						self::ACTION_TYPE_DISCOUNT => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_DISCOUNT'),
						self::ACTION_TYPE_EXTRA => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_EXTRA'),
						self::ACTION_TYPE_CLOSEOUT => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_CLOSEOUT')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_DEF'),
					'defaultValue' => self::ACTION_TYPE_DISCOUNT,
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Type',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'Value' => array(
				'JS' => array(
					'id' => 'Value',
					'name' => 'extra_size',
					'type' => 'input'
				),
				'ATOM' => array(
					'ID' => 'Value',
					'FIELD_TYPE' => 'double',
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			),
			'Unit' => array(
				'JS' => array(
					'id' => 'Unit',
					'name' => 'extra_unit',
					'type' => 'select',
					'values' => array(
						self::VALUE_UNIT_PERCENT => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_PERCENT'),
						self::VALUE_UNIT_CURRENCY => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_CUR_EACH'),
						self::VALUE_UNIT_SUMM => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_CUR_ALL')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_UNIT_DEF'),
					'defaultValue' => self::VALUE_UNIT_PERCENT,
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Unit',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'Max' => array(
				'JS' => array(
					'id' => 'Max',
					'name' => 'max_value',
					'type' => 'input',
				),
				'ATOM' => array(
					'ID' => 'Max',
					'FIELD_TYPE' => 'int',
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			),
			'All' => array(
				'JS' => array(
					'id' => 'All',
					'name' => 'aggregator',
					'type' => 'select',
					'values' => array(
						'AND' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ALL_EXT'),
						'OR' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ANY_EXT')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_DEF'),
					'defaultValue' => 'AND',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'All',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'True' => array(
				'JS' => array(
					'id' => 'True',
					'name' => 'value',
					'type' => 'select',
					'values' => array(
						'True' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TRUE'),
						'False' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_FALSE')
					),
					'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_DEF'),
					'defaultValue' => 'True',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'True',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			)
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneAtom)
				$arOneAtom = $arOneAtom['JS'];
			unset($arOneAtom);
		}

		return $arAtomList;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$mxResult = '';
		$boolError = false;

		//TODO: remove this after refactoring control
		if (!isset($arOneCondition['Max']))
			$arOneCondition['Max'] = 0;

		foreach (static::GetAtomsEx(false, false) as $atom)
		{
			//TODO: add full check (type, list values, etc)
			if (!isset($arOneCondition[$atom['id']])			)
				$boolError = true;
		}
		unset($atom);

		if (!isset($arSubs) || !is_array($arSubs))
		{
			$boolError = true;
		}

		$unit = '';
		if (!$boolError)
		{
			switch ($arOneCondition['Unit'])
			{
				case self::VALUE_UNIT_PERCENT:
					$unit = Sale\Discount\Actions::VALUE_TYPE_PERCENT;
					break;
				case self::VALUE_UNIT_CURRENCY:
					$unit = Sale\Discount\Actions::VALUE_TYPE_FIX;
					break;
				case self::VALUE_UNIT_SUMM:
					$unit = Sale\Discount\Actions::VALUE_TYPE_SUMM;
					break;
				default:
					$boolError = true;
					break;
			}
		}

		$discountParams = [];
		if (!$boolError)
		{
			$arOneCondition['Value'] = (float)$arOneCondition['Value'];
			switch ($arOneCondition['Type'])
			{
				case self::ACTION_TYPE_DISCOUNT:
					$discountParams = [
						'VALUE' => -$arOneCondition['Value'],
						'UNIT' => $unit,
						'LIMIT_VALUE' => $arOneCondition['Max'],
					];
					break;
				case self::ACTION_TYPE_EXTRA:
					$discountParams = [
						'VALUE' => $arOneCondition['Value'],
						'UNIT' => $unit,
						'LIMIT_VALUE' => 0,
					];
					break;
				case self::ACTION_TYPE_CLOSEOUT:
					if ($unit == Sale\Discount\Actions::VALUE_TYPE_FIX)
					{
						$discountParams = [
							'VALUE' => $arOneCondition['Value'],
							'UNIT' => Sale\Discount\Actions::VALUE_TYPE_CLOSEOUT,
							'LIMIT_VALUE' => 0,
						];
					}
					else
					{
						$boolError = true;
					}
					break;
				default:
					$boolError = true;
					break;
			}
		}

		if (!$boolError)
		{
			if (!empty($arSubs))
			{
				$filter = '$saleact'.$arParams['FUNC_ID'];

				if ($arOneCondition['All'] == 'AND')
				{
					$prefix = '';
					$logic = ' && ';
					$itemPrefix = ($arOneCondition['True'] == 'True' ? '' : '!');
				}
				else
				{
					$itemPrefix = '';
					if ($arOneCondition['True'] == 'True')
					{
						$prefix = '';
						$logic = ' || ';
					}
					else
					{
						$prefix = '!';
						$logic = ' && ';
					}
				}

				$commandLine = $itemPrefix.implode($logic.$itemPrefix, $arSubs);
				if ($prefix != '')
					$commandLine = $prefix.'('.$commandLine.')';

				$mxResult = $filter.'=function($row){';
				$mxResult .= 'return ('.$commandLine.');';
				$mxResult .= '};';
				$mxResult .= '\Bitrix\Sale\Discount\Actions::applyToBasket('.$arParams['ORDER'].', '.var_export($discountParams, true).', '.$filter.');';
				unset($filter);
			}
			else
			{
				$mxResult = '\Bitrix\Sale\Discount\Actions::applyToBasket('.$arParams['ORDER'].', '.var_export($discountParams, true).', "");';
			}
			unset($discountParams, $unit);
		}


		if($boolError)
		{
			return false;
		}

		$result = array(
			'COND' => $mxResult,
		);

		if($arOneCondition['Unit'] === self::VALUE_UNIT_SUMM)
		{
			$result['OVERWRITE_CONTROL'] = array('EXECUTE_MODULE' => 'sale');
		}

		return $result;
	}
}

class CSaleActionCtrlSubGroup extends CGlobalCondCtrlGroup
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 100;
		return $description;
	}

	public static function GetControlID()
	{
		return 'ActSaleSubGrp';
	}

	public static function GetShowIn($arControls)
	{
		$arControls = array(CSaleActionCtrlBasketGroup::GetControlID());
		return $arControls;
	}
}

class CSaleActionCondCtrlBasketFields extends CSaleCondCtrlBasketFields
{
	const CONTROL_ID_APPLIED_DISCOUNT = \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED;

	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 150;
		return $description;
	}

	public static function GetControls($strControlID = false)
	{
		$arControlList = CSaleCondCtrlBasketFields::GetControls(false);
		foreach ($arControlList as &$control)
		{
			if ($control['ID'] !== \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED)
				$control['EXECUTE_MODULE'] = 'sale';
		}
		unset($control);
		return static::searchControl($arControlList, $strControlID);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = array(CSaleActionCtrlBasketGroup::GetControlID(), CSaleActionCtrlSubGroup::GetControlID());
		return $arControls;
	}
}

class CSaleActionTree extends CGlobalCondTree
{
	protected $arExecuteFunc = array();
	protected $executeModule = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	public function Generate($arConditions, $arParams)
	{
		$strFinal = '';
		$this->arExecuteFunc = array();
		$this->usedModules = array();
		$this->usedExtFiles = array();
		$this->usedEntity = array();
		$this->executeModule = array();
		if (!$this->boolError)
		{
			$strResult = '';
			if (!empty($arConditions) && is_array($arConditions))
			{
				$arParams['FUNC_ID'] = '';
				$arResult = $this->GenerateLevel($arConditions, $arParams, true);
				if (empty($arResult))
				{
					$strResult = '';
					$this->boolError = true;
				}
				else
				{
					$strResult = current($arResult);
				}
			}
			else
			{
				$this->boolError = true;
			}
			if (!$this->boolError)
			{
				$strFinal = preg_replace("#;{2,}#",";", $strResult);
			}
			return $strFinal;
		}
		else
		{
			return '';
		}
	}

	public function GenerateLevel(&$arLevel, $arParams, $boolFirst = false)
	{
		$arResult = array();
		$boolFirst = ($boolFirst === true);
		if (empty($arLevel) || !is_array($arLevel))
		{
			return $arResult;
		}
		if (!isset($arParams['FUNC_ID']))
		{
			$arParams['FUNC_ID'] = '';
		}
		$intRowNum = 0;
		if ($boolFirst)
		{
			$arParams['ROW_NUM'] = $intRowNum;
			if (!empty($arLevel['CLASS_ID']))
			{
				if (isset($this->arControlList[$arLevel['CLASS_ID']]))
				{
					$arOneControl = $this->arControlList[$arLevel['CLASS_ID']];
					if ($arOneControl['GROUP'] == 'Y')
					{
						$arSubParams = $arParams;
						$arSubParams['FUNC_ID'] .= '_'.$intRowNum;
						$arSubEval = $this->GenerateLevel($arLevel['CHILDREN'], $arSubParams);
						if ($arSubEval === false || !is_array($arSubEval))
							return false;
						$arGroupParams = $arParams;
						$arGroupParams['FUNC_ID'] .= '_'.$intRowNum;
						$mxEval = call_user_func_array($arOneControl['Generate'],
							array($arLevel['DATA'], $arGroupParams, $arLevel['CLASS_ID'], $arSubEval)
						);
						if (is_array($mxEval))
						{
							if (isset($mxEval['FUNC']))
							{
								$this->arExecuteFunc[] = $mxEval['FUNC'];
							}
							$strEval = (isset($mxEval['COND']) ? $mxEval['COND'] : false);
						}
						else
						{
							$strEval = $mxEval;
						}
					}
					else
					{
						$strEval = call_user_func_array($arOneControl['Generate'],
							array($arLevel['DATA'], $arParams, $arLevel['CLASS_ID'])
						);
					}
					if ($strEval === false || !is_string($strEval) || $strEval === 'false')
					{
						return false;
					}
					$arResult[] = $strEval;
					$this->fillUsedData($arOneControl);
				}
			}
		}
		else
		{
			foreach ($arLevel as $arOneCondition)
			{
				$arParams['ROW_NUM'] = $intRowNum;
				if (!empty($arOneCondition['CLASS_ID']))
				{
					if (isset($this->arControlList[$arOneCondition['CLASS_ID']]))
					{
						$arOneControl = $this->arControlList[$arOneCondition['CLASS_ID']];
						if ($arOneControl['GROUP'] == 'Y')
						{
							$arSubParams = $arParams;
							$arSubParams['FUNC_ID'] .= '_'.$intRowNum;
							$arSubEval = $this->GenerateLevel($arOneCondition['CHILDREN'], $arSubParams);
							if ($arSubEval === false || !is_array($arSubEval))
								return false;
							$arGroupParams = $arParams;
							$arGroupParams['FUNC_ID'] .= '_'.$intRowNum;
							$mxEval = call_user_func_array($arOneControl['Generate'],
								array($arOneCondition['DATA'], $arGroupParams, $arOneCondition['CLASS_ID'], $arSubEval)
							);
							if (is_array($mxEval))
							{
								if (isset($mxEval['FUNC']))
								{
									$this->arExecuteFunc[] = $mxEval['FUNC'];
								}
								$strEval = (isset($mxEval['COND']) ? $mxEval['COND'] : false);
							}
							else
							{
								$strEval = $mxEval;
							}
						}
						else
						{
							$strEval = call_user_func_array($arOneControl['Generate'],
								array($arOneCondition['DATA'], $arParams, $arOneCondition['CLASS_ID'])
							);
						}
						if ($strEval === false || !is_string($strEval) || $strEval === 'false')
						{
							return false;
						}
						$arResult[] = $strEval;

						if(!empty($mxEval['OVERWRITE_CONTROL']) && is_array($mxEval['OVERWRITE_CONTROL']))
						{
							$arOneControl = array_merge($arOneControl, array_intersect_key($mxEval['OVERWRITE_CONTROL'], array(
								'EXECUTE_MODULE' => true,
							)));
						}

						$this->fillUsedData($arOneControl);
					}
				}
				$intRowNum++;
			}
			unset($arOneCondition);
		}

		if (!empty($arResult))
		{
			foreach ($arResult as $key => $value)
			{
				if ($value == '' || $value == '()')
					unset($arResult[$key]);
			}
		}
		if (!empty($arResult))
			$arResult = array_values($arResult);

		return $arResult;
	}

	public function GetExecuteModule()
	{
		return (!empty($this->executeModule) ? array_keys($this->executeModule) : array());
	}

	protected function fillUsedData(&$control)
	{
		parent::fillUsedData($control);
		if (!empty($control['EXECUTE_MODULE']))
			$this->executeModule[$control['EXECUTE_MODULE']] = true;
	}
}