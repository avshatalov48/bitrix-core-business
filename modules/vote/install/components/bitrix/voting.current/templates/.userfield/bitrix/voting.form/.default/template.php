<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$uid = $arParams["UID"];
$form = "vote-form-".$uid;

if (!empty($arResult["ERROR_MESSAGE"])):?>
<div class="vote-note-box vote-note-error">
	<div class="vote-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"])?></div>
</div>
<?endif;

if (empty($arResult["QUESTIONS"])):
	return true;
endif;
?>
<form action="<?=str_replace(array("view_form=Y", "view_form"), "", POST_FORM_ACTION_URI)?>" method="post" class="vote-form" name="<?=$form?>" id="<?=$form?>">
	<input type="hidden" name="vote" value="Y" />
	<input type="hidden" name="REVOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>" />
	<input type="hidden" name="PUBLIC_VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>" />
	<input type="hidden" name="VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>" />
	<?=bitrix_sessid_post()?>
	<ol class="bx-vote-question-list" id="vote-<?=$uid?>">
	<?foreach ($arResult["QUESTIONS"] as $arQuestion):?>
		<li id="question<?=$arQuestion["ID"]?>" <?if($arQuestion["REQUIRED"]=="Y"): ?> class="bx-vote-question-required"<? endif; ?>>
			<?if (!empty($arQuestion["IMAGE"]) && !empty($arQuestion["IMAGE"]["SRC"])): ?><div class="bx-vote-question-image"><img src="<?=$arQuestion["IMAGE"]["SRC"]?>" /></div><? endif; ?>
			<div class="bx-vote-question-title"><?=$arQuestion["QUESTION"]?></div>
			<table class="bx-vote-answer-list" cellspacing="0">
			<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
				<tr id="answer<?=$arAnswer["ID"]?>" class="bx-vote-answer-item">
					<td>
						<div class="bx-vote-bar"><?
			switch ($arAnswer["FIELD_TYPE"]):
				case 0://radio
				?><span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap"><?
					?><label class="bx-vote-block-input-wrap-inner" for="vote_radio_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?
						?><input type="radio" name="vote_radio_<?=$arAnswer["QUESTION_ID"]?>" <?
							?>id="vote_radio_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>" <?
							?>value="<?=$arAnswer["ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?> /><?
						?><span class="bx-vote-block-inp-substitute"></span><?
					?></label><?
					?><label for="vote_radio_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label><?
				?></span><?
				break;
				case 1://checkbox
					?><span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap"><?
						?><label class="bx-vote-block-input-wrap-inner" for="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?
							?><input <?=$value?> type="checkbox" name="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>[]" value="<?=$arAnswer["ID"]?>" <?
								?> id="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?> /><?
							?><span class="bx-vote-block-inp-substitute"></span><?
						?></label><?
						?><label for="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label><?
					?></span><?
				break;
				case 2://select
					?><span class="bx-vote-block-input-wrap bx-vote-block-dropdown-wrap"><?
						?><select name="vote_dropdown_<?=$arAnswer["QUESTION_ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?>><?
						foreach ($arAnswer["DROPDOWN"] as $arDropDown):
							?><option value="<?=$arDropDown["ID"]?>" <?=$arDropDown["~FIELD_PARAM"]?>><?=$arDropDown["MESSAGE"]?></option><?
						endforeach;
						?></select><?
					?></span><?
				break;
				case 3://multiselect
					?><span class="bx-vote-block-input-wrap bx-vote-block-multiselect-wrap"><?
						?><select name="vote_multiselect_<?=$arAnswer["QUESTION_ID"]?>[]" <?=$arAnswer["~FIELD_PARAM"]?> multiple="multiple"><?
						foreach ($arAnswer["MULTISELECT"] as $arMultiSelect):
							?><option value="<?=$arMultiSelect["ID"]?>" <?=$arMultiSelect["~FIELD_PARAM"]?>><?=$arMultiSelect["MESSAGE"]?></option><?
						endforeach;
						?></select><?
					?></span><?
				break;
				case 4://text field
					?><span class="bx-vote-block-input-wrap bx-vote-block-textfield-wrap"><?
						?><label for="vote_field_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label><?
						?><input type="text" name="vote_field_<?=$arAnswer["ID"]?>" id="vote_field_<?=$arAnswer["ID"]?>" <?
							?>value="<?=$arAnswer["~FIELD_TEXT"]?>" size="<?=$arAnswer["FIELD_WIDTH"]?>" <?=$arAnswer["~FIELD_PARAM"]?> /><?
					?></span><?
				break;
				case 5://memo
					?><span class="bx-vote-block-input-wrap bx-vote-block-memo-wrap"><?
						?><label for="vote_memo_<?=$arAnswer["ID"]?>"><?=$arAnswer["MESSAGE"]?></label><br /><?
						?><textarea name="vote_memo_<?=$arAnswer["ID"]?>" id="vote_memo_<?=$arAnswer["ID"]?>" <?
							?><?=$arAnswer["~FIELD_PARAM"]?> cols="<?=$arAnswer["FIELD_WIDTH"]?>" <?
							?>rows="<?=$arAnswer["FIELD_HEIGHT"]?>"><?=$arAnswer["~FIELD_TEXT"]?></textarea><?
					?></span><?
				break;
			endswitch;?>
							<div class="bx-vote-result-bar"></div>
						</div>
					</td>
					<td>
						<span class="bx-vote-voted-users-wrap"><?
							?><a href="#" class="bx-vote-voted-users" onclick="return false;"></a></span>
					</td>
					<td><span class="bx-vote-data-percent"></span></td>
				</tr>
				<?endforeach;?>
			</table>
		</li>
	<?endforeach;?>
	</ol><?
if (isset($arResult["CAPTCHA_CODE"]))
{ ?>
<div class="bx-vote-captcha">
	<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>" />
	<span class="vote-captcha-image">
		<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" />
	</span>
	<span class="bx-vote-captcha-input">
		<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?></label>
		<input type="text" size="20" name="captcha_word" id="captcha_word" />
	</span>
</div>
<? } // CAPTCHA_CODE ?>
	<div class="vote-buttons" style="display:none;">
		<input type="submit" value="<?=GetMessage("VOTE_SUBMIT_BUTTON")?>" />
	</div>
</form>
<?
$this->__component->arParams["RETURN"] = array();
?>