<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table class="learn-gradebook-table data-table">
	<tr>
		<th><?=GetMessage("LEARNING_PROFILE_COURSE")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_TEST")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_LAST_SCORE")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_LAST_RESULT")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_BEST_SCORE")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_RESULT")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_ATTEMPTS")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_ACTION")?></th>
	</tr>

<?if (!empty($arResult["RECORDS"])):?>

<?php
$strQuickInfoPrefix = $strQuickInfoPrefix = '';
foreach($arResult["RECORDS"] as $arGradebook):
	if (($arGradebook['APPROVED'] === 'N') && ($arGradebook["COMPLETED"]!="Y"))
	{
		$strQuickInfoPrefix = ' <span href="javascript:void(0);" 
			style="text-decoration:none; border-bottom:1px dashed blue;"
			title="' 
			. GetMessage('LEARNING_TEST_CHECKED_MANUALLY_SO_NOT_ALL_RESULTS_CAN_BE_ACTUAL') 
			. '">';

		$strQuickInfoPostfix = '</span>';
	}
	?>
	<tr>
		<td><a href="<?=$arGradebook["COURSE_DETAIL_URL"]?>"><?=$arGradebook["COURSE_NAME"]?></a></td>
		<td><?=$arGradebook["TEST_NAME"]?></td>
		<td><?php echo $strQuickInfoPrefix . $arResult['LAST_TEST_INFO'][$arGradebook['TEST_ID']]['LAST_SCORE'] . $strQuickInfoPostfix; ?></td>
		<td><?php
			if ($arResult['LAST_TEST_INFO'][$arGradebook['TEST_ID']]['LAST_COMPLETED'] === 'Y')
				echo GetMessage("LEARNING_PROFILE_YES");
			else
				echo $strQuickInfoPrefix . GetMessage("LEARNING_PROFILE_NO") . $strQuickInfoPostfix;
			?></td>
		<td><?=$arGradebook["RESULT"]?><?=(intval($arGradebook["MAX_RESULT"]) > 0 ? " / ".intval($arGradebook["MAX_RESULT"]) : "")?></td>
		<td><?php
			if ($arGradebook["COMPLETED"]=="Y")
				echo GetMessage("LEARNING_PROFILE_YES");
			else
				echo $strQuickInfoPrefix . GetMessage("LEARNING_PROFILE_NO") . $strQuickInfoPostfix;

			?> <?php if ($arGradebook["MARK"]):?>(<?php echo GetMessage("LEARNING_PROFILE_MARK")?>: <?php echo $arGradebook["MARK"]?>)<?php endif?></td>
		<td>
			<a title="<?=GetMessage("LEARNING_PROFILE_TEST_DETAIL")?>" href="<?=$arGradebook["ATTEMPT_DETAIL_URL"]?>"><?=$arGradebook["ATTEMPTS"]?></a>
			<?if ($arGradebook["ATTEMPT_LIMIT"]>0):?>
				&nbsp;/&nbsp;<?=$arGradebook["ATTEMPT_LIMIT"]?>
			<?endif?>
		</td>
		<td><a href="<?=$arGradebook["TEST_DETAIL_URL"]?>"><?=GetMessage("LEARNING_PROFILE_TRY")?></a></td>
	</tr>
<?endforeach?>

<?else:?>
	<tr>
		<td colspan="8">-&nbsp;<?=GetMessage("LEARNING_PROFILE_NO_DATA")?>&nbsp;-</td>
	</tr>
<?endif?>
</table>

<?if (!empty($arResult["ATTEMPTS"])):?>

<br /><b><?=GetMessage("LEARNING_ATTEMPTS_TITLE")?></b><br /><br />

<table class="learn-gradebook-table data-table">
	<tr>
		<th><?=GetMessage("LEARNING_PROFILE_DATE_END")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_TIME_DURATION")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_QUESTIONS")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_SCORE")?></th>
		<th><?=GetMessage("LEARNING_PROFILE_RESULT")?></th>
		<?php if ($arResult["ATTEMPTS"][0]["MARK"]):?>
			<th><?=GetMessage("LEARNING_PROFILE_MARK")?></th>
		<?php endif?>
	</tr>

<?foreach ($arResult["ATTEMPTS"] as $arAttempt):?>
	<tr>
		<?if (strlen($arAttempt["DATE_END"])>0):?>
		<td><?=$arAttempt["DATE_END"]?></td>
		<td><?=CCourse::TimeToStr((MakeTimeStamp($arAttempt["DATE_END"]) - MakeTimeStamp($arAttempt["DATE_START"])));?></td>
		<?else:?>
		<td><?=$arAttempt["DATE_START"]?></td>
		<td><?=GetMessage("LEARNING_ATTEMPT_NOT_FINISHED")?></td>
		<?endif?>
		<td><?=$arAttempt["QUESTIONS"]?></td>
		<td><?=$arAttempt["SCORE"]?><?=(intval($arAttempt["MAX_SCORE"]) > 0 ? " / ".intval($arAttempt["MAX_SCORE"]) : "")?></td>
		<td><?=$arAttempt["COMPLETED"]=="Y"?GetMessage("LEARNING_PROFILE_YES"):GetMessage("LEARNING_PROFILE_NO")?></td>
		<?php if ($arAttempt["MARK"]):?><td><?php echo $arAttempt["MARK"]?></td><?php endif?>
	</tr>
<?endforeach?>

</table>

<br />
<a href="<?=$arResult["CURRENT_PAGE"]?>"><?=GetMessage("LEARNING_BACK_TO_GRADEBOOK")?></a>
<?endif;?>
