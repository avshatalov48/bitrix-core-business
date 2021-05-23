<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (intval($arResult["ID"]) <= 0)
	return false; 
if (!function_exists("__vote_template_default_votes_ending"))
{
	function __vote_template_default_votes_ending($count)
	{
		$text = GetMessage("T_VOTES");
		$count = intval($count);
		$iCount = intval($count%100);
		
		if (!(10 < $iCount && $iCount < 20))
		{
			$count = intval($count % 10);
			if ($count == 1)
				$text = GetMessage("T_VOTE");
			elseif ($count > 1 && $count < 5)
				$text = GetMessage("T_VOTES_2");
		}
		
		return $text;
	}
}

CAjax::Init();
?><script src="<?=$this->__folder?>/script.js"></script><?
//Let's determine what value to display: rating or average ?
if($arParams["DISPLAY_AS_RATING"] == "vote_avg")
{
	if($arResult["PROPERTIES"]["vote_count"]["VALUE"])
		$DISPLAY_VALUE = round($arResult["PROPERTIES"]["vote_sum"]["VALUE"]/$arResult["PROPERTIES"]["vote_count"]["VALUE"], 2);
	else
		$DISPLAY_VALUE = 0;
}
else
	$DISPLAY_VALUE = $arResult["PROPERTIES"]["rating"]["VALUE"];
$arParams["IDENTIFICATOR"] = (empty($arParams["~IDENTIFICATOR"]) ? '' : $arParams["~IDENTIFICATOR"].'_');

?><div class="iblock-vote" id="vote_<?=$arParams["IDENTIFICATOR"]?><?echo $arResult["ID"]?>"><?
	?><table cellpadding="0" cellspacing="0" border="0" class="iblock-vote-starts" <?
		?><?=($arResult["VOTED"] ? 'title="'.GetMessage("T_RATING").': '.$DISPLAY_VALUE.'"' : "")?>><?
		?><tr><?
		if($arResult["VOTED"]):
			if($DISPLAY_VALUE):
				foreach($arResult["VOTE_NAMES"] as $i=>$name):
					if(round($DISPLAY_VALUE) > $i):
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star-voted"></div></th><?
					else:
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star-empty"></div></th><?
					endif; 
				endforeach; 
			else:
				foreach($arResult["VOTE_NAMES"] as $i=>$name):
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star"></div></th><?
				endforeach; 
			endif;
		else:
			if($DISPLAY_VALUE):
				foreach($arResult["VOTE_NAMES"] as $i=>$name):
					if(round($DISPLAY_VALUE) > $i):
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star-active star-voted" title="<?echo $name?>" <?
						?>onmouseover="if (voteScript) {voteScript.trace_vote(this, true);}" <?
						?>onmouseout="if (voteScript) {voteScript.trace_vote(this, false);}" <?
						?>onclick="if (voteScript) {voteScript.do_vote(this, 'vote_<?echo $arResult["ID"]?>', <?echo $arResult["AJAX_PARAMS"]?>);}"></div></th><?
					else:
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star-active star-empty" title="<?echo $name?>" <?
						?>onmouseover="if (voteScript) {voteScript.trace_vote(this, true);}" <?
						?>onmouseout="if (voteScript) {voteScript.trace_vote(this, false);}" <?
						?>onclick="if (voteScript) {voteScript.do_vote(this, 'vote_<?echo $arResult["ID"]?>', <?echo $arResult["AJAX_PARAMS"]?>);}"></div></th><?
					endif; 
				endforeach; 
			else:
				foreach($arResult["VOTE_NAMES"] as $i=>$name):
					?><th><div id="vote_<?echo $arResult["ID"]?>_<?echo $i?>" class="star-active star-empty" title="<?echo $name?>" <?
					?>onmouseover="if (voteScript) {voteScript.trace_vote(this, true);}" <?
					?>onmouseout="if (voteScript) {voteScript.trace_vote(this, false);}" <?
					?>onclick="if (voteScript) {voteScript.do_vote(this, 'vote_<?echo $arResult["ID"]?>', <?echo $arResult["AJAX_PARAMS"]?>);}"></div></th><?
				endforeach; 
			endif; 
		endif; 
	?><td class="vote-result" id="wait_vote_<?echo $arResult["ID"]?>"><?
	if ($arResult["PROPERTIES"]["vote_count"]["VALUE"] > 0):
		?><div class="vote-results"><?=$arResult["PROPERTIES"]["vote_count"]["VALUE"]?> <?=__vote_template_default_votes_ending($arResult["PROPERTIES"]["vote_count"]["VALUE"])?></div><?
	else: 
		?><div class="vote-results vote-no-results"><?=GetMessage("T_IBLOCK_VOTE_NO_RESULTS")?></div><?
	endif; 
?></td></tr></table></div>