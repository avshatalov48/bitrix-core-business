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
Extension::load(
	[
		'market.application',
	]
);

?>

<div class="mp_section">
	<h2 class="mp_title_section"><?=GetMessage("MARKETPLACE_BUYS")?></h2>
<?php
if (is_array($arResult["ITEMS_DB"]) && !empty($arResult["ITEMS_DB"])):
?>
	<div class="mp_section_container">
<?php
	foreach($arResult["ITEMS_DB"] as $app):
		$appUrl = str_replace(
			array("#app#"),
			array(urlencode($app['CODE'])),
			$arParams['DETAIL_URL_TPL']
		);
		$arParamsApp = array(
			"CODE" => $app["CODE"],
			"VERSION" => $arResult["ITEMS"][$app["CODE"]]["VER"],
			"url" => $appUrl,
		);
?>
		<div class="mp_sc_container">
			<div class="mp_lt_left_container">
<?php
		if(!empty($arResult["ITEMS"][$app["CODE"]]["ICON"])):
?>
				<span class="mp_sc_ls_img">
					<span><img src="<?=htmlspecialcharsbx($arResult["ITEMS"][$app["CODE"]]["ICON"])?>" alt=""/></span>
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
				<a href="<?=$appUrl;?>" class="mp_sc_ls_shadow"></a>
				<div class="mp_sc_ls_container">
<?php
		$itemName = $arResult["ITEMS"][$app["CODE"]]["NAME"]
				? $arResult["ITEMS"][$app["CODE"]]["NAME"]
				: $app["MENU_NAME"];
		if(mb_strlen($itemName) >= 48):
?>
					<a class="mp_sc_ls_title" href="<?=$appUrl;?>" title="<?=htmlspecialcharsbx($itemName)?>">
						<?=htmlspecialcharsbx(mb_substr($itemName, 0, 48)."...")?>
					</a>
<?php
		else:
?>
					<a class="mp_sc_ls_title" href="<?=$appUrl;?>">
						<?=htmlspecialcharsbx($itemName)?>
					</a>
<?php
		endif;
?>
					<!--<span class="mp_sc_ls_price"></span>
						<span class="mp_sc_ls_stars">12</span>-->
				</div>
			</div>
			<div class="mp_lt_centrer_container">
				<div style="text-overflow:ellipsis; max-height: 207px; overflow: hidden;"><?=($arResult["ITEMS"][$app["CODE"]]["DESC"])?>&nbsp;</div>
<?php
		if ($app["ACTIVE"] == "N" && $arResult["ITEMS"][$app["CODE"]]["PUBLIC"] == "N"):
?>
					<p class="mp_notice_cursiv"><?=GetMessage("MARKETPLACE_APP_INSTALL_PARTNER")?></p>
<?php
		else:
			//additional info
			if($app["ACTIVE"] == "Y" && is_array($app['APP_STATUS']) && $app['APP_STATUS']['PAYMENT_NOTIFY'] == 'Y'):
?>
					<div class="mp_notify_message" style="margin-top:10px"><?=\Bitrix\Rest\AppTable::getStatusMessage($app['APP_STATUS']['MESSAGE_SUFFIX'], $app['APP_STATUS']['MESSAGE_REPLACE'])?></div>
<?php
			endif;
		endif;
?>
			</div>
			<div class="mp_lt_right_container">
<?php
		if ($app["ACTIVE"] == "Y"):
?>
				<span id="mp_installed_block_<?=$app["CODE"]?>">
<?php
			if (is_array($arResult["ITEMS"][$app["CODE"]]["PRICE"]) && !empty($arResult["ITEMS"][$app["CODE"]]["PRICE"])):
?>
					<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult["ITEMS"][$app["CODE"]]['BUY'])?>)">
						<?=($app["STATUS"] == "P" && $app["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
					</a>
<?php
			endif;
?>
					<a class="bt_gray" href="javascript:void(0)" onclick="BX.rest.Marketplace.uninstallConfirm('<?=CUtil::JSEscape($app["CODE"])?>')"><?=GetMessage("MARKETPLACE_DELETE_BUTTON")?></a>
				</span>
<?php
		endif;

?>
				<span <?if ($app["ACTIVE"] == "Y"):?>style="display:none"<?endif?> id="mp_uninstalled_block_<?=$app["CODE"]?>">
<?php
		if (is_array($arResult["ITEMS"][$app["CODE"]]["PRICE"]) && !empty($arResult["ITEMS"][$app["CODE"]]["PRICE"]) && $arResult["ITEMS"][$app["CODE"]]["PUBLIC"] == "Y"):
?>
					<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult["ITEMS"][$app["CODE"]]['BUY'])?>)">
						<?=($app["STATUS"] == "P" && $app["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
					</a>
<?php
			if ($app["STATUS"] == "P"):
?>
					<a href="javascript:void(0)" class="bt_green" onclick="BX.Market.Application.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_INSTALL_BUTTON")?></a>
<?php
			else:
				if ($arResult["ITEMS"][$app["CODE"]]["DEMO"] == "D"):
?>
					<a href="javascript:void(0)" class="bt_green" onclick="BX.Market.Application.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_DEMO")?></a>
<?php
				elseif ($arResult["ITEMS"][$app["CODE"]]["DEMO"] == "T" && ($app["IS_TRIALED"] == "N" || MakeTimeStamp($app["DATE_FINISH"]) > time())):
?>
					<a href="javascript:void(0)" class="bt_green" onclick="BX.Market.Application.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_TRIAL")?></a>
<?php
				endif;
			endif;
		else:
?>
					<a href="javascript:void(0)" onclick="BX.Market.Application.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);" class="bt_green" ><?=GetMessage("MARKETPLACE_INSTALL_BUTTON")?></a>
<?php
		endif;
?>
				</span>
<?php

		if ($app["ACTIVE"] == "Y"):
?>
				<br/>
				<a href="javascript:void(0)" class="mp_set_rights" onclick="BX.rest.Marketplace.setRights('<?=CUtil::JSEscape($app["ID"])?>');"><?=GetMessage("MARKETPLACE_ADD_RIGHTS")?></a>
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
	<?=GetMessage("MARKETPLACE_BUYS_EMPTY")?>
<?php
endif;
?>
</div>
