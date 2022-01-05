<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//echo "<pre>"; print_r($arParams); echo "</pre>";
//echo "<pre>"; print_r($arResult); echo "</pre>";
$colspan = 2;
if ($arResult["CAN_EDIT"] == "Y") $colspan++;
if ($arResult["CAN_DELETE"] == "Y") $colspan++;
?>
<?if ($arResult["MESSAGE"] <> ''):?>
	<?=ShowNote($arResult["MESSAGE"])?>
<?endif?>
<table class="data-table">
<?if($arResult["NO_USER"] == "N"):?>
	<thead>
		<tr>
			<td<?=$colspan > 1 ? " colspan=\"".$colspan."\"" : ""?>><?=GetMessage("IBLOCK_ADD_LIST_TITLE")?></td>
		</tr>
	</thead>
	<tbody>
	<?if (count($arResult["ELEMENTS"]) > 0):?>
		<?foreach ($arResult["ELEMENTS"] as $arElement):?>
		<tr>
			<td class="data-name"><!--a href="detail.php?CODE=<?=$arElement["ID"]?>"--><?=$arElement["NAME"]?><!--/a--></td>
			<td class="data-status"><small><?=is_array($arResult["WF_STATUS"]) ? $arResult["WF_STATUS"][$arElement["WF_STATUS_ID"]] : $arResult["ACTIVE_STATUS"][$arElement["ACTIVE"]]?></small></td>
			<?if ($arResult["CAN_EDIT"] == "Y"):?>
			<td class="data-add"><?if ($arElement["CAN_EDIT"] == "Y"):?><a href="<?=$arParams["EDIT_URL"]?>?edit=Y&amp;CODE=<?=$arElement["ID"]?>"><?=GetMessage("IBLOCK_ADD_LIST_EDIT")?><?else:?>&nbsp;<?endif?></a></td>
			<?endif?>
			<?if ($arResult["CAN_DELETE"] == "Y"):?>
			<td class="data-dell"><?if ($arElement["CAN_DELETE"] == "Y"):?><a href="?delete=Y&amp;CODE=<?=$arElement["ID"]?>&amp;<?=bitrix_sessid_get()?>" onClick="return confirm('<?echo CUtil::JSEscape(str_replace("#ELEMENT_NAME#", $arElement["NAME"], GetMessage("IBLOCK_ADD_LIST_DELETE_CONFIRM")))?>')"><?=GetMessage("IBLOCK_ADD_LIST_DELETE")?></a><?else:?>&nbsp;<?endif?></td>
			<?endif?>
		</tr>
		<?endforeach?>
	<?else:?>
		<tr>
			<td<?=$colspan > 1 ? " colspan=\"".$colspan."\"" : ""?>><?=GetMessage("IBLOCK_ADD_LIST_EMPTY")?></td>
		</tr>
	<?endif?>
	</tbody>
<?endif?>
	<tfoot>
		<tr>
			<td<?=$colspan > 1 ? " colspan=\"".$colspan."\"" : ""?>><?if ($arParams["MAX_USER_ENTRIES"] > 0 && $arResult["ELEMENTS_COUNT"] < $arParams["MAX_USER_ENTRIES"]):?><a href="<?=$arParams["EDIT_URL"]?>?edit=Y"><?=GetMessage("IBLOCK_ADD_LINK_TITLE")?></a><?else:?><?=GetMessage("IBLOCK_LIST_CANT_ADD_MORE")?><?endif?></td>
		</tr>
	</tfoot>
</table>
<?if ($arResult["NAV_STRING"] <> ''):?><?=$arResult["NAV_STRING"]?><?endif?>