<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
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

use Bitrix\Rest\AppTable;
use Bitrix\Main\UI\Extension;

Extension::load(
	[
		'ui.design-tokens',
		'ui.fonts.opensans',
		'ui.buttons',
		'ui.alerts',
		'ui.viewer',
		'market.application',
	]
);

if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] === 'Y')
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass');
	$bodyClasses = 'pagetitle-toolbar-field-view no-all-paddings';
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
}

if (!is_array($arResult['APP']) || empty($arResult['APP']))
{
	echo GetMessage('MARKETPLACE_APP_NOT_FOUND');
	return;
}

$arParamsApp = [
	'CODE' => $arResult['APP']['CODE'],
	'VERSION' => $arResult['APP']['VER'],
	'IFRAME' => $arParams['IFRAME'],
	'SILENT_INSTALL' => $arResult['APP']['SILENT_INSTALL'],
	'REDIRECT_PRIORITY' => $arResult['REDIRECT_PRIORITY'],
	'FROM' => $arResult['ANALYTIC_FROM'],
];

if ($arResult['CHECK_HASH'])
{
	$arParamsApp['CHECK_HASH'] = $arResult['CHECK_HASH'];
	$arParamsApp['INSTALL_HASH'] = $arResult['INSTALL_HASH'];
}

$buttonList = [];
if ($arResult['CAN_INSTALL'])
{
	if ($arResult['APP']['STATUS'] === 'P' && $arResult['APP']['DATE_FINISH'])
	{
		$buyButtonMessage = GetMessage('MARKETPLACE_APP_PROLONG');
	}
	else
	{
		$buyButtonMessage = GetMessage('MARKETPLACE_APP_BUY');
	}

	if ($arResult['APP']['ACTIVE'] === 'Y')
	{
		if ($arResult['REST_ACCESS'])
		{
			// buttons for installed apps
			if ($arResult['APP']['BY_SUBSCRIPTION'] === 'Y')
			{
				if ($arResult['APP']['APP_STATUS']['PAYMENT_NOTIFY'] === 'Y')
				{
					$buttonList[] = [
						'TAGS' => [
							'href' => $arResult['SUBSCRIPTION_BUY_URL'],
							'target' => '_blank',
							'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
						],
						'TEXT' => GetMessage('MARKETPLACE_APP_PROLONG'),
					];
				}
			}
			elseif (
				$arResult['APP']['FREE'] === 'N'
				&& is_array($arResult['APP']['PRICE'])
				&& !empty($arResult['APP']['PRICE']))
			{

				if (!empty($arResult['APP']['VENDOR_SHOP_LINK']))
				{
					$buttonList[] = [
						'TAGS' => [
							'href' => htmlspecialcharsbx($arResult['APP']['VENDOR_SHOP_LINK']),
							'target' => '_blank',
							'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
						],
						'TEXT' => $buyButtonMessage
					];
				}
				else
				{
					$buttonList[] = [
						'TAGS' => [
							'href' => 'javascript:void(0)',
							'onclick' => 'BX.rest.Marketplace.buy(this, '. CUtil::PhpToJSObject($arResult['BUY']) .')',
							'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
						],
						'TEXT' => $buyButtonMessage
					];
				}
			}

			//import configuration
			if ($arResult['APP']['TYPE'] === AppTable::TYPE_CONFIGURATION)
			{
				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => 'BX.SidePanel.Instance.open(\'' . $arResult['IMPORT_PAGE'] . '\');',
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => GetMessage('MARKETPLACE_CONFIGURATION_INSTALL_SETTING_BTN')
				];
			}

			//update
			if ($arResult['APP']['UPDATES'])
			{
				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => 'BX.Market.Application.install(' . CUtil::PhpToJSObject($arParamsApp) . ');',
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => GetMessage('MARKETPLACE_APP_UPDATE_BUTTON')
				];
			}
		}
	}
	else
	{
		// buttons for uninstalled apps
		$action = 'top.BX.UI.InfoHelper.show(\'' . $arResult['REST_ACCESS_HELPER_CODE'] . '\');';
		// buttons for uninstalled apps
		if ($arResult['APP']['BY_SUBSCRIPTION'] === 'Y')
		{
			if ($arResult['REST_ACCESS'])
			{
				if ($arResult['SUBSCRIPTION_AVAILABLE'])
				{
					$action = 'BX.Market.Application.install(' . CUtil::PhpToJSObject($arParamsApp) . ');';
				}
				elseif (!empty($arResult['REST_ACCESS_HELPER_CODE']) && !$arResult['POPUP_BUY_SUBSCRIPTION_PRIORITY'])
				{
					$action = 'top.BX.UI.InfoHelper.show(\'' . $arResult['REST_ACCESS_HELPER_CODE'] . '\');';
				}
				else
				{
					$action = 'BX.rest.Marketplace.buySubscription(this, ' . CUtil::PhpToJSObject($arParamsApp) . ');';
				}
			}
			$buttonList[] = [
				'TAGS' => [
					'href' => 'javascript:void(0)',
					'onclick' => $action,
					'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
				],
				'TEXT' => GetMessage('MARKETPLACE_APP_INSTALL')
			];
		}
		elseif (
			$arResult['APP']['FREE'] === 'N'
			&& is_array($arResult['APP']['PRICE'])
			&& !empty($arResult['APP']['PRICE'])
		)
		{
			if (!empty($arResult['APP']['VENDOR_SHOP_LINK']))
			{
				$buttonList[] = [
					'TAGS' => [
						'href' => htmlspecialcharsbx($arResult['APP']['VENDOR_SHOP_LINK']),
						'target' => '_blank',
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => $buyButtonMessage
				];
			}
			else
			{
				if ($arResult['REST_ACCESS'])
				{
					$action = 'BX.rest.Marketplace.buy(this, ' . CUtil::PhpToJSObject($arResult['BUY']) . ');';
				}

				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => $action,
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => $buyButtonMessage
				];
			}

			if ($arResult['APP']['STATUS'] === 'P')
			{
				if ($arResult['REST_ACCESS'])
				{
					$action = 'BX.Market.Application.install(' . CUtil::PhpToJSObject($arParamsApp) . ');';
				}
				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => $action,
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => GetMessage('MARKETPLACE_APP_INSTALL')
				];
			}
			elseif ($arResult['APP']['DEMO'] === 'D')
			{
				if ($arResult['REST_ACCESS'])
				{
					$action = 'BX.Market.Application.install(' . CUtil::PhpToJSObject($arParamsApp) . ');';
				}
				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => $action,
						'class' => 'ui-btn ui-btn-md ui-btn-light-border ui-btn-round',
					],
					'TEXT' => GetMessage('MARKETPLACE_APP_DEMO')
				];
			}
			elseif (
				$arResult['APP']['DEMO'] === 'T'
				&& (!isset($arResult['APP']['IS_TRIALED'])
				|| $arResult['APP']['IS_TRIALED'] === 'N'
				|| MakeTimeStamp($arResult['APP']['DATE_FINISH']) > time())
			)
			{
				if ($arResult['REST_ACCESS'])
				{
					if ($arResult['PAID_APP_IN_SUBSCRIBE'] && !$arResult['SUBSCRIPTION_ACTIVE'])
					{
						if (!empty($arResult['REST_ACCESS_HELPER_CODE']) && !$arResult['POPUP_BUY_SUBSCRIPTION_PRIORITY'])
						{
							$action = 'top.BX.UI.InfoHelper.show(\'' . $arResult['REST_ACCESS_HELPER_CODE'] . '\');';
						}
						else
						{
							$action = 'BX.rest.Marketplace.buySubscription(this, ' . CUtil::PhpToJSObject($arParamsApp) . ');';
						}
					}
					else
					{
						$action = 'BX.Market.Application.install(' . CUtil::PhpToJSObject($arParamsApp) . ');';
					}
				}
				$message = '';
				if ($arResult['APP']['IS_TRIALED'] === 'Y')
				{
					$message = GetMessage('MARKETPLACE_APP_TRIAL');
					$message .= ' ('. $arResult['APP']['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#'] . ')';
				}
				else
				{
					$message = GetMessage('MARKETPLACE_APP_TRIAL');
					if ($arResult['APP']['TRIAL_PERIOD'] > 0)
					{
						$message .= ' ('. FormatDate('ddiff', time(), time() + $arResult['APP']['TRIAL_PERIOD'] * 24 * 60 * 60) . ')';
					}
				}
				$buttonList[] = [
					'TAGS' => [
						'href' => 'javascript:void(0)',
						'onclick' => $action,
						'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
					],
					'TEXT' => $message
				];
			}
		}
		else
		{
			//free
			$arParamsApp['STATUS'] = 'F';
			if ($arResult['REST_ACCESS'])
			{
				$action = 'BX.Market.Application.install( ' . CUtil::PhpToJSObject($arParamsApp) . ');';
			}
			$buttonList[] = [
				'TAGS' => [
					'href' => 'javascript:void(0)',
					'onclick' => $action,
					'class' => 'ui-btn ui-btn-md ui-btn-primary ui-btn-round',
				],
				'TEXT' => GetMessage('MARKETPLACE_APP_INSTALL')
			];
		}
	}
}

//delete
if ($arResult['APP']['ACTIVE'] === 'Y' && $arResult['ADMIN'])
{
	$buttonList[] = [
		'TAGS' => [
			'href' => 'javascript:void(0)',
			'onclick' => 'BX.rest.Marketplace.uninstallConfirm(\''
				. CUtil::JSEscape($arResult['APP']['CODE'])
				. '\',\''
				. CUtil::JSEscape($arResult['ANALYTIC_FROM'])
				. '\');',
			'class' => 'ui-btn ui-btn-md ui-btn-light-border ui-btn-round',
		],
		'TEXT' => GetMessage('MARKETPLACE_APP_DELETE'),
	];
}
?>
<div class="mp-detail" id="detail_cont">
	<div class="mp-detail-main">
		<div class="mp-detail-main-preview" <?php if ($arResult['APP']['ICON']):?>style="background-image: url('<?=$arResult['APP']['ICON']?>')"<?php endif;?>></div>
		<?php /*if ($arResult["APP"]["PROMO"] == "Y"):?>
			<span class="mp_discount_icon"></span>
		<?php endif;*/ ?>
		<div class="mp-detail-main-info">
			<div class="mp-detail-main-title"><?=htmlspecialcharsbx($arResult['APP']['NAME'])?></div>
			<?php
			//additional info
			if (
				$arResult['APP']['ACTIVE'] === 'Y'
				&& is_array($arResult['APP']['APP_STATUS'])
				&& $arResult['APP']['APP_STATUS']['PAYMENT_NOTIFY'] === 'Y'
			):?>
				<div class="ui-alert ui-alert-warning ui-alert-xs" style='margin-top:10px'>
					<span class="ui-alert-message">
						<?=AppTable::getStatusMessage(
							$arResult['APP']['APP_STATUS']['MESSAGE_SUFFIX'],
							$arResult['APP']['APP_STATUS']['MESSAGE_REPLACE']
						)?>
					</span>
				</div>
			<?php elseif (!empty($arResult['APP']['SHORT_DESC'])):?>
				<div class="mp-detail-main-description" data-role="mp-detail-main-description">
					<div
						class="mp-detail-main-description-wrapper"
						data-role="mp-detail-main-description-wrapper"
					>
						<?=$arResult['APP']['SHORT_DESC']?>
					</div>
				</div>
				<!--<div class="mp-detail-main-description-more" data-role="mp-detail-main-description-more">...<?=GetMessage('MARKETPLACE_MORE_BUTTON')?></div>-->
			<?php endif;?>

			<div class="mp-detail-main-controls">
				<?php
				if ($buttonList):
					foreach ($buttonList as $button):
						$tags = '';
						foreach ($button['TAGS'] as $tag => $value)
						{
							$tags .= ' ' . $tag . '="' . $value . '"';
						}
						?>
						<a<?=$tags?>><?=$button['TEXT']?></a>
					<?php
					endforeach;
				elseif ($arResult['APP']['ACTIVE'] === 'Y'):?>
					<div class="ui-btn ui-btn-md ui-btn-no-caps ui-btn-link mp-detail-main-controls-price">
						<?=GetMessage('MARKETPLACE_APP_IS_INSTALLED')?>
					</div>
				<?php else:?>
					<a
						href="javascript:void(0)"
						class="ui-btn ui-btn-md ui-btn-primary ui-btn-round js-employee-install-button"
					><?=GetMessage('MARKETPLACE_APP_INSTALL')?></a>
				<?php endif;?>
				<?php
				if ($arResult['APP']['ACTIVE'] !== 'Y'):?>
					<div class="mp-detail-main-controls-separator"></div>
					<div class="mp-detail-main-controls-info-container">
						<div class="mp-detail-main-controls-offer">
							<?php if ($arResult['APP']['BY_SUBSCRIPTION'] === 'Y'):?>
								<?=GetMessage('MARKETPLACE_APP_BY_SUBSCRIPTION')?>
							<?php elseif ($arResult['APP']['FREE'] === 'N' && is_array($arResult['APP']['PRICE']) && !empty($arResult['APP']['PRICE'])):?>
								<?=GetMessage(
									'MARKETPLACE_APP_PRICE',
									[
										'#PRICE#' => '<strong>' . htmlspecialcharsbx($arResult['APP']['PRICE'][1]) . '</strong>'
									]
								)?>
							<?php else:?>
								<?=GetMessage('MARKETPLACE_APP_FREE')?>
							<?php endif; ?>
						</div>
						<?php if ($arResult['APP']['HIDDEN_BUY'] === 'Y'):?>
							<div class="mp-detail-main-controls-description"><?=GetMessage('REST_MARKETPLACE_HIDDEN_BUY')?></div>
						<?php endif;?>
						<?php if ($arResult['APP']['SUBSCRIPTION_EXPANDS'] === 'Y'):?>
							<div class="mp-detail-main-controls-description"><?=GetMessage('REST_MARKETPLACE_SUBSCRIPTION_EXPANDS')?></div>
						<?php endif;?>
						<?php if ($arResult['APP']['SUBSCRIPTION_REQUIRED'] === 'Y'):?>
							<div class="mp-detail-main-controls-description"><?=GetMessage('REST_MARKETPLACE_SUBSCRIPTION_REQUIRED')?></div>
						<?php endif;?>
						<?php if ($arResult['APP']['EXTERNAL_PAYMENT'] === 'Y'):?>
							<div class="mp-detail-main-controls-description"><?=GetMessage('REST_MARKETPLACE_EXTERNAL_PAYMENT')?></div>
						<?php endif?>
					</div>
				<?php endif;?>
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
			<div class="mp-detail-info-owner-title"><?=GetMessage('MARKETPLACE_APP_DEVELOPER')?></div>
			<div class="mp-detail-info-owner-name">
				<?php if ($arResult['APP']['PARTNER_URL'] <> ''):?>
					<a href="<?=htmlspecialcharsbx($arResult['APP']['PARTNER_URL'])?>" target="_blank"><?=htmlspecialcharsbx($arResult['APP']['PARTNER_NAME'])?></a>
				<?php else:?>
					<?=htmlspecialcharsbx($arResult['APP']['PARTNER_NAME'])?>
				<?php endif?>
			</div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage('MARKETPLACE_APP_NUM_INSTALLS', array('#NUM_INSTALLS#' => htmlspecialcharsbx($arResult['APP']['NUM_INSTALLS'])))?></div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage('MARKETPLACE_APP_VERSION', array('#VER#' => htmlspecialcharsbx($arResult['APP']['VER'])))?></div>
		</div>

		<div class="mp-detail-info-installs">
			<div class="mp-detail-info-installs-title"><?=GetMessage('MARKETPLACE_APP_PUBLIC_DATE', array('#DATE#' => htmlspecialcharsbx($arResult['APP']['DATE_PUBLIC'])))?></div>
		</div>

		<?php if ($arResult['APP']['DATE_UPDATE'] <> ''):?>
			<div class="mp-detail-info-installs">
				<div class="mp-detail-info-installs-title"><?=GetMessage('MARKETPLACE_APP_UPDATE_DATE', array('#DATE#' => htmlspecialcharsbx($arResult['APP']['DATE_UPDATE'])))?></div>
			</div>
		<?php endif;?>
		<!--<div class="mp-detail-info-other-apps">
			<a href="#" target="_blank">other apps of developer</a>
		</div>-->
	</div>

	<div class="mp-detail-content">
		<div class="mp-detail-content-menu">
			<div class="mp-detail-content-menu-item mp-detail-content-menu-item-active" for="mp-detail-content-wrapper-desc"><?=GetMessage('MARKETPLACE_APP_DESCR_TAB')?></div>
			<div class="mp-detail-content-menu-item" for="mp-detail-content-wrapper-versions"><?=GetMessage('MARKETPLACE_APP_VERSIONS_TAB')?></div>
			<div class="mp-detail-content-menu-item " for="mp-detail-content-wrapper-support"><?=GetMessage('MARKETPLACE_APP_SUPPORT_TAB')?></div>
			<div class="mp-detail-content-menu-item" for="mp-detail-content-wrapper-install"><?=GetMessage('MARKETPLACE_APP_INSTALL_TAB')?></div>
			<div class="mp-detail-content-menu-border" data-role="mp-detail-content-menu-border"></div>
		</div>
		<div class="mp-detail-content-wrapper">
			<div class="mp-detail-content-wrapper-item mp-detail-content-wrapper-item-active" id="mp-detail-content-wrapper-desc">
				<?php
				if (isset($arResult['APP']['DESC_LANDING']) && !empty($arResult['APP']['DESC_LANDING']))
				{
				?>
					<div class="mp-detail-iframe-cont">
						<iframe src="<?=$arResult['APP']['DESC_LANDING']?>" frameborder="no" class="mp-detail-iframe" id="mp-detail-iframe"></iframe>
					</div>
				<?php
				}
				else
				{
					?>
					<?=$arResult['APP']['DESC']?>
					<?php
					if (is_array($arResult['APP']['IMAGES']) && count($arResult['APP']['IMAGES']) > 0)
					{
						?>
						<div class="mp-detail-image-scroller" data-role="mp-detail-image-scroller">
							<div class="mp-detail-image-scroller-wrapper">
								<?php foreach ($arResult['APP']['IMAGES'] as $src):?>
									<img class="mp-detail-image-scroller-item" src="<?=$src?>" alt="" data-viewer data-viewer-group-by="mp-img" data-actions="[]">
								<?php endforeach;?>
							</div>
						</div>
						<script>
							var MarketplaceDetailImageScroller = new BX.Rest.Marketplace.DetailImageScroller({
								target: document.querySelector('[data-role="mp-detail-image-scroller"]')
							});
							MarketplaceDetailImageScroller.init();
						</script>
						<?php
					}
				}
				?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-versions">
				<?php foreach ($arResult['APP']['VERSIONS'] as $number => $desc):?>
					<p class="mp-detail-content-version-title"><?=GetMessage('MARKETPLACE_APP_VERSION_MESS')?> <?=$number?></p>
					<div class="mp-detail-content-version-desc"><?=$desc?></div>
				<?php endforeach; ?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-support">
				<?=$arResult['APP']['SUPPORT']?>
			</div>
			<div class="mp-detail-content-wrapper-item" id="mp-detail-content-wrapper-install">
				<?=$arResult['APP']['INSTALL']?>
			</div>
		</div>
	</div>
</div>

<?php
$arJSParams = array(
	'ajaxPath' => $this->GetFolder().'/ajax.php',
	'siteId' => SITE_ID,
	'appName' => $arResult['APP']['NAME'],
	'appCode' => $arResult['APP']['CODE'],
	'importUrl' => $arResult['IMPORT_PAGE'],
	'openImport' => (
		$arResult['APP']['INSTALLED'] === AppTable::NOT_INSTALLED
		&& $arResult['APP']['TYPE'] === AppTable::TYPE_CONFIGURATION
		&& $arResult['APP']['ACTIVE'] === AppTable::ACTIVE
	),
);
?>

<script type="text/javascript">
	BX.message({
		"MARKETPLACE_APP_INSTALL_REQUEST" : "<?=GetMessageJS('MARKETPLACE_APP_INSTALL_REQUEST')?>",
		"MARKETPLACE_LICENSE_ERROR" : "<?=GetMessageJS('MARKETPLACE_LICENSE_ERROR')?>",
		"MARKETPLACE_LICENSE_TOS_ERROR_2" : "<?=GetMessageJS('MARKETPLACE_LICENSE_TOS_ERROR_2')?>",
		"REST_MP_INSTALL_REQUEST_CONFIRM" : "<?=GetMessageJS('REST_MP_INSTALL_REQUEST_CONFIRM')?>",
		"REST_MP_APP_INSTALL_REQUEST" : "<?=GetMessageJS('REST_MP_APP_INSTALL_REQUEST')?>"
	});
	BX.Rest.Marketplace.Detail.init(<?=CUtil::PhpToJSObject($arJSParams)?>);
	BX.viewImageBind('detail_img_block', {resize: 'WH',cycle: true}, {tag:'IMG'});
	<?php if ($arResult['START_INSTALL'] && $arResult['REST_ACCESS']):?>
		BX.Market.Application.install(<?=CUtil::PhpToJSObject($arParamsApp)?>);
	<?php endif;?>
</script>
