<?
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$count = 0;
$extCount = 0;
$hndlCount = 0;
$con = \Bitrix\Main\Application::getConnection();

$res = \Bitrix\Sale\Location\LocationTable::getList(array(
	'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
	'select' => array('CNT')
));

if($loc = $res->fetch())
	$count = $loc['CNT'];

$res = $con->query("SELECT COUNT(1) AS CNT FROM b_sale_hdale");

if($loc = $res->fetch())
	$hndlCount = $loc['CNT'];

$res = \Bitrix\Sale\Location\ExternalTable::getList(array(
	'filter' => array('=SERVICE_ID' => \Sale\Handlers\Delivery\Additional\Location::getExternalServiceId()),
	'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
	'select' => array('CNT')
));

if($loc = $res->fetch())
	$extCount = $loc['CNT'];

?>
<tr>
	<td width="40%"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_DLVRS_ADDL_LOC_TAB_COUNT')?>:</td>
	<td width="60%" id="b_sale_hndl_dlv_add_loc_count"><?=$count?></td>
</tr>
<tr>
	<td width="40%"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_DLVRS_ADDL_LOC_TAB_COUNT_H')?>:</td>
	<td width="60%" id="b_sale_hndl_dlv_add_loc_hount"><?=$hndlCount?></td>
</tr>
<tr>
	<td width="40%"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_DLVRS_ADDL_LOC_TAB_COUNT_COMP')?>:</td>
	<td width="60%"  id="b_sale_hndl_dlv_add_loc_ecount"><?=$extCount?></td>
</tr>
<tr>
	<td width="40%">&nbsp;</td>
	<td width="60%"><input type="button" value="<?=\Bitrix\Main\Localization\Loc::getMessage('SALE_DLVRS_ADDL_LOC_TAB_B_COMP')?>" onclick="BX.Sale.Handler.Delivery.Additional.startLocationsCompare();"><br></td>
</tr>
<script>
	BX.message({
		SALE_DLVRS_ADD_LOC_COMP_TITLE: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_LOC_COMP_TITLE")?>',
		SALE_DLVRS_ADD_LOC_COMP_CLOSE: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_LOC_COMP_CLOSE")?>',
		SALE_DLVRS_ADD_LOC_COMP_AJAX_ERROR: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_LOC_COMP_AJAX_ERROR")?>',
		SALE_DLVRS_ADD_LOC_COMP_PREPARE: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_LOC_COMP_PREPARE")?>',
		SALE_DLVRS_ADD_SP_SAVE: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_SP_SAVE")?>',
		SALE_DLVRS_ADD_SP_CHOOSE_TITLE: '<?=\Bitrix\Main\Localization\Loc::getMessage("SALE_DLVRS_ADD_SP_CHOOSE_TITLE")?>'
	});
</script>
