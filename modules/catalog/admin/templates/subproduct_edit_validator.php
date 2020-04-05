<?
/** @global CUser $USER */
/** @global int $ID */
use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

if ($USER->CanDoOperation('catalog_price'))
{
	$IBLOCK_ID = intval($IBLOCK_ID);
	if ($IBLOCK_ID <= 0)
		return;
	$PRODUCT_ID = (0 < $ID ? CIBlockElement::GetRealElement($ID) : 0);

	$boolPriceRights = ($PRODUCT_ID > 0
		? CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, "element_edit_price")
		: CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_edit_price")
	);

	if ($boolPriceRights)
	{
		Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/templates/product_edit_action.php');

		$arCatalogBasePrices = array();
		$arCatalogPrices = array();

		$SUBCAT_ROW_COUNTER = intval($SUBCAT_ROW_COUNTER);
		if ($SUBCAT_ROW_COUNTER < 0)
			$strWarning .= Loc::getMessage("C2IT_INTERNAL_ERROR")."<br>";

		$arCatalogBaseGroup = CCatalogGroup::GetBaseGroup();
		if (!$arCatalogBaseGroup)
			$strWarning .= Loc::getMessage("C2IT_NO_BASE_TYPE")."<br>";

		$SUBCAT_VAT_ID = intval($SUBCAT_VAT_ID);
		$SUBCAT_VAT_INCLUDED = !isset($SUBCAT_VAT_INCLUDED) || $SUBCAT_VAT_INCLUDED == 'N' ? 'N' : 'Y';

		$bUseExtForm = (isset($_POST['subprice_useextform']) && $_POST['subprice_useextform'] == 'Y');
		if (!$bUseExtForm)
			$SUBCAT_ROW_COUNTER = 0;

		for ($i = 0; $i <= $SUBCAT_ROW_COUNTER; $i++)
		{
			${"SUBCAT_BASE_PRICE_".$i} = str_replace(",", ".", ${"SUBCAT_BASE_PRICE_".$i});

			if (intval(${"SUBCAT_BASE_QUANTITY_FROM_".$i}) > 0
				|| intval(${"SUBCAT_BASE_QUANTITY_TO_".$i}) > 0
				|| strlen(${"SUBCAT_BASE_PRICE_".$i}) > 0
				|| ${"SUBCAT_PRICE_EXIST_".$i} == 'Y'
			)
			{
				$arCatalogBasePrices[] = array(
					"ID" => intval($SUBCAT_BASE_ID[$i]),
					"IND" => $i,
					"QUANTITY_FROM" => $bUseExtForm ? intval(${"SUBCAT_BASE_QUANTITY_FROM_".$i}) : '',
					"QUANTITY_TO" => $bUseExtForm ? intval(${"SUBCAT_BASE_QUANTITY_TO_".$i}) : '',
					"PRICE" => ($bUseExtForm || $i == 0) ? ${"SUBCAT_BASE_PRICE_".$i} : '',
					"CURRENCY" => ${"SUBCAT_BASE_CURRENCY_".$i},
					"CAT_PRICE_EXIST" => (${"SUBCAT_PRICE_EXIST_".$i} == 'Y' ? 'Y' : 'N'),
				);
			}
		}

		$intCount = count($arCatalogBasePrices);
		if ($bUseExtForm && $intCount > 0)
		{
			$allowEmptyRange = Main\Config\Option::get('catalog', 'save_product_with_empty_price_range') == 'Y';
			for ($i = 0; $i < $intCount - 1; $i++)
			{
				for ($j = $i + 1; $j < $intCount; $j++)
				{
					if ($arCatalogBasePrices[$i]["QUANTITY_FROM"] > $arCatalogBasePrices[$j]["QUANTITY_FROM"])
					{
						$tmp = $arCatalogBasePrices[$i];
						$arCatalogBasePrices[$i] = $arCatalogBasePrices[$j];
						$arCatalogBasePrices[$j] = $tmp;
					}
				}
			}

			for ($i = 0, $cnt = $intCount; $i < $cnt; $i++)
			{
				if ($i != 0 && $arCatalogBasePrices[$i]["QUANTITY_FROM"] <= 0
					|| $i == 0 && $arCatalogBasePrices[$i]["QUANTITY_FROM"] < 0)
					$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_FROM"], Loc::getMessage("C2IT_ERROR_BOUND_LEFT"))."<br>";

				if ($i != $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] <= 0
					|| $i == $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] < 0)
					$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_TO"], Loc::getMessage("C2IT_ERROR_BOUND_RIGHT"))."<br>";

				if ($arCatalogBasePrices[$i]["QUANTITY_FROM"] > $arCatalogBasePrices[$i]["QUANTITY_TO"]
					&& ($i != $cnt-1 || $arCatalogBasePrices[$i]["QUANTITY_TO"] > 0))
					$strWarning .= str_replace("#DIAP#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], Loc::getMessage("C2IT_ERROR_BOUND"))."<br>";

				if ($i < $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] >= $arCatalogBasePrices[$i+1]["QUANTITY_FROM"])
					$strWarning .= str_replace("#DIAP1#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], str_replace("#DIAP2#", $arCatalogBasePrices[$i+1]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i+1]["QUANTITY_TO"], Loc::getMessage("C2IT_ERROR_BOUND_CROSS")))."<br>";

				if ($i < $cnt-1
					&& $arCatalogBasePrices[$i+1]["QUANTITY_FROM"] - $arCatalogBasePrices[$i]["QUANTITY_TO"] > 1
					&& !$allowEmptyRange
				)
					$strWarning .= str_replace("#DIAP1#", ($arCatalogBasePrices[$i]["QUANTITY_TO"] + 1)."-".($arCatalogBasePrices[$i+1]["QUANTITY_FROM"] - 1), Loc::getMessage("C2IT_ERROR_BOUND_MISS"))."<br>";

				if ($i >= $cnt-1
					&& $arCatalogBasePrices[$i]["QUANTITY_TO"] > 0)
					$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_TO"], Loc::getMessage("C2IT_ERROR_BOUND_MISS_TOP"))."<br>";

				if ($arCatalogBasePrices[$i]['CAT_PRICE_EXIST'] != 'Y')
					$strWarning .= str_replace("#DIAP#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], Loc::getMessage("C2IT_ERROR_BOUND_PRICE"))."<br>";
			}
		}

		if (Main\Config\Option::get('catalog', 'save_product_without_price') != 'Y')
		{
			if ($intCount == 0)
				$strWarning .= Loc::getMessage("C2IT_ERROR_NO_PRICE").'<br>';
		}
	}
}