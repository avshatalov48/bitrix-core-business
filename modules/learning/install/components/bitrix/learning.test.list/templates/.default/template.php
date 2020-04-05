<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?ShowError($arResult["ERROR_MESSAGE"]);?>

<?if ($arResult["TESTS_COUNT"] > 0):?>

	<?foreach ($arResult["TESTS"] as $arTest):?>

		<?=GetMessage("LEARNING_TEST_NAME")?>: <?=$arTest["NAME"];?><br />
		<?if (strlen($arTest["DESCRIPTION"]) > 0):?>
			<?=$arTest["DESCRIPTION"]?><br />
		<?endif?>

		<?if ($arTest["ATTEMPT_LIMIT"] > 0):?>
			<?=GetMessage("LEARNING_TEST_ATTEMPT_LIMIT")?>: <?=$arTest["ATTEMPT_LIMIT"]?>
		<?else:?>
			<?=GetMessage("LEARNING_TEST_ATTEMPT_LIMIT")?>: <?=GetMessage("LEARNING_TEST_ATTEMPT_UNLIMITED")?>
		<?endif?>
		<br />

		<?if ($arTest["TIME_LIMIT"] > 0):?>
			<?=GetMessage("LEARNING_TEST_TIME_LIMIT")?>: <?=$arTest["TIME_LIMIT"]?> <?=GetMessage("LEARNING_TEST_TIME_LIMIT_MIN")?>
		<?else:?>
			<?=GetMessage("LEARNING_TEST_TIME_LIMIT")?>: <?=GetMessage("LEARNING_TEST_TIME_LIMIT_UNLIMITED")?>
		<?endif?>
		<br />

		<?=GetMessage("LEARNING_PASSAGE_TYPE")?>:
		<?if ($arTest["PASSAGE_TYPE"] == 2):?>
			<?=GetMessage("LEARNING_PASSAGE_FOLLOW_EDIT")?>
		<?elseif ($arTest["PASSAGE_TYPE"] == 1):?>
			<?=GetMessage("LEARNING_PASSAGE_FOLLOW_NO_EDIT")?>
		<?else:?>
			<?=GetMessage("LEARNING_PASSAGE_NO_FOLLOW_NO_EDIT")?>
		<?endif?>
		<br />

		<?if ($arTest["PREVIOUS_TEST_ID"] > 0 && $arTest["PREVIOUS_TEST_SCORE"] > 0 && $arTest["PREVIOUS_TEST_LINK"]):?>
			<?=str_replace(array("#TEST_LINK#", "#TEST_SCORE#"), array('"'.$arTest["PREVIOUS_TEST_LINK"].'"', $arTest["PREVIOUS_TEST_SCORE"]), GetMessage("LEARNING_PREV_TEST_REQUIRED"))?>
			<br />
		<?endif?>

		<br />
		<form action="<?=$arTest["TEST_DETAIL_URL"]?>" method="post">
			<input type="hidden" name="COURSE_ID" value="<?=$arTest["COURSE_ID"]?>" />
			<input type="hidden" name="ID" value="<?=$arTest["ID"]?>" />
			<?if ($arTest["ATTEMPT"] === false):?>
				<input type="submit" name="next" value="<?=GetMessage("LEARNING_BTN_START")?>"<?php echo isset($arTest["PREVIOUS_NOT_COMPLETE"]) ? " disabled=\"1\"" : ""?> />
			<?else:?>
				<input type="submit" name="next" value="<?=GetMessage("LEARNING_BTN_CONTINUE")?>"<?php echo isset($arTest["PREVIOUS_NOT_COMPLETE"]) ? " disabled=\"1\"" : ""?> />
			<?endif?>
		</form>
		<div class="test-list-hr"></div>

	<?endforeach?>

	<?=$arResult["NAV_STRING"];?>

<?endif?>