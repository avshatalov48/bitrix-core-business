<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if($this->sort)
{
	if(empty($aOptions["by"]))
		$aOptions["by"] = $this->sort->by_initial;
	if(empty($aOptions["order"]))
		$aOptions["order"] = $this->sort->order_initial;
}
if(intval($aOptions["page_size"]) <= 0)
{
	$aOptions["page_size"] = 20;
}

$obJSPopup = new CJSPopup(GetMessage("admin_lib_sett_title"));
$obJSPopup->ShowTitlebar();
$obJSPopup->StartContent();
echo '</form>';
?>
<div class="settings-form">
<form name="list_settings">
<h2><?=GetMessage("admin_lib_sett_cols")?></h2>
<table cellspacing="0" width="100%">
	<tr>
		<td colspan="2"><?=GetMessage("admin_lib_sett_all")?></td>
		<td colspan="2"><?=GetMessage("admin_lib_sett_sel")?></td>
	</tr>
	<tr>
		<td width="0">
			<select class="select" name="all_columns" id="list_settings_all_columns" size="10" multiple onchange="document.list_settings.add.disabled=(this.selectedIndex == -1);">
<?
$bNeedSort = false;
foreach($aAllCols as $header)
{
	echo '<option value="'.$header["id"].'">'.($header["name"]<>""? $header["name"]:$header["content"]).'</option>';
	if($header["sort"] <> "")
	{
		$bNeedSort = true;
	}
}
?>
			</select>
		</td>
		<td align="center" width="50%"><input type="button" name="add" value="&nbsp; &gt; &nbsp;" title="<?=GetMessage("admin_lib_sett_sel_title")?>" disabled onclick="BX.selectUtils.addSelectedOptions(document.list_settings.all_columns, 'list_settings_selected_columns');"></td>
		<td width="0">
			<select class="select" name="selected_columns" id="list_settings_selected_columns" size="10" multiple onchange="var frm=document.list_settings; frm.up.disabled=frm.down.disabled=frm.del.disabled=(this.selectedIndex == -1);">
<?
$bEmptyCols = empty($aCols);
foreach($this->aHeaders as $header)
{
	if(($bEmptyCols && $header["default"]==true) || in_array($header["id"], $aCols))
	{
		echo '<option value="'.$header["id"].'">'.($header["name"]<>""? $header["name"]:$header["content"]).'</option>';
	}
}
?>
			</select>
		</td>
		<td align="center" width="50%">
			<input type="button" name="up" class="button" value="<?=GetMessage("admin_lib_sett_up")?>" title="<?=GetMessage("admin_lib_sett_up_title")?>" disabled="disabled" onclick="BX.selectUtils.moveOptionsUp(document.list_settings.selected_columns);" /><br />
			<input type="button" name="down" class="button" value="<?=GetMessage("admin_lib_sett_down")?>" title="<?=GetMessage("admin_lib_sett_down_title")?>" disabled="disabled" onclick="BX.selectUtils.moveOptionsDown(document.list_settings.selected_columns);" /><br />
			<input type="button" name="del" class="button" value="<?=GetMessage("admin_lib_sett_del")?>" title="<?=GetMessage("admin_lib_sett_del_title")?>" disabled="disbled" onclick="BX.selectUtils.deleteSelectedOptions('list_settings_selected_columns'); document.list_settings.selected_columns.onchange();" /><br />
		</td>
	</tr>
</table>
<h2><?=GetMessage("admin_lib_sett_def_title")?></h2>
<table cellspacing="0" width="100%">
<?
if($this->sort && $bNeedSort)
{
?>
	<tr>
		<td align="right"><?=GetMessage("admin_lib_sett_sort")?></td>
		<td>
			<select name="order_field">
<?
	$by = strtoupper($aOptions["by"]);
	$order = strtoupper($aOptions["order"]);
	foreach($aAllCols as $header)
	{
		if($header["sort"] <> "")
		{
			echo '<option value="'.$header["sort"].'"'.($by == strtoupper($header["sort"])? ' selected':'').'>'.($header["name"]<>""? $header["name"]:$header["content"]).'</option>';
		}
	}
?>
			</select>
			<select name="order_direction">
				<option value="desc"<?=($order == "DESC"? ' selected':'')?>><?=GetMessage("admin_lib_sett_desc")?></option>
				<option value="asc"<?=($order == "ASC"? ' selected':'')?>><?=GetMessage("admin_lib_sett_asc")?></option>
			</select>
		</td>
	</tr>
<?
} // if($this->sort && $bNeedSort)
?>
	<tr>
		<td align="right"><?=GetMessage("admin_lib_sett_rec")?></td>
		<td>
			<select name="nav_page_size">
<?
$aSizes = array(10, 20, 50, 100, 200, 500);
foreach($aSizes as $size)
{
	echo '<option value="'.$size.'"'.($aOptions["page_size"] == $size? ' selected':'').'>'.$size.'</option>';
}
?>
			</select>
		</td>
	</tr>
</table>
<?
if($USER->CanDoOperation('edit_other_settings'))
{
?>
<h2><?=GetMessage("admin_lib_sett_common")?></h2>
<table cellspacing="0" width="100%">
	<tr>
		<td><input type="checkbox" name="set_default" id="set_default" value="Y"></td>
		<td><label for="set_default"><?=GetMessage("admin_lib_sett_common_set")?></label></td>
		<td><a class="delete-icon" title="<?=GetMessage("admin_lib_sett_common_del")?>" href="javascript:if(confirm('<?=CUtil::JSEscape(GetMessage("admin_lib_sett_common_del_conf"))?>'))<?=$this->table_id?>.DeleteSettings(true)"></a></td>
	</tr>
</table>
<?
} //if($USER->CanDoOperation('edit_other_settings'))
?>
</form>
</div>
<script type="text/javascript">
BX.adminFormTools.modifyFormElements('list_settings')
</script>
<?
$obJSPopup->StartButtons();
?>
<input class="adm-btn-save" type="button" value="<?=GetMessage("admin_lib_sett_save")?>" onclick="<?=$this->table_id?>.SaveSettings(this)" title="<?=GetMessage("admin_lib_sett_save_title")?>" />
<input type="button" value="<?=GetMessage("admin_lib_sett_cancel")?>" onclick="BX.WindowManager.Get().Close()" title="<?=GetMessage("admin_lib_sett_cancel_title")?>" />
<input type="button" value="<?=GetMessage("admin_lib_sett_reset")?>" onclick="if(confirm('<?=CUtil::JSEscape(GetMessage("admin_lib_sett_reset_ask"))?>'))<?=$this->table_id?>.DeleteSettings()" title="<?=GetMessage("admin_lib_sett_reset_title")?>" />
<?
$obJSPopup->EndButtons();
?>