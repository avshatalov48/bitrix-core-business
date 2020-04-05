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


<div class="mp_section">
	<h2 class="mp_title_section"><?=GetMessage("MARKETPLACE_UPDATES")?></h2>
<?php
if (is_array($arResult["ITEMS"]) && !empty($arResult["ITEMS"])):
?>
	<div class="mp_section_container">
<?php
	foreach($arResult["ITEMS"] as $app):
		$appUrl = str_replace(
				array("#app#"),
				array(urlencode($app['CODE'])),
				$arParams['DETAIL_URL_TPL']
		);
?>
		<div class="mp_sc_container">
			<div class="mp_lt_left_container">
<?php
		if(!empty($app["ICON"])):
?>
				<span class="mp_sc_ls_img">
					<span><img src="<?=htmlspecialcharsbx($app["ICON"])?>" alt=""></span>
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
				<a href="<?=$appUrl?>" class="mp_sc_ls_shadow"></a>
				<div class="mp_sc_ls_container">
					<a class="mp_sc_ls_title" href="<?=$appUrl?>"><?=htmlspecialcharsbx(strlen($app["NAME"]) <= 50 ? $app["NAME"] :  substr($app["NAME"], 0, 50)."...")?></a>
					<!--<span class="mp_sc_ls_stars">12</span>-->
				</div>
			</div>
			<div class="mp_lt_centrer_container">
<?php
		foreach($app["VERSIONS"] as $number=> $desc):
?>
				<p><b><?=GetMessage("MARKETPLACE_APP_VERSION")?> <?=$number?></b><br/>
					<?=$desc?></p>
<?php
		endforeach;
?>
			</div>
			<div class="mp_lt_right_container">
<?php
		$arParamsApp = array(
			"CODE" => $app["CODE"],
			"SHOW_VERSION" => $app["VER"],
			"url" => $appUrl,
		);

		if($app['CAN_INSTALL']):
?>
				<a class="bt_green" href="javascript:void(0)" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_UPDATE_BUTTON")?></a>
<?php
		else:
?>
				<a href="javascript:void(0)" style="text-decoration: none;"><?=GetMessage("MARKETPLACE_APP_PORTAL_ADMIN")?></a>
<?php
		endif;
?>
			</div>
			<div style="clear:both"></div>
		</div>
<?php
	endforeach;
?>
	</div>
	<script>
		BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: true});
	</script>
<?php
else:
?>
	<?=GetMessage("MARKETPLACE_UPDATES_EMPTY")?>
<?php
endif;
?>
</div>
<?php
if (isset($arResult["NEW_NUM_UPDATES"])):
?>
<script>
	BX('menu_num_updates').innerHTML = "<?=($arResult["NEW_NUM_UPDATES"] > 0 ? "(".$arResult["NEW_NUM_UPDATES"].")" : "")?>";
</script>
<?php
endif;

