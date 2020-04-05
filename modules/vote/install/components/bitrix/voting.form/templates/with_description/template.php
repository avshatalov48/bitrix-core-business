<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?=ShowNote($arResult["OK_MESSAGE"]);?>

<?if (!empty($arResult["VOTE"])):?>

<div class="voting-form-box">

	<?if (strlen($arResult["VOTE"]["TITLE"])>0) : ?>
		<b><?echo $arResult["VOTE"]["TITLE"];?></b><br />
	<?endif;?>

	<?if ($arResult["VOTE"]["DATE_START"]):?>
		<br /><?=GetMessage("VOTE_START_DATE")?>:&nbsp;<?echo $arResult["VOTE"]["DATE_START"]?>
	<?endif;?>

	<?if ($arResult["VOTE"]["DATE_END"] && $arResult["VOTE"]["DATE_END"]!="31.12.2030 23:59:59"):?>
			<br /><?=GetMessage("VOTE_END_DATE")?>:&nbsp;<?=$arResult["VOTE"]["DATE_END"]?>
	<?endif;?>

	<br /><?=GetMessage("VOTE_VOTES")?>:&nbsp;<?=$arResult["VOTE"]["COUNTER"]?>

	<?if ($arResult["VOTE"]["LAMP"]=="green"):?>
		<br /><span class="active"><?=GetMessage("VOTE_IS_ACTIVE")?></span>
	<?elseif ($arResult["VOTE"]["LAMP"]=="red"):?>
		<br /><span class="disable"><?=GetMessage("VOTE_IS_NOT_ACTIVE")?></span>
	<?endif;?>

	<br /><br />

	<?if ($arResult["VOTE"]["IMAGE"] !== false):?>
		<img src="<?=$arResult["VOTE"]["IMAGE"]["SRC"]?>" width="<?=$arResult["VOTE"]["IMAGE"]["WIDTH"]?>" height="<?=$arResult["VOTE"]["IMAGE"]["HEIGHT"]?>" hspace="3" vspace="3" align="left" border="0" />
		<?=$arResult["VOTE"]["DESCRIPTION"];?>
		<br clear="left" />
	<?else:?>
		<?=$arResult["VOTE"]["DESCRIPTION"];?>
	<?endif?>

	<?if (!empty($arResult["QUESTIONS"])):?>

		<form action="<?=POST_FORM_ACTION_URI?>" method="post">
		<input type="hidden" name="vote" value="Y">
		<input type="hidden" name="PUBLIC_VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>">
		<input type="hidden" name="VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>">
		<?=bitrix_sessid_post()?>

		<?foreach ($arResult["QUESTIONS"] as $arQuestion):?>

			<?if ($arQuestion["IMAGE"] !== false):?>
				<img src="<?=$arQuestion["IMAGE"]["SRC"]?>" width="30" height="30" />
			<?endif?>

			<b><?=$arQuestion["QUESTION"]?><?if($arQuestion["REQUIRED"]=="Y"){echo "<span class='starrequired'>*</span>";}?></b><br /><br />

			<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
				<?
				switch ($arAnswer["FIELD_TYPE"]):
					case 0://radio
						$value=(isset($_REQUEST['vote_radio_'.$arAnswer["QUESTION_ID"]]) && 
							$_REQUEST['vote_radio_'.$arAnswer["QUESTION_ID"]] == $arAnswer["ID"]) ? 'checked="checked"' : '';
					break;
					case 1://checkbox
						$value=(isset($_REQUEST['vote_checkbox_'.$arAnswer["QUESTION_ID"]]) && 
							array_search($arAnswer["ID"],$_REQUEST['vote_checkbox_'.$arAnswer["QUESTION_ID"]])!==false) ? 'checked="checked"' : '';
					break;
					case 2://select
						$value=(isset($_REQUEST['vote_dropdown_'.$arAnswer["QUESTION_ID"]])) ? $_REQUEST['vote_dropdown_'.$arAnswer["QUESTION_ID"]] : false;
					break;
					case 3://multiselect
						$value=(isset($_REQUEST['vote_multiselect_'.$arAnswer["QUESTION_ID"]])) ? $_REQUEST['vote_multiselect_'.$arAnswer["QUESTION_ID"]] : array();
					break;
					case 4://text field
						$value = isset($_REQUEST['vote_field_'.$arAnswer["ID"]]) ? htmlspecialcharsbx($_REQUEST['vote_field_'.$arAnswer["ID"]]) : '';
					break;
					case 5://memo
						$value = isset($_REQUEST['vote_memo_'.$arAnswer["ID"]]) ?  htmlspecialcharsbx($_REQUEST['vote_memo_'.$arAnswer["ID"]]) : '';
					break;
				endswitch;
				?>
				<?switch ($arAnswer["FIELD_TYPE"]):
					case 0://radio?>
						<label><input <?=$value?> type="radio" name="vote_radio_<?=$arAnswer["QUESTION_ID"]?>" value="<?=$arAnswer["ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?> />&nbsp;<?=$arAnswer["MESSAGE"]?></label>
						<br />
					<?break?>

					<?case 1://checkbox?>
						<label><input <?=$value?> type="checkbox" name="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>[]" value="<?=$arAnswer["ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?> />&nbsp;<?=$arAnswer["MESSAGE"]?></label>
						<br />
					<?break?>

					<?case 2://dropdown?>
						<select name="vote_dropdown_<?=$arAnswer["QUESTION_ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?>>
							<option value=""><?=GetMessage("VOTE_DROPDOWN_SET")?></option>
						<?foreach ($arAnswer["DROPDOWN"] as $arDropDown):?>
							<option value="<?=$arDropDown["ID"]?>" <?=($arDropDown["ID"] === $value)?'selected="selected"':''?>><?=$arDropDown["MESSAGE"]?></option>
						<?endforeach?>
						</select><br />
					<?break?>

					<?case 3://multiselect?>
						<select name="vote_multiselect_<?=$arAnswer["QUESTION_ID"]?>[]" <?=$arAnswer["~FIELD_PARAM"]?> multiple="multiple">
						<?foreach ($arAnswer["MULTISELECT"] as $arMultiSelect):?>
							<option value="<?=$arMultiSelect["ID"]?>" <?=(array_search($arMultiSelect["ID"], $value)!==false)?'selected="selected"':''?>><?=$arMultiSelect["MESSAGE"]?></option>
						<?endforeach?>
						</select><br />
					<?break?>

					<?case 4://text field?>
						<label><?if (strlen(trim($arAnswer["MESSAGE"]))>0):?>
							<?=$arAnswer["MESSAGE"]?><br />
						<?endif?>
						<input type="text" name="vote_field_<?=$arAnswer["ID"]?>" value="<?=$value?>" size="<?=$arAnswer["FIELD_WIDTH"]?>" <?=$arAnswer["~FIELD_PARAM"]?> /></label>
						<br />
					<?break?>

					<?case 5://memo?>
						<label><?if (strlen(trim($arAnswer["MESSAGE"]))>0):?>
							<?=$arAnswer["MESSAGE"]?><br />
						<?endif?>
						<textarea name="vote_memo_<?=$arAnswer["ID"]?>" <?=$arAnswer["~FIELD_PARAM"]?> cols="<?=$arAnswer["FIELD_WIDTH"]?>" rows="<?=$arAnswer["FIELD_HEIGHT"]?>"><?=$value?></textarea></label>
						<br />
					<?break?>

				<?endswitch?>

			<?endforeach?>
			<br />
		<?endforeach?>

		<? if (isset($arResult["CAPTCHA_CODE"])):  ?>
		<div class="vote-item-header">
			<div class="vote-item-title vote-item-question"><?=GetMessage("F_CAPTCHA_TITLE")?></div>
			<div class="vote-clear-float"></div>
		</div>
		<div class="vote-form-captcha">
			<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
			<div class="vote-reply-field-captcha-image">
				<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
			</div>
			<div class="vote-reply-field-captcha-label">
				<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class='starrequired'>*</span></label><br />
				<input type="text" size="20" name="captcha_word" autocomplete="off" />
			</div>
		</div>
		<? endif // CAPTCHA_CODE ?>
		
		<input type="submit" name="vote" value="<?=GetMessage("VOTE_SUBMIT_BUTTON")?>">&nbsp;&nbsp;
		<input type="reset" onclick="return resetForm(this.form, event)" value="<?=GetMessage("VOTE_RESET")?>">
		</form>
	<?endif?>

</div>
<?endif?>
