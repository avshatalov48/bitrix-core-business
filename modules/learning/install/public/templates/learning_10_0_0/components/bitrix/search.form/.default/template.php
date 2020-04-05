<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="search">
	<form action="<?=$arResult["FORM_ACTION"]?>">
		<span class="search-button"><input type="submit" name="s" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>"></span><span class="search-box"><input type="text" name="q" value="" size="15" maxlength="50"></span>
	</form>
</div>