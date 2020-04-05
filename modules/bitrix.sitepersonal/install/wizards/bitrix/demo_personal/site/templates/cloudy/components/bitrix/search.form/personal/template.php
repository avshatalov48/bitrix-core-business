<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="search">
	<form action="<?=$arResult["FORM_ACTION"]?>">
	<div id="search-button">
		<input type="submit" name="s" id="search-submit-button" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" onfocus="this.blur();"/>
	</div>
	<div id="search-textbox">
		<span><input type="text" name="q" maxlength="50" /></span>
	</div>
	</form>
</div>