<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

?>

<div class="mp_search_container">
	<form id="mp_search_form" name="mp_search_form" method="get" action="<?=$arParams["SEARCH_URL"]?>">
		<input type="text" name="q" class="mp_search_input_text" autocomplete="off" placeholder="<?=GetMessage("MARKETPLACE_SEARCH_PL")?>" id="mp_search_input" value="<?=htmlspecialcharsbx($arResult['SEARCH'])?>" />
		<span class="mp_search_submit" onclick="document.forms['mp_search_form'].submit();"/></span>
	</form>
	<div id="mp_search_container" class="mp_search_result" style="display: none; position: absolute"></div>
</div>

<script>
	new RestMapketplaceSearch({
		POST_URL:  '<?=CUtil::JSEscape($arParams['SEARCH_URL'])?>',
		CONTAINER_ID: 'mp_search_container',
		INPUT_ID: 'mp_search_input',
		MIN_QUERY_LEN: 2
	});
</script>