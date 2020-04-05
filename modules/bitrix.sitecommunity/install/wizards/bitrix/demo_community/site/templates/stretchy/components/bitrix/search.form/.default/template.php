<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="search">
<form action="<?= $arResult["FORM_ACTION"]?>">
	<div class="rounded-box">
		<b class="r1 top"></b>
		<div class="search-inner-box"><input type="text" name="q" /></div>
		<b class="r1 bottom"></b>
	</div>
	<div id="search-button">
		<div class="search-button-box"><b class="r1 top"></b><input type="submit" value="<?=GetMessage("SF_T_SEARCH_BUTTON");?>" name="s" /><b class="r1 bottom"></b></div>
	</div>
</form>
</div>