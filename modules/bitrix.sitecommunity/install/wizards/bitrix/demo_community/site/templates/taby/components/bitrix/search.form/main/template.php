<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form action="<?= $arResult["FORM_ACTION"]?>">
	<div id="search-button">
		<input type="submit" value="<?=GetMessage("SF_T_SEARCH_BUTTON");?>" name="s" />
	</div>
	<div class="search-box"><input type="text" name="q" /></div>
</form>