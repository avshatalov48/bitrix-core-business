<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bFirst = true;

foreach ($arResult["VALUE"] as $ID => $res):
	$res = CUser::FormatName(CSite::GetNameFormat(false), $res, true, true);
	if ($arParams['arUserField']['SETTINGS']['USER_URL'])
		$res = '<a href="'.str_replace(array('#ID#', '#USER_ID#'), urlencode($ID), $arParams['arUserField']['SETTINGS']['USER_URL']).'">'.$res.'</a>';
	elseif (StrLen($arParams['arUserField']['PROPERTY_VALUE_LINK']) > 0)
		$res = '<a href="'.str_replace('#VALUE#', urlencode($ID), $arParams['arUserField']['PROPERTY_VALUE_LINK']).'">'.$res.'</a>';

	if (!$bFirst):
		?>, <?
	else:
		$bFirst = false;
	endif;
	?><span class="fields enumeration"><?=$res?></span><?
endforeach;
?>