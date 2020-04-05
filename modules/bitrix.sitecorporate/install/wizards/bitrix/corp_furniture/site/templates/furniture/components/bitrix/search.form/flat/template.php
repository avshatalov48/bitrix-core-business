<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="search">
	<form action="<?=$arResult["FORM_ACTION"]?>">
		<div class="rounded-box">
			<div class="search-inner-box"><input type="text" name="q" maxlength="50" /></div>
		</div>
		<div id="search-button">
			<input type="submit" name="s" onfocus="this.blur();" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" id="search-submit-button">
		</div>
	</form>
</div>