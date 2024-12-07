<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="bx-idea-lifesearch-mainbox">
	<div class="bx-idea-lifesearch-box">
		<div>
			<input type="text" value="<?=$arResult["SEACRH"]?>" id="bx-idea-lifesearch-field" class="bx-idea-lifesearch-field">
		</div>
		<div class="bx-idea-lifesearch-tools">
			<div class="bx-idea-waiter bx-idea-waiter-index" id="bx-idea-waiter-big-lifesearch"></div>
			<div class="bx-idea-close-button bx-idea-close-button-index" id="bx-idea-close-button-lifesearch"></div>
		</div>
	</div>
</div>

<script>
	BX.message({IDEA_SEARCH_DEFAULT:'<?=GetMessageJS("IDEA_SEARCH_DEFAULT")?>'});
</script>