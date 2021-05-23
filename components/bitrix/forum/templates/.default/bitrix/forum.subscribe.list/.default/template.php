<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
CUtil::InitJSCore(array());
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$iIndex = rand();
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
	<div class="forum-header-title"><span><?=GetMessage("FSL_SUBSCR_MANAGE")?></span></div>
</div>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
	?>onsubmit="return Validate(this)" name="SUBSCRIBES_<?=$iIndex?>" id="SUBSCRIBES_<?=$iIndex?>">
	<?=bitrix_sessid_post()?>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-subscribe-list">
			<thead>
				<tr>
					<th class="forum-first-column"><span><?=GetMessage("FSL_FORUM_NAME")?></span></th>
					<th><span><?=GetMessage("FSL_TOPIC_NAME")?></span></th>
					<th><span><?=GetMessage("FSL_SUBSCR_DATE")?></span></th>
					<th><span><?=GetMessage("FSL_LAST_SENDED_MESSAGE")?></span></th>
					<th class="forum-last-column"><input type="checkbox" name="all_SID__" onclick="FSelectAll(this, true);" /></th>
				</tr>
			</thead>
<?
if ($arResult["SHOW_SUBSCRIBE_LIST"] != "Y"):
?>
			<tbody>
				<tr class="forum-row-first forum-row-odd">
					<td class="forum-first-column" colspan="5"><?=GetMessage("FSL_NOT_SUBCRIBED")?></td>
				</tr>
			<tbody>
<?
else:
?>
			<tbody>
<?
	$iCount = 0;
	foreach ($arResult["SUBSCRIBE_LIST"] as $res):
		$iCount++;
?>
				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
				$iCount == count($arResult["SUBSCRIBE_LIST"]) ? "forum-row-last " : ""))?><?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
					<td class="forum-first-column"><a href="<?=$res["list"]?>"><?=$res["FORUM_INFO"]["NAME"]?></a></td>
					<td><?
		if ($res["SUBSCRIBE_TYPE"] == "TOPIC"):
				?><a href="<?=$res["read"]?>"><?=$res["TOPIC_INFO"]["TITLE"]?></a><?
		elseif ($res["SUBSCRIBE_TYPE"] == "NEW_TOPIC_ONLY"):
				?><?=GetMessage("FSL_NEW_TOPICS")?><?
		else:
				?><?=GetMessage("FSL_ALL_MESSAGES")?><?
		endif;
					?></td>
					<td><?=$res["START_DATE"]?></td>
					<td align="center"><?
		if ($res["LAST_SEND"] > 0):
				?><a href="<?=$res["read_last_send"]?>"><?=GetMessage("FSL_HERE")?></a><?
		else:
				?>&nbsp;<?
		endif;
					?></td>
					<td class="forum-last-column">
						<input type="checkbox" name="SID[]" id="SID_<?=$res["ID"]?>" value="<?=$res["ID"]?>" class="forum-subscribe-checkbox" onclick="onClickCheckbox(this);" />
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
								<input type="hidden" name="ACTION" value="DEL" />
									<input type="submit" value="<?=GetMessage("F_DELETE_SUBSCRIBES")?>" onclick="" />
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
<script>
if (typeof oText != "object")
	var oText = {};
oText['s_no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_SUBSCRIBE'))?>';
oText['s_del'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_SUBSCRIBE"))?>';
</script>

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