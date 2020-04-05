<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="search">
<form action="<?=$arResult["FORM_ACTION"]?>">
<div class="rounded-box">
	<b class="r1 top"></b>
	<div class="search-inner-box">
		<input type="text" name="q" maxlength="50" />
	</div>
	<b class="r1 bottom"></b>
</div>
<div id="search-button">
	<input type="submit" name="s" id="search-submit-button" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" onfocus="this.blur();"/>
</div>
</form>
</div>