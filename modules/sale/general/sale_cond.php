<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\EventResult,
	Bitrix\Sale;
use Bitrix\Sale\Discount\CumulativeCalculator;

if (!Loader::includeModule('catalog'))
	return;

Loc::loadMessages(__FILE__);

class CSaleBasketFilter
{
	public static function ClearBasket($row)
	{
		return (
			(!isset($row['IN_SET']) || $row['IN_SET'] != 'Y') &&
			(
				(isset($row['TYPE']) && (int)$row['TYPE'] == CSaleBasket::TYPE_SET) ||
				(!isset($row['SET_PARENT_ID']) || (int)$row['SET_PARENT_ID'] <= 0)
			)
		);
	}

	public static function AmountFilter(&$arOrder, $func)
	{
		$dblSumm = 0.0;
		if (!empty($arOrder['BASKET_ITEMS']) && is_array($arOrder['BASKET_ITEMS']))
		{
			reset($arOrder['BASKET_ITEMS']);
			$arRes = (is_callable($func) ? array_filter($arOrder['BASKET_ITEMS'], $func) : $arOrder['BASKET_ITEMS']);
			if (!empty($arRes))
			{
				$arClear = array_filter($arRes, '\CSaleBasketFilter::ClearBasket');
				if (!empty($arClear))
				{
					foreach ($arClear as $arRow)
						$dblSumm += (float)$arRow['PRICE']*(float)$arRow['QUANTITY'];
					unset($arRow);
				}
				unset($arClear);
			}
			unset($arRes);
		}
		return $dblSumm;
	}

	public static function AmountBaseFilter(&$order, $func)
	{
		$summ = 0.0;
		if (!empty($order['BASKET_ITEMS']) && is_array($order['BASKET_ITEMS']))
		{
			reset($order['BASKET_ITEMS']);
			$basket = (is_callable($func) ? array_filter($order['BASKET_ITEMS'], $func) : $order['BASKET_ITEMS']);
			if (!empty($basket))
			{
				$clearBasket = array_filter($basket, '\CSaleBasketFilter::ClearBasket');
				if (!empty($clearBasket))
				{
					foreach ($clearBasket as $row)
						$summ += (float)$row['BASE_PRICE']*(float)$row['QUANTITY'];
					unset($arRow);
				}
				unset($clearBasket);
			}
			unset($basket);
		}
		return $summ;
	}

	public static function CountFilter(&$arOrder, $func)
	{
		$dblQuantity = 0.0;
		if (!empty($arOrder['BASKET_ITEMS']) && is_array($arOrder['BASKET_ITEMS']))
		{
			reset($arOrder['BASKET_ITEMS']);
			$arRes = (is_callable($func) ? array_filter($arOrder['BASKET_ITEMS'], $func) : $arOrder['BASKET_ITEMS']);
			if (!empty($arRes))
			{
				$arClear = array_filter($arRes, '\CSaleBasketFilter::ClearBasket');
				if (!empty($arClear))
				{
					foreach ($arClear as $arRow)
					{
						$dblQuantity += (float)$arRow['QUANTITY'];
					}
					unset($arRow);
				}
				unset($arClear);
			}
			unset($arRes);
		}
		return $dblQuantity;
	}

	public static function RowFilter(&$arOrder, $func)
	{
		$intCount = 0;
		if (!empty($arOrder['BASKET_ITEMS']) && is_array($arOrder['BASKET_ITEMS']))
		{
			reset($arOrder['BASKET_ITEMS']);
			$arRes = (is_callable($func) ? array_filter($arOrder['BASKET_ITEMS'], $func) : $arOrder['BASKET_ITEMS']);
			if (!empty($arRes))
			{
				$arClear = array_filter($arRes, '\CSaleBasketFilter::ClearBasket');
				$intCount = count($arClear);
				unset($arClear);
			}
			unset($arRes);
		}
		return $intCount;
	}

	public static function ProductFilter(&$arOrder, $func)
	{
		$boolFound = false;
		if (!empty($arOrder['BASKET_ITEMS']) && is_array($arOrder['BASKET_ITEMS']))
		{
			reset($arOrder['BASKET_ITEMS']);
			$arRes = (is_callable($func) ? array_filter($arOrder['BASKET_ITEMS'], $func) : $arOrder['BASKET_ITEMS']);
			if (!empty($arRes))
			{
				$arClear = array_filter($arRes, '\CSaleBasketFilter::ClearBasket');
				if (!empty($arClear))
					$boolFound = true;
				unset($arClear);
			}
			unset($arRes);
		}
		return $boolFound;
	}

	public static function BasketPropertyFilter($basketItem, $parameters)
	{
		$result = false;
		if (empty($basketItem['PROPERTIES']))
			return $result;

		$entity = '';
		if (isset($parameters['ENTITY_ID']))
			$entity = $parameters['ENTITY_ID'];
		if ($entity == '')
			return $result;

		foreach ($basketItem['PROPERTIES'] as $row)
		{
			if ($row[$entity] != $parameters['ENTITY_VALUE'])
				continue;

			switch ($parameters['LOGIC'])
			{
				case BT_COND_LOGIC_EQ:
					if ($row['VALUE'] === $parameters['VALUE'])
						$result = true;
					break;
				case BT_COND_LOGIC_NOT_EQ:
					if ($row['VALUE'] !== $parameters['VALUE'])
						$result = true;
					break;
				case BT_COND_LOGIC_CONT:
					if (mb_strpos($row['VALUE'], $parameters['VALUE']) !== false)
						$result = true;
					break;
				case BT_COND_LOGIC_NOT_CONT:
					if (mb_strpos($row['VALUE'], $parameters['VALUE']) === false)
						$result = true;
					break;
			}
		}
		unset($row, $entity);

		return $result;
	}
}

class CSaleCondCtrl extends CGlobalCondCtrl
{
}

class CSaleCondCtrlComplex extends CGlobalCondCtrlComplex
{
}

class CSaleCondCtrlGroup extends CGlobalCondCtrlGroup
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 100;
		return $description;
	}

	public static function GetShowIn($arControls)
	{
		return array(static::GetControlID());
	}
}

class CSaleCondCtrlBasketGroup extends CSaleCondCtrlGroup
{
	public static function GetControlID()
	{
		return array(
			'CondBsktSubGroup',
			'CondBsktCntGroup',
			'CondCumulativeGroup',
			'CondBsktAmtGroup',
			'CondBsktAmtBaseGroup',
			'CondBsktProductGroup',
			'CondBsktRowGroup'
		);
	}

	public static function GetControlDescr()
	{
		$className = get_called_class();
		$controls = static::GetControlID();
		if (empty($controls) || !is_array($controls))
			return false;
		$result = array();
		$sort = 200;
		foreach ($controls as $controlId)
		{
			$row = array(
				'ID' => $controlId,
				'GROUP' => 'Y',
				'GetControlShow' => array($className, 'GetControlShow'),
				'GetConditionShow' => array($className, 'GetConditionShow'),
				'IsGroup' => array($className, 'IsGroup'),
				'Parse' => array($className, 'Parse'),
				'Generate' => array($className, 'Generate'),
				'ApplyValues' => array($className, 'ApplyValues'),
				'InitParams' => array($className, 'InitParams'),
				'SORT' => $sort,
			);
			if ($controlId !== 'CondBsktSubGroup' && $controlId !== 'CondBsktProductGroup')
			{
				$row['EXECUTE_MODULE'] = 'sale';
			}
			if ($controlId === 'CondCumulativeGroup')
			{
				$row['FORCED_SHOW_LIST'] = array('Period', 'PeriodRelative');
			}
			$result[] = $row;
			$sort++;
		}
		unset($row, $controlId, $sort, $controls, $className);
		return $result;
	}

	public static function GetControlShow($arParams)
	{
		$result = array();

		$controls = static::GetControls();
		if (empty($controls) || !is_array($controls))
			return false;
		foreach ($controls as &$oneControl)
		{
			$row = array(
				'controlId' => $oneControl['ID'],
				'group' => true,
				'label' => $oneControl['LABEL'],
				'showIn' => $oneControl['SHOW_IN'],
				'visual' => $oneControl['VISUAL'],
				'control' => array()
			);
			if (isset($oneControl['PREFIX']))
				$row['control'][] = $oneControl['PREFIX'];
			switch ($oneControl['ID'])
			{
				case 'CondBsktCntGroup':
				case 'CondBsktAmtGroup':
				case 'CondBsktAmtBaseGroup':
				case 'CondBsktRowGroup':
					$row['control'][] = $oneControl['ATOMS']['All'];
					$row['control'][] = $oneControl['ATOMS']['Logic'];
					$row['control'][] = $oneControl['ATOMS']['Value'];
					break;
				case 'CondBsktProductGroup':
					$row['control'][] = $oneControl['ATOMS']['Found'];
					$row['control'][] = Loc::getMessage('BT_SALE_COND_GROUP_PRODUCT_DESCR');
					$row['control'][] = $oneControl['ATOMS']['All'];
					break;
				default:
					$oneControl['ATOMS'] = array_values($oneControl['ATOMS']);
					$row['control'] = (empty($row['control']) ? $oneControl['ATOMS'] : array_merge($row['control'], $oneControl['ATOMS']));
					break;
			}
			if ($oneControl['ID'] == 'CondBsktAmtGroup' || $oneControl['ID'] == 'CondBsktAmtBaseGroup' || $oneControl['ID'] == 'CondCumulativeGroup')
			{
				if (static::$boolInit)
				{
					$currency = '';
					if (isset(static::$arInitParams['CURRENCY']))
						$currency = static::$arInitParams['CURRENCY'];
					elseif (isset(static::$arInitParams['SITE_ID']))
						$currency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
					if (!empty($currency))
					{
						if($oneControl['ID'] == 'CondCumulativeGroup' && $row['control'][2]['id'] === 'Value')
						{
							//insert currency after Value atom.
							array_splice($row['control'], 3, 0, $currency);
							array_splice($row['control'], 4, 0, Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_BEFORE_CONDITION'));
							$row['containsOneAction'] = true;
						}
						else
						{
							$row['control'][] = $currency;
						}
					}
					unset($currency);
				}
			}
			if (!empty($row['control']))
				$result[] = $row;
			unset($row);
		}
		unset($oneControl);

		return $result;
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		$arControl = static::GetControls($arParams['ID']);
		if ($arControl === false)
			return false;
		$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);

		return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		$arControl = static::GetControls($arOneCondition['controlId']);
		if ($arControl === false)
			return false;
		$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);

		return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$mxResult = '';

		if (is_string($arControl))
			$arControl = static::GetControls($arControl);

		$boolError = !is_array($arControl);

		if (!isset($arSubs) || !is_array($arSubs))
			$boolError = true;

		$arValues = array();
		if (!$boolError)
		{
			$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);
			$arParams['COND_NUM'] = $arParams['FUNC_ID'];
			$arValues = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, true);
			$boolError = ($arValues === false);
		}

		if (!$boolError)
		{
			switch($arControl['ID'])
			{
				case 'CondBsktCntGroup':
					$mxResult = self::__GetCntGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondBsktAmtGroup':
					$mxResult = self::__GetAmtGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondBsktAmtBaseGroup':
					$mxResult = self::__GetAmtBaseGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondBsktProductGroup':
					$mxResult = self::__GetProductGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondBsktRowGroup':
					$mxResult = self::__GetRowGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondBsktSubGroup':
					$mxResult = self::__GetSubGroupCond($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
				case 'CondCumulativeGroup':
					$mxResult = self::getCodeForCumulativeGroupCondition($arOneCondition, $arValues['values'], $arParams, $arControl, $arSubs);
					break;
			}
		}

		return (!$boolError ? $mxResult : false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = ($boolEx === true);
		$arAmtLabels = array(
			BT_COND_LOGIC_EQ => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_EQ_LABEL'),
			BT_COND_LOGIC_NOT_EQ => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_NOT_EQ_LABEL'),
			BT_COND_LOGIC_GR => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_GR_LABEL'),
			BT_COND_LOGIC_LS => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_LS_LABEL'),
			BT_COND_LOGIC_EGR => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_EGR_LABEL'),
			BT_COND_LOGIC_ELS => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_ELS_LABEL')
		);

		$arAtomList = array(
			'CondBsktCntGroup' => array(
				'Logic' => array(
					'JS' => static::GetLogicAtom(
						static::GetLogic(
							array(
								BT_COND_LOGIC_EQ,
								BT_COND_LOGIC_NOT_EQ,
								BT_COND_LOGIC_GR,
								BT_COND_LOGIC_LS,
								BT_COND_LOGIC_EGR,
								BT_COND_LOGIC_ELS
							)
						)
					),
					'ATOM' => array(
						'ID' => 'logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'double',
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
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_NUMBER_GROUP_SELECT_DEF'),
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
				)
			),
			'CondCumulativeGroup' => array(
				'Logic' => array(
					'JS' => static::GetLogicAtom(
						static::GetLogic(
							array(
								BT_COND_LOGIC_EQ,
								BT_COND_LOGIC_NOT_EQ,
								BT_COND_LOGIC_GR,
								BT_COND_LOGIC_LS,
								BT_COND_LOGIC_EGR,
								BT_COND_LOGIC_ELS
							)
						)
					),
					'ATOM' => array(
						'ID' => 'logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'double',
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
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_NUMBER_GROUP_SELECT_DEF'),
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
				)
			),
			'CondBsktAmtGroup' => array(
				'Logic' => array(
					'JS' => static::GetLogicAtom(
						static::GetLogicEx(
							array_keys($arAmtLabels), $arAmtLabels
						)
					),
					'ATOM' => array(
						'ID' => 'logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'double',
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
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_BASKET_AMOUNT_GROUP_SELECT_DEF'),
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
				)
			),
			'CondBsktAmtBaseGroup' => array(
				'Logic' => array(
					'JS' => static::GetLogicAtom(
						static::GetLogicEx(
							array_keys($arAmtLabels), $arAmtLabels
						)
					),
					'ATOM' => array(
						'ID' => 'logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'double',
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
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_BASKET_AMOUNT_GROUP_SELECT_DEF'),
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
				)
			),
			'CondBsktProductGroup' => array(
				'Found' => array(
					'JS' => array(
						'id' => 'Found',
						'name' => 'search',
						'type' => 'select',
						'values' => array(
							'Found' => Loc::getMessage('BT_SALE_COND_PRODUCT_GROUP_SELECT_FOUND'),
							'NoFound' => Loc::getMessage('BT_SALE_COND_PRODUCT_GROUP_SELECT_NO_FOUND')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_PRODUCT_GROUP_SELECT_DEF'),
						'defaultValue' => 'Found',
						'first_option' => '...'
					),
					'ATOM' => array(
						'ID' => 'Found',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'All' => array(
					'JS' => array(
						'id' => 'All',
						'name' => 'aggregator',
						'type' => 'select',
						'values' => array(
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_PRODUCT_GROUP_SELECT_DEF'),
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
				)
			),
			'CondBsktRowGroup' => array(
				'Logic' => array(
					'JS' => static::GetLogicAtom(
						static::GetLogic(
							array(
								BT_COND_LOGIC_EQ,
								BT_COND_LOGIC_NOT_EQ,
								BT_COND_LOGIC_GR,
								BT_COND_LOGIC_LS,
								BT_COND_LOGIC_EGR,
								BT_COND_LOGIC_ELS
							)
						)
					),
					'ATOM' => array(
						'ID' => 'logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
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
							'AND' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_SALE_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_ROW_GROUP_SELECT_DEF'),
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
				)
			),
			'CondBsktSubGroup' => array(
				'All' => array(
					'JS' => array(
						'id' => 'All',
						'name' => 'aggregator',
						'type' => 'select',
						'values' => array(
							'AND' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ALL'),
							'OR' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ANY')
						),
						'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_DEF'),
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
							'True' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_TRUE'),
							'False' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_FALSE')
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
			)
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneControl)
			{
				foreach ($arOneControl as &$arOneAtom)
					$arOneAtom = $arOneAtom['JS'];
				unset($arOneAtom);
			}
			unset($arOneControl);
		}

		if ($strControlID === false)
			return $arAtomList;
		elseif (isset($arAtomList[$strControlID]))
			return $arAtomList[$strControlID];
		else
			return false;
	}

	/**
	 * @param bool|string $strControlID
	 * @return array|bool
	 */
	public static function GetControls($strControlID = false)
	{
		$arAtoms = static::GetAtomsEx();
		$arControlList = array(
			'CondCumulativeGroup' => array(
				'ID' => 'CondCumulativeGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondCumulativeGroup']
			),
			'CondBsktCntGroup' => array(
				'ID' => 'CondBsktCntGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_NUMBER_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_NUMBER_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondBsktCntGroup']
			),
			'CondBsktAmtGroup' => array(
				'ID' => 'CondBsktAmtGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_AMOUNT_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_AMOUNT_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondBsktAmtGroup']
			),
			'CondBsktAmtBaseGroup' => array(
				'ID' => 'CondBsktAmtBaseGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_AMOUNT_BASE_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_AMOUNT_BASE_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondBsktAmtBaseGroup']
			),
			'CondBsktProductGroup' => array(
				'ID' => 'CondBsktProductGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_PRODUCT_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_PRODUCT_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondBsktProductGroup']
			),
			'CondBsktRowGroup' => array(
				'ID' => 'CondBsktRowGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_ROW_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_ROW_PREFIX'),
				'SHOW_IN' => array(parent::GetControlID()),
				'VISUAL' => self::__GetVisual(),
				'ATOMS' => $arAtoms['CondBsktRowGroup']
			),
			'CondBsktSubGroup' => array(
				'ID' => 'CondBsktSubGroup',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_BASKET_SUB_LABEL'),
				'SHOW_IN' => array_diff(self::GetControlID(), array('CondCumulativeGroup')),
				'VISUAL' => self::__GetVisual(true),
				'ATOMS' => $arAtoms['CondBsktSubGroup']
			)
		);

		foreach ($arControlList as &$control)
		{
			$control['MODULE_ID'] = 'sale';
			$control['MODULE_ENTITY'] = 'sale';
			$control['ENTITY'] = 'BASKET';
			$control['GROUP'] = 'Y';
		}
		unset($control);

		return static::searchControl($arControlList, $strControlID);
	}

	private static function __GetVisual($boolExt = false)
	{
		$boolExt = ($boolExt === true);
		if ($boolExt)
		{
			$arResult = array(
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
					)
				),
				'logic' => array(
					array(
						'style' => 'condition-logic-and',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_AND')
					),
					array(
						'style' => 'condition-logic-and',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_NOT_AND')
					),
					array(
						'style' => 'condition-logic-or',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_OR')
					),
					array(
						'style' => 'condition-logic-or',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_NOT_OR')
					)
				)
			);
		}
		else
		{
			$arResult = array(
				'controls' => array(
					'All'
				),
				'values' => array(
					array(
						'All' => 'AND'
					),
					array(
						'All' => 'OR'
					),
				),
				'logic' => array(
					array(
						'style' => 'condition-logic-and',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_AND')
					),
					array(
						'style' => 'condition-logic-or',
						'message' => Loc::getMessage('BT_SALE_COND_GROUP_LOGIC_OR')
					)
				)
			);
		}
		return $arResult;
	}

	private static function getCodeForCumulativeGroupCondition($oneCondition, $values, $params, $control, $subs)
	{
		$dataSumConfiguration = 'array()';
		if ($subs && $subs[0])
		{
			$dataSumConfiguration = $subs[0];
		}

		$logic = static::SearchLogic(
			$values['logic'],
			static::GetLogic(
				array(
					BT_COND_LOGIC_EQ,
					BT_COND_LOGIC_NOT_EQ,
					BT_COND_LOGIC_GR,
					BT_COND_LOGIC_LS,
					BT_COND_LOGIC_EGR,
					BT_COND_LOGIC_ELS
				)
			)
		);

		if (!isset($logic['OP']['N']) || empty($logic['OP']['N']))
		{
			return '';
		}

		/** @see \CSaleCondCumulativeCtrl::getCumulativeValue */
		return str_replace(
			array('#FIELD#', '#VALUE#'),
			array("\CSaleCondCumulativeCtrl::getCumulativeValue({$params['ORDER']}, {$dataSumConfiguration}) ", $values['Value']),
			$logic['OP']['N']
		);
	}

	private static function __GetSubGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$mxResult = '';
		$boolError = false;

		if (empty($arSubs))
			return '(1 == 1)';

		if (!$boolError)
		{
			$strPrefix = '';
			$strLogic = '';
			$strItemPrefix = '';

			if ('AND' == $arOneCondition['All'])
			{
				$strLogic = ' && ';
				$strItemPrefix = ('True' == $arOneCondition['True'] ? '' : '!');
			}
			else
			{
				if ('True' == $arOneCondition['True'])
				{
					$strPrefix = '';
					$strLogic = ' || ';
				}
				else
				{
					$strPrefix = '!';
					$strLogic = ' && ';
				}
			}

			$strEval = $strItemPrefix.implode($strLogic.$strItemPrefix, $arSubs);
			if ('' != $strPrefix)
				$strEval = $strPrefix.'('.$strEval.')';
			$mxResult = $strEval;
		}

		return $mxResult;
	}

	private static function __GetRowGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$boolError = false;
		$strFunc = '';
		$strCond = '';

		$arLogic = static::SearchLogic(
			$arValues['logic'],
			static::GetLogic(
				array(
					BT_COND_LOGIC_EQ,
					BT_COND_LOGIC_NOT_EQ,
					BT_COND_LOGIC_GR,
					BT_COND_LOGIC_LS,
					BT_COND_LOGIC_EGR,
					BT_COND_LOGIC_ELS
				)
			)
		);

		if (!isset($arLogic['OP']['N']) || empty($arLogic['OP']['N']))
		{
			$boolError = true;
		}
		else
		{
			if (!empty($arSubs))
			{
				$strFuncName = '$salecond'.$arParams['FUNC_ID'];

				$strLogic = ('AND' == $arValues['All'] ? '&&' : '||');

				$strFunc = $strFuncName.'=function($row){';
				$strFunc .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
				$strFunc .= '};';

				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::RowFilter('.$arParams['ORDER'].', '.$strFuncName.')', $arValues['Value']),
					$arLogic['OP']['N']
				);
			}
			else
			{
				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::RowFilter('.$arParams['ORDER'].', "")', $arValues['Value']),
					$arLogic['OP']['N']
				);
			}
		}

		if (!$boolError)
		{
			if (!empty($strFunc))
			{
				return array(
					'FUNC' => $strFunc,
					'COND' => $strCond,
				);
			}
			else
			{
				return $strCond;
			}
		}
		else
		{
			return '';
		}
	}

	private static function __GetProductGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$strFunc = '';

		if (!empty($arSubs))
		{
			$strFuncName = '$salecond'.$arParams['FUNC_ID'];

			$strLogic = ('AND' == $arValues['All'] ? '&&' : '||');

			$strFunc = $strFuncName.'=function($row){';
			$strFunc .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
			$strFunc .= '};';

			$strCond = ('Found' == $arValues['Found'] ? '' : '!').'CSaleBasketFilter::ProductFilter('.$arParams['ORDER'].', '.$strFuncName.')';
		}
		else
		{
			$strCond = ('Found' == $arValues['Found'] ? '' : '!').'CSaleBasketFilter::ProductFilter('.$arParams['ORDER'].', "")';
		}

		if (!empty($strFunc))
		{
			return array(
				'FUNC' => $strFunc,
				'COND' => $strCond,
			);
		}
		else
		{
			return $strCond;
		}
	}

	private static function __GetAmtGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$boolError = false;

		$strFunc = '';
		$strCond = '';

		$arLogic = static::SearchLogic(
			$arValues['logic'],
			static::GetLogic(
				array(
					BT_COND_LOGIC_EQ,
					BT_COND_LOGIC_NOT_EQ,
					BT_COND_LOGIC_GR,
					BT_COND_LOGIC_LS,
					BT_COND_LOGIC_EGR,
					BT_COND_LOGIC_ELS
				)
			)
		);

		if (!isset($arLogic['OP']['N']) || empty($arLogic['OP']['N']))
		{
			$boolError = true;
		}
		else
		{
			if (!empty($arSubs))
			{
				$strFuncName = '$salecond'.$arParams['FUNC_ID'];

				$strLogic = ('AND' == $arValues['All'] ? '&&' : '||');

				$strFunc = $strFuncName.'=function($row){';
				$strFunc .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
				$strFunc .= '};';

				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::AmountFilter('.$arParams['ORDER'].', '.$strFuncName.')',
					$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
			else
			{
				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::AmountFilter('.$arParams['ORDER'].', "")',
					$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
		}

		if (!$boolError)
		{
			if (!empty($strFunc))
			{
				return array(
					'FUNC' => $strFunc,
					'COND' => $strCond,
				);
			}
			else
			{
				return $strCond;
			}
		}
		else
		{
			return '';
		}
	}

	private static function __GetAmtBaseGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$boolError = false;

		$strFunc = '';
		$strCond = '';

		$arLogic = static::SearchLogic(
			$arValues['logic'],
			static::GetLogic(
				array(
					BT_COND_LOGIC_EQ,
					BT_COND_LOGIC_NOT_EQ,
					BT_COND_LOGIC_GR,
					BT_COND_LOGIC_LS,
					BT_COND_LOGIC_EGR,
					BT_COND_LOGIC_ELS
				)
			)
		);

		if (!isset($arLogic['OP']['N']) || empty($arLogic['OP']['N']))
		{
			$boolError = true;
		}
		else
		{
			if (!empty($arSubs))
			{
				$strFuncName = '$salecond'.$arParams['FUNC_ID'];

				$strLogic = ('AND' == $arValues['All'] ? '&&' : '||');

				$strFunc = $strFuncName.'=function($row){';
				$strFunc .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
				$strFunc .= '};';

				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::AmountBaseFilter('.$arParams['ORDER'].', '.$strFuncName.')',
						$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
			else
			{
				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::AmountBaseFilter('.$arParams['ORDER'].', "")',
						$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
		}

		if (!$boolError)
		{
			if (!empty($strFunc))
			{
				return array(
					'FUNC' => $strFunc,
					'COND' => $strCond,
				);
			}
			else
			{
				return $strCond;
			}
		}
		else
		{
			return '';
		}
	}

	private static function __GetCntGroupCond($arOneCondition, $arValues, $arParams, $arControl, $arSubs)
	{
		$boolError = false;

		$strFunc = '';
		$strCond = '';

		$arLogic = static::SearchLogic(
			$arValues['logic'],
			static::GetLogic(
				array(
					BT_COND_LOGIC_EQ,
					BT_COND_LOGIC_NOT_EQ,
					BT_COND_LOGIC_GR,
					BT_COND_LOGIC_LS,
					BT_COND_LOGIC_EGR,
					BT_COND_LOGIC_ELS
				)
			)
		);

		if (!isset($arLogic['OP']['N']) || empty($arLogic['OP']['N']))
		{
			$boolError = true;
		}
		else
		{
			if (!empty($arSubs))
			{
				$strFuncName = '$salecond'.$arParams['FUNC_ID'];

				$strLogic = ('AND' == $arValues['All'] ? '&&' : '||');

				$strFunc = $strFuncName.'=function($row){';
				$strFunc .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
				$strFunc .= '};';

				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::CountFilter('.$arParams['ORDER'].', '.$strFuncName.')',
					$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
			else
			{
				$strCond = str_replace(
					array('#FIELD#', '#VALUE#'),
					array('CSaleBasketFilter::CountFilter('.$arParams['ORDER'].', "")',
					$arValues['Value']),
					$arLogic['OP']['N']
				);
			}
		}

		if (!$boolError)
		{
			if (!empty($strFunc))
			{
				return array(
					'FUNC' => $strFunc,
					'COND' => $strCond,
				);
			}
			else
			{
				return $strCond;
			}
		}
		else
		{
			return '';
		}
	}
}

class CSaleCondCtrlBasketFields extends CSaleCondCtrlComplex
{
	const ENTITY_BASKET_POSITION_WEIGHT = 'BX:CondBsktFldSummWeight';

	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 300;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$arControls = static::GetControls();
		$arResult = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('BT_MOD_SALE_COND_GROUP_BASKET_FIELDS_LABEL'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'children' => array()
		);
		foreach ($arControls as $arOneControl)
		{
			$arOne = array(
				'controlId' => $arOneControl['ID'],
				'group' => ('Y' == $arOneControl['GROUP']),
				'label' => $arOneControl['LABEL'],
				'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			);
			if (
				$arOneControl['ID'] == \CSaleCondCtrlBasketProperties::ENTITY_BASKET_PROPERTY
				|| $arOneControl['ID'] == \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED
			)
			{
				$arOne['control'] = array();
				if (isset($arOneControl['PREFIX']))
					$arOne['control'][] = array(
						'id' => 'prefix',
						'type' => 'prefix',
						'text' => $arOneControl['PREFIX']
					);
				foreach ($arOneControl['ATOMS'] as $atom)
					$arOne['control'][] = $atom;
				unset($atom);
			}
			else
			{
				$arOne['control'] = array(
					array(
						'id' => 'prefix',
						'type' => 'prefix',
						'text' => $arOneControl['PREFIX']
					),
					static::GetLogicAtom($arOneControl['LOGIC']),
					static::GetValueAtom($arOneControl['JS_VALUE'])
				);
				if ($arOneControl['ID'] == 'CondBsktFldPrice' || $arOneControl['ID'] == 'CondBsktFldSumm')
				{
					$boolCurrency = false;
					if (static::$boolInit)
					{
						if (isset(static::$arInitParams['CURRENCY']))
						{
							$arOne['control'][] = static::$arInitParams['CURRENCY'];
							$boolCurrency = true;
						}
						elseif (isset(static::$arInitParams['SITE_ID']))
						{
							$strCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
							if (!empty($strCurrency))
							{
								$arOne['control'][] = $strCurrency;
								$boolCurrency = true;
							}
						}
					}
					if (!$boolCurrency)
						$arOne = array();
				}
				elseif ($arOneControl['ID'] == 'CondBsktFldWeight' || $arOneControl['ID'] == self::ENTITY_BASKET_POSITION_WEIGHT)
				{
					$arOne['control'][] = Loc::getMessage('BT_MOD_SALE_COND_MESS_WEIGHT_UNIT');
				}
			}
			if (!empty($arOne))
				$arResult['children'][] = $arOne;
		}
		unset($arOneControl);

		return $arResult;
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		if ($arParams['ID'] == \CSaleCondCtrlBasketProperties::ENTITY_BASKET_PROPERTY)
			return \CSaleCondCtrlBasketProperties::GetConditionShow($arParams);
		if ($arParams['ID'] == \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED)
			return \CSaleCondCtrlBasketItemConditions::GetConditionShow($arParams);
		return parent::GetConditionShow($arParams);
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		if ($arOneCondition['controlId'] == \CSaleCondCtrlBasketProperties::ENTITY_BASKET_PROPERTY)
			return \CSaleCondCtrlBasketProperties::Parse($arOneCondition);
		if ($arOneCondition['controlId'] == \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED)
			return \CSaleCondCtrlBasketItemConditions::Parse($arOneCondition);
		return parent::Parse($arOneCondition);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';

		if (is_string($arControl))
			$arControl = static::GetControls($arControl);
		$boolError = !is_array($arControl);

		if (!$boolError)
		{
			if ($arControl['ID'] == \CSaleCondCtrlBasketProperties::ENTITY_BASKET_PROPERTY)
				return \CSaleCondCtrlBasketProperties::Generate($arOneCondition, $arParams, $arControl, $arSubs);
			if ($arControl['ID'] == \CSaleCondCtrlBasketItemConditions::ENTITY_BASKET_POSITION_ACTION_APPLIED)
				return \CSaleCondCtrlBasketItemConditions::Generate($arOneCondition, $arParams, $arControl, $arSubs);
		}

		$arValues = array();
		if (!$boolError)
		{
			$arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
			$boolError = ($arValues === false);
		}

		if (!$boolError)
		{
			$arLogic = static::SearchLogic($arValues['logic'], $arControl['LOGIC']);
			if (!isset($arLogic['OP'][$arControl['MULTIPLE']]) || empty($arLogic['OP'][$arControl['MULTIPLE']]))
			{
				$boolError = true;
			}
			else
			{
				$multyField = is_array($arControl['FIELD']);
				if ($multyField)
				{
					$fieldsList = array();
					foreach ($arControl['FIELD'] as &$oneField)
					{
						$fieldsList[] = $arParams['BASKET_ROW'].'[\''.$oneField.'\']';
					}
					unset($oneField);
					$issetField = implode(') && isset (', $fieldsList);
					$valueField = implode('*',$fieldsList);
					unset($fieldsList);
				}
				else
				{
					$issetField = $arParams['BASKET_ROW'].'[\''.$arControl['FIELD'].'\']';
					$valueField = $issetField;
				}
				switch ($arControl['FIELD_TYPE'])
				{
					case 'int':
					case 'double':
						$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($valueField, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
						break;
					case 'char':
					case 'string':
					case 'text':
						$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($valueField, '"'.EscapePHPString($arValues['value']).'"'), $arLogic['OP'][$arControl['MULTIPLE']]);
						break;
					case 'date':
					case 'datetime':
						$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($valueField, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
						break;
				}
				$strResult = 'isset('.$issetField.') && '.$strResult;
			}
		}

		return (!$boolError ? $strResult : false);
	}

	/**
	 * @param bool|string $strControlID
	 * @return array|bool
	 */
	public static function GetControls($strControlID = false)
	{
		$arControlList = array(
			'CondBsktFldProduct' => array(
				'ID' => 'CondBsktFldProduct',
				'FIELD' => 'PRODUCT_ID',
				'FIELD_TYPE' => 'int',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_PRODUCT_ID_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_PRODUCT_ID_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'dialog',
					'popup_url' =>  '/bitrix/tools/sale/product_search_dialog.php',
					'popup_params' => array(
						'lang' => LANGUAGE_ID,
						'caller' => 'discount_rules'
					),
					'param_id' => 'n',
					'show_value' => 'Y'
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'element'
				),
			),
			'CondBsktFldName' => array(
				'ID' => 'CondBsktFldName',
				'FIELD' => 'NAME',
				'FIELD_TYPE' => 'string',
				'FIELD_LENGTH' => 255,
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_PRODUCT_NAME_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_PRODUCT_NAME_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ, BT_COND_LOGIC_CONT, BT_COND_LOGIC_NOT_CONT)),
				'JS_VALUE' => array(
					'type' => 'input'
				),
				'PHP_VALUE' => ''
			),
			'CondBsktFldSumm' => array(
				'ID' => 'CondBsktFldSumm',
				'FIELD' => array(
					'PRICE',
					'QUANTITY'
				),
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_SUMM_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_SUMM_EXT_PREFIX'),
				'LOGIC' => static::GetLogic(
						array(
							BT_COND_LOGIC_EQ,
							BT_COND_LOGIC_NOT_EQ,
							BT_COND_LOGIC_GR,
							BT_COND_LOGIC_LS,
							BT_COND_LOGIC_EGR,
							BT_COND_LOGIC_ELS
						)
					),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			),
			'CondBsktFldPrice' => array(
				'ID' => 'CondBsktFldPrice',
				'FIELD' => 'PRICE',
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_PRICE_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_PRICE_EXT_PREFIX'),
				'LOGIC' => static::GetLogic(
					array(
						BT_COND_LOGIC_EQ,
						BT_COND_LOGIC_NOT_EQ,
						BT_COND_LOGIC_GR,
						BT_COND_LOGIC_LS,
						BT_COND_LOGIC_EGR,
						BT_COND_LOGIC_ELS
					)
				),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			),
			'CondBsktFldQuantity' => array(
				'ID' => 'CondBsktFldQuantity',
				'FIELD' => 'QUANTITY',
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_QUANTITY_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_QUANTITY_EXT_PREFIX'),
				'LOGIC' => static::GetLogic(
					array(
						BT_COND_LOGIC_EQ,
						BT_COND_LOGIC_NOT_EQ,
						BT_COND_LOGIC_GR,
						BT_COND_LOGIC_LS,
						BT_COND_LOGIC_EGR,
						BT_COND_LOGIC_ELS
					)
				),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			),
			self::ENTITY_BASKET_POSITION_WEIGHT => array(
				'ID' => self::ENTITY_BASKET_POSITION_WEIGHT,
				'FIELD' => array(
					'WEIGHT',
					'QUANTITY'
				),
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_SALE_COND_BASKET_POSITION_WEIGHT_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_BASKET_POSITION_WEIGHT_PREFIX'),
				'LOGIC' => static::GetLogic(
					array(
						BT_COND_LOGIC_EQ,
						BT_COND_LOGIC_NOT_EQ,
						BT_COND_LOGIC_GR,
						BT_COND_LOGIC_LS,
						BT_COND_LOGIC_EGR,
						BT_COND_LOGIC_ELS
					)
				),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			),
			'CondBsktFldWeight' => array(
				'ID' => 'CondBsktFldWeight',
				'FIELD' => 'WEIGHT',
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_WEIGHT_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_BASKET_ROW_WEIGHT_EXT_PREFIX'),
				'LOGIC' => static::GetLogic(
					array(
						BT_COND_LOGIC_EQ,
						BT_COND_LOGIC_NOT_EQ,
						BT_COND_LOGIC_GR,
						BT_COND_LOGIC_LS,
						BT_COND_LOGIC_EGR,
						BT_COND_LOGIC_ELS
					)
				),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			)
		);

		$additionalControls = \CSaleCondCtrlBasketItemConditions::GetControls(false);
		foreach ($additionalControls as $id => $data)
			$arControlList[$id] = $data;
		$additionalControls = \CSaleCondCtrlBasketProperties::GetControls(false);
		foreach ($additionalControls as $id => $data)
			$arControlList[$id] = $data;
		unset($id, $data, $additionalControls);

		foreach ($arControlList as &$control)
		{
			$control['MODULE_ID'] = 'sale';
			$control['MODULE_ENTITY'] = 'sale';
			if (!isset($control['ENTITY']))
				$control['ENTITY'] = 'BASKET';
			$control['MULTIPLE'] = 'N';
			$control['GROUP'] = 'N';
		}
		unset($control);

		return static::searchControl($arControlList, $strControlID);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = CSaleCondCtrlBasketGroup::GetControlID();
		$index = array_search('CondCumulativeGroup', $arControls);
		if ($index !== false)
		{
			unset($arControls[$index]);
			$arControls = array_values($arControls);
		}
		unset($index);

		return $arControls;
	}
}

class CSaleCondCtrlBasketItemConditions extends CGlobalCondCtrlAtoms
{
	const ENTITY_BASKET_POSITION_ACTION_APPLIED = 'CondBsktAppliedDiscount';

	public static function GetControlDescr()
	{
		return [];
	}

	public static function GetAtomsEx($controlId = false, $extendedMode = false)
	{
		$atomList = [
			self::ENTITY_BASKET_POSITION_ACTION_APPLIED => [
				'value' => [
					'JS' => [
						'id' => 'value',
						'name' => 'value',
						'type' => 'select',
						'values' => [
							'Y' => Loc::getMessage('BT_SALE_COND_BASKET_DISCOUNT_APPLIED_YES'),
							'N' => Loc::getMessage('BT_SALE_COND_BASKET_DISCOUNT_APPLIED_NO')
						],
						'defaultText' => '...',
						'defaultValue' => '',
						'first_option' => '...'
					],
					'ATOM' => [
						'ID' => 'value',
						'FIELD_TYPE' => 'char',
						'FIELD' => 'ACTION_APPLIED',
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					]
				]
			]
		];

		return static::searchControlAtoms($atomList, $controlId, $extendedMode);
	}

	public static function GetControls($controlId = false)
	{
		$atoms = static::GetAtomsEx();
		$controlList = array(
			self::ENTITY_BASKET_POSITION_ACTION_APPLIED => array(
				'ID' => self::ENTITY_BASKET_POSITION_ACTION_APPLIED,
				'LABEL' => Loc::getMessage('BT_SALE_COND_BASKET_DISCOUNT_APPLIED_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_BASKET_DISCOUNT_APPLIED_PREFIX'),
				'ATOMS' => $atoms[self::ENTITY_BASKET_POSITION_ACTION_APPLIED],
				'FIELD' => 'ACTION_APPLIED',
			)
		);
		unset($atoms);

		return static::searchControl($controlList, $controlId);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = \CSaleCondCtrlBasketGroup::GetControlID();
		$index = array_search('CondCumulativeGroup', $arControls);
		if ($index !== false)
		{
			unset($arControls[$index]);
			$arControls = array_values($arControls);
		}
		unset($index);

		return $arControls;
	}

	public static function GetConditionShow($params)
	{
		// remove excess condition - only for compatibility
		if (isset($params['DATA']['logic']))
		{
			if ($params['DATA']['logic'] == 'Not' && isset($params['DATA']['value']))
			{
				if ($params['DATA']['value'] == 'Y')
					$params['DATA']['value'] = 'N';
				elseif ($params['DATA']['value'] == 'N')
					$params['DATA']['value'] = 'Y';
			}
			unset($params['DATA']['logic']);
		}
		return parent::GetConditionShow($params);
	}

	public static function Parse($condition)
	{
		// remove excess condition - only for compatibility
		if (isset($condition['logic']))
		{
			if ($condition['logic'] == 'Not' && isset($condition['value']))
			{
				if ($condition['value'] == 'Y')
					$condition['value'] = 'N';
				elseif ($condition['value'] == 'N')
					$condition['value'] = 'Y';
			}
			unset($condition['logic']);
		}
		return parent::Parse($condition);
	}

	public static function Generate($condition, $params, $control, $childrens = false)
	{
		$result = '';

		if (is_string($control))
			$control = static::GetControls($control);
		$error = !is_array($control);

		$values = [];
		if (!$error)
		{
			$control['ATOMS'] = static::GetAtomsEx($control['ID'], true);
			$values = static::CheckAtoms($condition, $condition, $control, false);
			$error = ($values === false);
		}

		if (!$error)
		{
			$field = $params['BASKET_ROW'].'[\''.$control['FIELD'].'\']';
			$result = 'isset('.$field.') && '.$field.'==\''.\CUtil::JSEscape($values['value']).'\'';
			unset($field);
		}
		unset($values);

		return $result;
	}
}

class CSaleCondCtrlBasketProperties extends CGlobalCondCtrlAtoms
{
	const ENTITY_BASKET_PROPERTY = 'BX:CondBsktProp';

	const ENTITY_TYPE_NAME = 'NAME';
	const ENTITY_TYPE_CODE = 'CODE';

	public static function GetControlDescr()
	{
		return array();
	}

	public static function GetAtomsEx($controlId = false, $extendedMode = false)
	{
		$logic = array();
		$logicList = static::GetLogic(array(
			BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ, BT_COND_LOGIC_CONT, BT_COND_LOGIC_NOT_CONT
		));
		foreach ($logicList as $row)
			$logic[$row['ID']] = $row['LABEL'];
		unset($row, $logicList);

		$atomList = array(
			self::ENTITY_BASKET_PROPERTY => array(
				'Entity' => array(
					'JS' => array(
						'id' => 'Entity',
						'name' => 'Entity',
						'type' => 'select',
						'values' => array(
							self::ENTITY_TYPE_CODE => Loc::getMessage('BT_SALE_COND_BASKET_PROPERTY_ENTITY_TYPE_CODE'),
							self::ENTITY_TYPE_NAME => Loc::getMessage('BT_SALE_COND_BASKET_PROPERTY_ENTITY_TYPE_NAME')
						),
						'defaultText' => Loc::getMessage('BT_SALE_COND_BASKET_PROPERTY_ENTITY_TYPE_SELECT_DEF'),
						'defaultValue' => self::ENTITY_TYPE_CODE,
						'first_option' => '...'
					),
					'ATOM' => array(
						'ID' => 'Entity',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Name' => array(
					'JS' => array(
						'id' => 'Name',
						'name' => 'Name',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Name',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => ''
					)
				),
				'Logic' => array(
					'JS' => array(
						'id' => 'Logic',
						'name' => 'Logic',
						'type' => 'select',
						'values' => $logic,
						'defaultText' => '',
						'defaultValue' => BT_COND_LOGIC_EQ,
					),
					'ATOM' => array(
						'ID' => 'Logic',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				),
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'Value',
						'type' => 'input'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'string',
						'FIELD_LENGTH' => 255,
						'MULTIPLE' => 'N',
						'VALIDATE' => ''
					)
				)
			)
		);

		return static::searchControlAtoms($atomList, $controlId, $extendedMode);
	}

	public static function GetControls($controlId = false)
	{
		$atoms = static::GetAtomsEx();
		$controlList = array(
			self::ENTITY_BASKET_PROPERTY => array(
				'ID' => self::ENTITY_BASKET_PROPERTY,
				'LABEL' => Loc::getMessage('BT_SALE_COND_BASKET_PROPERTY_LABEL'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_BASKET_PROPERTY_PREFIX'),
				'ATOMS' => $atoms[self::ENTITY_BASKET_PROPERTY],
				'MODULE_ID' => 'sale',
				'MODULE_ENTITY' => 'sale',
				'ENTITY' => 'BASKET_PROPERTY',
				'FIELD' => array(
					'VALUE',
					'NAME',
					'CODE'
				),
				'MULTIPLE' => 'N',
				'GROUP' => 'N'
			)
		);

		return static::searchControl($controlList, $controlId);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = CSaleCondCtrlBasketGroup::GetControlID();
		$index = array_search('CondCumulativeGroup', $arControls);
		if ($index !== false)
		{
			unset($arControls[$index]);
			$arControls = array_values($arControls);
		}
		unset($index);

		return $arControls;
	}

	public static function Generate($condition, $params, $control, $childrens = false)
	{
		$result = '';

		if (is_string($control))
			$control = static::GetControls($control);
		$error = !is_array($control);

		$values = array();
		if (!$error)
		{
			$control['ATOMS'] = static::GetAtomsEx($control['ID'], true);
			$values = static::CheckAtoms($condition, $condition, $control, false);
			$error = ($values === false);
		}

		if (!$error)
		{
			$data = 'array('.
				'\'ENTITY_ID\' => \''.$values['Entity'].'\', '.
				'\'ENTITY_VALUE\' => \''.\CUtil::JSEscape($values['Name']).'\', '.
				'\'LOGIC\' => '.$values['Logic'].', '.
				'\'VALUE\' => \''.\CUtil::JSEscape($values['Value']).'\''.
				')';
			$result = '\CSaleBasketFilter::BasketPropertyFilter('.$params['BASKET_ROW'].', '.$data.')';
			unset($data);
		}

		return $result;
	}
}

class CSaleCondCtrlOrderFields extends CSaleCondCtrlComplex
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 300;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$arControls = static::GetControls();
		$arResult = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('BT_MOD_SALE_COND_CMP_ORDER_CONTROLGROUP_LABEL'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'children' => array()
		);
		foreach ($arControls as &$arOneControl)
		{
			if ('ORDER_PRICE' == $arOneControl['FIELD'])
			{
				$arJSControl = array(
					array(
						'id' => 'prefix',
						'type' => 'prefix',
						'text' => $arOneControl['PREFIX']
					),
					static::GetLogicAtom($arOneControl['LOGIC']),
					static::GetValueAtom($arOneControl['JS_VALUE'])
				);
				if (static::$boolInit)
				{
					if (isset(static::$arInitParams['CURRENCY']))
					{
						$arJSControl[] = static::$arInitParams['CURRENCY'];
					}
					elseif (isset(static::$arInitParams['SITE_ID']))
					{
						$strCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
						if (!empty($strCurrency))
						{
							$arJSControl[] = $strCurrency;
						}
					}
				}
				$arOne = array(
					'controlId' => $arOneControl['ID'],
					'group' => ('Y' == $arOneControl['GROUP']),
					'label' => $arOneControl['LABEL'],
					'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
					'control' => $arJSControl
				);
			}
			else
			{
				$arOne = array(
					'controlId' => $arOneControl['ID'],
					'group' => ('Y' == $arOneControl['GROUP']),
					'label' => $arOneControl['LABEL'],
					'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
					'control' => array(
						array(
							'id' => 'prefix',
							'type' => 'prefix',
							'text' => $arOneControl['PREFIX']
						),
						static::GetLogicAtom($arOneControl['LOGIC']),
						static::GetValueAtom($arOneControl['JS_VALUE'])
					)
				);
			}
			if ('ORDER_WEIGHT' == $arOneControl['FIELD'])
			{
				$arOne['control'][] = Loc::getMessage('BT_MOD_SALE_COND_MESS_WEIGHT_UNIT');
			}
			$arResult['children'][] = $arOne;
		}
		if (isset($arOneControl))
			unset($arOneControl);

		return $arResult;
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		$arControl = static::GetControls($arOneCondition['controlId']);
		if (false === $arControl)
			return false;
		return static::Check($arOneCondition, $arOneCondition, $arControl, false);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';

		if (is_string($arControl))
		{
			$arControl = static::GetControls($arControl);
		}
		$boolError = !is_array($arControl);

		$arValues = array();
		if (!$boolError)
		{
			$arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
			$boolError = (false === $arValues);
		}

		if (!$boolError)
		{
			$arLogic = static::SearchLogic($arValues['logic'], $arControl['LOGIC']);
			if (!isset($arLogic['OP'][$arControl['MULTIPLE']]) || empty($arLogic['OP'][$arControl['MULTIPLE']]))
			{
				$boolError = true;
			}
			else
			{
				$strJoinOperator = '';
				$boolMulti = false;
				if (isset($arControl['JS_VALUE']['multiple']) && 'Y' == $arControl['JS_VALUE']['multiple'])
				{
					$boolMulti = true;
					$strJoinOperator = (isset($arLogic['MULTI_SEP']) ? $arLogic['MULTI_SEP'] : ' && ');
				}
				$strField = $arParams['ORDER'].'[\''.$arControl['FIELD'].'\']';
				switch ($arControl['FIELD_TYPE'])
				{
					case 'int':
					case 'double':
						if (!$boolMulti)
						{
							$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($strField, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
						}
						else
						{
							$arResult = array();
							foreach ($arValues['value'] as &$mxValue)
							{
								$arResult[] = str_replace(array('#FIELD#', '#VALUE#'), array($strField, $mxValue), $arLogic['OP'][$arControl['MULTIPLE']]);
							}
							if (isset($mxValue))
								unset($mxValue);
							$strResult = '(('.implode(')'.$strJoinOperator.'(', $arResult).'))';
						}
						break;
					case 'char':
					case 'string':
					case 'text':
						if (!$boolMulti)
						{
							$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($strField, '"'.EscapePHPString($arValues['value']).'"'), $arLogic['OP'][$arControl['MULTIPLE']]);
						}
						else
						{
							$arResult = array();
							foreach ($arValues['value'] as &$mxValue)
							{
								$arResult[] = str_replace(array('#FIELD#', '#VALUE#'), array($strField, '"'.EscapePHPString($mxValue).'"'), $arLogic['OP'][$arControl['MULTIPLE']]);
							}
							if (isset($mxValue))
								unset($mxValue);
							$strResult = '(('.implode(')'.$strJoinOperator.'(', $arResult).'))';
						}
						break;
					case 'date':
					case 'datetime':
						if (!$boolMulti)
						{
							$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($strField, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
						}
						else
						{
							$arResult = array();
							foreach ($arValues['value'] as &$mxValue)
							{
								$arResult[] = str_replace(array('#FIELD#', '#VALUE#'), array($strField, $mxValue), $arLogic['OP'][$arControl['MULTIPLE']]);
							}
							if (isset($mxValue))
								unset($mxValue);
							$strResult = '(('.implode(')'.$strJoinOperator.'(', $arResult).'))';
						}
						break;
				}
				$strResult = 'isset('.$strField.') && '.$strResult;
			}
		}

		return (!$boolError ? $strResult : false);
	}

	/**
	 * @param bool|string $strControlID
	 * @return array|bool
	 */
	public static function GetControls($strControlID = false)
	{
		$arSalePersonTypes = array();
		$arFilter = array();
		if (static::$boolInit)
		{
			if (isset(static::$arInitParams['SITE_ID']))
				$arFilter['LID'] = static::$arInitParams['SITE_ID'];
		}
		$rsPersonTypes = CSalePersonType::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			$arFilter,
			false,
			false,
			array('ID', 'NAME', 'LIDS', 'SORT')
		);
		while ($arPersonType = $rsPersonTypes->Fetch())
		{
			$id = (int)$arPersonType['ID'];
			$arSalePersonTypes[$id] = $arPersonType['NAME'].' ('.implode(' ', $arPersonType['LIDS']).')';
			unset($id);
		}
		unset($arPersonType, $rsPersonTypes);

		;
		$salePaySystemList = [];
		$filter = [
			'!=ID' => Sale\PaySystem\Manager::getInnerPaySystemId(),
		];
		$iterator = Sale\PaySystem\Manager::getList([
			'select' => ['ID', 'NAME', 'SORT'],
			'filter' => $filter,
			'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
		]);
		while ($row = $iterator->fetch())
		{
			$salePaySystemList[$row['ID']] = $row['NAME'];
		}
		unset($row, $iterator);

		$linearDeliveryList = array();
		$deliveryList = array();
		$groupIds = array();
		$iterator = Sale\Delivery\Services\Table::getList(array(
			'select' => array('ID', 'CLASS_NAME'),
			'filter' => array('=CLASS_NAME' => '\Bitrix\Sale\Delivery\Services\Group')
		));
		while ($row = $iterator->fetch())
			$groupIds[] = (int)$row['ID'];
		unset($row, $iterator);

		$deliveryIterator = Sale\Delivery\Services\Table::getList(array(
			'select' => array('ID', 'CODE', 'NAME', 'PARENT_ID', 'SORT', 'CLASS_NAME'),
			'order' => array('ID' => 'ASC')
		));
		while ($delivery = $deliveryIterator->fetch())
		{
			if ($delivery['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\Group')
				continue;
			$deliveryId = (int)$delivery['ID'];
			$parentId = (int)$delivery['PARENT_ID'];
			if ($parentId > 0 && !in_array($parentId, $groupIds))
			{
				if (isset($deliveryList[$parentId]))
				{
					$deliveryList[$parentId]['PROFILES'][$deliveryId] = array(
						'ID' => $deliveryId,
						'TITLE' => $delivery['NAME'],
						'SORT' => (int)$delivery['SORT']
					);
				}
			}
			else
			{
				$deliveryList[$deliveryId] = array(
					'ID' => $deliveryId,
					'TITLE' => $delivery['NAME'],
					'SORT' => (int)$delivery['SORT'],
					'PROFILES' => array()
				);
			}
			unset($parentId, $deliveryId);
		}
		unset($delivery, $deliveryIterator);
		unset($groupIds);
		if (!empty($deliveryList))
		{
			Main\Type\Collection::sortByColumn($deliveryList, array('SORT' => SORT_ASC, 'TITLE' => SORT_ASC, 'ID' => SORT_ASC));
			foreach ($deliveryList as $delivery)
			{
				if (empty($delivery['PROFILES']))
				{
					$linearDeliveryList[$delivery['ID']] = $delivery['TITLE'];
				}
				else
				{
					$profileList = $delivery['PROFILES'];
					Main\Type\Collection::sortByColumn($profileList, array('SORT' => SORT_ASC, 'TITLE' => SORT_ASC, 'ID' => SORT_ASC));
					foreach ($profileList as $profile)
						$linearDeliveryList[$profile['ID']] = $delivery['TITLE'].': '.$profile['TITLE'];
					unset($profile, $profileList);
				}
			}
			unset($delivery);
		}
		unset($deliveryList);

		$arLabels = array(
			BT_COND_LOGIC_EQ => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_EQ_LABEL'),
			BT_COND_LOGIC_NOT_EQ => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_NOT_EQ_LABEL'),
			BT_COND_LOGIC_GR => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_GR_LABEL'),
			BT_COND_LOGIC_LS => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_LS_LABEL'),
			BT_COND_LOGIC_EGR => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_EGR_LABEL'),
			BT_COND_LOGIC_ELS => Loc::getMessage('BT_SALE_AMOUNT_LOGIC_ELS_LABEL')
		);
		$arLabelsWeight = array(
			BT_COND_LOGIC_EQ => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_EQ_LABEL'),
			BT_COND_LOGIC_NOT_EQ => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_NOT_EQ_LABEL'),
			BT_COND_LOGIC_GR => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_GR_LABEL'),
			BT_COND_LOGIC_LS => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_LS_LABEL'),
			BT_COND_LOGIC_EGR => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_EGR_LABEL'),
			BT_COND_LOGIC_ELS => Loc::getMessage('BT_SALE_WEIGHT_LOGIC_ELS_LABEL')
		);

		$arControlList = array(
			'CondSaleOrderSumm' => array(
				'ID' => 'CondSaleOrderSumm',
				'FIELD' => 'ORDER_PRICE',
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_ORDER_SUMM_LABEL_EXT'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_ORDER_SUMM_PREFIX_EXT'),
				'LOGIC' => static::GetLogicEx(array_keys($arLabels), $arLabels),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			),
			'CondSalePersonType' => array(
				'ID' => 'CondSalePersonType',
				'FIELD' => 'PERSON_TYPE_ID',
				'FIELD_TYPE' => 'int',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_PERSON_TYPE_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_PERSON_TYPE_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $arSalePersonTypes,
					'size' => self::getSelectSize($arSalePersonTypes),
					'show_value' => 'Y'
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list'
				)
			),
			'CondSalePaySystem' => array(
				'ID' => 'CondSalePaySystem',
				'FIELD' => 'PAY_SYSTEM_ID',
				'FIELD_TYPE' => 'int',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_PAY_SYSTEM_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_PAY_SYSTEM_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $salePaySystemList,
					'size' => self::getSelectSize($salePaySystemList),
					'show_value' => 'Y'
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list'
				)
			),
			'CondSaleDelivery' => array(
				'ID' => 'CondSaleDelivery',
				'FIELD' => 'DELIVERY_ID',
				'FIELD_TYPE' => 'string',
				'FIELD_LENGTH' => 50,
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_DELIVERY_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_CMP_SALE_DELIVERY_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $linearDeliveryList,
					'size' => self::getSelectSize($linearDeliveryList),
					'show_value' => 'Y'
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list'
				)
			),
			'CondSaleOrderWeight' => array(
				'ID' => 'CondSaleOrderWeight',
				'FIELD' => 'ORDER_WEIGHT',
				'FIELD_TYPE' => 'double',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_SALE_ORDER_WEIGHT_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_SALE_ORDER_WEIGHT_PREFIX'),
				'LOGIC' => static::GetLogicEx(array_keys($arLabelsWeight), $arLabelsWeight),
				'JS_VALUE' => array(
					'type' => 'input'
				)
			)
		);
		foreach ($arControlList as &$control)
		{
			$control['EXECUTE_MODULE'] = 'sale';
			$control['MODULE_ID'] = 'sale';
			$control['MODULE_ENTITY'] = 'sale';
			$control['ENTITY'] = 'ORDER';
			$control['MULTIPLE'] = 'N';
			$control['GROUP'] = 'N';
		}
		unset($control);

		return static::searchControl($arControlList, $strControlID);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = array(CSaleCondCtrlGroup::GetControlID());
		return $arControls;
	}

	public static function GetJSControl($arControl, $arParams = array())
	{
		return array();
	}

	private static function getSelectSize(array $rows): int
	{
		$result = 3;
		$rowCount = count($rows);
		if ($rowCount > 10)
		{
			$result = 10;
		}
		elseif ($rowCount > 3)
		{
			$result = $rowCount;
		}
		return $result;
	}
}

class CSaleCondCtrlPastOrder extends CSaleCondCtrlOrderFields
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 330;
		return $description;
	}

	public static function onBuildDiscountConditionInterfaceControls()
	{
		return new EventResult(
			EventResult::SUCCESS,
			static::getControlDescr(),
			'sale'
		);
	}

	public static function checkPastOrder($currentOrder, $callable)
	{
		if(!is_callable($callable))
		{
			return false;
		}

		if(empty($currentOrder['USER_ID']))
		{
			return false;
		}

		$orderUserId = (int)$currentOrder['USER_ID'];
		$pastOrder = static::getPastOrder($orderUserId);

		if (!$pastOrder)
		{
			return false;
		}

		if (!$pastOrder->isPaid())
		{
			return false;
		}

		return call_user_func_array($callable, array(static::convertToArray($pastOrder)));
	}

	private static function convertToArray(Sale\Order $order)
	{
		$orderData = array(
			'ID' => $order->getId(),
			'USER_ID' => $order->getUserId(),
			'SITE_ID' => $order->getSiteId(),
			'LID' => $order->getSiteId(), // compatibility only
			'ORDER_PRICE' => $order->getPrice(),
			'ORDER_WEIGHT' => $order->getBasket()->getWeight(),
			'CURRENCY' => $order->getCurrency(),
			'PERSON_TYPE_ID' => $order->getPersonTypeId(),
			'RECURRING_ID' => $order->getField('RECURRING_ID'),
			'BASKET_ITEMS' => array(),
			'PRICE_DELIVERY' => 0,
			'PRICE_DELIVERY_DIFF' => 0,
			'CUSTOM_PRICE_DELIVERY' => 'N',
			'SHIPMENT_CODE' => 0,
			'SHIPMENT_ID' => 0,
			'ORDER_PROP' => array(),
			'DELIVERY_ID' => array(),
			'PAY_SYSTEM_ID' => array(),
		);

		/** @var Sale\Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if (!$shipment->isShipped())
			{
				continue;
			}

			$orderData['DELIVERY_ID'][] = (int)$shipment->getDeliveryId();
		}

		/** @var Sale\Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			if ($payment->isInner())
			{
				continue;
			}

			$orderData['PAY_SYSTEM_ID'][] = (int)$payment->getPaymentSystemId();
		}

		return $orderData;
	}

	private static function getPastOrder($userId)
	{
		$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var \Bitrix\Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$orderData = $orderClass::getList(
			array(
				'select' => array('ID'),
				'filter' => array(
					'=CANCELED' => 'N',
					'=USER_ID' => $userId
				),
				'order' => array('ID' => 'DESC'),
				'limit' => 1

			)
		)->fetch();

		if (empty($orderData['ID']))
		{
			return null;
		}

		return $orderClass::load($orderData['ID']);
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$code = parent::Generate($arOneCondition, $arParams, $arControl, $arSubs);

		return static::GetClassName() . "::checkPastOrder({$arParams['ORDER']}, function({$arParams['ORDER']}){
			return {$code}; 		
		})";
	}

	public static function GetControlShow($arParams)
	{
		$controlShow = parent::GetControlShow($arParams);
		$controlShow['label'] = Loc::getMessage('BT_SALE_COND_GROUP_PAST_ORDER_NAME');

		return $controlShow;
	}

	public static function GetControls($strControlID = false)
	{
		$controls = array();
		foreach (parent::GetControls() as $control)
		{
			$control['ID'] = 'Past' . $control['ID'];
			$control['PREFIX'] = $control['PREFIX'] . Loc::getMessage("BT_SALE_COND_GROUP_PAST_ORDER_NAME_SUFFIX");

			if (in_array($control['FIELD'], array('PAY_SYSTEM_ID', 'DELIVERY_ID')))
			{
				$control['MULTIPLE'] = 'Y';
			}

			$controls[$control['ID']] = $control;
		}

		return static::searchControl($controls, $strControlID);
	}
}

class CSaleCondCtrlCommon extends CSaleCondCtrlComplex
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['EXECUTE_MODULE'] = 'sale';
		$description['SORT'] = 2000;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$arControls = static::GetControls();
		$arResult = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('BT_MOD_SALE_COND_CMP_COMMON_CONTROLGROUP_LABEL'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'children' => array()
		);
		foreach ($arControls as &$arOneControl)
		{
			$arResult['children'][] = array(
				'controlId' => $arOneControl['ID'],
				'group' => false,
				'label' => $arOneControl['LABEL'],
				'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
				'control' => array(
					$arOneControl['PREFIX'],
					static::GetLogicAtom($arOneControl['LOGIC']),
					static::GetValueAtom($arOneControl['JS_VALUE'])
				)
			);
		}
		if (isset($arOneControl))
			unset($arOneControl);

		return $arResult;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';

		if (is_string($arControl))
		{
			$arControl = static::GetControls($arControl);
		}
		$boolError = !is_array($arControl);

		$arValues = array();
		if (!$boolError)
		{
			$arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
			$boolError = ($arValues === false);
		}

		if (!$boolError)
		{
			$arLogic = static::SearchLogic($arValues['logic'], $arControl['LOGIC']);
			if (!isset($arLogic['OP'][$arControl['MULTIPLE']]) || empty($arLogic['OP'][$arControl['MULTIPLE']]))
			{
				$boolError = true;
			}
			else
			{
				$boolMulti = false;
				if (isset($arControl['JS_VALUE']['multiple']) && 'Y' == $arControl['JS_VALUE']['multiple'])
				{
					$boolMulti = true;
				}
				$intDayOfWeek = "(int)date('N')";
				if (!$boolMulti)
				{
					$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($intDayOfWeek, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
				}
				else
				{
					$arResult = array();
					foreach ($arValues['value'] as &$mxValue)
					{
						$arResult[] = str_replace(array('#FIELD#', '#VALUE#'), array($intDayOfWeek, $mxValue), $arLogic['OP'][$arControl['MULTIPLE']]);
					}
					if (isset($mxValue))
						unset($mxValue);
					$strResult = '(('.implode(') || (', $arResult).'))';
				}
			}
		}

		return (!$boolError ? $strResult : false);
	}

	/**
	 * @param bool|string $strControlID
	 * @return array|bool
	 */
	public static function GetControls($strControlID = false)
	{
		$arDayOfWeek = array(
			1 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_1'),
			2 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_2'),
			3 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_3'),
			4 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_4'),
			5 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_5'),
			6 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_6'),
			7 => Loc::getMessage('BT_MOD_SALE_COND_DAY_OF_WEEK_7')
		);
		$arControlList = array(
			'CondSaleCmnDayOfWeek' => array(
				'ID' => 'CondSaleCmnDayOfWeek',
				'EXECUTE_MODULE' => 'sale',
				'MODULE_ID' => false,
				'MODULE_ENTITY' => 'datetime',
				'FIELD' => 'DAY_OF_WEEK',
				'FIELD_TYPE' => 'int',
				'MULTIPLE' => 'N',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_COND_CMP_CMN_DAYOFWEEK_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_COND_CMP_CMN_DAYOFWEEK_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $arDayOfWeek
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list'
				)
			)
		);

		return static::searchControl($arControlList, $strControlID);
	}

	public static function GetShowIn($arControls)
	{
		$arControls = array(CSaleCondCtrlGroup::GetControlID());
		return $arControls;
	}
}

class CSaleCondTree extends CGlobalCondTree
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
				$strFinal = 'function('.$arParams['ORDER'].'){';
				if (!empty($this->arExecuteFunc))
				{
					$strFinal .= implode('; ', $this->arExecuteFunc).'; ';
				}
				$strFinal .= 'return '.$strResult.'; };';
				$strFinal = preg_replace("#;{2,}#",";", $strFinal);
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
				$defaultBlock = $this->GetDefaultConditions();
				if ($arLevel['CLASS_ID'] !== $defaultBlock['CLASS_ID'])
				{
					return false;
				}
				if (isset($this->arControlList[$arLevel['CLASS_ID']]))
				{
					$arOneControl = $this->arControlList[$arLevel['CLASS_ID']];
					if ($arOneControl['GROUP'] == 'Y')
					{
						$arSubParams = $arParams;
						$arSubParams['FUNC_ID'] .= '_'.$intRowNum;
						$arSubEval = $this->GenerateLevel($arLevel['CHILDREN'], $arSubParams);
						if (false === $arSubEval || !is_array($arSubEval))
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
					$arResult[] = '('.$strEval.')';
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
						$arResult[] = '('.$strEval.')';
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

class CSaleCondCumulativeCtrl extends \CSaleCondCtrlComplex
{
	const TYPE_ORDER_ARCHIVED     = CumulativeCalculator::TYPE_ORDER_ARCHIVED;
	const TYPE_ORDER_NON_ARCHIVED = CumulativeCalculator::TYPE_ORDER_NON_ARCHIVED;

	public static function onBuildDiscountConditionInterfaceControls()
	{
		return new EventResult(
			EventResult::SUCCESS,
			static::getControlDescr(),
			'sale'
		);
	}

	public static function getControlDescr()
	{
		$description = parent::getControlDescr();
		$description['SORT'] = 700;
		return $description;
	}

	public static function getControlShow($params)
	{
		$result = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_GROUP_TITLE'),
			'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
			'children' => array()
		);

		foreach (static::getControls() as $control)
		{
			$controlShow = array(
				'controlId' => $control['ID'],
				'group' => false,
				'label' => $control['LABEL'],
				'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
				'control' => array()
			);

			if ($controlShow['controlId'] === 'Period')
			{
				$controlShow['control'] = array(
					$control['PREFIX'],
					Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_START'),
					$control['ATOMS']['ValueStart'],
					Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_END'),
					$control['ATOMS']['ValueEnd'],
				);
			}

			if ($controlShow['controlId'] === 'PeriodRelative')
			{
				$controlShow['control'] = array(
					$control['PREFIX'],
					$control['ATOMS']['Value'],
					$control['ATOMS']['TypeRelativePeriod'],
				);
			}

			$result['children'][] = $controlShow;
		}

		return $result;
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		$arControl = static::GetControls($arParams['ID']);
		if ($arControl === false)
			return false;
		$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);

		return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
	}

	public static function generate($oneCondition, $params, $control, $subs = false)
	{
		if (is_string($control))
		{
			$control = static::getControls($control);
		}

		if (!is_array($control))
		{
			return false;
		}

		$control['ATOMS'] = static::GetAtomsEx($control['ID'], true);
		$params['COND_NUM'] = $params['FUNC_ID'];

		$values = static::CheckAtoms($oneCondition, $oneCondition, $control, true);
		if ($values === false)
		{
			return false;
		}

		//be careful! this conditions have to work only with CSaleCondCtrlBasketGroup because this class provide filter to
		//sum orders.
		return static::exportConfiguration($values);
	}

	private static function exportConfiguration(array $configuration)
	{
		unset($configuration['id']);

		$controlId = $configuration['controlId'];
		if ($controlId === 'Period')
		{
			if (!empty($configuration['values']['ValueStart']))
			{
				static::ConvertDateTime2Int(
					$configuration['values']['ValueStart'],
					'FULL',
					\CTimeZone::getOffset()
				);
			}
			if (!empty($configuration['values']['ValueEnd']))
			{
				static::ConvertDateTime2Int(
					$configuration['values']['ValueEnd'],
					'FULL',
					\CTimeZone::getOffset()
				);
			}
		}

		return var_export($configuration, true);
	}

	public static function getCumulativeValue($currentOrder, array $dataSumConfiguration = array())
	{
		if(empty($currentOrder['USER_ID']))
		{
			return false;
		}

		$cumulativeCalculator = new CumulativeCalculator((int)$currentOrder['USER_ID'], $currentOrder['SITE_ID']);
		$cumulativeCalculator->setSumConfiguration(
			static::convertDataToSumConfiguration($dataSumConfiguration)
		);

		return $cumulativeCalculator->calculate();
	}

	protected static function convertDataToSumConfiguration(array $dataSumConfiguration)
	{
		$sumConfiguration = array(
			'type_sum_period'  => CumulativeCalculator::TYPE_COUNT_PERIOD_ALL_TIME,
		);

		$controlId = $dataSumConfiguration['controlId'];
		if ($controlId === 'Period')
		{
			$sumConfiguration['type_sum_period'] = CumulativeCalculator::TYPE_COUNT_PERIOD_INTERVAL;
			$sumConfiguration['sum_period_data'] = array(
				'order_start' => $dataSumConfiguration['values']['ValueStart'],
				'order_end' => $dataSumConfiguration['values']['ValueEnd'],
			);
		}
		elseif ($controlId == 'PeriodRelative')
		{
			$sumConfiguration['type_sum_period'] = CumulativeCalculator::TYPE_COUNT_PERIOD_RELATIVE;
			$sumConfiguration['sum_period_data'] = array(
				'period_value' => $dataSumConfiguration['values']['Value'],
				'period_type' => $dataSumConfiguration['values']['TypeRelativePeriod'],
			);
		}

		return $sumConfiguration;
	}

	/**
	 * @param bool|string $controlId
	 *
	 *@return array|bool
	 */
	public static function getControls($controlId = false)
	{
		$atoms = static::GetAtomsEx();
		$controlList = array(
			'Period' => array(
				'ID' => 'Period',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD'),
				'ATOMS' => $atoms['Period']
			),
			'PeriodRelative' => array(
				'ID' => 'PeriodRelative',
				'LABEL' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_RELATIVE'),
				'PREFIX' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_RELATIVE'),
				'ATOMS' => $atoms['PeriodRelative']
			),
		);

		if (false === $controlId)
		{
			return $controlList;
		}
		elseif (isset($controlList[$controlId]))
		{
			return $controlList[$controlId];
		}
		else
		{
			return false;
		}
	}

	public static function getShowIn($arControls)
	{
		return array('CondCumulativeGroup');
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		$arControl = static::GetControls($arOneCondition['controlId']);
		if ($arControl === false)
			return false;
		$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);

		$checkAtoms = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);

		return $checkAtoms;
	}

	public static function ConvertInt2DateTime(&$value, $format, $offset)
	{
		$error = false;
		if (is_array($value))
		{
			foreach ($value as $i => $piece)
			{
				if (static::ConvertInt2DateTime($piece, $format, $offset))
				{
					$error = true;
				}
				$value[$i] = $piece;
			}

			return $error;
		}
		else
		{
			if (!$value)
			{
				return false;
			}

			return parent::ConvertInt2DateTime($value, $format, $offset);
		}
	}

	public static function ConvertDateTime2Int(&$value, $format, $offset)
	{
		$error = false;
		if (is_array($value))
		{
			foreach ($value as $i => $piece)
			{
				if (static::ConvertDateTime2Int($piece, $format, $offset))
				{
					$error = true;
				}
				$value[$i] = $piece;
			}

			return $error;
		}
		else
		{
			if (!$value)
			{
				return false;
			}

			return parent::ConvertDateTime2Int($value, $format, $offset);
		}
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$arAtomList = array(
			'Period' => array(
				'ValueStart' => array(
					'JS' => array(
						'id' => 'ValueStart',
						'name' => 'ValueStart',
						'type' => 'datetime',
						'format' => 'datetime'
					),
					'ATOM' => array(
						'ID' => 'ValueStart',
						'FIELD_TYPE' => 'datetime',
						'MULTIPLE' => 'N'
					)
				),
				'ValueEnd' => array(
					'JS' => array(
						'id' => 'ValueEnd',
						'name' => 'ValueEnd',
						'type' => 'datetime',
						'format' => 'datetime'
					),
					'ATOM' => array(
						'ID' => 'ValueEnd',
						'FIELD_TYPE' => 'datetime',
						'MULTIPLE' => 'N'
					)
				)
			),
			'PeriodRelative' => array(
				'Value' => array(
					'JS' => array(
						'id' => 'Value',
						'name' => 'Value',
						'type' => 'input',
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'int',
						'MULTIPLE' => 'N'
					)
				),
				'TypeRelativePeriod' => array(
					'JS' => array(
						'id' => 'TypeRelativePeriod',
						'name' => 'TypeRelativePeriod',
						'type' => 'select',
						'values' => array(
							'Y' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_RELATIVE_Y'),
							'M' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_RELATIVE_M'),
							'D' => Loc::getMessage('BT_SALE_COND_GROUP_CUMULATIVE_COND_PERIOD_RELATIVE_D'),
						),
						'defaultText' => '...',
						'defaultValue' => 'Y',
						'first_option' => '...'
					),
					'ATOM' => array(
						'ID' => 'TypeRelativePeriod',
						'FIELD_TYPE' => 'char',
						'MULTIPLE' => 'N',
						'VALIDATE' => 'list'
					)
				)
			),
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneControl)
			{
				foreach ($arOneControl as &$arOneAtom)
					$arOneAtom = $arOneAtom['JS'];
				unset($arOneAtom);
			}
			unset($arOneControl);
		}

		if ($strControlID === false)
			return $arAtomList;
		elseif (isset($arAtomList[$strControlID]))
			return $arAtomList[$strControlID];
		else
			return false;
	}
}
