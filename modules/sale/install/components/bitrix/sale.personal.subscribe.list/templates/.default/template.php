<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult["NAV_STRING"] <> ''):?>
	<p><?=$arResult["NAV_STRING"]?></p>
<?endif?>
<table class="sale_personal_subscr_list data-table">
	<tr>
		<th>ID<br /><?= SortingEx("ID") ?></th>
		<th align="center"><?= GetMessage("STPSL_PRODUCT") ?><br /><?= SortingEx("PRODUCT_NAME") ?></th>
		<th align="center"><?= GetMessage("STPSL_PERIOD_TYPE") ?><br /><?= SortingEx("RECUR_SCHEME_TYPE") ?></th>
		<th align="center"><?= GetMessage("STPSL_PERIOD_BETW") ?><br /><?= SortingEx("RECUR_SCHEME_LENGTH") ?></th>
		<th align="center"><?= GetMessage("STPSL_CANCELED") ?><br /><?= SortingEx("CANCELED") ?></th>
		<th align="center"><?= GetMessage("STPSL_DATE_LAST") ?><br /><?= SortingEx("PRIOR_DATE") ?></th>
		<th align="center"><?= GetMessage("STPSL_DATE_NEXT") ?><br /><?= SortingEx("NEXT_DATE") ?></th>
		<th align="center"><?= GetMessage("STPSL_LAST_SUCCESS") ?><br /><?= SortingEx("SUCCESS_PAYMENT") ?></th>
		<th align="center"><?= GetMessage("STPSL_ACTIONS") ?></th>
	</tr>
	<?foreach($arResult["RECURRING"] as $val):?>
		<tr>
			<td align="center"><b><?= $val["ID"]?></b></td>
			<td><?
				if ($val["PRODUCT_URL"] <> '')
					echo "<a href=\"".$val["PRODUCT_URL"]."\">";
				if ($val["PRODUCT_NAME"] <> '')
					echo $val["PRODUCT_NAME"];
				else
					echo $val["PRODUCT_ID"];
				if ($val["PRODUCT_URL"] <> '')
					echo "</a>";
				?></td>
			<td><?=$val["SALE_TIME_PERIOD_TYPES"];?></td>
			<td><?=$val["RECUR_SCHEME_LENGTH"]; ?></td>
			<td><?=(($val["CANCELED"] == "Y") ? GetMessage("STPSL_YES") : GetMessage("STPSL_NO")); ?></td>
			<td><?=$val["PRIOR_DATE"]; ?></td>
			<td><?=$val["NEXT_DATE"]; ?></td>
			<td><?=(($val["SUCCESS_PAYMENT"] == "Y") ? GetMessage("STPSL_YES") : GetMessage("STPSL_NO")); ?></td>
			<td><?
				if ($val["CANCELED"] != "Y")
				{
					?>
					<a title="<?= GetMessage("STPSL_CANCEL") ?>" href="<?=$val["URL_TO_CANCEL"]?>"><?= GetMessage("STPSL_CANCEL1") ?></a>
					<?
				}
				?></td>
		</tr>
	<?endforeach;?>
</table>
<?if($arResult["NAV_STRING"] <> ''):?>
	<p><?=$arResult["NAV_STRING"]?></p>
<?endif?>