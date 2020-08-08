<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<div class="search-page">
	<form action="" method="get">
		<? if($arParams["USE_SUGGEST"] === "Y"):
			if(mb_strlen($arResult["REQUEST"]["~QUERY"]) && is_object($arResult["NAV_RESULT"]))
			{
				$arResult["FILTER_MD5"] = $arResult["NAV_RESULT"]->GetFilterMD5();
				$obSearchSuggest = new CSearchSuggest($arResult["FILTER_MD5"], $arResult["REQUEST"]["~QUERY"]);
				$obSearchSuggest->SetResultCount($arResult["NAV_RESULT"]->NavRecordCount);
			}
			?>
			<?$APPLICATION->IncludeComponent("bitrix:search.suggest.input", "", array(
					"NAME" => "q",
					"VALUE" => $arResult["REQUEST"]["~QUERY"],
					"INPUT_SIZE" => 40,
					"DROPDOWN_SIZE" => 10,
					"FILTER_MD5" => $arResult["FILTER_MD5"],
				),
				$component, array("HIDE_ICONS" => "Y")
			);?>
			<input class="btn btn-primary" type="submit" value="<?=GetMessage("SEARCH_GO")?>" />
		<?else:?>
			<div class="input-group">
				<input type="text" class="form-control" name="q" value="<?=$arResult["REQUEST"]["QUERY"]?>" size="40" />
				<div class="input-group-append">
					<input class="btn btn-primary" type="submit" value="<?=GetMessage("SEARCH_GO")?>" />
				</div>
			</div>
		<?endif;?>
		<input type="hidden" name="how" value="<?echo $arResult["REQUEST"]["HOW"]=="d"? "d": "r"?>" />
		<? if($arParams["SHOW_WHEN"]):?>
	<script>
	var switch_search_params = function()
	{
		var sp = document.getElementById('search_params');
		var flag;

		if(sp.style.display == 'none')
		{
			flag = false;
			sp.style.display = 'block'
		}
		else
		{
			flag = true;
			sp.style.display = 'none';
		}

		var from = document.getElementsByName('from');
		for(var i = 0; i < from.length; i++)
			if(from[i].type.toLowerCase() == 'text')
				from[i].disabled = flag

		var to = document.getElementsByName('to');
		for(var i = 0; i < to.length; i++)
			if(to[i].type.toLowerCase() == 'text')
				to[i].disabled = flag

		return false;
	}
	</script>
	<br /><a class="search-page-params" href="#" onclick="return switch_search_params()"><?echo GetMessage('CT_BSP_ADDITIONAL_PARAMS')?></a>
	<div id="search_params" class="search-page-params" style="display:<?echo $arResult["REQUEST"]["FROM"] || $arResult["REQUEST"]["TO"]? 'block': 'none'?>">
		<?$APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'SHOW_INPUT' => 'Y',
				'INPUT_NAME' => 'from',
				'INPUT_VALUE' => $arResult["REQUEST"]["~FROM"],
				'INPUT_NAME_FINISH' => 'to',
				'INPUT_VALUE_FINISH' =>$arResult["REQUEST"]["~TO"],
				'INPUT_ADDITIONAL_ATTR' => 'size="10"',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);?>
	</div>
<?endif?>
</form><br />

<?if(isset($arResult["REQUEST"]["ORIGINAL_QUERY"])):
	?>
	<div class="search-language-guess">
		<?echo GetMessage("CT_BSP_KEYBOARD_WARNING", array("#query#"=>'<a href="'.$arResult["ORIGINAL_QUERY_URL"].'">'.$arResult["REQUEST"]["ORIGINAL_QUERY"].'</a>'))?>
	</div><br /><?
endif;?>
</div>