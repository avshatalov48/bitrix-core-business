<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/.default/script.js");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$iIndex = rand();
//$arResult["FID"] = (is_array($arResult["FID"]) ? $arResult["FID"] : array($arResult["FID"]));
$arParams["SEO_USER"] = (in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["USER_TMPL"] = '<noindex><a rel="nofollow" href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a></noindex>';
if ($arParams["SEO_USER"] == "N") $arParams["USER_TMPL"] = '<a href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a>';
elseif ($arParams["SEO_USER"] == "TEXT") $arParams["USER_TMPL"] = '#NAME#';
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
if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=$arResult["FolderName"]?></span></div>
</div>

<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
	?>onsubmit="return Validate(this)"  name="REPLIER" id="REPLIER">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="action" value="" />
	<input type="hidden" name="folder_id" value="" />
	<input type="hidden" name="FID" value="<?=$arResult["FID"]?>" />
	<input type="hidden" name="PAGE_NAME" value="pm_list" />
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-pmessages">
			<thead>
				<tr>
					<th class="forum-first-column"><span><?=GetMessage("PM_HEAD_SUBJ")?></span><?=$arResult["SortingEx"]["POST_SUBJ"]?></th>
					<th><span><?
					if ($arResult["StatusUser"] == "RECIPIENT"):
						?><?=GetMessage("PM_HEAD_RECIPIENT")?><?
					elseif ($arResult["StatusUser"] == "SENDER"):
						?><?=GetMessage("PM_HEAD_SENDER")?><?
					else:
						?><?=GetMessage("PM_HEAD_AUTHOR")?><?
					endif;
					?></span><?=$arResult["SortingEx"]["AUTHOR_NAME"]?></th>
					<th><span><?=GetMessage("PM_HEAD_DATE")?></span><?=$arResult["SortingEx"]["POST_DATE"]?></th>
					<th class="forum-last-column forum-column-action"><input type="checkbox" name="all_message__" onclick="FSelectAll(this, 'message[]');" /></th>
				</tr>
			</thead>
<?
if ($arResult["MESSAGE"] == "N" || empty($arResult["MESSAGE"])):
?>
			<tbody>
				<tr class="forum-row-first forum-row-odd">
					<td class="forum-first-column" colspan="5"><?=GetMessage("PM_EMPTY_FOLDER")?></td>
				</tr>
			<tbody>
			<tfoot>
				<tr>
					<td colspan="5" class="forum-column-footer">
						<div class="forum-footer-inner">&nbsp;</div>
					</td>
				</tr>
			</tfoot>
<?
else:
?>
			<tbody>
<?
	$iCount = 0;
	foreach ($arResult["MESSAGE"] as $res):
		$iCount++;
?>
				<tr onmouseup="OnRowClick(<?=$res["ID"]?>, this);" id="message_row_<?=$res["ID"]?>" class="<?=($iCount == 1 ? "forum-row-first " : (
				$iCount == count($arResult["MESSAGE"]) ? "forum-row-last " : ""))?><?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?> <?
					?><?=($res["IS_READ"] != "Y" ? "forum-pmessage-new" : "")?>">
					<td class="forum-first-column">
						<a href="<?=$res["pm_read"]?>" onmouseup="FCancelBubble(event);" class="<?
							?><?=($res["IS_READ"] != "Y" ? "forum-pmessage-new" : "")?>"><?=$res["POST_SUBJ"]?></a></td>
					<td><span onmouseup="FCancelBubble(event)"><?
						?><?=str_replace(array("#URL#", "#NAME#"), array($res["profile_view"], $res["SHOW_NAME"]), $arParams["USER_TMPL"])?></span>
					</td>
					<td><?=$res["POST_DATE"]?></td>
					<td class="forum-last-column forum-column-action">
						<input type=checkbox name="message[]" id="message_id_<?=$res["ID"]?>" value="<?=$res["ID"]?>" <?
							?><?=$res["checked"]?> onclick="OnInputClick(this);" />
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
								<input type="button" name="action_delete" value="<?=GetMessage("PM_ACT_DELETE")?>" onclick="ChangeAction('delete', this);" />
								<?=GetMessage("PM_ACT_MOVE")?> <?=GetMessage("PM_ACT_IN")?>
								<select name="folder_id_move"><?
								for ($ii = 1; $ii <= $arResult["SystemFolder"]; $ii++)
								{
									if (($arResult["version"] == 2 && $ii==2) || $arParams["FID"] == $ii)
										continue;
									?><option value="<?=$ii?>"><?=GetMessage("PM_FOLDER_ID_".$ii)?></option><?
								}
								if (($arResult["UserFolder"] != "N") && is_array($arResult["UserFolder"]))
								{
									foreach ($arResult["UserFolder"] as $res):
										if ($arParams["FID"] == $res["ID"])
											continue;
									?><option value="<?=$res["ID"]?>"><?=$res["TITLE"]?></option><?
									endforeach;
								}
								?></select> 
								<input type="button" name="button_move" value="OK" onclick="ChangeAction('move', this)" />
							</div>
						</div>
					</td>
				</tr>
			</tfoot>
<?
endif;
?>
			</table>
		</div>
	</div>
</div>
</form>
<?
if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<script>
if (typeof oText != "object")
	var oText = {};
oText['no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_MESSAGES'))?>';
oText['del_message'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_MESSAGE"))?>';
</script>
