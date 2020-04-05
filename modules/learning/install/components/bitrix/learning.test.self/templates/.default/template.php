<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?ShowError($arResult["ERROR_MESSAGE"]);?>

<?if ($arResult["QUESTIONS_COUNT"] > 0):?>


<div class="learn-question-tabs">
	<?=GetMessage("LEARNING_QUESTION_S");?>&nbsp;
	<?for ($tabIndex = 1; $tabIndex <= $arResult["QUESTIONS_COUNT"]; $tabIndex++):?>
		<span class="learn-tab" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.SelectTab(<?=$tabIndex?>)" id="learn_tab_<?=$arResult["LESSON"]["ID"]?>_<?=$tabIndex?>">&nbsp;<?=$tabIndex?>&nbsp;</span>
	<?endfor?>
</div>

<br />

<?foreach ($arResult["QUESTIONS"] as $index => $arQuestion):?>
<div id="learn_question_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>" style="display:none;">
	<div class="learn-question-cloud">
		<div class="learn-question-number"><?=GetMessage("LEARNING_QUESTION_S")?><br /><?=($index+1)?> <?=GetMessage("LEARNING_QUESTION_FROM");?> <?=$arResult["QUESTIONS_COUNT"]?></div>
		<div class="learn-question-name"><?=$arQuestion["NAME"]?>
		<?if (strlen($arQuestion["DESCRIPTION"]) > 0):?>
			<br /><br /><?=$arQuestion["DESCRIPTION"]?>
		<?endif?>
		<?if ($arQuestion["FILE"] !== false):?>
			<br /><br /><img src="<?=$arQuestion["FILE"]["SRC"]?>" width="<?=$arQuestion["FILE"]["WIDTH"]?>" height="<?=$arQuestion["FILE"]["HEIGHT"]?>" />
		<?endif?>
		</div>
		<div id="INCORRECT_MESSAGE_FOR_QUESTION_<?php echo (int) $arQuestion['ID']; ?>" style="display:none; color:red;"><?php
			if (strlen($arQuestion['INCORRECT_MESSAGE']) > 0)
				echo GetMessage('INCORRECT_QUESTION_MESSAGE') . ': ' . $arQuestion['INCORRECT_MESSAGE'];
		?></div>
	</div>

	<br /><strong><?php if ($arQuestion["QUESTION_TYPE"] == "T"):?><?=GetMessage("LEARNING_INPUT_ANSWER")?><?php else:?><?=GetMessage("LEARNING_SELECT_ANSWER");?><?php endif?>:</strong>

	<form name="form_self_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>" onSubmit="return false;" action="">
		<input type="hidden" name="QUESTION_TYPE" id="question_type_<?php echo (int) $arQuestion['ID']; ?>" value="<?php echo $arQuestion["QUESTION_TYPE"]?>" />
		<?php if ($arQuestion["QUESTION_TYPE"] == "R"):?>
			<?php for ($i = 0; $i < sizeof($arQuestion["ANSWERS"]); $i++):?>
				<div class="learn-answer" id="correct_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>_<?=$i?>"></div>
				<div class="sorting">
				<?php echo $i+1?>.
				<select name="answer[<?php echo $arQuestion["ANSWERS_ORIGINAL"][$i]["ID"]?>]" onChange="LearnTab_<?=$arResult["LESSON"]["ID"]?>.OnChangeAnswer(<?php echo (int) $arQuestion['ID']; ?>);">
					<option value="0">&nbsp;</option>
					<?php for ($j = 0; $j < sizeof($arQuestion["ANSWERS"]); $j++):?>
						<option value="<?php echo $arQuestion["ANSWERS"][$j]["ID"]?>"><?php echo $arQuestion["ANSWERS"][$j]["ANSWER"]?></option>
					<?php endfor?>
				</select>
				</div>
			<?php endfor?>
		<?php else:?>
			<?$answerIndex = 0; foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
				<?if ($arQuestion["QUESTION_TYPE"] == "M"):?>
					<div class="learn-answer" id="correct_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>_<?=$answerIndex?>"></div>
					<label><input type="checkbox" name="answer[]" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.OnChangeAnswer(<?php echo (int) $arQuestion['ID']; ?>);" />&nbsp;<?=$arAnswer["ANSWER"]?></label>
				<?else:?>
					<div class="learn-answer" id="correct_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>_<?=$answerIndex?>"></div>
					<label><input type="radio" name="answer" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.OnChangeAnswer(<?php echo (int) $arQuestion['ID']; ?>);" />&nbsp;<?=$arAnswer["ANSWER"]?></label>
				<?endif?>
				<input type="hidden" name="right_<?=$answerIndex?>" value="<?=$arAnswer["CORRECT"]?>" /><br clear="all" />
			<?$answerIndex++;endforeach?>
		<?php endif;

	$jsIncorrectBlockId = 'null';
	if (strlen($arQuestion['INCORRECT_MESSAGE']) > 0)
		$jsIncorrectBlockId = "'INCORRECT_MESSAGE_FOR_QUESTION_" . (string) ((int) $arQuestion['ID']) . "'";
	?>
	<p><input type="submit" name="submit" disabled="disabled" 
		value="<?=GetMessage("LEARNING_SUBMIT_ANSWER");?>" 
		onclick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.CheckAnswer(<?php echo $jsIncorrectBlockId; ?>);"></p>
	</form>
</div>
<?endforeach?>

<script type="text/javascript">var LearnTab_<?=$arResult["LESSON"]["ID"]?> = new LearnTabs(<?=$arResult["LESSON"]["ID"]?>, 1);</script>
<noscript><?=GetMessage("LEARNING_ENABLE_JAVASCRIPT");?></noscript>

<?endif?>