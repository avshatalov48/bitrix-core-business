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
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.viewer"));

if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y")
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
	$bodyClasses = 'pagetitle-toolbar-field-view no-all-paddings';
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
}

if (!is_array($arResult["APP"]) || empty($arResult["APP"]))
{
	echo GetMessage("MARKETPLACE_APP_NOT_FOUND");
	return;
}

$arParamsApp = array(
	"CODE" => $arResult["APP"]["CODE"],
	"VERSION" => $arResult["APP"]["VER"],
	"IFRAME" => $arParams["IFRAME"],
	"REDIRECT_PRIORITY" => $arResult["REDIRECT_PRIORITY"],
	"FROM" => $arResult['ANALYTIC_FROM']
);

if($arResult['CHECK_HASH'])
{
	$arParamsApp['CHECK_HASH'] = $arResult['CHECK_HASH'];
	$arParamsApp['INSTALL_HASH'] = $arResult['INSTALL_HASH'];
}
?>

<div class="mp-detail" id="detail_cont">
	<div class="mp-detail-main">
		<div class="mp-detail-main-preview" <?if($arResult["APP"]["ICON"]):?>style="background-image: url('<?=$arResult["APP"]["ICON"]?>')"<?endif;?>></div>
		<?/*if ($arResult["APP"]["PROMO"] == "Y"):?>
			<span class="mp_discount_icon"></span>
		<?endif;*/ ?>

		<div class="mp-detail-main-info">
			<div class="mp-detail-main-title"><?=htmlspecialcharsbx($arResult["APP"]["NAME"]);?></div>

			<?
			//additional info
			if ($arResult["APP"]["ACTIVE"] == "Y" && is_array($arResult["APP"]['APP_STATUS']) && $arResult["APP"]['APP_STATUS']['PAYMENT_NOTIFY'] == 'Y')
			{
				if($arResult["ADMIN"])
				{
					$arResult["APP"]['APP_STATUS']['MESSAGE_SUFFIX'] .= '_A';
				}
				?>
				<div class='ui-alert ui-alert-warning ui-alert-xs' style='margin-top:10px'>
					<span class="ui-alert-message">
						<?=GetMessage('PAYMENT_MESSAGE'.$arResult["APP"]['APP_STATUS']['MESSAGE_SUFFIX'], $arResult["APP"]['APP_STATUS']['MESSAGE_REPLACE'])?>
					</span>
				</div>
				<?
			}
			elseif (!empty($arResult["APP"]["SHORT_DESC"]))
			{
			?>
				<div class="mp-detail-main-description" data-role="mp-detail-main-description">
					<div class="mp-detail-main-description-wrapper" data-role="mp-detail-main-description-wrapper"><?=$arResult["APP"]["SHORT_DESC"];?></div>
				</div>
				<!--<div class="mp-detail-main-description-more" data-role="mp-detail-main-description-more">...<?=GetMessage("MARKETPLACE_MORE_BUTTON")?></div>-->
			<?
			}
			?>

			<div class="mp-detail-main-controls">
				<?
				if($arResult["CAN_INSTALL"])
				{
					// buttons for installed apps

					if ($arResult["APP"]["ACTIVE"] == "Y")
					{
						?>
						<span id="mp_installed_block">
							<!-- prolong -->
							<?php if ($arResult["APP"]['BY_SUBSCRIPTION'] === 'Y'):?>
								<a href="<?=$arResult['SUBSCRIPTION_BUY_URL']?>" target="_blank" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round">
									<?=GetMessage("MARKETPLACE_APP_PROLONG")?>
								</a>
							<?php
							elseif ($arResult["APP"]['FREE'] === 'N' && is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"])):?>
								<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round"
									onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult['BUY'])?>)">
									<?=($arResult["APP"]["STATUS"] == "P" && $arResult["APP"]["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
								</a>
							<? endif; ?>
							<? if($arResult["APP"]['TYPE'] == \Bitrix\Rest\AppTable::TYPE_CONFIGURATION):?>
								<span onclick="BX.SidePanel.Instance.open('<?=$arResult['IMPORT_PAGE']?>');" class="ui-btn ui-btn-md ui-btn-round ui-btn-primary"><?=GetMessage("MARKETPLACE_CONFIGURATION_INSTALL_SETTING_BTN")?></span>
							<? endif?>

							<!-- delete -->
							<?if($arResult["ADMIN"]):?>
								<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-round"
								onclick="BX.rest.Marketplace.uninstallConfirm('<?=CUtil::JSEscape($arResult["APP"]["CODE"])?>', '<?=CUtil::JSEscape($arResult['ANALYTIC_FROM'])?>')"><?=GetMessage("MARKETPLACE_APP_DELETE")?></a>
							<? endif; ?>

							<!-- update -->
							<?
							if ($arResult["APP"]["UPDATES"]):?>
								<a id="update_btn" href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round"
									onclick="BX.rest.Marketplace.install(<?=CUtil::PhpToJSObject($arParamsApp)?>)"><?=GetMessage("MARKETPLACE_APP_UPDATE_BUTTON")?></a>
							<?endif; ?>
						</span>
						<?
					}
					?>

					<!-- buttons for uninstalled apps-->
					<span <?if ($arResult["APP"]["ACTIVE"] == "Y"):?>style="display:none"<?endif?> id="mp_uninstalled_block">
						<!--paid-->
						<?
						if ($arResult["APP"]["BY_SUBSCRIPTION"] == "Y")
						{
							if ($arResult['SUBSCRIPTION_AVAILABLE'])
							{
								?>
								<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
								<?
							}
							else
							{
								?>
								<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.buySubscription(this, <?=CUtil::PhpToJSObject($arParamsApp)?>)">
									<?=GetMessage("MARKETPLACE_APP_INSTALL")?>
								</a>
								<?
							}
						}
						else if ($arResult["APP"]['FREE'] === 'N' &&  is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"]))
						{
							?>
							<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.buy(this, <?=CUtil::PhpToJSObject($arResult['BUY'])?>)">
								<?=($arResult["APP"]["STATUS"] == "P" && $arResult["APP"]["DATE_FINISH"]) ? GetMessage("MARKETPLACE_APP_PROLONG") : GetMessage("MARKETPLACE_APP_BUY")?>
							</a>
							<?
							if ($arResult["APP"]["STATUS"] == "P")
							{
								?>
								<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);"><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
								<?
							}
							else
							{
								if ($arResult["APP"]["DEMO"] == "D")
								{
									?>
									<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.install(<?
									echo CUtil::PhpToJSObject($arParamsApp) ?>);"><?=GetMessage("MARKETPLACE_APP_DEMO")?>
									</a>
									<?
								}
								elseif ($arResult["APP"]["DEMO"] == "T" && (!isset($arResult["APP"]["IS_TRIALED"]) || $arResult["APP"]["IS_TRIALED"] == "N" || MakeTimeStamp($arResult["APP"]["DATE_FINISH"]) > time()))
								{
									?>
									<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round" onclick="BX.rest.Marketplace.install(<?
									echo CUtil::PhpToJSObject($arParamsApp) ?>);">
										<?if ($arResult["APP"]["IS_TRIALED"] == "Y"):?>
											<?=GetMessage("MARKETPLACE_APP_TRIAL")?> (<?=$arResult["APP"]["APP_STATUS"]["MESSAGE_REPLACE"]["#DAYS#"]?>)
										<?else:?>
											<?=GetMessage("MARKETPLACE_APP_TRIAL")?> (<?=FormatDate("ddiff", time(), time() + $arResult["APP"]["TRIAL_PERIOD"] * 24 * 60 * 60)?>)
										<?endif; ?>
									</a>
									<?
								}
							}
						}
						else
						{
							//free
							$arParamsApp["STATUS"] = "F";
							?>
							<a
								href="javascript:void(0)"
								onclick="BX.rest.Marketplace.install(<?=CUtil::PhpToJSObject($arParamsApp)?>);"
								class="ui-btn ui-btn-md ui-btn-primary ui-btn-round"
							>
								<?=GetMessage("MARKETPLACE_APP_INSTALL")?>
							</a>
							<?
						}
						?>
					</span>
					<?
				} //--CAN_INSTALL
				else
				{
					if ($arResult["APP"]["ACTIVE"] == "Y"):?>
						<div class="ui-btn ui-btn-md ui-btn-no-caps ui-btn-link mp-detail-main-controls-price">
							<?=GetMessage("MARKETPLACE_APP_IS_INSTALLED")?>
						</div>
					<?else:?>
						<a href="javascript:void(0)" class="ui-btn ui-btn-md ui-btn-primary ui-btn-round js-employee-install-button"><?=GetMessage("MARKETPLACE_APP_INSTALL")?></a>
					<?endif;
				}

				if ($arResult["APP"]["ACTIVE"] != "Y") //show price only for uninstalled apps
				{
				?>
					<div class="ui-btn ui-btn-md ui-btn-no-caps ui-btn-link mp-detail-main-controls-price">
						<?if ($arResult["APP"]["BY_SUBSCRIPTION"] == "Y"):?>
							<?=GetMessage("MARKETPLACE_APP_BY_SUBSCRIPTION")?>
						<?elseif ($arResult["APP"]['FREE'] === 'N' && is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"])):?>
							<?=GetMessage("MARKETPLACE_APP_PRICE", array("#PRICE#"=>htmlspecialcharsbx($arResult["APP"]["PRICE"][1])))?>
						<?else:?>
							<?=GetMessage("MARKETPLACE_APP_FREE")?>
						<?endif;?>
					</div>
				<?
				}
				?>

				<?if ($arResult["APP"]["HIDDEN_BUY"] == "Y"):?>
					<div class="mp-detail-main-controls-btn-free"><?=ToLower(GetMessage("MARKETPLACE_HIDDEN_BUY"))?></div>
				<?endif?>
			</div>
		</div>
	</div>


	<div class="mp-detail-info">
		<!--<div class="mp-detail-info-rating">
			<div class="mp-detail-info-rating-title">rating:</div>
			<div class="mp-detail-info-rating-stars">
				<div class="mp-detail-info-rating-stars-item"></div>
				<div class="mp-detail-info-rating-stars-item mp-detail-info-rating-stars-item-active"></div>
				<div class="mp-detail-info-rating-stars-item"></div>
				<div class="mp-detail-info-rating-stars-item"></div>
				<div class="mp-detail-info-rating-stars-item"></div>
			</div>
		</div>-->
		<div class="mp-detail-info-owner">
			<div class="mp-detail-info-owner-title"><?=GetMessage("MARKETPLACE_APP_DEVELOPER")?></div>
			<div class="mp-detail-info-owner-name">
				<?if ($arResult["APP"]["PARTNER_URL"] <> ''):?>
					<a href="<?=htmlspecialcharsbx($arResult["APP"]["PARTNER_URL"])?>" target="_blank"><?=htmlspecialcharsbx($arResult["APP"]["PARTNER_NAME"])?></a>
				<?else:?>
					<?=htmlspecialcharsbx($arResult["APP"]["PARTNER_NAME"])?>
				<?endif?>
			</div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage("MARKETPLACE_APP_NUM_INSTALLS", array("#NUM_INSTALLS#" => htmlspecialcharsbx($arResult["APP"]["NUM_INSTALLS"])))?></div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage("MARKETPLACE_APP_VERSION", array("#VER#" => htmlspecialcharsbx($arResult["APP"]["VER"])))?></div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage("MARKETPLACE_APP_PUBLIC_DATE", array("#DATE#" => htmlspecialcharsbx($arResult["APP"]["DATE_PUBLIC"])))?></div>
		</div>

		<?
		if($arResult["APP"]["DATE_UPDATE"] <> ''):?>
			<div class="mp-detail-info-installs">
				<div class="mp-detail-info-installs-title"><?=GetMessage("MARKETPLACE_APP_UPDATE_DATE", array("#DATE#" => htmlspecialcharsbx($arResult["APP"]["DATE_UPDATE"])))?></div>
			</div>
		<?endif;
		?>
		<!--<div class="mp-detail-info-other-apps">
			<a href="#" target="_blank">other apps of developer</a>
		</div>-->
	</div>

	<div class="mp-detail-content">
		<div class="mp-detail-content-menu">
			<div class="mp-detail-content-menu-item mp-detail-content-menu-item-active" for="mp-detail-content-wrapper-desc"><?=GetMessage("MARKETPLACE_APP_DESCR_TAB")?></div>
			<div class="mp-detail-content-menu-item" for="mp-detail-content-wrapper-versions"><?=GetMessage("MARKETPLACE_APP_VERSIONS_TAB")?></div>
			<div class="mp-detail-content-menu-item " for="mp-detail-content-wrapper-support"><?=GetMessage("MARKETPLACE_APP_SUPPORT_TAB")?></div>
			<div class="mp-detail-content-menu-item" for="mp-detail-content-wrapper-install"><?=GetMessage("MARKETPLACE_APP_INSTALL_TAB")?></div>
			<div class="mp-detail-content-menu-border" data-role="mp-detail-content-menu-border"></div>
		</div>
		<div class="mp-detail-content-wrapper">
			<div class="mp-detail-content-wrapper-item mp-detail-content-wrapper-item-active" id="mp-detail-content-wrapper-desc">
				<?
				if (isset($arResult["APP"]["DESC_LANDING"]) && !empty($arResult["APP"]["DESC_LANDING"]))
				{
				?>
					<div class="mp-detail-iframe-cont">
						<iframe src="<?=$arResult["APP"]["DESC_LANDING"];?>" frameborder="no" class="mp-detail-iframe" id="mp-detail-iframe"></iframe>
					</div>
				<?
				}
				else
				{
					?>
					<?=$arResult["APP"]["DESC"];?>
					<?
					if (is_array($arResult["APP"]["IMAGES"]) && count($arResult["APP"]["IMAGES"]) > 0)
					{
						?>
						<div class="mp-detail-image-scroller" data-role="mp-detail-image-scroller">
							<div class="mp-detail-image-scroller-wrapper">
								<?foreach ($arResult["APP"]["IMAGES"] as $src):?>
									<img class="mp-detail-image-scroller-item" src="<?=$src?>" alt="" data-viewer data-viewer-group-by="mp-img" data-actions="[]">
								<?endforeach; ?>
							</div>
						</div>
						<script>
							var MarketplaceDetailImageScroller = new BX.Rest.Marketplace.DetailImageScroller({
								target: document.querySelector('[data-role="mp-detail-image-scroller"]')
							});
							MarketplaceDetailImageScroller.init();
						</script>
						<?
					}
				}
				?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-versions">
				<?foreach($arResult["APP"]["VERSIONS"] as $number=>$desc):?>
					<p class="mp-detail-content-version-title"><?=GetMessage("MARKETPLACE_APP_VERSION_MESS")?> <?=$number?></p>
					<div class="mp-detail-content-version-desc"><?=$desc?></div>
				<?endforeach; ?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-support">
				<?=$arResult["APP"]["SUPPORT"];?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-install">
				<?=$arResult["APP"]["INSTALL"];?>
			</div>
		</div>
	</div>
</div>

<?
$arJSParams = array(
	"ajaxPath" => $this->GetFolder()."/ajax.php",
	"siteId" => SITE_ID,
	"appName" => $arResult["APP"]["NAME"],
	"appCode" => $arResult["APP"]["CODE"],
	'importUrl' => $arResult['IMPORT_PAGE'],
	"openImport" => (
		$arResult["APP"]["INSTALLED"] === \Bitrix\Rest\AppTable::NOT_INSTALLED
		&& $arResult["APP"]["TYPE"] ===  \Bitrix\Rest\AppTable::TYPE_CONFIGURATION
		&& $arResult["APP"]["ACTIVE"] ===  \Bitrix\Rest\AppTable::ACTIVE
	),
);
?>

<script type="text/javascript">
	BX.message({
		"MARKETPLACE_APP_INSTALL_REQUEST" : "<?=GetMessageJS("MARKETPLACE_APP_INSTALL_REQUEST")?>",
		"MARKETPLACE_LICENSE_ERROR" : "<?=GetMessageJS("MARKETPLACE_LICENSE_ERROR")?>",
		"MARKETPLACE_LICENSE_TOS_ERROR" : "<?=GetMessageJS("MARKETPLACE_LICENSE_TOS_ERROR")?>",
		"REST_MP_INSTALL_REQUEST_CONFIRM" : "<?=GetMessageJS("REST_MP_INSTALL_REQUEST_CONFIRM")?>",
		"REST_MP_APP_INSTALL_REQUEST" : "<?=GetMessageJS("REST_MP_APP_INSTALL_REQUEST")?>"
	});
	BX.Rest.Marketplace.Detail.init(<?=CUtil::PhpToJSObject($arJSParams)?>);
	BX.viewImageBind('detail_img_block', {resize: 'WH',cycle: true}, {tag:'IMG'});
	<?if($arResult['START_INSTALL']):?>
		BX.rest.Marketplace.install(<?echo CUtil::PhpToJSObject($arParamsApp)?>);
	<?endif;?>
</script>
