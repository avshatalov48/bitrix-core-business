<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php if (sizeof($arResult["ACCESS_ERRORS"])):?>

	<?php foreach ($arResult["ACCESS_ERRORS"] as $error):?>
		<p><font class="errortext"><?php echo $error?></font></p>
	<?php endforeach?>

<?php else:?>

	<?if (!empty($arResult["QUESTION"])):?>
	<?php if (is_array($arResult["INCORRECT_QUESTION"])):?>
		<div id="learn-test-message">
			<?php if ($arResult["INCORRECT_QUESTION"]["ID"] != $arResult["QUESTION"]["ID"]):?>
				<?=GetMessage("INCORRECT_QUESTION_NAME");?>: <?php echo $arResult["INCORRECT_QUESTION"]["NAME"]?><br />
			<?php endif?>
			<?=GetMessage("INCORRECT_QUESTION_MESSAGE");?>: <?php echo $arResult["INCORRECT_QUESTION"]["INCORRECT_MESSAGE"]?>
		</div>
	<?php endif?>
	<div class="learn-test-tabs"><?=GetMessage("LEARNING_QUESTION_TITLE");?>&nbsp;

	<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 2 && $arResult["NAV"]["PREV_NOANSWER"] != $arResult["NAV"]["PREV_QUESTION"] && $arResult["NAV"]["PREV_NOANSWER"]):?>

		<a class="previous" href="<?=$arResult["QBAR"][$arResult["NAV"]["PREV_NOANSWER"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_PREVIOUS_NOANSWER_TITLE")?>">&lsaquo;&lsaquo;</a>
		<a class="first" href="<?=$arResult["QBAR"][$arResult["NAV"]["PREV_QUESTION"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_PREVIOUS_TITLE")?>">&lsaquo;</a>

	<?elseif ($arResult["NAV"]["PREV_QUESTION"]):?>
		<a class="previous" href="<?=$arResult["QBAR"][$arResult["NAV"]["PREV_QUESTION"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_PREVIOUS_TITLE")?>">&lsaquo;</a>
	<?endif?>


	<?while($arResult["NAV"]["START_PAGE"] <= $arResult["NAV"]["END_PAGE"]):?>

		<?if ($arResult["NAV"]["START_PAGE"] == $arResult["NAV"]["PAGE_NUMBER"]):?>
			<a class="selected" title="<?=GetMessage("LEARNING_QBAR_CURRENT_TITLE")?>">&nbsp;<?=$arResult["NAV"]["START_PAGE"]?>&nbsp;</a>
		<?elseif ($arResult["QBAR"][$arResult["NAV"]["START_PAGE"]]["ANSWERED"] == "Y"):?>

			<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 2):?>
				<a href="<?=$arResult["QBAR"][$arResult["NAV"]["START_PAGE"]]["URL"]?>" class="answered" title="<?=GetMessage("LEARNING_QBAR_ANSWERED_TITLE")?>">&nbsp;<?=$arResult["NAV"]["START_PAGE"]?>&nbsp;</a>
			<?else:?>
				<a class="disabled" title="<?=GetMessage("LEARNING_QBAR_ANSWERED_TITLE")?>">&nbsp;<?=$arResult["NAV"]["START_PAGE"]?>&nbsp;</a>
			<?endif?>

		<?else:?>

			<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 0):?>
			<a title="<?=GetMessage("LEARNING_QBAR_NOANSWERED_TITLE")?>">&nbsp;<?=$arResult["NAV"]["START_PAGE"]?>&nbsp;</a>
			<?else:?>
			<a title="<?=GetMessage("LEARNING_QBAR_NOANSWERED_TITLE")?>" href="<?=$arResult["QBAR"][$arResult["NAV"]["START_PAGE"]]["URL"]?>">&nbsp;<?=$arResult["NAV"]["START_PAGE"]?>&nbsp;</a>
			<?endif?>

		<?endif;?>

	<?
	$arResult["NAV"]["START_PAGE"]++;
	endwhile;
	?>

	<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 2 && $arResult["NAV"]["NEXT_NOANSWER"] != $arResult["NAV"]["NEXT_QUESTION"] && $arResult["NAV"]["NEXT_NOANSWER"]):?>

		<a class="last" href="<?=$arResult["QBAR"][$arResult["NAV"]["NEXT_QUESTION"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_NEXT_TITLE")?>">&rsaquo;</a>
		<a class="next" href="<?=$arResult["QBAR"][$arResult["NAV"]["NEXT_NOANSWER"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_NEXT_NOANSWER_TITLE")?>">&rsaquo;&rsaquo;</a>

	<?elseif ($arResult["NAV"]["NEXT_QUESTION"]):?>
		<a class="next" href="<?=$arResult["QBAR"][$arResult["NAV"]["NEXT_QUESTION"]]["URL"]?>" title="<?=GetMessage("LEARNING_QBAR_NEXT_TITLE")?>">&rsaquo;</a>
	<?endif?>

	<?if ($arResult["TEST"]["TIME_LIMIT"]>0 && $arParams["SHOW_TIME_LIMIT"] == "Y"):?>
		<div id="learn-test-timer" title="<?=GetMessage("LEARNING_TEST_TIME_LIMIT");?>"><?=$arResult["SECONDS_TO_END_STRING"]?></div>
		<script type="text/javascript">
			var clockID = null; clockID = setTimeout("UpdateClock(<?=$arResult["SECONDS_TO_END"]?>)", 950);
		</script>
	<?endif?>

	</div>



	<div class="learn-question-cloud">
		<div class="learn-question-number"><?=GetMessage("LEARNING_QUESTION_TITLE")?><br />
			<?=$arResult["NAV"]["PAGE_NUMBER"]?> <?=GetMessage("LEARNING_QUESTION_OF");?> <?=$arResult["NAV"]["PAGE_COUNT"]?>
		</div>
		<div class="learn-question-name"><?=$arResult["QUESTION"]["NAME"]?>
			<?if ($arResult["QUESTION"]["DESCRIPTION"] <> ''):?>
				<br /><br /><?=$arResult["QUESTION"]["DESCRIPTION"]?>
			<?endif?>

			<?if ($arResult["QUESTION"]["FILE"] !== false):?>
				<br /><br /><img src="<?=$arResult["QUESTION"]["FILE"]["SRC"]?>" width="<?=$arResult["QUESTION"]["FILE"]["WIDTH"]?>" height="<?=$arResult["QUESTION"]["FILE"]["HEIGHT"]?>" />
			<?endif?>
		</div>
	</div>

	<br /><b><?php if ($arResult["QUESTION"]["QUESTION_TYPE"] == "T"):?><?=GetMessage("LEARNING_INPUT_ANSWER")?><?php else:?><?=GetMessage("LEARNING_CHOOSE_ANSWER")?><?php endif?>:</b>

	<form name="learn_test_answer" action="<?=$arResult["ACTION_PAGE"]?>" method="post">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="TEST_RESULT" value="<?=$arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["ID"]?>">
		<input type="hidden" name="<?=$arParams["PAGE_NUMBER_VARIABLE"]?>" value="<?=($arResult["NAV"]["PAGE_NUMBER"] + 1)?>">
		<input type="hidden" name="back_page" value="<?=$arResult["SAFE_REDIRECT_PAGE"]?>" />

		<?php if ($arResult["QUESTION"]["QUESTION_TYPE"] == "T"):?>
			<textarea name="answer" rows="5" cols="60"><?php echo (isset($arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["RESPONSE"]) ? implode(',', $arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["RESPONSE"]) : "")?></textarea><br />
		<?php elseif ($arResult["QUESTION"]["QUESTION_TYPE"] == "R"):?>
			<?php for ($i = 0; $i < sizeof($arResult["QUESTION"]["ANSWERS"]); $i++):?>
				<div class="sorting">
				<?php echo $i+1?>.
				<select name="answer[]">
					<option value="0">&nbsp;</option>
					<?php for ($j = 0; $j < sizeof($arResult["QUESTION"]["ANSWERS"]); $j++):?>
						<option value="<?php echo $arResult["QUESTION"]["ANSWERS"][$j]["ID"]?>" <?php echo ($arResult["QUESTION"]["ANSWERS"][$j]["ID"] == $arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["RESPONSE"][$i] ? " selected" : "")?>><?php echo $arResult["QUESTION"]["ANSWERS"][$j]["ANSWER"]?></option>
					<?php endfor?>
				</select>
				</div>
			<?php endfor?>
		<?php else:?>
			<?foreach($arResult["QUESTION"]["ANSWERS"] as $arAnswer):?>

				<?if ($arResult["QUESTION"]["QUESTION_TYPE"] == "M"):?>
					<label><input type="checkbox" name="answer[]" value="<?=$arAnswer["ID"]?>" <?if (in_array($arAnswer["ID"], $arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["RESPONSE"])):?>checked <?endif?>/>&nbsp;<?=$arAnswer["ANSWER"]?></label><br />
				<?elseif ($arResult["QUESTION"]["QUESTION_TYPE"] == "S"):?>
					<label><input type="radio" name="answer" value="<?=$arAnswer["ID"]?>" <?if (in_array($arAnswer["ID"], $arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["RESPONSE"])):?>checked <?endif?>/>&nbsp;<?=$arAnswer["ANSWER"]?></label><br />
				<?endif?>

			<?endforeach?>
		<?php endif?>

		<br />

		<?if ($arResult["TEST"]["PASSAGE_TYPE"] > 0 && $arResult["NAV"]["PREV_QUESTION"]):?>
			<input type="submit" name="previous" onClick="javascript:window.location='<?=CUtil::JSEscape($arResult["QBAR"][$arResult["NAV"]["PREV_QUESTION"]]["URL"])?>'; return false;" value="<?=GetMessage("LEARNING_BTN_PREVIOUS")?>" />
		<?endif?>

		<input type="submit" name="next" value="<?=GetMessage("LEARNING_BTN_NEXT")?>"<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 0):?> OnClick="return <?php if ($arResult["QUESTION"]["QUESTION_TYPE"] == "R"):?>checkSorting('<?=GetMessage("LEARNING_INVALID_SORT_CONFIRM")?>');<?php else:?>checkForEmpty('<?php if ($arResult["QUESTION"]["QUESTION_TYPE"] == "T"):?><?=GetMessage("LEARNING_EMPTY_RESPONSE_CONFIRM")?><?php else:?><?=GetMessage("LEARNING_NO_RESPONSE_CONFIRM")?><?php endif?>');<?php endif?>"<?endif?>>
		&nbsp;&nbsp;&nbsp;
		<?php
		{
			?>
			<input type="submit" name="finish" value="<?=GetMessage("LEARNING_BTN_FINISH")?>" onClick="return confirm('<?=GetMessage("LEARNING_BTN_CONFIRM_FINISH")?>')">
			<?php
		}
		?>
		<input type="hidden" name="ANSWERED" value="Y">

	</form>
	<?php if (intval($arResult["TEST"]["CURRENT_INDICATION"]) > 0):?>
		<div><?php if ($arResult["TEST"]["CURRENT_INDICATION_PERCENT"] == "Y"):?><?=GetMessage("LEARNING_CURRENT_RIGHT_COUNT")?> - <?php echo $arResult["COMPLETE_PERCENT"]?>%.<?php endif?><?php if ($arResult["TEST"]["CURRENT_INDICATION_MARK"] == "Y" && $arResult["CURRENT_MARK"]):?> <?=GetMessage("LEARNING_CURRENT_MARK")?> - <?php echo $arResult["CURRENT_MARK"]?>.<?php endif?></div>
	<?php endif?>

	<?elseif ($arResult["TEST_FINISHED"] === true):?>

		<?ShowError($arResult["ERROR_MESSAGE"]);?>
		<?php if ($arResult["ATTEMPT"]["COMPLETED"]):?>
			<?php if ($arResult["ATTEMPT"]["COMPLETED"] == "N"):?>
				<?php ShowError(GetMessage("LEARNING_TEST_FAILED"))?>
			<?php elseif ($arResult["ATTEMPT"]["COMPLETED"] == "Y"):?>
				<b><?php ShowNote(GetMessage("LEARNING_TEST_PASSED"));?></b>
			<?php endif?>
		<?php endif?>
		<?php if (intval($arResult["TEST"]["FINAL_INDICATION"]) > 0):?>
			<table class="learn-result-table data-table">
				<?php if ($arResult["TEST"]["FINAL_INDICATION_CORRECT_COUNT"] == "Y"):?>
					<tr>
						<th><?php echo GetMessage("LEARNING_RESULT_QUESTIONS_COUNT")?></th>
						<td><?php echo $arResult["ATTEMPT"]["QUESTIONS"]?></td>
					</tr>
					<tr>
						<th><?php echo GetMessage("LEARNING_RESULT_RIGHT_COUNT")?></th>
						<td><?php echo $arResult["ATTEMPT"]["CORRECT_COUNT"]?></td>
					</tr>
				<?php endif?>

				<?
				$percent = round($arResult["ATTEMPT"]["SCORE"] / $arResult["ATTEMPT"]["MAX_SCORE"] * 100, 2);
				?>

				<?php if ($arResult["TEST"]["FINAL_INDICATION_SCORE"] == "Y"):?>
				<tr>
					<th><?php echo GetMessage("LEARNING_RESULT_MAX_SCORE")?></th>
					<td><?php echo $arResult["ATTEMPT"]["MAX_SCORE"]?></td>
				</tr>
				<tr>
					<th><?php echo GetMessage("LEARNING_RESULT_SCORE")?></th>
					<td><?php echo $arResult["ATTEMPT"]["SCORE"]?> (<?=$percent?>%)</td>
				</tr>
				<?php endif?>
				<?php if (
					$arResult["ATTEMPT"]["MARK"] &&
					(
						$arResult["ATTEMPT"]["COMPLETED"] === "Y" ||
						(
							$arResult["ATTEMPT"]["COMPLETED"] === "N" &&
							$percent < $arResult["TEST"]["COMPLETED_SCORE"]
						)
					)
				):?>
					<?php if ($arResult["TEST"]["FINAL_INDICATION_MARK"] == "Y"):?>
						<tr>
							<th><?php echo GetMessage("LEARNING_RESULT_MARK")?></th>
							<td><?php echo $arResult["ATTEMPT"]["MARK"]?></td>
						</tr>
					<?php endif?>
					<?php if ($arResult["ATTEMPT"]["MESSAGE"] && $arResult["TEST"]["FINAL_INDICATION_MESSAGE"] == "Y"):?>
						<tr>
							<th><?php echo GetMessage("LEARNING_RESULT_MESSAGE")?></th>
							<td><?php echo $arResult["ATTEMPT"]["MESSAGE"]?></td>
						</tr>
					<?php endif?>
				<?php endif?>
			</table>
		<?php endif?>
		<?ShowNote(GetMessage("LEARNING_COMPLETED"));?>

		<?php if ($arResult["GRADEBOOK_URL"]):?>
		<a href="<?=$arResult["GRADEBOOK_URL"]?>"><?=GetMessage("LEARNING_PROFILE")?></a>
		<?php endif?>

	<?elseif ($arResult["ERROR_MESSAGE"] <> ''):?>

		<?ShowError($arResult["ERROR_MESSAGE"]);?>
		<br />
		<form name="learn_test_start" method="post" action="<?=$arResult["ACTION_PAGE"]?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="back_page" value="<?=$arResult["SAFE_REDIRECT_PAGE"]?>" />
		<input type="submit" name="next" value="<?=GetMessage("LEARNING_BTN_CONTINUE")?>">
		</form>

	<?else:?>

		<?=GetMessage("LEARNING_TEST_NAME")?>: <?=$arResult["TEST"]["NAME"];?><br />
		<?if ($arResult["TEST"]["DESCRIPTION"] <> ''):?>
			<?=$arResult["TEST"]["DESCRIPTION"]?><br />
		<?endif?>

		<?if ($arResult["TEST"]["ATTEMPT_LIMIT"] > 0):?>
			<?=GetMessage("LEARNING_TEST_ATTEMPT_LIMIT")?>: <?=$arResult["TEST"]["ATTEMPT_LIMIT"]?>
		<?else:?>
			<?=GetMessage("LEARNING_TEST_ATTEMPT_LIMIT")?>: <?=GetMessage("LEARNING_TEST_ATTEMPT_UNLIMITED")?>
		<?endif?>
		<br />

		<?if ($arResult["TEST"]["TIME_LIMIT"] > 0):?>
			<?=GetMessage("LEARNING_TEST_TIME_LIMIT")?>: <?=$arResult["TEST"]["TIME_LIMIT"]?> <?=GetMessage("LEARNING_TEST_TIME_LIMIT_MIN")?>
		<?else:?>
			<?=GetMessage("LEARNING_TEST_TIME_LIMIT")?>: <?=GetMessage("LEARNING_TEST_TIME_LIMIT_UNLIMITED")?>
		<?endif?>
		<br />

		<?=GetMessage("LEARNING_PASSAGE_TYPE")?>:
		<?if ($arResult["TEST"]["PASSAGE_TYPE"] == 2):?>
			<?=GetMessage("LEARNING_PASSAGE_FOLLOW_EDIT")?>
		<?elseif ($arResult["TEST"]["PASSAGE_TYPE"] == 1):?>
			<?=GetMessage("LEARNING_PASSAGE_FOLLOW_NO_EDIT")?>
		<?else:?>
			<?=GetMessage("LEARNING_PASSAGE_NO_FOLLOW_NO_EDIT")?>
		<?endif?>
		<br />

		<?if ($arResult["TEST"]["PREVIOUS_TEST_ID"] > 0 && $arResult["TEST"]["PREVIOUS_TEST_SCORE"] > 0 && $arResult["TEST"]["PREVIOUS_TEST_LINK"]):?>
			<?=str_replace(array("#TEST_LINK#", "#TEST_SCORE#"), array('"'.$arResult["TEST"]["PREVIOUS_TEST_LINK"].'"', $arResult["TEST"]["PREVIOUS_TEST_SCORE"]), GetMessage("LEARNING_PREV_TEST_REQUIRED"))?>
			<br />
		<?endif?>

		<br />
		<form name="learn_test_start" method="post" action="<?=$arResult["ACTION_PAGE"]?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="back_page" value="<?=$arResult["SAFE_REDIRECT_PAGE"]?>" />
		<input type="submit" name="next" value="<?=GetMessage("LEARNING_BTN_START")?>">
		</form>

	<?endif?>
<?php endif?>