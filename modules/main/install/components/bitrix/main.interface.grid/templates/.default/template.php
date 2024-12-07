<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\Web\Json;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

//color schemes
if($arParams["USE_THEMES"])
	$arThemes = CGridOptions::GetThemes($this->GetFolder());
else
	$arThemes = array();
?>

<?if(!empty($arParams["FILTER"])):?>

<?$APPLICATION->IncludeComponent(
	"bitrix:main.interface.filter",
	$arParams["FILTER_TEMPLATE_NAME"],
	array(
		"GRID_ID"=>$arParams["~GRID_ID"],
		"FILTER"=>$arParams["~FILTER"],
		"FILTER_ROWS"=>$arResult["FILTER_ROWS"],
		"FILTER_FIELDS"=>$arResult["FILTER"],
		"OPTIONS"=>$arResult["OPTIONS"],
	),
	$component,
	array("HIDE_ICONS"=>true)
);?>

<?endif;?>

<?if($arParams["SHOW_FORM_TAG"]):?>
<form name="form_<?=$arParams["GRID_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST">

<?=bitrix_sessid_post();?>
<?endif?>
<table cellspacing="0" class="bx-interface-grid<?if(!empty($arResult["OPTIONS"]["theme"])) echo " bx-interface-grid-theme-".$arResult["OPTIONS"]["theme"]?>" id="<?=$arParams["GRID_ID"]?>">
	<tr class="bx-grid-gutter" oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu">
<?if($arResult["ALLOW_EDIT"]):?>
		<td><div class="empty"></div></td>
<?endif?>
		<td><div class="empty"></div></td>
<?foreach($arResult["HEADERS"] as $header):?>
		<td<?=(!empty($header["sort_state"])? ' class="bx-sorted"':'')?>><div class="empty"></div></td>
<?endforeach?>
	</tr>
	<tr class="bx-grid-head" oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu"<?if($USER->IsAuthorized()):?> ondblclick="bxGrid_<?=$arParams["GRID_ID"]?>.EditCurrentView()"<?endif?>>
<?if($arResult["ALLOW_EDIT"]):?>
		<td class="bx-checkbox-col" width="1%"><input type="checkbox" name="" id="<?=$arParams["GRID_ID"]?>_check_all" value="" title="<?echo GetMessage("interface_grid_check_all")?>" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.SelectAllRows(this);"></td>
<?endif?>
		<td class="bx-actions-col" width="1%"><a href="javascript:void(0);"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.menu.ShowMenu(this, bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu, false, false, bxGrid_<?=$arParams["GRID_ID"]?>.SaveColumns);return false;"
			title="<?echo GetMessage("interface_grid_settings")?>" class="bx-action"><div class="empty"></div></a></td>
<?
$colspan = count($arResult["HEADERS"])+($arResult["ALLOW_EDIT"]? 2:1);
foreach($arResult["HEADERS"] as $id=>$header):
?>
<?
if(!empty($header["sort"])):
	$order_title = GetMessage("interface_grid_sort").' '.$header["name"];
	$order_class = "";
	if($header["sort_state"] == "desc")
	{
		$order_class = " bx-sort-down";
		$order_title .= " ".GetMessage("interface_grid_sort_down");
	}
	elseif($header["sort_state"] == "asc")
	{
		$order_class = " bx-sort-up";
		$order_title .= " ".GetMessage("interface_grid_sort_up");
	}
?>
		<td class="bx-sortable<?=(!empty($header["sort_state"])? ' bx-sorted':'')?>"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.Sort('<?=CUtil::addslashes($header["sort_url"])?>', '<?=$header["sort"]?>', '<?=$header["sort_state"]?>', '<?=$header["order"]?>', arguments);"
			oncontextmenu="return [
				{
					'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_sort_asc"))?>',
					'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.Sort(\'<?=CUtil::addslashes($header["sort_url"])?>\', \'desc\')',
					'ICONCLASS':'grid-sort-asc'
				},
				{
					'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_sort_desc"))?>',
					'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.Sort(\'<?=CUtil::addslashes($header["sort_url"])?>\', \'asc\')',
					'ICONCLASS':'grid-sort-desc'
				},
				{
					'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_hide_col"))?>',
					'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.HideColumn(\'<?=CUtil::JSEscape($id)?>\')',
					'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>
				}
			]"
			title="<?=$order_title?>"
		>
			<table cellspacing="0" class="bx-grid-sorting">
				<tr>
					<td><?=$header["name"]?></td>
					<td class="bx-sort-sign<?=$order_class?>"><div class="empty"></div></td>
				</tr>
			</table>
		</td>
<?else:?>
		<td oncontextmenu="return [
			{
				'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_hide_col"))?>',
				'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.HideColumn(\'<?=CUtil::JSEscape($id)?>\')',
				'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>
			}
		]">
			<?=$header["name"]?>
		</td>
<?endif?>
<?endforeach?>
	</tr>

<?
$jsActions = array();
if(!empty($arParams["ROWS"])):

foreach($arParams["ROWS"] as $index=>$aRow):

	$jsActions[$index] = array();
	$sDefAction = '';
	$sDefTitle = '';
	if(isset($aRow["actions"]) && is_array($aRow["actions"]))
	{
		$jsActions[$index] = $aRow["actions"];

		//find default action
		foreach($aRow["actions"] as $action)
		{
			if (isset($action["DEFAULT"]) && $action["DEFAULT"] == true)
			{
				$sDefAction = $action["ONCLICK"];
				$sDefTitle = $action["TEXT"];
				break;
			}
		}
	}
?>
	<tr oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.oActions[<?=$index?>]"<?if(!empty($sDefAction)):?> ondblclick="<?=htmlspecialcharsbx($sDefAction)?>" title="<?=GetMessage("interface_grid_dblclick")?><?=$sDefTitle?>"<?endif?>>
<?if($arResult["ALLOW_EDIT"]):?>
	<?
	if($aRow["editable"] !== false):
		$data_id = (!empty($aRow["id"])? $aRow["id"] : $aRow["data"]["ID"]);
	?>
		<td class="bx-checkbox-col"><input type="checkbox" name="ID[]" id="ID_<?=$data_id?>" value="<?=$data_id?>" title="<?echo GetMessage("interface_grid_check")?>"></td>
	<?else:?>
		<td class="bx-checkbox-col">&nbsp;</td>
	<?endif?>
<?endif?>
	<?if(isset($aRow["actions"]) && is_array($aRow["actions"]) && !empty($aRow["actions"])):?>
		<td class="bx-actions-col"><a href="javascript:void(0);"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ShowActionMenu(this, <?=$index?>);"
			title="<?echo GetMessage("interface_grid_act")?>" class="bx-action"><div class="empty"></div></a></td>
	<?else:?>
		<td class="bx-actions-col">&nbsp;</td>
	<?endif?>
<?foreach($arResult["HEADERS"] as $id=>$header):?>
		<td<?=(!empty($header["sort_state"])? ' class="bx-sorted"':'')?><?
if(!empty($header["align"]))
	echo ' align="'.$header["align"].'"';
elseif(isset($header["type"]) && $header["type"] == "checkbox")
	echo ' align="center"';
		?>><?
	if(isset($header["type"]) && $header["type"] == "checkbox"
		&& !empty($aRow["data"][$id])
		&& ($aRow["data"][$id] == 'Y' || $aRow["data"][$id] == 'N')
	)
	{
		echo ($aRow["data"][$id] == 'Y'? GetMessage("interface_grid_yes"):GetMessage("interface_grid_no"));
	}
	else
	{
		$val = ($aRow["columns"][$id] ?? $aRow["data"][$id]);
		echo (!empty($val)? $val:'&nbsp;');
	}
		?></td>
<?endforeach?>
	</tr>
<?endforeach; // $arParams["ROWS"]?>
<?
else: //!empty($arParams["ROWS"])
?>
	<tr><td colspan="<?=$colspan?>"><?echo GetMessage("interface_grid_no_data")?></td></tr>
<?endif?>

<?if (
	$arResult["ALLOW_EDIT"]
	|| (isset($arParams["FOOTER"]) && is_array($arParams["FOOTER"]) && !empty($arParams["FOOTER"]))
	|| !empty($arResult["NAV_STRING"])
):?>
	<tr class="bx-grid-footer">
		<td colspan="<?=$colspan?>">
			<table cellpadding="0" cellspacing="0" border="0" class="bx-grid-footer">
				<tr>
			<?if($arResult["ALLOW_EDIT"]):?>
					<td><?echo GetMessage("interface_grid_checked")?> <span id="<?=$arParams["GRID_ID"]?>_selected_span">0</span></td>
			<?endif?>
			<?foreach($arParams["FOOTER"] as $footer):?>
					<td><?=$footer["title"]?>: <span><?=$footer["value"]?></span></td>
			<?endforeach?>
					<td class="bx-right"><?=(!empty($arResult["NAV_STRING"]) ? $arResult["NAV_STRING"] : '&nbsp;')?></td>
				</tr>
			</table>
		</td>
	</tr>
<?endif?>
</table>

<?if($arResult["ALLOW_EDIT"]):?>
<div class="bx-grid-multiaction">
<input type="hidden" name="action_button_<?=$arParams["GRID_ID"]?>" value="">
<table cellpadding="0" cellspacing="0" border="0" class="bx-grid-multiaction">
	<tr class="bx-top"><td class="bx-left"><div class="empty"></div></td><td><div class="empty"></div></td><td class="bx-right"><div class="empty"></div></td></tr>
	<tr>
		<td class="bx-left"><div class="empty"></div></td>
		<td class="bx-content">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
		<td style="display:none" id="bx_grid_<?=$arParams["GRID_ID"]?>_action_buttons">
			<input type="submit" name="save" value="<?echo GetMessage("interface_grid_save")?>" title="<?echo GetMessage("interface_grid_save_title")?>">
			<input type="button" name="" value="<?echo GetMessage("interface_grid_cancel")?>" title="<?echo GetMessage("interface_grid_cancel_title")?>" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ActionCancel();">
		</td>

<?
$bNeedSep = false;
if($arParams["ACTION_ALL_ROWS"]):
	$bNeedSep = true;
?>
		<td>
			<input title="<?echo GetMessage("interface_grid_for_all")?>" type="checkbox" name="action_all_rows_<?=$arParams["GRID_ID"]?>" id="actallrows_<?=$arParams["GRID_ID"]?>" value="Y" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ForAllClick(this);"<?if(empty($arParams["ROWS"])) echo ' disabled'?>>
		</td>
		<td><label title="<?echo GetMessage("interface_grid_for_all")?>" for="actallrows_<?=$arParams["GRID_ID"]?>"><?echo GetMessage("interface_grid_for_all_box")?></label></td>
<?endif?>
<?if($arResult["ALLOW_INLINE_EDIT"]):?>
	<?if($bNeedSep):?>
		<td><div class="bx-separator"></div></td>
	<?endif;?>
		<td><a href="javascript:void(0);" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ActionEdit(this);" title="<?echo GetMessage("interface_grid_edit_selected")?>" class="context-button icon action-edit-button-dis" id="edit_button_<?=$arParams["GRID_ID"]?>"></a></td>
<?
	$bNeedSep = true;
endif;
?>
<?if($arParams["ACTIONS"]["delete"] == true):?>
	<?if($bNeedSep && !$arResult["ALLOW_INLINE_EDIT"]):?>
		<td><div class="bx-separator"></div></td>
	<?endif?>
		<td><a href="javascript:void(0);" onclick="var el; if(bxGrid_<?=$arParams["GRID_ID"]?>.IsActionEnabled() && confirm(((el=document.getElementById('actallrows_<?=$arParams["GRID_ID"]?>')) && el.checked? '<?=CUtil::JSEScape(GetMessage("interface_grid_delete"))?>':'<?=CUtil::JSEScape(GetMessage("interface_grid_delete_checked"))?>'))) bxGrid_<?=$arParams["GRID_ID"]?>.ActionDelete();" title="<?echo GetMessage("interface_grid_delete_title")?>" class="context-button icon action-delete-button-dis" id="delete_button_<?=$arParams["GRID_ID"]?>"></a></td>
<?
	$bNeedSep = true;
endif;
?>
<?
$bShowApply = false;
if(isset($arParams["ACTIONS"]["list"]) && is_array($arParams["ACTIONS"]["list"]) && !empty($arParams["ACTIONS"]["list"])):
	$bShowApply = true;
?>
	<?
	if($bNeedSep):
		$bNeedSep = false;
	?>
		<td><div class="bx-separator"></div></td>
	<?endif?>
		<td>
			<select name="" onchange="this.form.elements['action_button_<?=$arParams["GRID_ID"]?>'].value = this.value;">
				<option value=""><?=GetMessage("interface_grid_actions_list")?></option>
	<?foreach($arParams["ACTIONS"]["list"] as $key => $val):?>
				<option value="<?=htmlspecialcharsbx($key)?>"><?=htmlspecialcharsbx($val)?></option>
	<?endforeach?>
			</select>
		</td>
<?endif?>
<?
if(!empty($arParams["~ACTIONS"]["custom_html"])):
	$bShowApply = true;
?>
	<?if($bNeedSep):?>
		<td><div class="bx-separator"></div></td>
	<?endif?>
		<td style="padding-left:2px;"><?=$arParams["~ACTIONS"]["custom_html"]?></td>
<?endif?>
<?if($bShowApply):?>
		<td style="padding-left:2px;"><input type="submit" name="apply" value="<?echo GetMessage("interface_grid_apply")?>" disabled></td>
<?endif?>
				</tr>
			</table>
		</td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr class="bx-bottom"><td class="bx-left"><div class="empty"></div></td><td><div class="empty"></div></td><td class="bx-right"><div class="empty"></div></td></tr>
</table>
</div>
<?endif?>
<?if($arParams["SHOW_FORM_TAG"]):?>
</form>
<?endif?>
<?if($arResult["EDIT_DATE"]):?>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	array(
		"SILENT"=>"Y",
	),
	$component,
	array("HIDE_ICONS"=>true)
);?>
<?endif;?>

<?if($USER->IsAuthorized()):?>
<div style="display:none">

<div id="view_settings_<?=$arParams["GRID_ID"]?>">
<table width="100%">
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_view_sect")?></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?echo GetMessage("interface_grid_view_name")?></td>
		<td><input type="text" name="view_name" value="" size="40" maxlength="255"></td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_view_cols")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table>
				<tr>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("interface_grid_view_av_cols")?></div>
						<select style="min-width:150px;" name="view_all_cols" multiple size="12" ondblclick="this.form.add_btn.onclick()" onchange="this.form.add_btn.disabled = (this.selectedIndex == -1)">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="add_btn" value="&gt;" title="<?echo GetMessage("interface_grid_view_add_col")?>" style="width:30px;" onclick="jsSelectUtils.addSelectedOptions(this.form.view_all_cols, this.form.view_cols, false); jsSelectUtils.deleteSelectedOptions(this.form.view_all_cols); "></div>
						<div style="margin-bottom:5px"><input type="button" name="del_btn" value="&lt;" title="<?echo GetMessage("interface_grid_view_del_col")?>" style="width:30px;" onclick="jsSelectUtils.addSelectedOptions(this.form.view_cols, this.form.view_all_cols, false, true); jsSelectUtils.deleteSelectedOptions(this.form.view_cols);"></div>
					</td>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("interface_grid_view_sel_col")?></div>
						<select style="min-width:150px;" name="view_cols" multiple size="12" ondblclick="this.form.del_btn.onclick()" onchange="this.form.del_btn.disabled = this.form.up_btn.disabled = this.form.down_btn.disabled = this.form.rename_btn.disabled = (this.selectedIndex == -1)">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="up_btn" value="<?echo GetMessage("interface_grid_view_up")?>" title="<?echo GetMessage("interface_grid_view_up_title")?>" class="bx-grid-btn" style="width:100px;" onclick="jsSelectUtils.moveOptionsUp(this.form.view_cols)"></div>
						<div style="margin-bottom:5px"><input type="button" name="down_btn" value="<?echo GetMessage("interface_grid_view_down")?>" title="<?echo GetMessage("interface_grid_view_down_title")?>" class="bx-grid-btn" style="width:100px;" onclick="jsSelectUtils.moveOptionsDown(this.form.view_cols)"></div>
						<div style="margin-bottom:5px"><input type="button" name="rename_btn" value="<?echo GetMessage("interface_grid_name_btn")?>" title="<?echo GetMessage("interface_grid_name_btn_title")?>" class="bx-grid-btn" style="width:100px;" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.RenameColumn()"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_view_sort_sect")?></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("interface_grid_view_sort_name")?></td>
		<td><select name="view_sort_by">
			<option value=""><?=GetMessage("interface_grid_default")?></option>
<?
foreach($arParams["HEADERS"] as $header):
	if(!empty($header["sort"])):
?>
			<option value="<?=$header["sort"]?>"><?=$header["name"]?></option>
<?
	endif;
endforeach;
?>
		</select></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("interface_grid_view_sort_order")?></td>
		<td><select name="view_sort_order">
			<option value=""><?=GetMessage("interface_grid_default")?></option>
			<option value="asc"><?echo GetMessage("interface_grid_view_sort_asc")?></option>
			<option value="desc"><?echo GetMessage("interface_grid_view_sort_desc")?></option>
		</select></td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_view_nav_sect")?></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?echo GetMessage("interface_grid_view_nav_name")?></td>
		<td><select name="view_page_size">
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
		</select></td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_saved_filter")?></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("interface_grid_saved_filter_apply")?></td>
		<td><select name="view_filters">
		</select></td>
	</tr>
<?if($arResult["IS_ADMIN"]):?>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_common")?></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="set_default_settings" id="set_default_settings_<?=$arParams["GRID_ID"]?>" onclick="document['settings_' + '<?=$arParams["GRID_ID"]?>'].delete_users_settings.disabled = !this.checked;"><label for="set_default_settings_<?=$arParams["GRID_ID"]?>"><?echo GetMessage("interface_grid_common_default")?></label></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="delete_users_settings" id="delete_users_settings_<?=$arParams["GRID_ID"]?>" disabled><label for="delete_users_settings_<?=$arParams["GRID_ID"]?>"><?echo GetMessage("interface_grid_common_default_apply")?></label></td>
	</tr>
<?endif;?>
</table>
</div>

<div id="rename_column_<?=$arParams["GRID_ID"]?>">
<table width="100%">
	<tr>
		<td align="right" width="50%"><?echo GetMessage("interface_grid_name_def")?></td>
		<td><input type="text" name="col_name_def" value="" size="35" disabled="disabled"></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?echo GetMessage("interface_grid_name_new")?></td>
		<td><input type="text" name="col_name" value="" size="35"></td>
	</tr>
</table>
</div>

<div id="views_list_<?=$arParams["GRID_ID"]?>">
<div style="float:left; width:80%">
<select name="views_list" size="17" style="width:100%; height:250px;" ondblclick="this.form.views_edit.onclick()">
<?foreach($arResult["OPTIONS"]["views"] as $view_id=>$view):?>
	<option value="<?=htmlspecialcharsbx($view_id)?>"<?if($view_id == $arResult["OPTIONS"]["current_view"]):?> selected<?endif?>><?=htmlspecialcharsbx((!empty($view["name"])? $view["name"]:GetMessage("interface_grid_view_noname")))?></option>
<?endforeach?>
</select>
</div>
<div style="width:20%;float:left;">
	<div style=margin-left:5px;>
	<div style="margin-bottom:5px"><input type="button" name="views_add" value="<?echo GetMessage("interface_grid_view_add")?>" title="<?echo GetMessage("interface_grid_view_add_title")?>" style="width:100%;" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.AddView()"></div>
	<div style="margin-bottom:5px"><input type="button" name="views_edit" value="<?echo GetMessage("interface_grid_view_edit")?>" title="<?echo GetMessage("interface_grid_view_edit_title")?>" style="width:100%;" onclick="if(this.form.views_list.value) bxGrid_<?=$arParams["GRID_ID"]?>.EditView(this.form.views_list.value)"></div>
	<div style="margin-bottom:5px"><input type="button" name="views_delete" value="<?echo GetMessage("interface_grid_view_del")?>" title="<?echo GetMessage("interface_grid_view_del_title")?>" style="width:100%;" onclick="if(this.form.views_list.value) bxGrid_<?=$arParams["GRID_ID"]?>.DeleteView(this.form.views_list.value)"></div>
	</div>
</div>
</div>

<?if(!empty($arParams["FILTER"])):?>
<div id="filter_settings_<?=$arParams["GRID_ID"]?>">
<table width="100%">
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_filter_name")?></td>
	</tr>
	<tr>
		<td align="right" width="40%"><?echo GetMessage("interface_grid_filter_name1")?></td>
		<td><input type="text" name="filter_name" value="" size="40" maxlength="255"></td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_grid_filter_fields")?></td>
	</tr>
<?
foreach($arParams["FILTER"] as $field):
	if(isset($field["enable_settings"]) && $field["enable_settings"] === false)
		continue;
?>
	<tr>
		<td align="right"><?=$field["name"]?>:</td>
		<td>
<?
	//default attributes
	if(!isset($field["params"]) || !is_array($field["params"]))
		$field["params"] = array();
	if(empty($field["type"]) || $field["type"] == 'text')
	{
		if(empty($field["params"]["size"]))
			$field["params"]["size"] = "30";
	}
	elseif($field["type"] == 'date')
	{
		if(empty($field["params"]["size"]))
			$field["params"]["size"] = "10";
	}
	elseif($field["type"] == 'number')
	{
		if(empty($field["params"]["size"]))
			$field["params"]["size"] = "8";
	}

	$params = '';
	foreach($field["params"] as $p=>$v)
		$params .= ' '.$p.'="'.$v.'"';

	switch($field["type"]):
		case 'custom':
			echo $field["value"];
			break;
		case 'checkbox':
?>
<input type="hidden" name="<?=$field["id"]?>" value="N">
<input type="checkbox" name="<?=$field["id"]?>" value="Y"<?=$params?>>
<?
			break;
		case 'list':
?>
<select name="<?=$field["id"].(isset($field["params"]["multiple"])? '[]':'')?>"<?=$params?>>
<?
			if(is_array($field["items"])):
				if(isset($field["params"]["multiple"])):
?>
	<option value=""><?echo GetMessage("interface_grid_no_no_no_1")?></option>
<?
				endif;
				foreach($field["items"] as $k=>$v):
?>
	<option value="<?=htmlspecialcharsbx($k)?>"><?=htmlspecialcharsbx($v)?></option>
<?
				endforeach;
?>
</select>
<?
			endif;
			break;
		case 'date':
			$APPLICATION->IncludeComponent(
				"bitrix:main.calendar.interval",
				"",
				array(
					"FORM_NAME" => "flt_settings_".$arParams["GRID_ID"],
					"SELECT_NAME" => $field["id"]."_datesel",
					"SELECT_VALUE" => "",
					"INPUT_NAME_DAYS" => $field["id"]."_days",
					"INPUT_VALUE_DAYS" => "",
					"INPUT_NAME_FROM" => $field["id"]."_from",
					"INPUT_VALUE_FROM" => "",
					"INPUT_NAME_TO" => $field["id"]."_to",
					"INPUT_VALUE_TO" => "",
					"INPUT_PARAMS" => $params,
				),
				$component,
				array("HIDE_ICONS"=>true)
			);
			break;
		case 'quick':
?>
<input type="text" name="<?=$field["id"]?>" value=""<?=$params?>>
<?
			if(is_array($field["items"])):
?>
<select name="<?=$field["id"]?>_list">
<?foreach($field["items"] as $key=>$item):?>
	<option value="<?=htmlspecialcharsbx($key)?>"><?=htmlspecialcharsbx($item)?></option>
<?endforeach?>
</select>
<?
			endif;
			break;
		case 'number':
?>
<input type="text" name="<?=$field["id"]?>_from" value=""<?=$params?>> ...
<input type="text" name="<?=$field["id"]?>_to" value=""<?=$params?>>
<?
			break;
		default:
?>
<input type="text" name="<?=$field["id"]?>" value=""<?=$params?>>
<?
			break;
	endswitch;
?>
		</td>
	</tr>
<?endforeach?>
</table>
</div>

<div id="filters_list_<?=$arParams["GRID_ID"]?>">
<div style="float:left; width:80%">
<select name="filters_list" size="17" style="width:100%; height:250px;" ondblclick="if(this.value) this.form.filters_edit.onclick()">
<?foreach($arResult["OPTIONS"]["filters"] as $filter_id=>$filter):?>
	<option value="<?=htmlspecialcharsbx($filter_id)?>"><?=htmlspecialcharsbx((!empty($filter["name"])? $filter["name"]:GetMessage("interface_grid_view_noname")))?></option>
<?endforeach?>
</select>
</div>
<div style="width:20%;float:left;">
	<div style=margin-left:5px;>
	<div style="margin-bottom:5px"><input type="button" name="filters_add" value="<?echo GetMessage("interface_grid_view_add")?>" title="<?echo GetMessage("interface_grid_filter_add_title")?>" style="width:100%;" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.AddFilter()"></div>
	<div style="margin-bottom:5px"><input type="button" name="filters_edit" value="<?echo GetMessage("interface_grid_view_edit")?>" title="<?echo GetMessage("interface_grid_filter_edit_title")?>" style="width:100%;" onclick="if(this.form.filters_list.value) bxGrid_<?=$arParams["GRID_ID"]?>.EditFilter(this.form.filters_list.value)"></div>
	<div style="margin-bottom:5px"><input type="button" name="filters_delete" value="<?echo GetMessage("interface_grid_view_del")?>" title="<?echo GetMessage("interface_grid_filter_del_title")?>" style="width:100%;" onclick="if(this.form.filters_list.value) bxGrid_<?=$arParams["GRID_ID"]?>.DeleteFilter(this.form.filters_list.value)"></div>
	</div>
</div>
</div>
<?
endif //!empty($arParams["FILTER"])
?>

</div>
<?
endif //$GLOBALS['USER']->IsAuthorized()
?>

<?
$variables = array(
	"mess"=>array(
		"calend_title"=>GetMessage("interface_grid_date"),
		"for_all_confirm"=>GetMessage("interface_grid_del_confirm"),
		"settingsTitle"=>GetMessage("interface_grid_settings_title"),
		"settingsSave"=>GetMessage("interface_grid_settings_save"),
		"viewsTitle"=>GetMessage("interface_grid_views_title"),
		"viewsApply"=>GetMessage("interface_grid_views_apply"),
		"viewsApplyTitle"=>GetMessage("interface_grid_views_apply_title"),
		"viewsNoName"=>GetMessage("interface_grid_view_noname"),
		"viewsNewView"=>GetMessage("interface_grid_views_new"),
		"viewsDelete"=>GetMessage("interface_grid_del_view"),
		"viewsFilter"=>GetMessage("interface_grid_filter_sel"),
		"filtersTitle"=>GetMessage("interface_grid_filter_saved"),
		"filtersApply"=>GetMessage("interface_grid_apply"),
		"filtersApplyTitle"=>GetMessage("interface_grid_filter_apply_title"),
		"filtersNew"=>GetMessage("interface_grid_filter_new"),
		"filtersDelete"=>GetMessage("interface_grid_filter_del"),
		"filterSettingsTitle"=>GetMessage("interface_grid_filter_title"),
		"filterHide"=>GetMessage("interface_grid_to_head_1"),
		"filterShow"=>GetMessage("interface_grid_from_head_1"),
		"filterApplyTitle"=>GetMessage("interface_grid_filter_apply"),
		"renameTitle"=>GetMessage("interface_grid_name_title"),
	),
	"ajax"=>array(
		"AJAX_ID"=> $arParams["AJAX_ID"] ?? '',
		"AJAX_OPTION_SHADOW" => isset($arParams["AJAX_OPTION_SHADOW"]) && $arParams["AJAX_OPTION_SHADOW"] === "Y",
	),
	"settingWndSize"=>CUtil::GetPopupSize("InterfaceGridSettingWnd"),
	"viewsWndSize"=>CUtil::GetPopupSize("InterfaceGridViewsWnd", array('height' => 350, 'width' => 500)),
	"filtersWndSize"=>CUtil::GetPopupSize("InterfaceGridFiltersWnd", array('height' => 350, 'width' => 500)),
	"filterSettingWndSize"=>CUtil::GetPopupSize("InterfaceGridFilterSettingWnd"),
	"renameWndSize"=>CUtil::GetPopupSize("InterfaceGridRenameWnd", array('height' => 150, 'width' => 500)),
	"calendar_image"=>$this->GetFolder()."/images/calendar.gif",
	"server_time"=>(time()+date("Z")+CTimeZone::GetOffset()),
	"component_path"=>$component->GetRelativePath(),
	"template_path"=>$this->GetFolder(),
	"sessid"=>bitrix_sessid(),
	"current_url"=>$arResult["CURRENT_URL"],
	"user_authorized"=>$USER->IsAuthorized(),
);
?>

<script>
var settingsDialog<?=$arParams["GRID_ID"]?>;
var viewsDialog<?=$arParams["GRID_ID"]?>;
var filtersDialog<?=$arParams["GRID_ID"]?>;
var filterSettingsDialog<?=$arParams["GRID_ID"]?>;

jsDD.Enable();

if(!window['bxGrid_<?=$arParams["GRID_ID"]?>'])
	bxGrid_<?=$arParams["GRID_ID"]?> = new BxInterfaceGrid('<?=$arParams["GRID_ID"]?>');

bxGrid_<?=$arParams["GRID_ID"]?>.oActions = <?= Json::encode($jsActions) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oColsMeta = <?= Json::encode($arResult["COLS_EDIT_META"]) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oEditData = <?= Json::encode($arResult["DATA_FOR_EDIT"]) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oColsNames = <?= Json::encode(htmlspecialcharsback($arResult["COLS_NAMES"])) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oOptions = <?= Json::encode($arResult["OPTIONS"]) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.vars = <?= Json::encode($variables) ?>;
bxGrid_<?=$arParams["GRID_ID"]?>.menu = new PopupMenu('bxMenu_<?=$arParams["GRID_ID"]?>', 1010);
bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu = [
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_views_setup"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_views_setup_title"))?>', 'DEFAULT':true, 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.EditCurrentView()', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'grid-settings'},
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns_title"))?>', 'MENU':[
<?
foreach($arParams["HEADERS"] as $header):
?>
		{'TEXT': '<?=CUtil::JSEscape($header["name"])?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns_showhide"))?>',<?if(array_key_exists($header["id"], $arResult["HEADERS"])):?>'ICONCLASS':'checked',<?endif?> 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.CheckColumn(\'<?=CUtil::JSEscape($header["id"])?>\', this)', 'AUTOHIDE':false},
<?
endforeach;
?>
		{'SEPARATOR': true},
		{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns_apply"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns_apply_title"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.ApplySaveColumns()'}
	], 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>},
	{'SEPARATOR': true},
<?
foreach($arResult["OPTIONS"]["views"] as $view_id=>$view):
?>
	{'TEXT': '<?=htmlspecialcharsbx($view["name"]<>''? CUtil::JSEscape($view["name"]) : GetMessage("interface_grid_view_noname"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_view_title"))?>'<?if($view_id == $arResult["OPTIONS"]["current_view"]):?>, 'ICONCLASS':'checked'<?endif?>, 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SetView(\'<?=$view_id?>\')'},
<?
endforeach;
?>
	{'SEPARATOR': true},
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_views"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_views_mnu_title"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.ShowViews()', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'grid-views'}
<?if(!empty($arThemes)):?>
	, {'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_colors"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_colors_title"))?>', 'CLASS': 'bx-grid-themes-menu-item', 'MENU':[
<?
$i = 0;
foreach($arThemes as $theme):
?>
		<?if($i > 0) echo ','?>{'TEXT': '<?=CUtil::JSEscape($theme["name"])?><?if($theme["theme"] == $arResult["GLOBAL_OPTIONS"]["theme"]) echo ' '.CUtil::JSEscape(GetMessage("interface_grid_default"))?>', 'ONCLICK': 'bxGrid_<?=$arParams["GRID_ID"]?>.SetTheme(this, \'<?=CUtil::JSEscape($theme["theme"])?>\')'<?if($theme["theme"] == $arResult["OPTIONS"]["theme"] || $theme["theme"] == "grey" && $arResult["OPTIONS"]["theme"] == ''):?>, 'ICONCLASS':'checked'<?endif?>}
<?
	$i++;
endforeach;
?>
	], 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'grid-themes'}
<?endif?>
];

BX.ready(function(){bxGrid_<?=$arParams["GRID_ID"]?>.InitTable()});

<?if(!empty($arParams["FILTER"])):?>
bxGrid_<?=$arParams["GRID_ID"]?>.oFilterRows = <?= Json::encode($arResult["FILTER_ROWS"])?>;
bxGrid_<?=$arParams["GRID_ID"]?>.filterMenu = [
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_rows"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_rows_title"))?>', 'MENU':[
<?foreach($arParams["FILTER"] as $field):?>
		{'ID':'flt_<?=$arParams["GRID_ID"]?>_<?=$field["id"]?>', 'TEXT': '<?=CUtil::JSEscape($field["name"])?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRow(\'<?=CUtil::JSEscape($field["id"])?>\', this)', 'AUTOHIDE':false<?if($arResult["FILTER_ROWS"][$field["id"]]):?>, 'ICONCLASS':'checked'<?endif?>},
<?endforeach?>
		{'SEPARATOR': true},
		{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_show_all"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRows(true)'},
		{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_hide_all"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRows(false)'}
	]},
<?if(!empty($arResult["OPTIONS"]["filters"]) && is_array($arResult["OPTIONS"]["filters"])):?>
	{'SEPARATOR': true},
<?foreach($arResult["OPTIONS"]["filters"] as $filter_id=>$filter):?>
	{'ID': 'mnu_<?=$arParams["GRID_ID"]?>_<?=$filter_id?>', 'TEXT': '<?=htmlspecialcharsbx(CUtil::JSEscape($filter["name"]))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_filter_apply"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.ApplyFilter(\'<?=CUtil::JSEscape($filter_id)?>\')'},
<?
	endforeach;
endif;
?>
	{'SEPARATOR': true},
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_filters"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_filters_title"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.ShowFilters()', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'grid-filters'},
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_filters_save"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_filters_save_title"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.AddFilterAs()', 'DISABLED':<?=($USER->IsAuthorized() && !empty($arResult["FILTER"])? 'false':'true')?>}
];

BX.ready(function(){bxGrid_<?=$arParams["GRID_ID"]?>.InitFilter()});
<?endif?>

phpVars.messLoading = '<?=GetMessageJS("interface_grid_loading")?>';
</script>