<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
	<div class="flat">
	<form action="<?=$arResult["FORM_ACTION"]?>">
	<div id="search-button">
			<input type="submit" name="s" id="search-submit-button" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" onfocus="this.blur();">
	</div>
	<div class="search-box"><input type="text" name="q"></div>
	</form>
	</div>