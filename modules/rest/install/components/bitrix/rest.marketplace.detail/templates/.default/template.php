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

if (!is_array($arResult["APP"]) || empty($arResult["APP"]))
{
	echo GetMessage("MARKETPLACE_APP_NOT_FOUND");
	return;
}

$arParamsApp = array(
	"CODE" => $arResult["APP"]["CODE"],
	"VERSION" => $arResult["APP"]["VER"],
	"IFRAME" => $arParams["IFRAME"],
);

if($arResult['CHECK_HASH'])
{
	$arParamsApp['CHECK_HASH'] = $arResult['CHECK_HASH'];
	$arParamsApp['INSTALL_HASH'] = $arResult['INSTALL_HASH'];
}
?>
<div class="mp_dt_title_icon">
	<span class="mp_sc_ls_img">
<?
	if($arResult["APP"]["ICON"]):
?>
		<span><img src="<?=$arResult["APP"]["ICON"]?>" alt=""></span>
<?
	else:
?>
		<span class="mp_empty_icon"></span>
<?
	endif;
?>
	</span>
	<span class="mp_sc_ls_shadow">
<?
	if ($arResult["APP"]["PROMO"] == "Y"):
?>
		<span class="mp_discount_icon"></span>
<?
	endif;
?>
	</span>
</div>
<h2 class="mp_dt_title_section"><?=htmlspecialcharsbx($arResult["APP"]["NAME"]);?></h2>
<div class="mp_td_owner">
	<?=GetMessage("MARKETPLACE_APP_FROM")?>
	<?if ($arResult["APP"]["PARTNER_URL"]):?><a href="<?=htmlspecialcharsbx($arResult["APP"]["PARTNER_URL"])?>" target="_blank"><?endif?>
	<?=htmlspecialcharsbx($arResult["APP"]["PARTNER_NAME"])?>
	<?if ($arResult["APP"]["PARTNER_URL"]):?></a><?endif?>
</div>
<div style="clear:both"></div>
<div class="mp_dt_container" id="detail_cont">
	<div class="mp_dt_left_container">
<?
	if (is_array($arResult["APP"]["IMAGES"]) && count($arResult["APP"]["IMAGES"]) > 0):
?>
		<div id="detail_img_block">
			<div class="mp_dt_preview"><img src="<?=$arResult["APP"]["IMAGES"][0]?>" alt="" width="180px" height="180px"></div>
<?
		unset($arResult["APP"]["IMAGES"][0]);
		if(count($arResult["APP"]["IMAGES"]) > 0):
?>
			<div class="mp_dt_lc_slider">
				<div class="mp_dt_lc_slider_container">
					<ul class="mp_dt_preview_list" >
<?
			foreach($arResult["APP"]["IMAGES"] as $src):
?>
						<li><a href="<?=$src?>"><img src="<?=$src?>" alt="" width="39px" height="39px"></a></li>
<?
			endforeach;
?>
					</ul>
				</div>
			</div>
<?
		endif;
?>
		</div>
<?
	endif;
	if(strlen($arResult["APP"]["DATE_UPDATE"]) > 0):
?>
		<div class="mp_dt_lc_desc"><?=GetMessage("MARKETPLACE_APP_UPDATE_DATE", array("#DATE#" => htmlspecialcharsbx($arResult["APP"]["DATE_UPDATE"])))?></div>
<?
	endif;
?>
		<div class="mp_dt_lc_desc"><?=GetMessage("MARKETPLACE_APP_PUBLIC_DATE", array("#DATE#" => htmlspecialcharsbx($arResult["APP"]["DATE_PUBLIC"])))?></div>
		<div class="mp_dt_lc_desc"><?=GetMessage("MARKETPLACE_APP_VERSION", array("#VER#" => htmlspecialcharsbx($arResult["APP"]["VER"])))?></div>
		<div class="mp_dt_lc_desc"><?=GetMessage("MARKETPLACE_APP_NUM_INSTALLS", array("#NUM_INSTALLS#" => htmlspecialcharsbx($arResult["APP"]["NUM_INSTALLS"])))?></div>
	</div>
	<div class="mp_dt_right_container">
		<div class="mp_dt_rc_header">
			<div class="mp_dt_rc_price">
<?
	if (is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"])):
?>
				<?=GetMessage("MARKETPLACE_APP_PRICE", array("#PRICE#"=>htmlspecialcharsbx($arResult["APP"]["PRICE"][1])))?>
<?
	else:
?>
				<?=GetMessage("MARKETPLACE_APP_FREE")?>
<?
	endif;
?>
			</div>
<?
	if($arResult["ADMIN"])
	{
?>
			<!-- buttons for installed apps-->
<?
		if ($arResult["APP"]["ACTIVE"] == "Y"):
?>
			<span id="mp_installed_block">
					<!-- prolong -->
<?
			if (is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"])):
?>
				<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult['BUY'])?>)">
					<?=($arResult["APP"]["STATUS"] == "P" && $arResult["APP"]["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
				</a>
<?
			endif;
?>
				<!-- delete -->
				<a href="javascript:void(0)" class="bt_gray" onclick="BX.rest.Marketplace.uninstallConfirm('<?=CUtil::JSEscape($arResult["APP"]["CODE"])?>')"><?=GetMessage("MARKETPLACE_APP_DELETE")?></a>
				<!-- update -->
<?
			if ($arResult["APP"]["UPDATES"]):
?>
				<a id="update_btn" href="javascript:void(0)" class="bt_gray" onclick="BX.rest.Marketplace.install(<?=CUtil::PhpToJSObject($arParamsApp)?>)"><?=GetMessage("MARKETPLACE_APP_UPDATE_BUTTON")?></a>
<?
			endif;
?>
			</span>
<?
		endif;
?>
			<!-- buttons for uninstalled apps-->
			<span <?if ($arResult["APP"]["ACTIVE"] == "Y"):?>style="display:none"<?endif?> id="mp_uninstalled_block">
				<!--paid-->
<?
		if (is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"])):
?>
				<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult['BUY'])?>)">
					<?=($arResult["APP"]["STATUS"] == "P" && $arResult["APP"]["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
				</a>
<?
			if ($arResult["APP"]["STATUS"] == "P"):
?>
				<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
<?
			else:
				if ($arResult["APP"]["DEMO"] == "D"):
?>
				<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_DEMO")?></a>
<?
				elseif ($arResult["APP"]["DEMO"] == "T" && (!isset($arResult["APP"]["IS_TRIALED"]) || $arResult["APP"]["IS_TRIALED"] == "N" || MakeTimeStamp($arResult["APP"]["DATE_FINISH"]) > time())):
?>
				<a href="javascript:void(0)" class="bt_green" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);">
<?
				if ($arResult["APP"]["IS_TRIALED"] == "Y"):
?>
					<?=GetMessage("MARKETPLACE_APP_TRIAL")?> (<?=$arResult["APP"]["APP_STATUS"]["MESSAGE_REPLACE"]["#DAYS#"]?>)
<?
				else:
?>
					<?=GetMessage("MARKETPLACE_APP_TRIAL")?> (<?=FormatDate("ddiff", time(), time()+$arResult["APP"]["TRIAL_PERIOD"]*24*60*60)?>)
<?
				endif;
?>
				</a>
<?
			endif;
		endif;
?>
				<!--free-->
<?
	else:
		$arParamsApp["STATUS"] = "F";
?>
				<a href="javascript:void(0)" onclick="BX.rest.Marketplace.install(<?=CUtil::PhpToJSObject($arParamsApp)?>);" class="bt_green" ><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
<?
	endif;
?>
			</span>
<?
	}
	else
	{
		if ($arResult["APP"]["ACTIVE"] == "Y"):
?>
			<a href="javascript:void(0)" style="text-decoration: none;"><?=GetMessage("MARKETPLACE_APP_IS_INSTALLED")?></a>
<?
		else:
?>
			<a href="javascript:void(0)" class="bt_green js-employee-install-button"><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
<?
		endif;
	}
	//additional info
	if($arResult["APP"]["ACTIVE"] == "Y" && is_array($arResult["APP"]['APP_STATUS']) && $arResult["APP"]['APP_STATUS']['PAYMENT_NOTIFY'] == 'Y')
	{
		if($arResult["ADMIN"])
		{
			$arResult["APP"]['APP_STATUS']['MESSAGE_SUFFIX'] .= '_A';
		}

		echo "<div class='mp_notify_message' style='margin-top:10px'>".GetMessage('PAYMENT_MESSAGE'.$arResult["APP"]['APP_STATUS']['MESSAGE_SUFFIX'], $arResult["APP"]['APP_STATUS']['MESSAGE_REPLACE'])."</div>";
	}
?>
		</div>
		<div class="mp_dt_rc_content" id="mp_tabs_cont">
			<ul class="mp_dt_rc_tab_button" id="mp_tabs_block">
				<li class="active" id="detail_descr_tab" onclick="MpChangeTab(this, 'detail_descr');"><a href="javascript:void(0)"><?=GetMessage("MARKETPLACE_APP_DESCR_TAB")?> <span class="arrow"></span></a></li>
				<li id="detail_versions_tab" onclick="MpChangeTab(this, 'detail_versions');"><a href="javascript:void(0)"><?=GetMessage("MARKETPLACE_APP_VERSIONS_TAB")?> <span class="arrow"></span></a></li>
				<li id="detail_support_tab" onclick="MpChangeTab(this, 'detail_support');"><a href="javascript:void(0)"><?=GetMessage("MARKETPLACE_APP_SUPPORT_TAB")?> <span class="arrow"></span></a></li>
				<li id="detail_install_tab" onclick="MpChangeTab(this, 'detail_install');"><a href="javascript:void(0)"><?=GetMessage("MARKETPLACE_APP_INSTALL_TAB")?> <span class="arrow"></span></a></li>
			</ul>
			<div class="mp_dt_rc_tab_container" style="display: block;" id="detail_descr"><?=$arResult["APP"]["DESC"];?></div>
			<div class="mp_dt_rc_tab_container" style="display: none;" id="detail_versions">
<?
	foreach($arResult["APP"]["VERSIONS"] as $number=>$desc):
?>
				<p>
					<b><?=GetMessage("MARKETPLACE_APP_VERSION_MESS")?> <?=$number?></b><br/>
					<?=$desc?>
				</p>
<?
	endforeach;
?>
			</div>
			<div class="mp_dt_rc_tab_container" style="display: none;" id="detail_support"><?=$arResult["APP"]["SUPPORT"];?></div>
			<div class="mp_dt_rc_tab_container" style="display: none;" id="detail_install"><?=$arResult["APP"]["INSTALL"];?></div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

<?
$arJSParams = array(
	"ajaxPath" => $this->GetFolder()."/ajax.php",
	"siteId" => SITE_ID,
	"appName" => $arResult["APP"]["NAME"],
	"appCode" => $arResult["APP"]["CODE"]
);
?>

<script type="text/javascript">
	BX.message({
		"MARKETPLACE_APP_INSTALL_REQUEST" : "<?=GetMessageJS("MARKETPLACE_APP_INSTALL_REQUEST")?>",
		"MARKETPLACE_LICENSE_ERROR" : "<?=GetMessageJS("MARKETPLACE_LICENSE_ERROR")?>"
	});
	new BX.Rest.Marketplace.Detail(<?=CUtil::PhpToJSObject($arJSParams)?>);

	BX.viewImageBind('detail_img_block', {resize: 'WH',cycle: true}, {tag:'IMG'});

	function MpChangeTab(element, id)
	{
		var tabs = BX.findChildren(BX("mp_tabs_block"), {tagName:"li"}, true);
		for (var i=0; i<tabs.length; i++)
		{
			BX.removeClass(tabs[i], "active");
		}
		BX.addClass(element, 'active');
		var texts = BX.findChildren(BX("mp_tabs_cont"), {className:"mp_dt_rc_tab_container"}, true);
		for (var i=0; i<texts.length; i++)
		{
			texts[i].style.display='none';
		}

		BX(id).style.display='block';
	}
	<?
	if($arResult['START_INSTALL']):
	?>
		BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);
	<?
	endif;
	?>
</script>
