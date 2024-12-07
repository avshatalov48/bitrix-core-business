<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

?>
<tr>
	<td width="40%"><?=Loc::getMessage('SALE_LOCATION_MAP_LOC_COUNT')?>:</td>
	<td width="60%" id="b_sale_hndl_dlv_add_loc_count"><?=$arResult['BITRIX_LOCATIONS_COUNT']?></td>
</tr>
<tr>
	<td width="40%"><?=Loc::getMessage('SALE_LOCATION_MAP_LOC_MAPPED')?>:</td>
	<td width="60%"  id="b_sale_hndl_dlv_add_loc_ecount"><?=$arResult['SERVICE_LOCATIONS_COUNT']?></td>
</tr>
<?if(intval($arResult['SERVICE_LOCATIONS_COUNT']) > 0):?>
	<tr>
		<td width="40%"><?=Loc::getMessage('SALE_LOCATION_MAP_NEW')?></td>
		<td>
			<input type="button" value="<?=Loc::getMessage('SALE_LOCATION_MAP_NEW_B')?>" onclick="BX.Sale.Location.Map.startLocationsCompare(false);">
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SALE_LOCATION_MAP_ALL')?></td>
		<td>
			<input type="button" value="<?=Loc::getMessage('SALE_LOCATION_MAP_ALL_B')?>" onclick="BX.Sale.Location.Map.startLocationsCompare(true);">
		</td>
	</tr>
<?else:?>
	<tr>
		<td width="40%">&nbsp;</td>
		<td>
			<input type="button" value="<?=Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_MAP')?>" onclick="BX.Sale.Location.Map.startLocationsCompare(false);">
		</td>
	</tr>
<?endif;?>

<script>

	BX.message({
		"SALE_LOCATION_MAP_CLOSE": "<?=Loc::getMessage("SALE_LOCATION_MAP_CLOSE")?>",
		"SALE_LOCATION_MAP_LOC_MAPPING": "<?=Loc::getMessage("SALE_LOCATION_MAP_LOC_MAPPING")?>",
		"SALE_LOCATION_MAP_CANCEL": "<?=Loc::getMessage("SALE_LOCATION_MAP_CANCEL")?>",
		"SALE_LOCATION_MAP_PREPARING": "<?=Loc::getMessage("SALE_LOCATION_MAP_PREPARING")?>",
		"SALE_LOCATION_MAP_LOC_MAPPED": "<?=Loc::getMessage("SALE_LOCATION_MAP_LOC_MAPPED")?>"
	});

	BX.ready(function() {
		BX.Sale.Location.Map.ajaxUrl = "<?=$componentPath.'/ajax.php'?>";
		BX.Sale.Location.Map.serviceLocationClass = "<?=CUtil::JSEscape($arParams['EXTERNAL_LOCATION_CLASS'])?>";
	});
</script>
