<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (is_array($arResult["RESULT"]))
{
	if ($arResult["RESULT"]["RESULT"] == "NEXT_STEP")
		require("step.php");
	else
	{
		if ($arResult["RESULT"]["RESULT"] == "ERROR")
			echo ShowError($arResult["RESULT"]["TEXT"]);
		elseif ($arResult["RESULT"]["RESULT"] == "NOTE")
			echo ShowNote($arResult["RESULT"]["TEXT"]);
		elseif ($arResult["RESULT"]["RESULT"] == "OK")
		{

			echo GetMessage('SALE_SADC_RESULT').": <b>";
			if (array_key_exists('DELIVERY_DISCOUNT_PRICE', $arResult["RESULT"])
					&& round($arResult["RESULT"]['DELIVERY_DISCOUNT_PRICE'], 4) != round($arResult["RESULT"]["VALUE"], 4))
			{
				echo (strlen($arResult["RESULT"]["DELIVERY_DISCOUNT_PRICE_FORMATED"]) > 0 ? $arResult["RESULT"]["DELIVERY_DISCOUNT_PRICE_FORMATED"] : number_format($arResult["RESULT"]["DELIVERY_DISCOUNT_PRICE"], 2, ',', ' '));

				echo "</b><br/><span style='text-decoration:line-through;color:#828282;'>".(strlen($arResult["RESULT"]["VALUE_FORMATTED"]) > 0 ? $arResult["RESULT"]["VALUE_FORMATTED"] : number_format($arResult["RESULT"]["VALUE"], 2, ',', ' '))."</span>";
			}
			else
			{
				echo (strlen($arResult["RESULT"]["VALUE_FORMATTED"]) > 0 ? $arResult["RESULT"]["VALUE_FORMATTED"] : number_format($arResult["RESULT"]["VALUE"], 2, ',', ' '))."</b>";
			}
			echo "<br />";

			if (strlen($arResult["RESULT"]["TRANSIT"]) > 0)
			{
				echo '<br />';
				echo GetMessage('SALE_SADC_TRANSIT').': <b>'.$arResult["RESULT"]["TRANSIT"].'</b>';
			}

			if (!empty($arResult["RESULT"]["PACKS_COUNT"]))
			{
				echo '<br />';
				echo GetMessage('SALE_SADC_PACKS').': <b>'.$arResult["RESULT"]["PACKS_COUNT"].'</b>';
			}

		}
	}
}

if ($arParams["STEP"] == 0)
	require("start.php");
?>