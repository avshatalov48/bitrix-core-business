<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult["VOTE"]) || empty($arResult["QUESTIONS"])):
	return true;
endif;

/********************************************************************
				Input params
********************************************************************/
/********************************************************************
				/Input params
********************************************************************/
?>
<div class="hr-title"><h2><?=$arParams["TITLE_BLOCK"];?></h2></div>
<div class="voting-form-box">
<?

$iCount = 0;
foreach ($arResult["QUESTIONS"] as $arQuestion):
	$iCount++;
?>
	<div class="vote-question-item">
		<div class="vote-item-title" ><h2><?=$arQuestion["QUESTION"]?></h2></div>
		<div class="vote-answers-list">
		<table class="vote-answers-list" cellspacing="0" cellpadding="0" border="0">
<?
	$i = 0;
	foreach ($arQuestion["ANSWERS"] as $arAnswer):
		$i++;
?>
		<tr>
			<td class="vote-answer-name"><?=$i?>. <?=$arAnswer["MESSAGE"]?></td>
			<td class="vote-answer-percent"><?=round($arAnswer["PERCENT"])?>%</td>
			<td class="vote-answer-counter">(<?=$arAnswer["COUNTER"]?>)</td>
		</tr>
<?
	endforeach; 
?>
		<tr>
			<td class="vote-answer-name"></td>
			<td class="vote-answer-percent"></td>
			<td class="vote-answer-counter"></td>
		</tr>
		</table>
		</div>
	</div>
<?
endforeach; 
?>

<?
if ($arParams["CAN_VOTE"] == "Y"):
?>
<div class="vote-form-box-buttons vote-vote-footer">
	<span class="vote-form-box-button vote-form-box-button-first vote-form-box-button-last"><?
		?><input type="button" name="vote" onclick="window.location='<?
			?><?=CUtil::JSEscape($APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL", "view_result")))?>';" <?
			?>value="<?=GetMessage("VOTE_BACK")?>" /></span>
</div>
<?	
endif;
?>
</div>