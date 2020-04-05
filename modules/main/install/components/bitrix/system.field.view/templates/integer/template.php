<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$first = true;
foreach ($arResult["VALUE"] as $res):
	if (!$first):
		?><span class="fields separator"></span><?
	else:
		$first = false;	
	endif;

	if (StrLen($arParams['arUserField']['PROPERTY_VALUE_LINK']) > 0)
		$res = '<a href="'.str_replace('#VALUE#', urlencode($res), $arParams['arUserField']['PROPERTY_VALUE_LINK']).'">'.$res.'</a>';
?><span class="fields integer"><?=$res?></span><?
endforeach;?>
