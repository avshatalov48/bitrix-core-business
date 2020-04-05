<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$INPUT_ID = trim($arParams["~INPUT_ID"]);
if(strlen($INPUT_ID) <= 0)
	$INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if(strlen($CONTAINER_ID) <= 0)
	$CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);


if (isset($_REQUEST["q"]) && $_REQUEST["needconvert"] == "Y" && SITE_CHARSET != "utf-8")
	$_REQUEST["q"] = $APPLICATION->ConvertCharsetArray($_REQUEST["q"], "utf-8", SITE_CHARSET);

if($arParams["SHOW_INPUT"] !== "N"):?>
<div id="<?echo $CONTAINER_ID?>">
<div class="item_list_search_input_container" <?if (!isset($_REQUEST["q"])):?>style="margin-right: 100px;"<?endif?>>
	<form action="<?echo $arResult["FORM_ACTION"]?>">
		<input name="s" type="submit" value="" onclick="
			<?if (isset($_REQUEST["q"])):?>
				document.location.href= '<?=CUtil::JSEscape($arResult["FORM_ACTION"])?>?s=&q='+BX('<?echo $INPUT_ID?>').value;
			<?else:?>
				var search_cont = BX('<?echo $INPUT_ID?>').value;
				BX('<?echo $INPUT_ID?>').value = '';
				app.openNewPage('<?=CUtil::JSEscape($arResult["FORM_ACTION"])?>?s=&q='+search_cont+'&needconvert=Y');
			<?endif?>
			return false;
		"/>
		<input id="<?echo $INPUT_ID?>" type="text" name="q" value=""  maxlength="50" autocomplete="off" />
	</form>
</div>
</div>
<?endif?>
<script type="text/javascript">
	<?if (isset($_REQUEST["q"])):?>
	BX("<?=$INPUT_ID?>").value = "<?=CUtil::JSEscape($_REQUEST["q"])?>";
	<?endif?>
</script>
