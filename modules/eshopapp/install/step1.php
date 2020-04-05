<?if(!check_bitrix_sessid()) return;?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="eshopapp">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<!--<input type="radio" value="demo" name="iblock_to_install" id="eshopapp_demo_iblock" onclick="BX('eshopapp_iblock').style.display='none'" checked> <label for="eshopapp_demo_iblock"><?=GetMessage("MOD_INSTALL_DEMO")?></label>
	<br/>
	<input type="radio" value="choose" name="iblock_to_install"  id="eshopapp_choose_iblock" onclick="BX('eshopapp_iblock').style.display='block'"> <label for="eshopapp_choose_iblock"><?=GetMessage("MOD_INSTALL_CHOOSE_IBLOCK")?></label>-->
	<p style="display:none;color:red" id="error_iblock"><?=GetMessage("APP_IBLOCK_ERROR")?></p>
	<p><?=GetMessage("MOD_CHOOSE_IBLOCK_INFO")?></p>
	<div id="eshopapp_iblock"><?=GetIBlockDropDownListEx(0, 'eshopapp_iblock_type', 'eshopapp_iblock_id');?></div>
	<br/>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>" onclick="if ((BX('eshopapp_iblock_type').value == '0') || !(parseInt(BX('eshopapp_iblock_id').value) > 0)) {BX('error_iblock').style.display = 'block';return false;} ">
<form>
