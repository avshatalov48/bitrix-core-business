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
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-forum"><a href="<?=$arResult["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("FL_TITLE")?></span></div>
</div>

<?
if (empty($arResult["TOPIC"])):
?>
<div class="forum-info-box forum-move-topics">
	<div class="forum-info-box-inner">
		<?=GetMessage("F_EMPTY_TOPIC_LIST")?>
	</div>
</div>
<?
	return false;
endif;
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" onsubmit="this.form_topics_submit.disabled=true;" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="topic_move" />
	<input type="hidden" name="action" value="move" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
	<?=bitrix_sessid_post()?>
<div class="forum-info-box forum-move-topics">
	<div class="forum-info-box-inner">
<?
foreach ($arResult["TOPIC"] as $Topic):
?>
		<div class="forum-topic-move">
			<input type="checkbox" checked="checked" name="TID[]" value="<?=$Topic["ID"]?>" id="TID_<?=$Topic["ID"]?>" />
			<a href="<?=$Topic["read"]?>"><?=$Topic["TITLE"]?></a>
		</div>
<?
endforeach;

?>
		<div class="forum-topic-move-buttons">
			<input type="submit" value="<?=GetMessage("FM_MOVE_TOPIC")?>" name="form_topics_submit" /> <span><?=GetMessage("F_IN")?></span> 
			<select name="newFID" class="forums-selector-single">
<?
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
?>
				<optgroup label="<?=str_pad("", ($res["DEPTH"] - 1)*6, "&nbsp;").$res["NAME"]
				?>" class="forums-selector-optgroup level<?=$res["DEPTH"]?>"></optgroup>
<?
		else:
?>
				<option value="<?=$res["ID"]?>" <?=($arParams["TID"] == $res["ID"] ? "selected='selected'" : "")?> <?
					?>class="forums-selector-option level<?=$res["DEPTH"]?>"><?=($res["DEPTH"] > 0 ? str_pad("", $res["DEPTH"]*6, "&nbsp;")."&nbsp;" : "").
						$res["NAME"]?></option>
<?
		endif;
	endforeach;
?>
			</select>
		</div>
		
		<div class="forum-topic-move">
			<input type="checkbox" id="leaveLink" name="leaveLink" value="Y" />
			<label for="leaveLink"><?=GetMessage("FM_LEAVE_LINK")?></label>
		</div>
	</div>
</div>
</form>