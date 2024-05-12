<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

//color schemes
$arThemes = CGridOptions::GetThemes($this->GetFolder());
if(!empty($arParams["FILTER"])):
	$APPLICATION->IncludeComponent(
	"bitrix:bizproc.interface.filter",
	isset($arParams['~FILTER_TEMPLATE']) ? $arParams['~FILTER_TEMPLATE'] : '',
	array(
		"GRID_ID"=>$arParams["~GRID_ID"],
		"FILTER"=>$arParams["~FILTER"],
		"FILTER_PRESETS"=>$arParams["~FILTER_PRESETS"],
		"FILTER_ROWS"=>$arResult["FILTER_ROWS"],
		"FILTER_FIELDS"=>$arResult["FILTER"],
		"RENDER_FILTER_INTO_VIEW"=>isset($arParams['~RENDER_FILTER_INTO_VIEW']) ? $arParams['~RENDER_FILTER_INTO_VIEW'] : '',
		"HIDE_FILTER" => isset($arParams["~HIDE_FILTER"]) ? $arParams["~HIDE_FILTER"] : false,
		"OPTIONS"=>$arResult["OPTIONS"]
	),
	$component,
	array("HIDE_ICONS"=>true)
);
endif;

if (!empty($arParams["ERROR_MESSAGES"]))
{
	?>
	<div class="bp-grid-errortext">
		<p><?= implode('<br/>', $arParams["ERROR_MESSAGES"]) ?></p>
	</div>
	<?
}
if($arParams["SHOW_FORM_TAG"]):?>
<form name="form_<?=$arParams["GRID_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" target="_self" method="POST">
<?=bitrix_sessid_post();?>
<?endif?>
<div class="bx-bizproc-interface-list">
<table cellspacing="0" class="bx-bizproc-interface-grid<?if($arResult["OPTIONS"]["theme"] <> '') echo " bx-bizproc-interface-grid-theme-".$arResult["OPTIONS"]["theme"]?>" id="<?=$arParams["GRID_ID"]?>">
	<thead>
	<tr class="bx-grid-gutter" oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu">
<?if($arResult["ALLOW_EDIT"]):?>
		<td><div class="empty"></div></td>
<?endif?>
		<td><div class="empty"></div></td>
<?foreach($arResult["HEADERS"] as $header):?>
		<td<?=(!empty($header["sort_state"]) ? ' class="bx-bizproc-sorted"':'')?>><div class="empty"></div></td>
<?endforeach?>
	</tr>
	<tr class="bx-bizproc bx-bizproc-grid-head" oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu"<?if($GLOBALS['USER']->IsAuthorized()):?> ondblclick="bxGrid_<?=$arParams["GRID_ID"]?>.EditCurrentView()"<?endif?>>
<?if($arResult["ALLOW_EDIT"]):?>
		<td class="bx-bizproc-checkbox-col" width="1%"><input type="checkbox" name="" id="<?=$arParams["GRID_ID"]?>_check_all" value="" title="<?echo GetMessage("interface_grid_check_all")?>" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.SelectAllRows(this);"></td>
<?endif?>
		<td class="bx-bizproc-actions-col" width="1%"><a href="javascript:void(0);"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.menu.ShowMenu(this, bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu, false, false, bxGrid_<?=$arParams["GRID_ID"]?>.SaveColumns);return false;"
			title="<?echo GetMessage("interface_grid_settings")?>" class="bx-bizproc-action"><div class="empty"></div></a></td>
<?
$colspan = count($arResult["HEADERS"])+($arResult["ALLOW_EDIT"]? 2:1);
foreach($arResult["HEADERS"] as $id=>$header):
	$headerName = !isset($header['hideName']) || !$header['hideName'] ? $header["name"] : '&nbsp;';
	$headerWidth = isset($header['width']) ? $header['width'] : '';

	if (isset($header['iconCls']) && !empty($header['hideName']))
		$headerName = '<span class="'.$header['iconCls'].'"></span>';

if($header["sort"] <> ''):
	$order_title = GetMessage("interface_grid_sort").' '.$header["name"];
	$order_class = "";
	if($header["sort_state"] == "desc"):
		$order_class = " bx-bizproc-sort-down";
		$order_title .= " ".GetMessage("interface_grid_sort_down");
	elseif($header["sort_state"] == "asc"):
		$order_class = " bx-bizproc-sort-up";
		$order_title .= " ".GetMessage("interface_grid_sort_up");
	endif;

	$headerClassName = 'bx-bizproc-sortable';
	if($header["sort_state"])
		$headerClassName .= ' bx-bizproc-sorted';
	if(isset($header['class']))
		$headerClassName .= ' bx-bizproc-'.$header['class'];
?>
		<td class="<?=$headerClassName?>"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.Sort('<?=CUtil::addslashes($header["sort_url"])?>', '<?=$header["sort_state"]?>', '<?=$header["order"]?>', arguments);"
			oncontextmenu="return [{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_sort_asc"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.Sort(\'<?=CUtil::addslashes($header["sort_url"])?>\', \'desc\')', 'ICONCLASS':'bx-bizproc-grid-sort-asc'}, {'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_sort_desc"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.Sort(\'<?=CUtil::addslashes($header["sort_url"])?>\', \'asc\')', 'ICONCLASS':'grid-sort-desc'}, {'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_hide_col"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.HideColumn(\'<?=CUtil::JSEscape($id)?>\')', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>}]"
			title="<?=$order_title?>"
			<?=$headerWidth !== '' ? ' style="width:'.$headerWidth.';"' : ''?>>
			<table cellspacing="0" class="bx-bizproc-grid-sorting">
				<tr>
					<td><?=$headerName?></td>
					<td class="bx-bizproc-sort-sign<?=$order_class?>"><div class="empty"></div></td>
				</tr>
			</table>
		</td>
<?else:?>
		<td oncontextmenu="return [{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_hide_col"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.HideColumn(\'<?=CUtil::JSEscape($id)?>\')', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>}]"
		<?=$headerWidth !== '' ? ' style="width:'.$headerWidth.';"' : ''?>>
			<?=$headerName?>
		</td>
<?endif?>
<?endforeach?>
	</tr>
	</thead>
	<tbody>

<?
$jsActions = array();
if(!empty($arParams["ROWS"])):

foreach($arParams["ROWS"] as $index=>$aRow):

	$jsActions[$index] = array();
	if(is_array($aRow["actions"]))
	{
		$jsActions[$index] = $aRow["actions"];

		//find default action
		$sDefAction = '';
		$sDefTitle = '';
		foreach($aRow["actions"] as $action)
		{
			if($action["DEFAULT"] == true)
			{
				$sDefAction = $action["ONCLICK"];
				$sDefTitle = $action["TEXT"];
				break;
			}
		}
	}
?>
	<tr class="bx-bizproc-table-body <?=isset($aRow["rowClass"]) ? $aRow["rowClass"] : ''?>" oncontextmenu="return bxGrid_<?=$arParams["GRID_ID"]?>.oActions[<?=$index?>]"<?if($sDefAction <> ''):?> ondblclick="<?=htmlspecialcharsbx($sDefAction)?>"<?endif?>>
<?if($arResult["ALLOW_EDIT"]):?>
	<?
	if($aRow["editable"] !== false):
		$data_id = ($aRow["id"] ?? $aRow["data"]["ID"]);
	?>
		<td class="bx-bizproc-checkbox-col"><input type="checkbox" name="ID[]" id="ID_<?=$data_id?>" value="<?=$data_id?>" title="<?echo GetMessage("interface_grid_check")?>"></td>
	<?else:?>
		<td class="bx-bizproc-checkbox-col">&nbsp;</td>
	<?endif?>
<?endif?>
	<?if(is_array($aRow["actions"]) && count($aRow["actions"]) > 0):?>
		<td class="bx-bizproc-actions-col"><a href="javascript:void(0);"
			onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ShowActionMenu(this, <?=$index?>);"
			title="<?echo GetMessage("interface_grid_act")?>" class="bx-bizproc-action"><div class="empty"></div></a></td>
	<?else:?>
		<td>&nbsp;</td>
	<?endif?>
<?
	$columnClasses = isset($aRow['columnClasses']) && is_array($aRow['columnClasses']) ? $aRow['columnClasses'] : null;
	foreach($arResult["HEADERS"] as $id=>$header):
	$columnClass = 	!empty($header["sort_state"]) ? 'bx-bizproc-sorted' : '';
	if($columnClasses && isset($columnClasses[$id]) && $columnClasses[$id] !== '')
	{
		if($columnClass !== '')
		{
			$columnClass .= ' ';
		}
		$columnClass .= $columnClasses[$id];
	}

	?><td<?=($columnClass !== '' ? ' class="'.$columnClass.'"' : '')?><?
if (!empty($header["align"]))
	echo ' align="'.$header["align"].'"';
elseif(isset($header["type"]) && $header["type"] == "checkbox")
	echo ' align="center"';
		?>><?
	if (
		isset($header["type"])
		&& $header["type"] == "checkbox"
		&& $aRow["data"][$id] <> ''
		&& ($aRow["data"][$id] == 'Y' || $aRow["data"][$id] == 'N')
	)
	{
		echo ($aRow["data"][$id] == 'Y'? GetMessage("interface_grid_yes"):GetMessage("interface_grid_no"));
	}
	else
	{
		$val = (isset($aRow["columns"][$id])? $aRow["columns"][$id] : $aRow["data"][$id]);
		echo ($val <> ''? $val:'&nbsp;');
	}
		?></td>
<?endforeach?>
	</tr>
<?endforeach; // $arParams["ROWS"]?>
<?
else: //!empty($arParams["ROWS"])
?>
	<tr><td class="bx-bizproc-not-result" colspan="<?=$colspan?>"><?echo GetMessage("interface_grid_no_data")?></td></tr>
<?endif?>
	</tbody>
<?if($arResult["ALLOW_EDIT"] || is_array($arParams["FOOTER"]) && count($arParams["FOOTER"]) > 0 || $arResult["NAV_STRING"] <> ''):?>
	<tfoot>
	<tr class="bx-bizproc-grid-footer">
		<td colspan="<?=$colspan?>">
			<table cellpadding="0" cellspacing="0" border="0" class="bx-bizproc-table-footer bx-bizproc-grid-footer">
				<tbody><tr>
			<?if($arResult["ALLOW_EDIT"]):?>
					<td><?echo GetMessage("interface_grid_checked")?> <span id="<?=$arParams["GRID_ID"]?>_selected_span">0</span></td>
			<?endif?>
			<?foreach($arParams["FOOTER"] as $footer):?>
					<? if(!empty($footer['custom_html'])): ?>
						<?= $footer['custom_html'] ?>
					<?else:?>
					<td><?=$footer["title"]?>: <span><?=$footer["value"]?></span></td>
					<?endif?>
			<?endforeach?>
					<?
					// page size
					$nPageSize = 20;
					if (is_array($arResult['OPTIONS']['views'])
						&& isset($arResult['OPTIONS']['current_view'])
						&& is_array($arResult['OPTIONS']['views'][$arResult['OPTIONS']['current_view']])
						&& isset($arResult['OPTIONS']['views'][$arResult['OPTIONS']['current_view']]['page_size']))
					{
						$nPageSize = $arResult['OPTIONS']['views'][$arResult['OPTIONS']['current_view']]['page_size'];
					}

					if (is_object($arParams['NAV_OBJECT'])
						&& $arParams['NAV_OBJECT'] instanceof \CAllDBResult
						&& $arParams['NAV_OBJECT']->bShowAll
						&& $arParams['NAV_OBJECT']->NavShowAll)
					{
						$nPageSize = 0;
					}
					$arPageSize = array(10, 20, 50, 100, 200);
					?>
					<td>
						<span class="bx-pagination-text"><?= htmlspecialcharsbx(GetMessage("navigation_records").':') ?></span>
						<select id="<?=$arParams["GRID_ID"].'_page_size_control'?>" class="bx24-dropdown bx-pagination-dropdown">
							<?
							if ($nPageSize !== 0)
							{
								foreach($arPageSize as $val)
								{
									?><option value="<?=$val?>"<?= (($val == $nPageSize) ? ' selected="selected"' : '') ?><?= ($val === 0 ? ' disabled' : '') ?>><?= htmlspecialcharsbx($val !== 0 ? $val : GetMessage('navigation_records_all')) ?></option><?
								}
							}
							else
							{
								?><option value="0" selected="selected" disabled><?= htmlspecialcharsbx(GetMessage('navigation_records_all')) ?></option><?
							}
							?>
						</select>
					</td>
					<td class="bx-bizproc-right"><?= (!empty($arResult["NAV_STRING"]) ? $arResult["NAV_STRING"] : '&nbsp;') ?></td>
				</tr></tbody>
			</table>
		</td>
	</tr>
	</tfoot>
<?endif?>
</table>

<?if($arResult["ALLOW_EDIT"]):?>
<div class="bx-bizproc-footer-interface-toolbar-container bp-footer-interface-toolbar-container">
<input type="hidden" name="action_button_<?=$arParams["GRID_ID"]?>" value="">
<table cellpadding="0" cellspacing="0" border="0" class="">
	<tr>
<?
$bNeedSep = false;
if($arParams["ACTION_ALL_ROWS"]):
	$bNeedSep = true;
?>
		<td class="vam">
			<input title="<?echo GetMessage("interface_grid_for_all")?>" type="checkbox" name="action_all_rows_<?=$arParams["GRID_ID"]?>" id="actallrows_<?=$arParams["GRID_ID"]?>" value="Y" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ForAllClick(this);"<?if(empty($arParams["ROWS"])) echo ' disabled'?>>
		</td>
		<td class="vam"><label title="<?echo GetMessage("interface_grid_for_all")?>" for="actallrows_<?=$arParams["GRID_ID"]?>"><?echo GetMessage("interface_grid_for_all_box")?></label></td>
<?endif?>
<?if($arResult["ALLOW_INLINE_EDIT"]):?>
	<?if($bNeedSep):?>
<!--		<td><div class="bx-bizproc-separator"></div></td>-->
	<?endif;?>
		<td class="vam"><a href="javascript:void(0);" onclick="bxGrid_<?=$arParams["GRID_ID"]?>.ActionEdit(this);" title="<?echo GetMessage("interface_grid_edit_selected")?>" class="bx-bizproc-bd-icon-edit" id="edit_button_<?=$arParams["GRID_ID"]?>"></a></td>
<?
	$bNeedSep = true;
endif;
?>
<?if(!empty($arParams["ACTIONS"]["delete"])):?>
	<?if($bNeedSep && !$arResult["ALLOW_INLINE_EDIT"]):?>
		<td><div class="bx-bizproc-separator"></div></td>
	<?endif?>
		<td class="vam"><a href="javascript:void(0);" style="margin-top: 20px" onclick="var el; if(bxGrid_<?=$arParams["GRID_ID"]?>.IsActionEnabled() && confirm(((el=document.getElementById('actallrows_<?=$arParams["GRID_ID"]?>')) && el.checked? '<?=CUtil::JSEScape(GetMessage("interface_grid_delete"))?>':'<?=CUtil::JSEScape(GetMessage("interface_grid_delete_checked"))?>'))) bxGrid_<?=$arParams["GRID_ID"]?>.ActionDelete();" title="<?echo GetMessage("interface_grid_delete_title")?>" class="bx-bizproc-bd-icon-del" id="delete_button_<?=$arParams["GRID_ID"]?>"></a></td>
<?
	$bNeedSep = true;
endif;
?>
<?
$bShowApply = false;
if(!empty($arParams["ACTIONS"]["list"])):
	$bShowApply = true;
?>
	<?
	if($bNeedSep):
		$bNeedSep = false;
	?>
		<td><div class="bx-bizproc-separator"></div></td>
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
		<td><div class="bx-bizproc-separator"></div></td>
	<?endif?>
		<td class="vam" style="padding-left:2px;"><?=$arParams["~ACTIONS"]["custom_html"]?></td>
<?endif?>
<?if($bShowApply):?>
		<td class="vam" style="padding-left:2px;"><input class="bx-bizproc-btn bx-bizproc-btn-medium bx-bizproc-btn-gray mb0" type="submit" name="apply" value="<?echo GetMessage("interface_grid_apply")?>" disabled></td>
<?endif?>
<!--				</tr>-->
<!--			</table>-->
<!--		</td>-->
		<td class="bx-bizproc-right"><div class="empty"></div></td>
	</tr>
	<tr class="bx-bizproc-bottom"><td class="bx-bizproc-left"><div class="empty"></div></td><td><div class="empty"></div></td><td class="bx-bizproc-right"><div class="empty"></div></td></tr>
</table>
</div>
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

<?if($GLOBALS['USER']->IsAuthorized()):?>
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
						<div style="margin-bottom:5px"><input type="button" name="add_btn" value="&gt;" title="<?echo GetMessage("interface_grid_view_add_col")?>" style="width:30px;" disabled onclick="jsSelectUtils.addSelectedOptions(this.form.view_all_cols, this.form.view_cols, false); jsSelectUtils.deleteSelectedOptions(this.form.view_all_cols); "></div>
						<div style="margin-bottom:5px"><input type="button" name="del_btn" value="&lt;" title="<?echo GetMessage("interface_grid_view_del_col")?>" style="width:30px;" disabled onclick="jsSelectUtils.addSelectedOptions(this.form.view_cols, this.form.view_all_cols, false, true); jsSelectUtils.deleteSelectedOptions(this.form.view_cols);"></div>
					</td>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("interface_grid_view_sel_col")?></div>
						<select style="min-width:150px;" name="view_cols" multiple size="12" ondblclick="this.form.del_btn.onclick()" onchange="this.form.del_btn.disabled = this.form.up_btn.disabled = this.form.down_btn.disabled = (this.selectedIndex == -1)">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="up_btn" value="<?echo GetMessage("interface_grid_view_up")?>" title="<?echo GetMessage("interface_grid_view_up_title")?>" class="bx-grid-btn" style="width:60px;" disabled onclick="jsSelectUtils.moveOptionsUp(this.form.view_cols)"></div>
						<div style="margin-bottom:5px"><input type="button" name="down_btn" value="<?echo GetMessage("interface_grid_view_down")?>" title="<?echo GetMessage("interface_grid_view_down_title")?>" class="bx-grid-btn" style="width:60px;" disabled onclick="jsSelectUtils.moveOptionsDown(this.form.view_cols)"></div>
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
	$sort = isset($header["sort"]) ? $header["sort"] : "";
	$enableDefaultSort = $sort !== "" && (!isset($header["enableDefaultSort"]) || $header["enableDefaultSort"]);
	if($enableDefaultSort):
		?><option value="<?=$sort?>"><?=$header["name"]?></option><?
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
</table>
</div>

<div id="views_list_<?=$arParams["GRID_ID"]?>">
<div style="float:left; width:80%">
<select name="views_list" size="17" style="width:100%; height:250px;" ondblclick="this.form.views_edit.onclick()">
<?foreach($arResult["OPTIONS"]["views"] as $view_id=>$view):?>
	<option value="<?=htmlspecialcharsbx($view_id)?>"<?if($view_id == $arResult["OPTIONS"]["current_view"]):?> selected<?endif?>><?=htmlspecialcharsbx(($view["name"] <> ''? $view["name"]:GetMessage("interface_grid_view_noname")))?></option>
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
	{
		$field["params"] = [];
	}
if ($field["type"] == '' || $field["type"] == 'text')
	{
		if($field["params"]["size"] == '')
			$field["params"]["size"] = "30";
	}
	elseif($field["type"] == 'date')
	{
		if (empty($field["params"]["size"]))
		{
			$field["params"]["size"] = "10";
		}
	}
	elseif($field["type"] == 'number')
	{
		if($field["params"]["size"] == '')
			$field["params"]["size"] = "8";
	}

	$params = '';
	foreach($field["params"] as $p=>$v)
		$params .= ' '.$p.'="'.$v.'"';

	switch($field["type"]):
		case 'custom':
			echo isset($field["settingsHtml"]) ? $field["settingsHtml"] : $field["value"];
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
	<option value="<?=htmlspecialcharsbx($filter_id)?>"><?=htmlspecialcharsbx(($filter["name"] <> ''? $filter["name"]:GetMessage("interface_grid_view_noname")))?></option>
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
	),
	"ajax"=>array(
		"AJAX_ID"=>$arParams["AJAX_ID"],
		"AJAX_OPTION_SHADOW"=> (isset($arParams["AJAX_OPTION_SHADOW"]) && $arParams["AJAX_OPTION_SHADOW"] == "Y"),
	),
	"settingWndSize"=>CUtil::GetPopupSize("InterfaceGridSettingWnd"),
	"viewsWndSize"=>CUtil::GetPopupSize("InterfaceGridViewsWnd", array('height' => 350, 'width' => 500)),
	"filtersWndSize"=>CUtil::GetPopupSize("InterfaceGridFiltersWnd", array('height' => 350, 'width' => 500)),
	"filterSettingWndSize"=>CUtil::GetPopupSize("InterfaceGridFilterSettingWnd"),
	"calendar_image"=>$this->GetFolder()."/images/calendar.gif",
	"server_time"=>(time()+date("Z")+CTimeZone::GetOffset()),
	"component_path"=>$component->GetRelativePath(),
	"template_path"=>$this->GetFolder(),
	"sessid"=>bitrix_sessid(),
	"current_url"=>$arResult["CURRENT_URL"],
	"user_authorized"=>$GLOBALS['USER']->IsAuthorized(),
);

$colsEditMeta = $arResult["COLS_EDIT_META"];
if (is_array($colsEditMeta) && !empty($colsEditMeta))
{
	foreach ($colsEditMeta as &$fInfo)
	{
		if (isset($fInfo['type']) && $fInfo['type'] === 'list' && is_array($fInfo['items']) && !empty($fInfo['items']))
		{
			$items = array();
			foreach ($fInfo['items'] as $itemValue => $itemTitle)
				$items[] = array('val' => $itemValue, 'ttl' => $itemTitle);
			$fInfo['items'] = $items;
		}
	}
	unset($fInfo);
}
?>

<script>
var settingsDialog<?=$arParams["GRID_ID"]?>;
var viewsDialog<?=$arParams["GRID_ID"]?>;
var filtersDialog<?=$arParams["GRID_ID"]?>;
var filterSettingsDialog<?=$arParams["GRID_ID"]?>;

jsDD.Reset();

if(!window['bxGrid_<?=$arParams["GRID_ID"]?>'])
	bxGrid_<?=$arParams["GRID_ID"]?> = new BxInterfaceGrid('<?=$arParams["GRID_ID"]?>');

bxGrid_<?=$arParams["GRID_ID"]?>.oActions = <?=CUtil::PhpToJsObject($jsActions)?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oColsMeta = <?=CUtil::PhpToJsObject($colsEditMeta)?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oEditData = <?=CUtil::PhpToJsObject($arResult["DATA_FOR_EDIT"])?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oColsNames = <?=CUtil::PhpToJsObject(htmlspecialcharsback($arResult["COLS_NAMES"]))?>;
bxGrid_<?=$arParams["GRID_ID"]?>.oOptions = <?=CUtil::PhpToJsObject($arResult["OPTIONS"])?>;
bxGrid_<?=$arParams["GRID_ID"]?>.vars = <?=CUtil::PhpToJsObject($variables)?>;
bxGrid_<?=$arParams["GRID_ID"]?>.menu = new PopupMenu('bxMenu_<?=$arParams["GRID_ID"]?>', 1010);
bxGrid_<?=$arParams["GRID_ID"]?>.settingsMenu = [
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_views_setup"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_views_setup_title"))?>', 'DEFAULT':true, 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.EditCurrentView()', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'grid-settings'},
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_columns_title"))?>', 'MENU':[
<?
unset($colsEditMeta);
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
];
<?
$isAjaxRequest = isset($_REQUEST['bxajaxid']) || isset($_REQUEST['AJAX_CALL']);
$ajaxInitEvent = isset($arParams['~AJAX_INIT_EVENT']) ? CUtil::JSEscape($arParams['~AJAX_INIT_EVENT']) : '';
?>
BX.ready(
		function()
		{
		<?if($isAjaxRequest && $ajaxInitEvent !== ''):?>
			BX.addCustomEvent(
					window,
					'<?=$ajaxInitEvent?>',
					function()
					{
						bxGrid_<?=$arParams["GRID_ID"]?>.InitTable();
						BX.removeCustomEvent(window, '<?=$ajaxInitEvent?>', this);
					}
			);
			<?else:?>
			bxGrid_<?=$arParams["GRID_ID"]?>.InitTable();
			<?endif;?>
		}
);

<?if(!empty($arParams["FILTER"])):?>
bxGrid_<?=$arParams["GRID_ID"]?>.oFilterRows = <?=CUtil::PhpToJsObject($arResult["FILTER_ROWS"])?>;
bxGrid_<?=$arParams["GRID_ID"]?>.filterMenu = [
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_rows"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_rows_title"))?>', 'MENU':[
<?foreach($arParams["FILTER"] as $field):?>
		{'ID':'flt_<?=$arParams["GRID_ID"]?>_<?=$field["id"]?>', 'TEXT': '<?=CUtil::JSEscape($field["name"])?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRow(\'<?=CUtil::JSEscape($field["id"])?>\', this)', 'AUTOHIDE':false<?if($arResult["FILTER_ROWS"][$field["id"]]):?>, 'ICONCLASS':'checked'<?endif?>},
<?endforeach?>
		{'SEPARATOR': true},
		{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_show_all"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRows(true)'},
		{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_grid_flt_hide_all"))?>', 'ONCLICK':'bxGrid_<?=$arParams["GRID_ID"]?>.SwitchFilterRows(false)'}
	]},
<?if(is_array($arResult["OPTIONS"]["filters"]) && !empty($arResult["OPTIONS"]["filters"])):?>
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
<script>
	bxGrid_<?=$arParams["GRID_ID"]?>.pageSizeControl = new BX.Bizproc.GridPageSizeControl(
		{
			gridId: "<?=$arParams["GRID_ID"]?>",
			grid: bxGrid_<?=$arParams["GRID_ID"]?>,
			nodeId: "<?=$arParams["GRID_ID"].'_page_size_control'?>"
		}
	);
</script>