<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (count($arResult['FORMS']) <= 0)
{
	ShowNote(GetMessage('FRLM_NO_RESULTS'));
	return;
}
?>
<div class="bx-mylist-layout">
<?
//echo '<pre>'; print_r($arResult); echo '</pre>';
foreach ($arResult['FORMS'] as $FORM_ID => $arForm):
?>
	<div class="bx-mylist-form" id="bx_mylist_form_<?echo $FORM_ID?>">
		<div class="bx-mylist-form-info">
			<b><?echo $arForm['NAME']?></b>
		</div>
		<ul class="bx-mylist-form-results">
<?
	$i = 0;
	foreach ($arResult['RESULTS'][$FORM_ID] as $arRes):
?>
			<li class="bx-mylist-row-<?echo ($i++) % 2;?>"><div class="bx-mylist-form-status"><span class="<?=$arRes["STATUS_CSS"]?>"><?=$arRes["STATUS_TITLE"]?></span></div> <div class="bx-mylist-form-data"><span class="bx-mylist-form-date intranet-date"><?echo FormatDateFromDB($arRes['DATE_CREATE'], 'SHORT')?></span> <a href="<?echo $arRes['__LINK']?>"><?echo GetMessage('FRLM_RESULT').$arRes['ID']?><?echo $arRes['__TITLE'] ? ': '.htmlspecialcharsbx($arRes['__TITLE']) : ''?></a></div></li>
<?
	endforeach;
?>
		</ul>
<?
	if ($arForm['__LINK']):
?>
		<div class="bx-mylist-form-link">
			<a href="<?echo $arForm['__LINK']?>"><?echo GetMessage('FRLM_MORE_RESULTS')?></a>
		</div>
<?
	endif;
?>
	</div>
<?
endforeach;
?>
</div>