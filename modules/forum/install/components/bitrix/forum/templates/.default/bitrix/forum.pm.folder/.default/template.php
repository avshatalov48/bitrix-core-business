<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$iIndex = rand();
$arResult["FID"] = (is_array($arResult["FID"]) ? $arResult["FID"] : array($arResult["FID"]));
/********************************************************************
				/Input params
********************************************************************/
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;
?>
<div style="float:right;">
	<div class="forum-pm-progress-bar-out"><div class="forum-pm-progress-bar-in" style="width:<?=$arResult["count"]?>%">&nbsp;</div></div>
	<div class="forum-pm-progress-bar-out1"><div class="forum-pm-progress-bar-in1"><?=GetMessage("PM_POST_FULLY")." ".$arResult["count"]?>%</div></div>
</div>
<div class="forum-clear-float"></div>

<?

if (($arParams["mode"] == "new") || ($arParams["mode"] == "edit"))
{
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="PAGE_NAME" value="pm_folder"/>
	<input type="hidden" name="action" value="<?=$arResult["action"]?>" />
	<input type="hidden" name="mode" value="<?=$arParams["mode"]?>" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
<div class="forum-info-box forum-create-folder">
	<div class="forum-info-box-inner">
		<span class="forum-messages-folder"><?=GetMessage("PM_FOLDER_TITLE")?> 
			<span><input type="text" name="FOLDER_TITLE" size="40" maxlength="64" value="<?=$arResult["POST_VALUES"]["FOLDER_TITLE"]?>" tabindex="1"/></span>
			<span><input type="submit" name="SAVE" value="OK" tabindex="2"/></span>
		</span>
	</div>
</div>
</form>
<?
	return false;
}
?>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-pmessage"><a href="<?=$arResult["URL"]["MESSAGE_NEW"]?>"><?=GetMessage("PM_HEAD_NEW_MESSAGE")?></a></span>&nbsp;&nbsp;
		<span class="forum-option-pmessage-folder"><a href="<?=$arResult["URL"]["FOLDER_NEW"]?>"><?=GetMessage("PM_HEAD_NEW_FOLDER")?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("PM_PM")?></span></div>
</div>

<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
	?>onsubmit="return Validate(this)" name="MESSAGES_<?=$iIndex?>" id="MESSAGES_<?=$iIndex?>">
	<?=bitrix_sessid_post()?>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table">
			<thead>
				<tr>
					<th class="forum-first-column forum-column-foldername"><div class="forum-head-title"><span><?=GetMessage("F_FOLDER")?></span></div></th>
					<th class="forum-column-message"><span><?=GetMessage("F_MESSAGES")?></span></th>
					<th class="forum-last-column forum-column-action"><input type="checkbox" name="all_FID__" onclick="FSelectAll(this, 'FID[]');" /></th>
				</tr>
			</thead>
			<tbody>
<?
$iCount = 0;
for ($ii = 1; $ii <= $arResult["FORUM_SystemFolder"]; $ii++):
	if ($arParams["version"] == 2 && $ii == 2)
		continue;
	$iCount++;
?>
				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
				$iCount == count($arResult["USER_FOLDER"]) ? "forum-row-last " : ""))?><?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
					<td class="forum-first-column forum-column-foldername">
						<a href="<?=$arResult["SYSTEM_FOLDER"][$ii]["URL"]["FOLDER"]?>"><?=GetMessage("PM_FOLDER_ID_".$ii)?></a>
					</td>
					<td>
						<?=$arResult["SYSTEM_FOLDER"][$ii]["cnt"]?>
				<?
				if ($arResult["SYSTEM_FOLDER"][$ii]["CNT_NEW"] > 0):
				?>
						(<a href="<?=$arResult["SYSTEM_FOLDER"][$ii]["URL"]["FOLDER"]?>"><?=$arResult["SYSTEM_FOLDER"][$ii]["CNT_NEW"]?></a>)
				<?
				endif;
				?>
					</td>
					<td class="forum-last-column">
						<input type="checkbox" name="FID[]" id="FID_<?=$ii?>" value="<?=$ii?>"  onclick="onClickCheckbox(this);" <?
				if (in_array($ii, $arResult["FID"])):
						?> checked="checked" <?
				elseif (!empty($arResult["USER_FOLDER"])):
/*						?> disabled="disabled" <?*/
				endif;
						?> />
					</td>
				</tr>
<?
endfor;
foreach ($arResult["USER_FOLDER"] as $res):
	$iCount++;
?>
				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
				$iCount == count($arResult["USER_FOLDER"]) ? "forum-row-last " : ""))?><?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
					<td class="forum-first-column forum-column-foldername">
						<a href="<?=$res["pm_list"]?>"><?=$res["TITLE"]?></a> 
						<span class="folder-edit"> ( <a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a> ) </span>
					</td>
					<td class="forum-column-message">
						<?=$res["CNT"]?>
				<?
				if ($res["CNT_NEW"] > 0):
				?>
						(<a href="<?=$res["URL"]["FOLDER"]?>"><?=$res["CNT_NEW"]?></a>)
				<?
				endif;
				?>
					</td>
					<td class="forum-last-column forum-column-action">
						<input type="checkbox" name="FID[]" id="FID_<?=$res["ID"]?>" value="<?=$res["ID"]?>"  onclick="onClickCheckbox(this);"<?
				if (in_array($res["ID"], $arResult["FID"])):
						?> checked="checked" <?
				endif;
						?> />
					</td>
				</tr>
<?
endforeach;
?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5" class="forum-column-footer">
						<div class="forum-footer-inner">
							<div class="forum-topics-moderate">
								<select name="action" onclick="onClickSelect(this)">
									<option value="remove"><?=GetMessage("F_REMOVE")?></option>
								<?
								if (!empty($arResult["USER_FOLDER"])):
								?>
									<option value="delete"><?=GetMessage("F_DELETE")?></option>
								<?
								endif;
								?>
								</select>&nbsp;<input type="submit" value="OK" onclick="" />
							</div>
						</div>
					</td>
				</tr>
			</tfoot>
			</table>
		</div>
	</div>
</div>
</form>
<script>
if (typeof oText != "object")
	var oText = {};
oText['s_no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_DATA'))?>';
oText['s_del'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_FOLDERS"))?>';
oText['s_del_mess'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_MESSAGES"))?>';
function onClickSelect(oObj)
{
	var items = oObj.form.getElementsByTagName('input');
	if (!items && typeof items == "object" )
		return true;
	if (!items.length || (typeof(items.length) == 'undefined'))
		items = [items];
	for (var ii = 0; ii < items.length; ii++)
	{
		if (!(items[ii].type == "checkbox" && items[ii].name == 'FID[]'))
			continue;
		else if (parseInt(items[ii].value) > <?=FORUM_SystemFolder?>)
			continue;
		items[ii].disabled = (oObj.value=='delete');
	}
	return true;
}
</script>

<?
	// GetMessage("PM_FOLDER_ID_1");
	// GetMessage("PM_FOLDER_ID_2");
	// GetMessage("PM_FOLDER_ID_3");
	// GetMessage("PM_FOLDER_ID_4");
?>
