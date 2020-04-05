<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (is_array($arResult["RESULT"]))
{
	if ($arResult["RESULT"]["RESULT"] == "NEXT_STEP")
		require("step.php");
	else
	{
		if ($arResult["RESULT"]["RESULT"] == "ERROR" || $arResult["RESULT"]["RESULT"] == "NOTE")
		{
		?>
			<script>
				app.alert(
				{
					text : "<?=$arResult["RESULT"]["TEXT"]?>",
					title : "<?=GetMessage("ALERT_ERROR")?>",
					button:"OK"

				}
				);
			</script>
		<?
			/*echo ShowError($arResult["RESULT"]["TEXT"]);
		elseif ($arResult["RESULT"]["RESULT"] == "NOTE")
			echo ShowNote($arResult["RESULT"]["TEXT"]); */
		}
		elseif ($arResult["RESULT"]["RESULT"] == "OK")
		{
			echo GetMessage('SALE_SADC_RESULT').": <b>".(strlen($arResult["RESULT"]["VALUE_FORMATTED"]) > 0 ? $arResult["RESULT"]["VALUE_FORMATTED"] : number_format($arResult["RESULT"]["VALUE"], 2, ',', ' '))."</b>";
			/*if ($arResult["RESULT"]["TRANSIT"] > 0)
			{
				echo '<br />';
				echo GetMessage('SALE_SADC_TRANSIT').': <b>'.$arResult["RESULT"]["TRANSIT"].'</b>';
			}  */
		}
	}
}

if ($arParams["STEP"] == 0)
	require("start.php");
?>