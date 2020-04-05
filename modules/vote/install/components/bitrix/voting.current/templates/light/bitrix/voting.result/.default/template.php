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
<ol class="vote-items-list vote-question-list vote-question-list-main-page">
<?

$iCount = 0;
foreach ($arResult["QUESTIONS"] as $arQuestion):
	$iCount++;
?>
	<li class="vote-question-item <?=($iCount == 1 ? "vote-item-vote-first " : "")?><?
				?><?=($iCount == count($arResult["QUESTIONS"]) ? "vote-item-vote-last " : "")?><?
				?><?=($iCount%2 == 1 ? "vote-item-vote-odd " : "vote-item-vote-even ")?><?
				?>">
		<div class="vote-item-title vote-item-question"><?=$arQuestion["QUESTION"]?></div>
		
		<ol class="vote-items-list vote-answers-list">
<?
	foreach ($arQuestion["ANSWERS"] as $arAnswer):
?>
			<li class="vote-answer-item">
				<div class="vote-answer-item">
					<div class="vote-answer-item-bar"><div class="vote-answer-item-bar-inner" <?
						?>style="width:<?=($arAnswer["BAR_PERCENT"] > 0 ? $arAnswer["BAR_PERCENT"] : 2)?>%">&nbsp;</div></div>
					<div class="vote-answer-item-title"><div class="vote-answer-item-title-inner"><?
						?><?=$arAnswer["MESSAGE"]?> - <?=$arAnswer["COUNTER"]?> (<?=$arAnswer["PERCENT"]?>%)</div></div>
				</div>
			</li>
<?
	endforeach; 
?>
		</ol>
	</li>
<?
endforeach; 
?>
</ol>