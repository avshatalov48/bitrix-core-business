<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if ($arResult)
{
	?>
	<form method="GET" action="<?=$arResult["CURRENT_PAGE"]?>" name="bfilter_<?=$arResult["FILTER_ID"]?>">
	<table class="data-table">
		<thead>
			<tr>
				<td colspan="2"><?=GetMessage("SPCAS1_FILTER")?></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=GetMessage("SPCAS1_PERIOD")?></td>
				<td><?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	Array(
		"SHOW_INPUT" => "Y", 
		"FORM_NAME" => "bfilter_".$arResult["FILTER_ID"], 
		"INPUT_NAME" => "filter_date_from", 
		"INPUT_NAME_FINISH" => "filter_date_to", 
		"INPUT_VALUE" => $arResult["FILTER"]["filter_date_from"], 
		"INPUT_VALUE_FINISH" => $arResult["FILTER"]["filter_date_to"], 
		"SHOW_TIME" => "N" 
	)
);?></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<input type="submit" name="filter" value="<?=GetMessage("SPCAS1_SET")?>" />&nbsp;&nbsp;
					<input type="submit" name="del_filter" value="<?=GetMessage("SPCAS1_UNSET")?>" />
				</td>
			</tr>
		</tfoot>
	</table>
	<br />
	</form>
	
	<table class="data-table">
		<thead>
			<tr>
				<td><?=GetMessage("SPCAS1_NAME")?></td>
				<td><?=GetMessage("SPCAS1_QUANTITY")?></td>
				<td><?=GetMessage("SPCAS1_SUM")?></td>
			</tr>
		</thead>
		<?
		if ($arResult["ROWS"])
		{
		?>
			<tbody>
			<?
			foreach ($arResult["ROWS"] as $arRow)
			{
				?>
				<tr>
					<td><?=$arRow["NAME"]?></td>
					<td><?=$arRow["QUANTITY"]?></td>
					<td><?=$arRow["SUM_FORMAT"]?></td>
				</tr>
				<?
			}
			?>
			</tbody>
			<tfoot>
				<tr>
					<td><?=GetMessage("SPCAS1_ITOG")?></td>
					<td><?=$arResult["TOTAL"]["QUANTITY"]?></td>
					<td><?=$arResult["TOTAL"]["SUM_FORMAT"]?></td>
				</tr>
			</tfoot>
			<?
		}
		else
		{
			?>
			<tbody>
				<tr>
					<td colspan="3"><? ShowNote(GetMessage("SPCAS1_NO_ACT"))?></td>
				</tr>
			</tbody>
			<?
		}
		?>
	</table>
	<?
}
else
{
	?><? ShowError(GetMessage("SPCAS1_UNACTIVE_AFF"))?><?
}
?>