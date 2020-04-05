<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="hr-title"><h2><?=$arParams["TITLE_BLOCK"];?></h2></div>
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="vote-note-box vote-note-error">
	<div class="vote-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"])?></div>
</div>
<?
endif;

if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="vote-note-box vote-note-note">
	<div class="vote-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"])?></div>
</div>
<?
endif;

if (empty($arResult["VOTE"])):
	return false;
elseif (empty($arResult["QUESTIONS"])):
	return true;
endif;

?>
<div class="voting-form-box">
<form action="<?=POST_FORM_ACTION_URI?>" method="post" class="vote-form">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="vote" value="Y"/>
	<input type="hidden" name="PUBLIC_VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>"/>
	<input type="hidden" name="VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>"/>
	
<div class="vote-question-item">
<?
	$iCount = 0;
	foreach ($arResult["QUESTIONS"] as $arQuestion):
		$iCount++;

?>
		<div class="vote-item-title" ><h2><?=$arQuestion["QUESTION"]?></h2></div>
		
		<div class="vote-answers-list">
		<table class="vote-answers-list" cellspacing="0" cellpadding="0" border="0">
<?
		$iCountAnswers = 0;
		foreach ($arQuestion["ANSWERS"] as $arAnswer):
			$iCountAnswers++;

?>
			<tr><td class="vote-answer-name">
<?
			switch ($arAnswer["FIELD_TYPE"]):
					case 0://radio
?>
						<span class="vote-answer-item vote-answer-item-radio">
							<input type="radio" name="vote_radio_<?=$arAnswer["QUESTION_ID"]?>" <?
								?>id="vote_radio_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>" <?
								?>value="<?=$arAnswer["ID"]?>" <?=$arAnswer["FIELD_PARAM"]?> />
							<label for="vote_radio_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label>
						</span>
<?
					break;
					case 1://checkbox?>
						<span class="vote-answer-item vote-answer-item-checkbox">
							<input type="checkbox" name="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>[]" value="<?=$arAnswer["ID"]?>" <?
								?> id="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>" <?=$arAnswer["FIELD_PARAM"]?> />
							<label for="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label>
						</span>
					<?break?>

					<?case 2://dropdown?>
						<span class="vote-answer-item vote-answer-item-dropdown">
							<select name="vote_dropdown_<?=$arAnswer["QUESTION_ID"]?>" <?=$arAnswer["FIELD_PARAM"]?>>
							<?foreach ($arAnswer["DROPDOWN"] as $arDropDown):?>
								<option value="<?=$arDropDown["ID"]?>"><?=$arDropDown["MESSAGE"]?></option>
							<?endforeach?>
							</select>
						</span>
					<?break?>

					<?case 3://multiselect?>
						<span class="vote-answer-item vote-answer-item-multiselect">
							<select name="vote_multiselect_<?=$arAnswer["QUESTION_ID"]?>[]" <?=$arAnswer["FIELD_PARAM"]?> multiple="multiple">
							<?foreach ($arAnswer["MULTISELECT"] as $arMultiSelect):?>
								<option value="<?=$arMultiSelect["ID"]?>"><?=$arMultiSelect["MESSAGE"]?></option>
							<?endforeach?>
							</select>
						</span>
					<?break?>

					<?case 4://text field?>
						<span class="vote-answer-item vote-answer-item-textfield">
							<label for="vote_field_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label>
							<input type="text" name="vote_field_<?=$arAnswer["ID"]?>" id="vote_field_<?=$arAnswer["ID"]?>" <?
								?>value="" size="<?=$arAnswer["FIELD_WIDTH"]?>" <?=$arAnswer["FIELD_PARAM"]?> /></span>
					<?break?>

					<?case 5://memo?>
						<span class="vote-answer-item vote-answer-item-memo">
							<label for="vote_memo_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label>
							<textarea name="vote_memo_<?=$arAnswer["ID"]?>" id="vote_memo_<?=$arAnswer["ID"]?>" <?
								?><?=$arAnswer["FIELD_PARAM"]?> cols="<?=$arAnswer["FIELD_WIDTH"]?>" <?
								?>rows="<?=$arAnswer["FIELD_HEIGHT"]?>"></textarea>
						</span>
					<?break;
				endswitch;
?>
			</td></tr>
<?
			endforeach;
?>
			<tr><td class="vote-answer-name"></td></tr>
		</table>
		</div>
</div>
<?
		endforeach;
?>
<div class="vote-form-box-buttons vote-vote-footer">
	<span class="vote-form-box-button vote-form-box-button-first"><input type="submit" name="vote" value="<?=GetMessage("VOTE_SUBMIT_BUTTON")?>" /></span>
	<span class="vote-form-box-button vote-form-box-button-last"><input type="button" name="" onclick="window.location='<?
			?><?=CUtil::JSEscape($APPLICATION->GetCurPageParam("view_result=Y", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL", "view_result")))?>';" <?
			?>value="<?=GetMessage("VOTE_RESULTS")?>"/></span>
</div>
</form>

</div>