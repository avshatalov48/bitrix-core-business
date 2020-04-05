<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/voting.current/templates/.default/style.css');
endif;

if (strlen($arResult["NAV_STRING"]) > 0):
?>
<div class="vote-navigation-box vote-navigation-top">
	<div class="vote-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="vote-clear-float"></div>
</div>
<?
endif;

?>
<ol class="vote-items-list voting-list-box">
<?
$iCount = 0;
foreach ($arResult["VOTES"] as $arVote):
	$iCount++;
?>
	<li class="vote-item-vote <?=($iCount == 1 ? "vote-item-vote-first " : "")?><?
				?><?=($iCount == count($arResult["VOTES"]) ? "vote-item-vote-last " : "")?><?
				?><?=($iCount%2 == 1 ? "vote-item-vote-odd " : "vote-item-vote-even ")?><?
				?><?=($arVote["LAMP"]=="green" ? "vote-item-vote-active " : "")?><?
				?><?=($arVote["LAMP"]=="red" ? "vote-item-vote-disable " : "")?><?
				?>">
		<div class="vote-item-header">
			<div class="vote-item-links float-links">
<?
	if ($arVote["LAMP"]=="green" && $arVote["MAX_PERMISSION"] >= 2 && $arVote["USER_ALREADY_VOTE"] != "Y"):
?>
				[&nbsp;<a href="<?=$arVote["VOTE_FORM_URL"]?>"><?=GetMessage("VOTE_VOTING")?></a>&nbsp;]
<?
	endif;
		
	if ($arVote["MAX_PERMISSION"] >= 1):
?>
				&nbsp;&nbsp;[&nbsp;<a href="<?=$arVote["VOTE_RESULT_URL"]?>"><?=GetMessage("VOTE_RESULTS")?></a>&nbsp;]
<?
	endif;
?>
			</div>
			
<?
	if (strlen($arVote["TITLE"]) > 0):
?>
			<span class="vote-item-title"><?=$arVote["TITLE"];?></span>
<?
		if ($arVote["LAMP"]=="green"):
/*?>
			<span class="vote-item-lamp vote-item-lamp-green">[ <span class="active"><?=GetMessage("VOTE_IS_ACTIVE_SMALL")?></span> ]</span>
<?*/
		elseif ($arVote["LAMP"]=="red"):
?>
			<span class="vote-item-lamp vote-item-lamp-red">[ <span class="disable"><?=GetMessage("VOTE_IS_NOT_ACTIVE_SMALL")?></span> ]</span>
<?
		endif;
	endif;
?>
			<div class="vote-clear-float"></div>
		</div>
<?
	
	if ($arVote["DATE_START"] || ($arVote["DATE_END"] && $arVote["DATE_END"] != "31.12.2030 23:59:59")):
?>
		<div class="vote-item-date">
<?
		if ($arVote["DATE_START"]):
?>
			<span class="vote-item-date-start"><?=FormatDate($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), MakeTimeStamp($arVote["DATE_START"]))?></span>
<?

		endif;
		if ($arVote["DATE_END"] && $arVote["DATE_END"]!="31.12.2030 23:59:59"):
			if ($arVote["DATE_START"]):
?>
			<span class="vote-item-date-sep"> - </span>
<?
			endif;
?>
			<span class="vote-item-date-end"><?=FormatDate($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), MakeTimeStamp($arVote["DATE_END"]))?></span>
<?
		endif;
?>
		</div> 
<?
	endif;
?>
		<div class="vote-item-counter"><span><?=GetMessage("VOTE_VOTES")?>:</span> <?=$arVote["COUNTER"]?></div>
<?
	if (strlen($arVote["TITLE"]) <= 0):
		if ($arVote["LAMP"]=="green"):
?>
		<div class="vote-item-lamp vote-item-lamp-green"><span class="active"><?=GetMessage("VOTE_IS_ACTIVE")?></span></div>
<?
		elseif ($arVote["LAMP"]=="red"):
?>
		<div class="vote-item-lamp vote-item-lamp-red"><span class="disable"><?=GetMessage("VOTE_IS_NOT_ACTIVE")?></span></div>
<?
		endif;
	endif;
	
	if ($arVote["IMAGE"] !== false || !empty($arVote["DESCRIPTION"])):
?>
		<div class="vote-item-footer">
<?
		if ($arVote["IMAGE"] !== false):
?>
			<div class="vote-item-image">
				<img src="<?=$arVote["IMAGE"]["SRC"]?>" width="<?=$arVote["IMAGE"]["WIDTH"]?>" height="<?=$arVote["IMAGE"]["HEIGHT"]?>" border="0" />
			</div>
<?
		endif;
	
		if (!empty($arVote["DESCRIPTION"])):
?>
			<div class="vote-item-description"><?=$arVote["DESCRIPTION"];?></div>
<?
		endif
?>
			<div class="vote-clear-float"></div>
		</div>
<?
	endif;
?>
	</li>
<?
endforeach;
?>
</ol>
<?

if (strlen($arResult["NAV_STRING"]) > 0):
?>
<div class="vote-navigation-box vote-navigation-bottom">
	<div class="vote-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="vote-clear-float"></div>
</div>
<?
endif;
?>