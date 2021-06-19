<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="search-page">
<form method="get" action="<?=$arParams["SEARCH_PAGE"]?>">
<?php if ($arParams["COURSE_ID"] && $arParams["SEF_MODE"] != "Y"):?>
	<input type="hidden" name="COURSE_ID" value="<?php echo $arParams["COURSE_ID"]?>" />
	<input type="hidden" name="SEARCH" value="Y" />
<?php endif?>
<table cellspacing="2" cellpadding="0" border="0">
	<tr>
	<td><?=GetMessage("LEARNING_MAIN_SEARCH_SEARCH")?></td>
	<td><input type="text" name="q" size="20" value="<?=$arResult["q"]?>"></td>
	<td><input type="submit" value="<?=GetMessage("SEARCH_GO")?>"></td>
	</tr>
</table>
<?if($arResult["how"]=="d"):?>
	<input type="hidden" name="how" value="d">
<?endif;?>
</form><br />

<?if($arResult["REQUEST"]["QUERY"] === false && $arResult["REQUEST"]["TAGS"] === false):?>
<?elseif($arResult["ERROR_CODE"]!=0):?>
	<p><?=GetMessage("SEARCH_ERROR")?></p>
	<?ShowError($arResult["ERROR_TEXT"]);?>
	<p><?=GetMessage("SEARCH_CORRECT_AND_CONTINUE")?></p>
	<br /><br />
	<p><?=GetMessage("SEARCH_SINTAX")?><br /><b><?=GetMessage("SEARCH_LOGIC")?></b></p>
	<table border="0" cellpadding="5">
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_OPERATOR")?></td><td valign="top"><?=GetMessage("SEARCH_SYNONIM")?></td>
			<td><?=GetMessage("SEARCH_DESCRIPTION")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_AND")?></td><td valign="top">and, &amp;, +</td>
			<td><?=GetMessage("SEARCH_AND_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_OR")?></td><td valign="top">or, |</td>
			<td><?=GetMessage("SEARCH_OR_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_NOT")?></td><td valign="top">not, ~</td>
			<td><?=GetMessage("SEARCH_NOT_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top">( )</td>
			<td valign="top">&nbsp;</td>
			<td><?=GetMessage("SEARCH_BRACKETS_ALT")?></td>
		</tr>
	</table>
<?elseif(count($arResult["SEARCH_RESULT"])>0):?>
	<?if($arParams["DISPLAY_TOP_PAGER"] != "N") echo $arResult["NAV_STRING"]?>
	<br /><hr />
	<?php foreach($arResult["SEARCH_RESULT"] as $arItem):?>
		<a href="<?echo $arItem["URL"]?>"><?echo $arItem["TITLE_FORMATED"]; ?></a>
		<p><?echo $arItem["BODY_FORMATED"]?></p>
		<small><?echo GetMessage("SEARCH_MODIFIED")?> <?echo $arItem["DATE_CHANGE"]?></small>
		<hr />
	<?php endforeach?>
	<?=$arResult["NAV_STRING"]?>
	<br />
	<p>
	<?if($arResult["how"]=="d"):?>
		<a href="<?=$arResult["ORDER_LINK"]?>"><?=GetMessage("SEARCH_SORT_BY_RANK")?></a>&nbsp;|&nbsp;<b><?=GetMessage("SEARCH_SORTED_BY_DATE")?></b>
	<?else:?>
		<b><?=GetMessage("SEARCH_SORTED_BY_RANK")?></b>&nbsp;|&nbsp;<a href="<?=$arResult["ORDER_LINK"]?>"><?=GetMessage("SEARCH_SORT_BY_DATE")?></a>
	<?endif;?>
	</p>
<?else:?>
	<?ShowNote(GetMessage("SEARCH_NOTHING_TO_FOUND"));?>
<?endif;?>
</div>