<?
/** @global CUser $USER */
/** @global int $ID */
use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

if ($USER->CanDoOperation('catalog_price'))
{
	$IBLOCK_ID = intval($IBLOCK_ID);
	if (0 >= $IBLOCK_ID)
		return;
	$MENU_SECTION_ID = intval($MENU_SECTION_ID);
	$ID = (int)$ID;
	$PRODUCT_ID = (0 < $ID ? CIBlockElement::GetRealElement($ID) : 0);

	$boolPriceRights = ($PRODUCT_ID > 0
		? CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, "element_edit_price")
		: CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $MENU_SECTION_ID, "element_edit_price")
	);

	if ($boolPriceRights)
	{
		Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/templates/product_edit_action.php');

		$arCatalogBasePrices = array();
		$arCatalogPrices = array();

		$CAT_ROW_COUNTER = intval($CAT_ROW_COUNTER);
		if ($CAT_ROW_COUNTER < 0)
			$strWarning .= Loc::getMessage("C2IT_INTERNAL_ERROR")."<br>";

		$arCatalogBaseGroup = CCatalogGroup::GetBaseGroup();
		if (!$arCatalogBaseGroup)
			$strWarning .= Loc::getMessage("C2IT_NO_BASE_TYPE")."<br>";

		$CAT_VAT_ID = intval($CAT_VAT_ID);
		$CAT_VAT_INCLUDED = !isset($CAT_VAT_INCLUDED) || $CAT_VAT_INCLUDED == 'N' ? 'N' : 'Y';

		$bUseExtForm = (isset($_POST['price_useextform']) && $_POST['price_useextform'] == 'Y');

		if (!$bUseExtForm)
			$CAT_ROW_COUNTER = 0;

		for ($i = 0; $i <= $CAT_ROW_COUNTER; $i++)
		{
			${"CAT_BASE_PRICE_".$i} = str_replace(",", ".", ${"CAT_BASE_PRICE_".$i});

			if (intval(${"CAT_BASE_QUANTITY_FROM_".$i}) > 0
				|| intval(${"CAT_BASE_QUANTITY_TO_".$i}) > 0
				|| strlen(${"CAT_BASE_PRICE_".$i}) > 0
				|| ${"CAT_PRICE_EXIST_".$i} == 'Y'
			)
			{
				$arCatalogBasePrices[] = array(
					"ID" => intval($CAT_BASE_ID[$i]),
					"IND" => $i,
					"QUANTITY_FROM" => $bUseExtForm ? intval(${"CAT_BASE_QUANTITY_FROM_".$i}) : '',
					"QUANTITY_TO" => $bUseExtForm ? intval(${"CAT_BASE_QUANTITY_TO_".$i}) : '',
					"PRICE" => ($bUseExtForm || $i == 0) ? ${"CAT_BASE_PRICE_".$i} : '',
					"CURRENCY" => ${"CAT_BASE_CURRENCY_".$i},
					"CAT_PRICE_EXIST" => (${"CAT_PRICE_EXIST_".$i} == 'Y' ? 'Y' : 'N'),
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
			$boolSKUExists = (!empty($boolSKUExists) && $boolSKUExists === true);
			if (!$boolSKUExists && $intCount == 0)
				$strWarning .= Loc::getMessage("C2IT_ERROR_NO_PRICE").'<br>';
		}
	}
}