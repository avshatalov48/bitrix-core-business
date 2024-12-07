<?php

use Bitrix\Main\Localization\Loc;

class CCatalogActionCtrlBasketProductFields extends CCatalogCondCtrlIBlockFields
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 200;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$result = parent::GetControlShow($arParams);
		$result['label'] = Loc::getMessage('BT_MOD_SALE_ACT_IBLOCK_CONTROLGROUP_LABEL');
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
		$key = array_search(CSaleActionCtrlAction::GetControlID(), $arControls);
		if (false !== $key)
		{
			unset($arControls[$key]);
			$arControls = array_values($arControls);
		}
		return $arControls;
	}

	/**
	 * @param bool|string $strControlID
	 * @return bool|array
	 */
	public static function GetControls($strControlID = false)
	{
		$priceTypeList = array();
		foreach (\CCatalogGroup::GetListArray() as $priceType)
		{
			$priceType['ID'] = (int)$priceType['ID'];
			$priceType['NAME_LANG'] = (string)$priceType['NAME_LANG'];
			$priceTypeList[$priceType['ID']] = $priceType['NAME'].($priceType['NAME_LANG'] != '' ? ' ('.$priceType['NAME_LANG'].')' : '');
		}
		unset($priceType);

		$priceTypeLogic = array(
			BT_COND_LOGIC_EQ => Loc::getMessage('BT_MOD_SALE_ACT_CATALOG_PRICE_TYPE_LOGIC_EQ_LABEL'),
			BT_COND_LOGIC_NOT_EQ => Loc::getMessage('BT_MOD_SALE_ACT_CATALOG_PRICE_TYPE_LOGIC_NOT_EQ_LABEL'),
		);

		$controls = parent::GetControls(false);

		$controls['CondCatalogPriceType'] = array(
			'ID' => 'CondCatalogPriceType',
			'PARENT' => false,
			'EXECUTE_MODULE' => 'catalog',
			'EXIST_HANDLER' => 'Y',
			'MODULE_ID' => 'catalog',
			'MODULE_ENTITY' => 'catalog',
			'ENTITY' => 'PRICE',
			'FIELD' => 'CATALOG_GROUP_ID',
			'FIELD_TABLE' => 'CATALOG_GROUP_ID',
			'FIELD_TYPE' => 'int',
			'MULTIPLE' => 'N',
			'GROUP' => 'N',
			'LABEL' => Loc::getMessage('BT_MOD_SALE_ACT_CATALOG_PRICE_TYPE_LABEL'),
			'PREFIX' => Loc::getMessage('BT_MOD_SALE_ACT_CATALOG_PRICE_TYPE_PREFIX'),
			'LOGIC' => static::GetLogicEx(array_keys($priceTypeLogic), $priceTypeLogic),
			'JS_VALUE' => array(
				'type' => 'select',
				'values' => $priceTypeList,
				'multiple' => 'Y',
				'show_value' => 'Y',
			),
			'PHP_VALUE' => array(
				'VALIDATE' => 'list'
			)
		);

		return static::searchControl($controls, $strControlID);
	}

	/**
	 * @return string|array
	 */
	public static function GetControlID()
	{
		$result = parent::GetControlID();
		$result[] = 'CondCatalogPriceType';
		return $result;
	}
}

class CCatalogActionCtrlBasketProductProps extends CCatalogCondCtrlIBlockProps
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['SORT'] = 300;
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
		$key = array_search(CSaleActionCtrlAction::GetControlID(), $arControls);
		if (false !== $key)
		{
			unset($arControls[$key]);
			$arControls = array_values($arControls);
		}
		return $arControls;
	}

}

class CCatalogGifterProduct extends CGlobalCondCtrlAtoms
{
	public static function GetShowIn($params)
	{
		return array();
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$result = '';
		if (is_string($arControl))
			$arControl = static::GetControls($arControl);
		$boolError = !is_array($arControl);

		if (!$boolError)
		{
			$arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);
			$arValues = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
			$boolError = ($arValues === false);
		}

		if (!$boolError)
		{
			if (!is_array($arOneCondition['Value']))
				$arOneCondition['Value'] = array($arOneCondition['Value']);
			$stringDataArray = 'array('.implode(',', $arOneCondition['Value']).')';
			$type = $arOneCondition['Type'];

			$result = static::GetClassName() . "::GenerateApplyCallableFilter('{$arControl['ID']}', {$stringDataArray}, '{$type}')";
		}

		return $result;
	}

	public static function GetControls($strControlID = false)
	{
		$arAtoms = static::GetAtomsEx(false, false);
		$arControlList = array(
			'GifterCondIBElement' => array(
				'ID' => 'GifterCondIBElement',
				'PARENT' => true,
				'FIELD' => 'ID',
				'FIELD_TYPE' => 'int',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_ACT_IBLOCK_ELEMENT_ID_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_ACT_IBLOCK_ELEMENT_ID_LABEL'),
				'ATOMS' => $arAtoms['GifterCondIBElement'],
			),
			'GifterCondIBSection' => array(
				'ID' => 'GifterCondIBSection',
				'PARENT' => false,
				'FIELD' => 'SECTION_ID',
				'FIELD_TYPE' => 'int',
				'LABEL' => Loc::getMessage('BT_MOD_SALE_ACT_IBLOCK_SECTION_ID_LABEL'),
				'PREFIX' => Loc::getMessage('BT_MOD_SALE_ACT_IBLOCK_SECTION_ID_LABEL'),
				'ATOMS' => $arAtoms['GifterCondIBSection'],
			),
		);

		foreach ($arControlList as &$control)
		{
			$control['EXIST_HANDLER'] = 'Y';
			$control['MODULE_ID'] = 'catalog';
			$control['MODULE_ENTITY'] = 'iblock';
			$control['ENTITY'] = 'ELEMENT';
		}
		unset($control);

		if ($strControlID === false)
		{
			return $arControlList;
		}
		elseif (isset($arControlList[$strControlID]))
		{
			return $arControlList[$strControlID];
		}
		else
		{
			return false;
		}
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$atomList = array(
			'GifterCondIBElement' => array(
				'Type' => array(
					'JS' => array(
						'id' => 'Type',
						'name' => 'Type',
						'type' => 'select',
						'values' => array(
							CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ONE'),
							CSaleDiscountActionApply::GIFT_SELECT_TYPE_ALL => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ALL'),
						),
						'defaultText' => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_DEF'),
						'defaultValue' => CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE,
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
						'name' => 'Value',
						'type' => 'multiDialog',
						'popup_url' => self::getAdminSection().'cat_product_search_dialog.php',
						'popup_params' => array(
							'lang' => LANGUAGE_ID,
							'caller' => 'discount_rules',
							'allow_select_parent' => 'Y',
						),
						'param_id' => 'n',
						'show_value' => 'Y'
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'int',
						'MULTIPLE' => 'Y',
						'VALIDATE' => 'element'
					)
				)
			),
			'GifterCondIBSection' => array(
				'Type' => array(
					'JS' => array(
						'id' => 'Type',
						'name' => 'Type',
						'type' => 'select',
						'values' => array(
							CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ONE'),
							CSaleDiscountActionApply::GIFT_SELECT_TYPE_ALL => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_ALL'),
						),
						'defaultText' => Loc::getMessage('BT_SALE_ACT_GIFT_SELECT_TYPE_SELECT_DEF'),
						'defaultValue' => CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE,
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
						'name' => 'Value',
						'type' => 'popup',
						'popup_url' => self::getAdminSection().'iblock_section_search.php',
						'popup_params' => array(
							'lang' => LANGUAGE_ID,
							'discount' => 'Y',
							'simplename' => 'Y'
						),
						'param_id' => 'n',
						'show_value' => 'Y',
					),
					'ATOM' => array(
						'ID' => 'Value',
						'FIELD_TYPE' => 'int',
						'MULTIPLE' => 'N',
						'VALIDATE' => 'section'
					)
				)
			)
		);

		return static::searchControlAtoms($atomList, $strControlID, $boolEx);
	}

	public static function GenerateApplyCallableFilter($controlId, array $gifts, $type)
	{
		$gifts = array_combine($gifts, $gifts);
		return function($row) use($controlId, $gifts, $type)
		{
			static $isApplied = false;
			if($isApplied && $type === CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE)
			{
				return false;
			}
			$right = false;
			switch($controlId)
			{
				case 'GifterCondIBElement':
					$right =
						(
							(isset($row['CATALOG']['PARENT_ID']) && array_intersect($gifts, (array)$row['CATALOG']['PARENT_ID'])) ||
							(isset($row['CATALOG']['ID']) && isset($gifts[$row['CATALOG']['ID']]))
						) &&
						isset($row['QUANTITY']) && $row['QUANTITY'] == \CCatalogGifterProduct::getRatio($row['CATALOG']['ID'])
						&& isset($row['PRICE']) && $row['PRICE'] > 0
					;
					break;

				case 'GifterCondIBSection':
					$right =
						(
							isset($row['CATALOG']['SECTION_ID']) && array_intersect($gifts, (array)$row['CATALOG']['SECTION_ID'])
						) &&
						isset($row['QUANTITY']) && $row['QUANTITY'] == \CCatalogGifterProduct::getRatio($row['CATALOG']['ID'])
						&& isset($row['PRICE']) && $row['PRICE'] > 0
					;

					break;
			}

			if($right)
			{
				$isApplied = true;
			}

			return $right;
		};
	}

	public static function getRatio($productId)
	{
		if (!\Bitrix\Main\Loader::includeModule('catalog'))
		{
			return 1;
		}

		$ratio = \Bitrix\Catalog\MeasureRatioTable::getList(
			array(
				'select' => array('RATIO'),
				'filter' => array('PRODUCT_ID' => $productId, '=IS_DEFAULT' => 'Y')
			)
		)->fetch();

		return empty($ratio['RATIO'])? 1 : $ratio['RATIO'];
	}

	public static function ProvideGiftData(array $actionData)
	{
		$type = $actionData['DATA']['Type'];
		$values = (array)$actionData['DATA']['Value'];

		switch($actionData['CLASS_ID'])
		{
			case 'GifterCondIBElement':
				return array(
					'Type' => $type,
					'GiftValue' => $values,
				);
			case 'GifterCondIBSection':
				return array(
					'Type' => $type,
					'GiftValue' => static::GetProductIdsBySection(array_pop($values)),
				);
		}

		return array();
	}

	protected static function GetProductIdsBySection($sectionId)
	{
		if(!\Bitrix\Main\Loader::includeModule('iblock'))
		{
			return array();
		}
		$ids = array();
		$query = CIBlockElement::getList(array(), array(
			'ACTIVE_DATE' => 'Y',
			'SECTION_ID' => $sectionId,
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
			'ACTIVE' => 'Y',
		), false, false, array('ID'));

		while($row = $query->fetch())
		{
			$ids[] = $row['ID'];
		}

		return $ids;
	}

	public static function ExtendProductIds(array $giftedProductIds)
	{
		$products = CCatalogSku::getProductList($giftedProductIds);
		if (empty($products))
			return $giftedProductIds;

		foreach($products as $product)
			$giftedProductIds[] = $product['ID'];
		unset($product);

		return $giftedProductIds;
	}

	/**
	 * @return string
	 */
	private static function getAdminSection()
	{
		//TODO: need use \CAdminPage::getSelfFolderUrl, but in general it is impossible now
		return (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/');
	}
}
