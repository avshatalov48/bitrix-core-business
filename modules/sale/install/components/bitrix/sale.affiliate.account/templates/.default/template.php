<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if ($arResult)
{
	?>
	<form method="GET" action="<?=$arResult["CURRENT_PAGE"]?>" name="bfilter_<?=$arResult["FILTER_ID"]?>">
	<table class="data-table">
		<thead>
		<tr>
			<td colspan="2"><?=GetMessage("SPCA_FILTER")?></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?=GetMessage("SPCA_PERIOD")?></td>
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
				<input type="submit" name="filter" value="<?=GetMessage("SPCA_SET")?>">&nbsp;&nbsp;
				<input type="submit" name="del_filter" value="<?=GetMessage("SPCA_UNSET")?>">
			</td>
		</tr>
		</tfoot>
	</table>
	<br />
	</form>

	<table class="data-table">
		<thead>
			<tr>
				<td><?=GetMessage("SPCA_DATE")?></td>
				<td><?=GetMessage("SPCA_INCOME")?></td>
				<td><?=GetMessage("SPCA_OUTCOME")?></td>
				<td><?=GetMessage("SPCA_COMMENT")?></td>
			</tr>
		</thead>
		<tbody>
		<?
		if (count($arResult["TRANSACT"]) > 0)
		{
			foreach ($arResult["TRANSACT"] as $arTransact)
			{
				?>
				<tr>
					<td><?=$arTransact["TRANSACT_DATE"]?></td>
					<td><?=$arTransact["AMOUNT_INCOME"]?></td>
					<td><?=$arTransact["AMOUNT_OUTCOME"]?></td>
					<td><?=$arTransact["DESCRIPTION_NOTES"]?></td>
				</tr>
				<?
			}
		}
		else
		{
			?>
			<tr>
				<td colspan="4"><? ShowNote(GetMessage("SPCA_NO_ACT"))?></td>
			</tr>
			<?
		}
		?>
		</tbody>
		<tfoot>
		<tr>
			<td><?=GetMessage("SPCA_ON_ACCT")?> <?=$arResult["CURRENT_DATE"]?></td>
			<td><?=$arResult["PAID_SUM_INCOME"]?></td>
			<td><?=$arResult["PAID_SUM_OUTCOME"]?></td>
			<td>&nbsp;</td>
		</tr>
		</tfoot>
	</table>
	<?
}
else
{
	?><? ShowError(GetMessage("SPCA_UNACTIVE_AFF"))?><?
}
?>