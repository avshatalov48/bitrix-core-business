<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$first = true;
foreach ($arResult["VALUE"] as $res):
	if (!$first):
		?><span class="fields separator"></span><?
	else:
		$first = false;	
	endif;

?><span class="fields boolean"><?=($res? GetMessage("MAIN_YES"):GetMessage("MAIN_NO"))?></span><?
endforeach;?>
