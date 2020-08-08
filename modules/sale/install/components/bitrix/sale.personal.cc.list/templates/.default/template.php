<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form method="GET" action="<?=$arParams["URL_TO_NEW"]?>">
	<input type="submit" name="Add" value="<?echo GetMessage("STPCL_NEW")?>">
	<input type="hidden" name="ID" value="new">
</form>
<?if($arResult["ERROR_MESSAGE"] <> '')
	ShowError($arResult["ERROR_MESSAGE"]);?>
<?if($arResult["NAV_STRING"] <> ''):?>
	<p><?=$arResult["NAV_STRING"]?></p>
<?endif?>
<table class="sale_personal_cc_list data-table">
	<tr>
		<th align="center">ID<br /><?= SortingEx("ID")?></th>
		<th align="center"><?= GetMessage("STPCL_TYPE") ?><br /><?= SortingEx("CARD_TYPE") ?></th>
		<th align="center"><?= GetMessage("STPCL_PAY_SYS") ?><br /><?= SortingEx("PAY_SYSTEM_ACTION_ID") ?></th>
		<th align="center"><?= GetMessage("STPCL_CEXP") ?></th>
		<th align="center"><?= GetMessage("STPCL_ACTIV") ?><br /><?= SortingEx("ACTIVE") ?></th>
		<th align="center"><?= GetMessage("STPCL_ACTIONS") ?></th>
	</tr>
	<?foreach($arResult["CARDS"] as $val):?>
		<tr>
			<td align="center"><b><?= $val["ID"] ?></b></td>
			<td><?=$val["CARD_TYPE"] ?></td>
			<td><?=$val["PAY_SYSTEM"]["NAME"]?></td>
			<td><?=$val["CARD_EXP_MONTH"]."/".$val["CARD_EXP_YEAR"]; ?></td>
			<td><?= (($val["ACTIVE"] == "Y") ? GetMessage("STPCL_YES") : GetMessage("STPCL_NO")); ?></td>
			<td>
				<a title="<?= GetMessage("STPCL_UPDATE_ALT") ?>" href="<?=$val["URL_TO_DETAIL"]?>"><?= GetMessage("STPCL_UPDATE") ?></a><br />
				<a title="<?= GetMessage("STPCL_DELETE_ALT") ?>" href="javascript:if(confirm('<?echo GetMessage("STPCL_DELETE_PROMT")?>')) window.location='<?=$val["URL_TO_DELETE"]?>'"><?= GetMessage("STPCL_DELETE") ?></a>
			</td>
		</tr>
	<?endforeach;?>
</table>
<?if($arResult["NAV_STRING"] <> ''):?>
	<p><?=$arResult["NAV_STRING"]?></p>
<?endif?>