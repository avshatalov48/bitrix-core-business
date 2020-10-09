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
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if (is_array($arResult["ITEMS"])):
?>
<h2 class="mp_title_section"><?=$arParams["TITLE"]?></h2>
<div class="mp_section_container">
<?php
	foreach($arResult["ITEMS"] as $key=>$arBlock):
		if (empty($arBlock))
		{
			continue;
		}
?>
	<div class="mp_sc_container">
		<div class="mp_sc_title"><?=GetMessage("MARKETPLACE_PRICE_".$key)?></div>
		<div class="mp_sc_slide">
			<ul class="mp_sc_list_solutions">
<?php
		if (is_array($arBlock)):
			foreach($arBlock as $app):
				$appUrl = str_replace(
					array("#app#"),
					array(urlencode($app['CODE'])),
					$arParams['DETAIL_URL_TPL']
				);
				$appInstalled = in_array($app['CODE'], $arResult['ITEMS_INSTALLED']);

?>
				<li>

<?php
				if(!empty($app["ICON"])):
?>
					<span class="mp_sc_ls_img">
						<span><img src="<?=htmlspecialcharsbx($app["ICON"])?>" alt="" /></span>
					</span>
<?php
				else:
?>
					<span class="mp_sc_ls_img">
						<span class="mp_empty_icon"></span>
					</span>
<?php
				endif;
?>
					<a href="<?=$appUrl?>" class="mp_sc_ls_shadow">
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
						<a class="mp_sc_ls_title crop" href="<?=$appUrl;?>"><?=htmlspecialcharsbx(mb_strlen($app["NAME"]) <= 25 ? $app["NAME"] : mb_substr($app["NAME"], 0, 25)."...")?></a>
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
					<div class="mp_sc_ls_li_hover"><a href="<?=$appUrl;?>"><?=GetMessage("MARKETPLACE_SHOW_APP")?></a></div>
				</li>
<?php
			endforeach;
		endif;
?>
			</ul>
		</div>
		<div style="clear:both;"></div>
	</div>
	<!--<div class="mp_sc_container buttons">
		<a href="" class="mp_allnews">All news</a>
	</div>-->
<?php
	endforeach;
?>
</div>
<?
endif;
