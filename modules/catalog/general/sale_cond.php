<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CCatalogCondCtrlBasketProductFields extends CCatalogCondCtrlIBlockFields
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 400;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$result = parent::GetControlShow($arParams);
		$result['label'] = Loc::getMessage('BT_MOD_SALE_COND_IBLOCK_CONTROLGROUP_LABEL');
		return $result;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strParentResult = '';
		$strResult = '';
		$parentResultValues = array();
		$resultValues = array();

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
				$useParent = ($arControl['PARENT'] && isset($arLogic['PARENT']));
				$strParent = $arParams['BASKET_ROW'].'[\'CATALOG\'][\'PARENT_'.$arControl['FIELD'].'\']';
				$strField = $arParams['BASKET_ROW'].'[\'CATALOG\'][\''.$arControl['FIELD'].'\']';
				switch ($arControl['FIELD_TYPE'])
				{
					case 'int':
					case 'double':
						if (is_array($arValues['value']))
						{
							if (!isset($arLogic['MULTI_SEP']))
							{
								$boolError = true;
							}
							else
							{
								foreach ($arValues['value'] as &$value)
								{
									if ($useParent)
										$parentResultValues[] = str_replace(
											array('#FIELD#', '#VALUE#'),
											array($strParent, $value),
											$arLogic['OP'][$arControl['MULTIPLE']]
										);
									$resultValues[] = str_replace(
										array('#FIELD#', '#VALUE#'),
										array($strField, $value),
										$arLogic['OP'][$arControl['MULTIPLE']]
									);
								}
								unset($value);
								if ($useParent)
									$strParentResult = '('.implode($arLogic['MULTI_SEP'], $parentResultValues).')';
								$strResult = '('.implode($arLogic['MULTI_SEP'], $resultValues).')';
								unset($resultValues, $parentResultValues);
							}
						}
						else
						{
							if ($useParent)
								$strParentResult = str_replace(
									array('#FIELD#', '#VALUE#'),
									array($strParent, $arValues['value']),
									$arLogic['OP'][$arControl['MULTIPLE']]
								);
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, $arValues['value']),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
					case 'char':
					case 'string':
					case 'text':
						if (is_array($arValues['value']))
						{
							$boolError = true;
						}
						else
						{
							if ($useParent)
								$strParentResult = str_replace(
									array('#FIELD#', '#VALUE#'),
									array($strParent, '"'.EscapePHPString($arValues['value']).'"'),
									$arLogic['OP'][$arControl['MULTIPLE']]
								);
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, '"'.EscapePHPString($arValues['value']).'"'),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
					case 'date':
					case 'datetime':
						if (is_array($arValues['value']))
						{
							$boolError = true;
						}
						else
						{
							if ($useParent)
								$strParentResult = str_replace(
									array('#FIELD#', '#VALUE#'),
									array($strParent, $arValues['value']),
									$arLogic['OP'][$arControl['MULTIPLE']]
								);
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, $arValues['value']),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
				}

				$strResult = 'isset('.$strField.') && '.$strResult;
				if ($useParent)
					$strResult = 'isset('.$strParent.') ? (('.$strResult.')'.$arLogic['PARENT'].$strParentResult.') : ('.$strResult.')';
				$strResult = '('.$strResult.')';
			}
		}

		return (!$boolError ? $strResult : false);
	}

	public static function GetShowIn($arControls)
	{
		if (!empty($arControls))
		{
			$strDisableKey = CSaleCondCtrlGroup::GetControlID();
			$arControlsMap = array_fill_keys($arControls, true);
			if (array_key_exists($strDisableKey, $arControlsMap))
				unset($arControlsMap[$strDisableKey]);
			$arControls = array_keys($arControlsMap);
		}
		return $arControls;
	}
}

class CCatalogCondCtrlBasketProductProps extends CCatalogCondCtrlIBlockProps
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 500;
		return $description;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';
		$resultValues = array();
		$arValues = false;

		if (is_string($arControl))
		{
			$arControl = static::GetControls($arControl);
		}
		$boolError = !is_array($arControl);

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
				$strField = $arParams['BASKET_ROW'].'[\'CATALOG\'][\''.$arControl['FIELD'].'\']';
				switch ($arControl['FIELD_TYPE'])
				{
					case 'int':
					case 'double':
						if (is_array($arValues['value']))
						{
							if (!isset($arLogic['MULTI_SEP']))
							{
								$boolError = true;
							}
							else
							{
								foreach ($arValues['value'] as &$value)
								{
									$resultValues[] = str_replace(
										array('#FIELD#', '#VALUE#'),
										array($strField, $value),
										$arLogic['OP'][$arControl['MULTIPLE']]
									);
								}
								unset($value);
								$strResult = '('.implode($arLogic['MULTI_SEP'], $resultValues).')';
								unset($resultValues);
							}
						}
						else
						{
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, $arValues['value']),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
					case 'char':
					case 'string':
					case 'text':
						if (is_array($arValues['value']))
						{
							$boolError = true;
						}
						else
						{
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, '"'.EscapePHPString($arValues['value']).'"'),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
					case 'date':
					case 'datetime':
						if (is_array($arValues['value']))
						{
							$boolError = true;
						}
						else
						{
							$strResult = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($strField, $arValues['value']),
								$arLogic['OP'][$arControl['MULTIPLE']]
							);
						}
						break;
				}
				$strResult = '(isset('.$strField.') && '.$strResult.')';
			}
		}

		return (!$boolError ? $strResult : false);
	}

	public static function GetShowIn($arControls)
	{
		if (!empty($arControls))
		{
			$strDisableKey = CSaleCondCtrlGroup::GetControlID();
			$arControlsMap = array_fill_keys($arControls, true);
			if (array_key_exists($strDisableKey, $arControlsMap))
				unset($arControlsMap[$strDisableKey]);
			$arControls = array_keys($arControlsMap);
		}
		return $arControls;
	}
}

class CCatalogCondCtrlCatalogSettings extends CGlobalCondCtrlComplex
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 900;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$controlList = static::GetControls();
		$result = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('BX_COND_CATALOG_SETTINGS_CONTROLGROUP_LABEL'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'children' => array()
		);
		foreach ($controlList as &$control)
		{
			$jsControl = array(
				'controlId' => $control['ID'],
				'group' => ($control['GROUP'] == 'Y'),
				'label' => $control['LABEL'],
				'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
				'control' => array()
			);
			if ($control['ID'] == 'CondCatalogRenewal')
			{
				$jsControl['control'] = array(
					array(
						'id' => 'prefix',
						'type' => 'prefix',
						'text' => $control['PREFIX'],
					),
					static::GetValueAtom($control['JS_VALUE'])
				);
			}
			else
			{
				$jsControl['control'] = array(
					array(
						'id' => 'prefix',
						'type' => 'prefix',
						'text' => $control['PREFIX'],
					),
					static::GetLogicAtom($control['LOGIC']),
					static::GetValueAtom($control['JS_VALUE'])
				);
			}
			$result['children'][] = $jsControl;
		}
		unset($jsControl, $control, $controlList);
		return $result;
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;

		if ($arParams['ID'] == 'CondCatalogRenewal')
		{
			$control = static::GetControls($arParams['ID']);
			if ($control === false)
				return false;

			return array(
				'id' => $arParams['COND_NUM'],
				'controlId' => $control['ID'],
				'values' => array('value' => 'Y')
			);
		}
		else
		{
			return parent::GetConditionShow($arParams);
		}
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		if ($arOneCondition['controlId'] == 'CondCatalogRenewal')
		{
			$control = static::GetControls($arOneCondition['controlId']);
			if ($control === false)
				return false;
			return array('value' => 'Y');
		}
		else
		{
			return parent::Parse($arOneCondition);
		}
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';

		if (is_string($arControl))
		{
			$arControl = static::GetControls($arControl);
		}
		$boolError = !is_array($arControl);

		if (!$boolError && $arOneCondition['value'] === 'Y')
		{
			$strField = $arParams['ORDER']."['RECURRING_ID']";
			$strResult = 'isset('.$strField.') && '.$strField;
		}

		return (!$boolError ? $strResult : false);
	}

	public static function GetShowIn($arControls)
	{
		return array(CSaleCondCtrlGroup::GetControlID());
	}

	public static function GetControlID()
	{
		return array(
			'CondCatalogRenewal'
		);
	}
	/**
	 * @param bool|string $strControlID
	 * @return bool|array
	 */
	public static function GetControls($strControlID = false)
	{
		$controlList = array(
			'CondCatalogRenewal' => array(
				'ID' => 'CondCatalogRenewal',
				'PARENT' => false,
				'EXECUTE_MODULE' => 'catalog',
				'EXIST_HANDLER' => 'Y',
				'MODULE_ID' => 'catalog',
				'MODULE_ENTITY' => 'catalog',
				'ENTITY' => 'DISCOUNT',
				'FIELD' => 'RENEWAL',
				'FIELD_TABLE' => 'RENEWAL',
				'FIELD_TYPE' => 'char',
				'MULTIPLE' => 'N',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('BX_COND_CATALOG_RENEWAL_LABEL'),
				'PREFIX' => Loc::getMessage('BX_COND_CATALOG_RENEWAL_PREFIX'),
				'JS_VALUE' => array(
					'type' => 'hidden',
					'value' => 'Y',
				),
			)
		);
		if ($strControlID === false)
		{
			return $controlList;
		}
		elseif (isset($controlList[$strControlID]))
		{
			return $controlList[$strControlID];
		}
		else
		{
			return false;
		}
	}
}