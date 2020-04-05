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
 * @global CUser $USER
 */
?>

<h2 class="mp_title_section"><?=htmlspecialcharsbx($arResult["CAT_NAME"])?></h2>
<?php
if (is_array($arResult["ITEMS"])):
?>
<div class="mp_section_container">
	<div class="mp_sc_container">
		<div class="mp_sc_slide">
			<ul class="mp_sc_list_solutions">
<?php
	foreach($arResult["ITEMS"] as $app):
		$appUrl = str_replace(
			array("#app#"),
			array(urlencode($app['CODE'])),
			$arParams['DETAIL_URL_TPL']
		);
		$appInstalled = in_array($app['CODE'], $arResult['ITEMS_INSTALLED'])
?>
				<li>
					<span class="mp_sc_ls_img">
<?php
		if($app["ICON"]):
?>
						<span><img src="<?=htmlspecialcharsbx($app["ICON"])?>" alt=""></span>
<?php
		else:
?>
						<span class="mp_empty_icon"></span>
<?php
		endif;
?>
					</span>
					<a href="<?=$appUrl?>" class="mp_sc_ls_shadow" target="_self">
<?php
		if ($app["PROMO"] == "Y"):
?>
						<span class="mp_discount_icon"></span>
<?php
		endif;
?>
					</a>
<?php
if($appInstalled):
?>
					<span class="mp_installed_icon"><?=GetMessage('MARKETPLACE_INSTALLED')?></span>
<?php
endif;
?>
					<div class="mp_sc_ls_container">
						<a class="mp_sc_ls_title crop" href="<?=$appUrl;?>" target="_self"><?=htmlspecialcharsbx(strlen($app["NAME"]) <= 25 ? $app["NAME"] :  substr($app["NAME"], 0, 25)."...")?></a>
						<span class="mp_sc_ls_price">
<?php
		if (is_array($app["PRICE"]) && !empty($app["PRICE"][1])):
?>
							<?=GetMessage("MARKETPLACE_APP_PRICE", array("#PRICE#" => $app["PRICE"][1]))?>
<?php
		else:
?>
							<?=GetMessage("MARKETPLACE_APP_FREE")?>
<?php
		endif;
?>
						</span>
						<!--<span class="mp_sc_ls_stars">12</span>-->
					</div>
					<div class="mp_sc_ls_li_hover"><a href="<?=$appUrl;?>" target="_self"><?=GetMessage("MARKETPLACE_SHOW_APP")?></a></div>
				</li>
<?php
	endforeach;
?>
			</ul>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
<br/>
<?php
	$APPLICATION->IncludeComponent(
		"bitrix:main.pagenavigation",
		"",
		array(
			"NAV_OBJECT" => $arResult['NAV'],
			"SEF_MODE" => "N",
		),
		$component
	);
?>
<script>
(function(){
	BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: <?=$arParams['IFRAME'] ? 'false' : 'true'?>});
<?
if($arParams['IFRAME']):
?>

	var installCallback = function()
	{
		top.BX.removeCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', installCallback);
		location.reload();
	};
	top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', installCallback);
<?php
endif;
?>
})();
</script>

<?php
else:
?>
<?=GetMessage("MARKETPLACE_EMPTY_CATEGORY")?>
<?php
endif;
