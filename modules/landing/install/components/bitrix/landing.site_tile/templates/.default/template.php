<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @var string $templateFolder */
/** @var \LandingSiteTileComponent $component */

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'sidepanel',
	'main.qrcode',
	'ui.dialogs.messagebox',
]);

//todo: when no site has been created yet, we display a banner but simply without a button
// if (!$arParams['ITEMS'] && !$arParams['PAGE_URL_SITE_ADD'])
// {
// 	return;
// }

$isAjax = $component->isAjax();
?>

<script>
	BX.ready(function()
	{
		<?if ($arResult['SIDE_PANEL_SHORT'] && !$isAjax):?>
		BX.SidePanel.Instance.bindAnchors({
			rules: [
				{
					condition: <?= \CUtil::PhpToJSObject($arResult['SIDE_PANEL_SHORT'])?>,
					stopParameters: ['tab', 'action'],
					options: {
						allowChangeHistory: false,
						width: 600,
						contentClassName: 'landing-site-contacts-wrapper'
					}
				}
			]
		});
		<?endif;?>
		<?php if ($arResult['SIDE_PANEL'] && !$isAjax): ?>
		<?php $addUrlCondition = $component->getUrlAddSidepanelCondition(); ?>
		BX.SidePanel.Instance.bindAnchors({
			rules: [
				<?php if ($addUrlCondition): ?>
				{
					condition: [<?= CUtil::phpToJSObject($addUrlCondition) ?>],
					options: {
						allowChangeHistory: false,
						customLeftBoundary: 0,
						cacheable: false,
						<?php if ($arParams['TYPE'] === 'STORE'): ?>
							width: 1200,
						<?php endif; ?>
					}
				},
				<?php endif; ?>
				{
					condition: <?= \CUtil::PhpToJSObject($arResult['SIDE_PANEL'])?>,
					stopParameters: ['tab', 'action'],
					options: { allowChangeHistory: false }
				}
			]
		});
		<?php endif; ?>
	});
</script>

<?if (!$arParams['ITEMS']):
	$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT1');
	if ($arParams['TYPE'] === 'STORE' && \Bitrix\Landing\Manager::getZone() === 'ru')
	{
		$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT6');
	}
	else
	{
		$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT2');
	}
	$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT3');
	if ($arParams['TYPE']  === 'STORE' && \Bitrix\Landing\Manager::getZone() === 'ru')
	{
		$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT7');
	}
	else
	{
		$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT4');
	}
	if ($arParams['TYPE']  === 'STORE')
	{
		$features[] = $component->getMessageType('LANDING_SITE_TILE_EMPTY_FEAT5');
	}
	\trimArr($features, true);
	$langImg = \Bitrix\Landing\Manager::availableOnlyForZone('ru') ? 'ru' : 'en';
	?>
	<div class="landing-sites__grid-empty landing-sites__scope">
		<div class="landing-sites__grid-empty--all-info">
			<div class="landing-sites__grid-empty--info-text-container">
				<div class="landing-sites__grid-empty--info-block-title">
					<div class="landing-sites__grid-empty--title-quickly">
						<?= $component->getMessageType('LANDING_SITE_TILE_EMPTY_HEADER1')?>
					</div>
					<div class="landing-sites__grid-empty--title">
						<?= $component->getMessageType('LANDING_SITE_TILE_EMPTY_HEADER2')?>
					</div>
				</div>
				<div class="landing-sites__grid-empty--info-block-content">
					<ul class="landing-sites__grid-empty--list-items">
						<?foreach ($features as $feature):?>
						<li class="landing-sites__grid-empty--list-item"><?= $feature?></li>
						<?endforeach;?>
					</ul>
					<div class="landing-sites__grid-empty--bth-container">
						<?php if ($arParams['PAGE_URL_SITE_ADD'] !== ''): ?>
							<a href="<?= $arParams['PAGE_URL_SITE_ADD']?>" class="ui-btn ui-btn-lg ui-btn-success landing-sites__grid-empty--bth-radiance">
								<span class="landing-sites__grid-empty--bth-radiance-left"></span>
								<?= $component->getMessageType('LANDING_SITE_TILE_EMPTY_ADD_2')?>
								<span class="landing-sites__grid-empty--bth-radiance-right"></span>
							</a>
						<?php else: ?>
							<p class="landing-sites__grid-empty--text">
								<?= $component->getMessageType('LANDING_SITE_TILE_EMPTY_ADD_NO_RIGHT')?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="landing-sites__grid-empty--info-image-block">
				<img src="<?= $templateFolder?>/images/empty_<?= strtolower($arParams['TYPE'])?>_<?= $langImg?>.png" alt="" class="landing-sites__grid-empty--info-image"/>
			</div>
		</div>
	</div>
	<?return;?>
<?endif;?>

<div class="landing-sites" id="landing-sites"></div>

<script>
	BX.message(<?= \CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);
	BX.ready(function()
	{
		let backend = BX.Landing.Backend.getInstance();
		let items = <?= \CUtil::PhpToJSObject(array_values($arParams['ITEMS']))?>;
		let switchDomainPage = '<?= \CUtil::jsEscape($arParams['PAGE_URL_SITE_DOMAIN_SWITCH'])?>';

		<?if ($arParams['FEEDBACK_CODE']):?>
		<?php
		if ($arParams['TYPE'] === 'PAGE')
		{
			$title = Loc::getMessage('LANDING_SITE_TILE_DEV_HELP');
			$text = Loc::getMessage('LANDING_SITE_TILE_DEV_ORDER');
			$buttonText = Loc::getMessage('LANDING_SITE_TILE_DEV_BTN');
		}
		else
		{
			$title = Loc::getMessage('LANDING_SITE_TILE_DEV_STORE_HELP');
			$text = Loc::getMessage('LANDING_SITE_TILE_DEV_STORE_ORDER');
			$buttonText = Loc::getMessage('LANDING_SITE_TILE_DEV_STORE_BTN');
		}
		?>
		items.push({
			id: '<?= $arParams['FEEDBACK_CODE']?>',
			type: 'itemMarketing',
			title: '<?= \CUtil::jsEscape($title)?>',
			text: '<?= \CUtil::jsEscape($text)?>',
			buttonText: '<?= \CUtil::jsEscape($buttonText)?>',
			onClick: function()
			{
				BX.fireEvent(BX('landing-feedback-<?= $arParams['FEEDBACK_CODE']?>-button'), 'click');
			}
		});
		<?endif;?>

		new BX.Landing.Component.SiteTile({
			renderTo: BX('landing-sites'),
			items: items,
			scrollerText: '<?= CUtil::JSEscape($component->getMessageType('LANDING_SITE_TILE_SCROLLER'))?>',
			notPublishedText: {
				title: '<?= CUtil::JSEscape($component->getMessageType('LANDING_SITE_TILE_NOT_PUBLISHED_TITLE')) ?>',
				message: '<?= CUtil::JSEscape($component->getMessageType('LANDING_SITE_TILE_NOT_PUBLISHED_MSG')) ?>',
			},
		});

		BX.addCustomEvent('BX.Landing.SiteTile:unPublish', function(param) {
			var item = param.data;
			item.lock();
			backend.action('Site::unPublic', {
				id: item.id
			}).then(function()
			{
				if (item.domainStatus === 'success')
				{
					item.updateDomainStatus('unknown');
				}
				item.unLock();
				item.updatePublishedStatus(false);
			});
		});

		var publicationFunc = function(item)
		{
			item.lock();

			backend.action('Site::publication', {
					id: item.id
				})
				.then(function()
				{
					item.updateDomainStatus(item.domainStatus);
					item.unLock();
					item.updatePublishedStatus(true);
				})
				.catch(function(data)
				{
					if (data.type === 'error' && typeof data.result[0] !== 'undefined')
					{
						let errorCode = data.result[0].error;
						let errorText = data.result[0].error_description;
						if (errorCode === 'PUBLIC_SITE_REACHED')
						{
							<?if ($arParams['TYPE'] === 'STORE'):?>
							BX.UI.InfoHelper.show('limit_shop_number');
							<?else:?>
							BX.UI.InfoHelper.show('limit_sites_number');
							<?endif;?>
						}
						else if (errorCode === 'PUBLIC_SITE_REACHED_FREE')
						{
							BX.UI.InfoHelper.show('limit_sites_free');
						}
						else if (errorCode === 'FREE_DOMAIN_IS_NOT_ALLOWED')
						{
							BX.UI.InfoHelper.show('limit_free_domen');
						}
						else if (errorCode === 'EMAIL_NOT_CONFIRMED')
						{
							BX.UI.InfoHelper.show('limit_sites_confirm_email');
						}
						else if (errorCode === 'PHONE_NOT_CONFIRMED' && top.BX.Bitrix24 && top.BX.Bitrix24.PhoneVerify)
						{
							top.BX.Bitrix24.PhoneVerify
								.getInstance()
								.setEntityType('landing_site')
								.setEntityId(item.id)
								.startVerify({mandatory: false})
							;
						}
						else if (typeof BX.Landing.AlertShow !== 'undefined')
						{
							BX.Landing.AlertShow({
								message: errorText
							});
						}
						else
						{
							alert(errorText);
						}
					}
					item.unLock();
				});
		}

		BX.addCustomEvent('BX.Landing.SiteTile:publish', function(param) {
			var item = param.data;

			<?if ($arResult['AGREEMENT']):?>
			if (typeof landingAgreementPopup !== 'undefined')
			{
				landingAgreementPopup({
					success: function()
					{
						publicationFunc(item);
					}
				});
				return;
			}
			<?endif;?>

			publicationFunc(item);
		});

		BX.addCustomEvent('BX.Landing.SiteTile:remove', function(param) {
			var item = param.data[0];
			var messageBox = param.data[1];
			item.lock();
			backend.action('Site::markDelete', {
				id: item.id
			}).then(function()
			{
				item.remove();
				top.BX.onCustomEvent('BX.Landing.Filter:apply');
			}).catch(function(err)
			{
				if (item.domainProvider && item.domainProvider.length > 0)
				{
					top.BX.SidePanel.Instance.open(
						switchDomainPage.replace('#site_edit#', item.id),
						{
							width: 750,
							allowChangeHistory: false,
							events: {
								onClose: function(event)
								{
									top.BX.onCustomEvent('BX.Landing.Filter:apply');
								}
							}
						}
					);
				}
			});
		});

		BX.addCustomEvent('BX.Landing.SiteTile:restore', function(param) {
			var item = param.data;
			item.lock();
			backend.action('Site::markUnDelete', {
				id: item.id
			}).then(function()
			{
				item.remove();
			});
		});
		<?if ($arParams['TYPE'] === 'STORE'):?>
		BX.addCustomEvent('BX.Landing.SiteTile:onBottomMenuClick', function(param) {
			var type = param.data[0];
			var event = param.data[1];
			var item = param.data[2];
			if (type === 'orders')
			{
				if (item.ordersCount <= 0)
				{
					item.getPopupHelper().show();
					event.preventDefault();
				}
			}
		});
		<?endif;?>
	});
</script>
