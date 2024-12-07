<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Main\UI\PageNavigation $navigation */
/** @var \LandingSitesComponent $component */
/** @var \CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

// Tool availability (by intranet settings)
if (!$component->isToolAvailable())
{
	echo $component->getToolUnavailableInfoScript();

	return;
}

use \Bitrix\Crm\Integration\NotificationsManager;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Restriction;
use \Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$zone = Manager::getZone();
$context = \Bitrix\Main\Application::getInstance()->getContext();
$isCrm = Manager::isB24();
$request = $context->getRequest();
$navigation = $arResult['NAVIGATION'];
if ($navigation)
{
	$lastPage = $navigation->getPageCount() == 0 ||
				$navigation->getPageCount() == $navigation->getCurrentPage();
}
else
{
	$lastPage = true;
}

$urlAddCondition = '';
if ($arResult['ACCESS_SITE_NEW'] === 'Y' && !$arResult['IS_DELETED'])
{
	$urlAddCondition = ($arParams['TYPE'] === 'STORE')
		? $component->getUrlAddSidepanelCondition(true, ['super' => 'Y'])
		: $component->getUrlAddSidepanelCondition()
	;
}

// errors title
Manager::setPageTitle($component->getMessageType('LANDING_TPL_TITLE'));
if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
}
if ($arResult['FATAL'])
{
	return;
}

// assets
Manager::setPageView(
	'BodyClass',
	'no-all-paddings landing-tile no-background'
);

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'sidepanel',
	'landing_master',
	'action_dialog',
	'ui.buttons',
]);

\Bitrix\Main\Page\Asset::getInstance()->addCSS(
	'/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css'
);

// for 'group' type replace titles with the same group name
if ($arParams['TYPE'] == \Bitrix\Landing\Site\Type::SCOPE_CODE_GROUP)
{
	$arResult['SITES'] = \Bitrix\Landing\Binding\Group::recognizeSiteTitle(
		$arResult['SITES']
	);
}

// feedback form
if (
	$lastPage && !$arResult['IS_DELETED'] &&
	($arParams['TYPE'] === 'PAGE' || $arParams['TYPE'] === 'KNOWLEDGE'  || $arParams['TYPE'] === 'STORE') &&
	(!isset($arResult['LICENSE']) || $arResult['LICENSE'] !== 'nfr')
)
{
	if ($arParams['TYPE'] === 'KNOWLEDGE')
	{
		$formCode = 'knowledge';
	}
	else if ($arParams['TYPE'] === 'PAGE')
	{
		$formCode = 'developer';
	}
	else
	{
		$formCode = 'store';
	}
	$params = $component->getFeedbackParameters($formCode);
	if (is_array($params))
	{
		$params['TITLE'] = Loc::getMessage('LANDING_TPL_FEEDBACK_FORM_TITLE');
	}
	?>
	<div style="display: none">
		<?$APPLICATION->includeComponent(
			'bitrix:ui.feedback.form',
			'',
			$params
		);?>
	</div>
	<?
}

// slider's script
if ($arResult['EXPORT_DISABLED'] === 'Y')
{
	echo '<script>function landingExportDisabled(){' . Restriction\Manager::getActionCode('limit_sites_transfer') . '}</script>';
}
?>
<?if ($request->get('IS_AJAX') != 'Y'):?>
<script>
	top.BX.addCustomEvent(
		'BX.Rest.Configuration.Install:onFinish',
		function(event)
		{
			if (!!event.data.elementList && event.data.elementList.length > 0)
			{
				var gotoSiteButton = null;
				for (var i = 0; i < event.data.elementList.length; i++)
				{
					gotoSiteButton = event.data.elementList[i];
					var replaces = [];
					if (gotoSiteButton.dataset.isSite === 'Y')
					{
						var sitePath = '<?= CUtil::jsEscape($arParams['PAGE_URL_SITE']);?>';
						replaces.push([/#site_show#/, gotoSiteButton.dataset.siteId]);

						if (gotoSiteButton.dataset.isLanding === 'Y')
						{
							sitePath = '<?= CUtil::jsEscape($arParams['PAGE_URL_LANDING_VIEW']);?>';
							replaces.push([/#landing_edit#/, gotoSiteButton.dataset.landingId]);
						}

						if (gotoSiteButton.getAttribute('href').substr(0, 1) === '#')
						{
							replaces.forEach(function(replace) {
								sitePath = sitePath.replace(replace[0], replace[1]);
							});

							gotoSiteButton.setAttribute('href', sitePath);
							setTimeout(() => {window.location.href = sitePath}, 3000);
						}
					}
				}
			}

			BX.onCustomEvent('BX.Landing.Filter:apply');
		}
	);
</script>

	<?php if (isset($arResult['FORCE_VERIFY_SITE_ID']) && $arResult['FORCE_VERIFY_SITE_ID'] > 0): ?>
		<script>
			if (
				top.BX.Bitrix24
				&& BX.Type.isObject(BX.Bitrix24.PhoneVerify)
			)
			{
				BX.Bitrix24.PhoneVerify
					.getInstance()
					.setEntityType('landing_site')
					.setEntityId(<?= $arResult['FORCE_VERIFY_SITE_ID'] ?>)
					.startVerify({mandatory: false})
				;
			}
		</script>
	<?php endif; ?>

<?endif?>

<?
if ($arParams['TYPE'] !== 'KNOWLEDGE' && $arParams['TYPE'] !== 'GROUP' && $isCrm && (($arParams['OLD_TILE'] ?? 'N') !== 'Y'))
{
	if ($arParams['TYPE'] === 'STORE')
	{
		$ordersLink = 'crm/deal/?redirect_to';
		if (
			Loader::includeModule('crm')
			&& is_callable(['CCrmSaleHelper', 'isWithOrdersMode'])
		)
		{
			$ordersLink = \CCrmSaleHelper::isWithOrdersMode()
				? 'shop/orders/'
				: 'crm/deal/?redirect_to';
		}
		$ordersLink = SITE_DIR . $ordersLink;

			$menuItems = [
			[
				'text' => $component->getMessageType('LANDING_TPL_ACTION_SETTINGS'),
				'href' => $arParams['~PAGE_URL_SITE_SETTINGS'],
				'access' => 'settings',
				'sidepanel' => true
			],
			[
				'delimiter' => true
			],
			$arResult['EXPORT_DISABLED'] === 'Y'
			? [
				  'text' => $component->getMessageType('LANDING_TPL_ACTION_EXPORT'),
				  'onclick' => 'landingExportDisabled();'
			]
			: [
				'text' => $component->getMessageType('LANDING_TPL_ACTION_EXPORT'),
				'href' => $arParams['~PAGE_URL_SITE_EXPORT'],
				'sidepanel' => true
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_PS'),
				'href' => SITE_DIR . 'shop/settings/sale_pay_system/?lang=' . LANGUAGE_ID,
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_ORDERS'),
				'href' => $ordersLink,
				'bottom' => true,
				'code' => 'orders'
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_PRODUCTS'),
				'href' => $arParams['SEF']['site_master'] . '?redirect_to=products',
				'sidepanel' => true,
				'bottom' => true,
				'code' => 'products'
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_MARKETING'),
				'href' => SITE_DIR . 'marketing/?marketing_title=Y',
				'sidepanel' => true,
				'bottom' => true,
				'code' => 'marketing'
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_PAGES'),
				'href' => $arParams['~PAGE_URL_SITE'],
				'bottom' => true,
				'code' => 'pages'
			],
			[
				'href' => $arParams['~PAGE_URL_SITE_DOMAIN'],
				'sidepanel' => true
			],
			[
				'href' => $arParams['~PAGE_URL_SITE_CONTACTS'],
				'shortsidepanel' => true
			],
			[
				'href' => '/bitrix/components/bitrix/sale.bsm.site.master/slider.php',
				'sidepanel' => true
			],
			[
				'delimiter' => true
			]
		];
	}
	else
	{
		$urlCreatePage = $component->getUrlAdd(false, [
			'context_section' => 'site_list',
			'context_element' => 'tile_menu_link',
		]);
		$urlCreatePage = str_replace('%23', '#', $urlCreatePage);
		$menuItems = [
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_ADDPAGE2'),
				'href' => $urlCreatePage,
				'access' => 'edit',
				'sidepanel' => true
			],
			[
				'delimiter' => true
			],
			[
				'text' => $component->getMessageType('LANDING_TPL_ACTION_EDIT'),
				'href' => $arParams['~PAGE_URL_SITE_SETTINGS'],
				'access' => 'settings',
				'sidepanel' => true
			],
			$arResult['EXPORT_DISABLED'] === 'Y'
			? [
				'text' => $component->getMessageType('LANDING_TPL_ACTION_EXPORT'),
				'access' => 'export',
				'onclick' => 'landingExportDisabled();'
			]
			: [
				'text' => $component->getMessageType('LANDING_TPL_ACTION_EXPORT'),
				'href' => $arParams['~PAGE_URL_SITE_EXPORT'],
				'sidepanel' => true
			],
			[
				'delimiter' => true
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_DEALS'),
				'href' => SITE_DIR . 'crm/deal/?redirect_to',
				'bottom' => true,
				'code' => 'orders'
			],
			[
				'text' => Loc::getMessage('LANDING_TPL_ACTION_MARKETING'),
				'href' => '/marketing/?marketing_title=Y',
				'sidepanel' => true,
				'bottom' => true,
				'code' => 'marketing'
			],
			[
				'text' => 'Cookies',
				'href' => $arParams['~PAGE_URL_SITE_SETTINGS'] . '#cookies',
				'bottom' => true,
				'code' => 'cookies'
			],
			[
				'href' => $arParams['~PAGE_URL_SITE_DOMAIN'],
				'sidepanel' => true
			],
			[
				'href' => $arParams['~PAGE_URL_SITE_CONTACTS'],
				'shortsidepanel' => true
			],
			[
				'href' => '/bitrix/components/bitrix/sale.bsm.site.master/slider.php',
				'sidepanel' => true
			],
		];
	}

	$urlAdd = '';
	if ($arResult['ACCESS_SITE_NEW'] === 'Y' && !$arResult['IS_DELETED'])
	{
		$urlAddParams = [
			'context_section' => 'site_list',
			'context_element' => 'banner',
		];
		if ($arParams['TYPE'] === 'STORE')
		{
			$urlAddParams['super'] = 'Y';
		}
		$urlAdd = $component->getUrlAdd(true, $urlAddParams);
	}

	$APPLICATION->includeComponent(
		'bitrix:landing.site_tile',
		'.default',
		[
			'ITEMS' => $arResult['SITES'],
			'TYPE' => $arParams['TYPE'],
			'FEEDBACK_CODE' => $formCode ?? null,
			'PAGE_URL_SITE_ADD' => $urlAdd,
			'PAGE_URL_SITE' => $arParams['~PAGE_URL_SITE'],
			'PAGE_URL_SITE_EDIT' => $arParams['~PAGE_URL_SITE_EDIT'],
			'PAGE_URL_DOMAIN' => $arParams['~PAGE_URL_SITE_DOMAIN'],
			'PAGE_URL_CONTACTS' => $arParams['~PAGE_URL_SITE_CONTACTS'],
			'PAGE_URL_SITE_DOMAIN_SWITCH' => $arParams['~PAGE_URL_SITE_DOMAIN_SWITCH'],
			'PAGE_URL_CRM_ORDERS' => $ordersLink ?? '',
			'MENU_ITEMS' => $menuItems,
			'AGREEMENT' => $arResult['AGREEMENT'],
			'DELETE_LOCKED' => $arResult['DELETE_LOCKED'],
		],
		$component
	);
	if ($navigation->getPageCount() > 1)
	{
		?>
		<div class="landing-navigation --themes">
			<?$APPLICATION->IncludeComponent(
				'bitrix:main.pagenavigation',
				'',
				array(
					'NAV_OBJECT' => $navigation,
					'SEF_MODE' => 'N',
					'BASE_LINK' => $arResult['CUR_URI']
				),
				false
			);?>
		</div>
		<?
	}
	return;
}
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">
		<?php if ($arResult['ACCESS_SITE_NEW'] == 'Y' && $arParams['SHOW_MASTER_BUTTON'] == 'Y'):?>
		<div class="landing-item landing-item-add-new landing-item-add-new-super">
			<?php
			$uriSuperStore = $component->getPageParam(
				str_replace('#site_edit#', 0, $arParams['PAGE_URL_SITE_EDIT']),
				['super' => 'Y']
			);
			?>
			<span class="landing-item-inner" data-href="<?= $uriSuperStore ?>">
				<span class="landing-item-add-new-inner">
					<span class="landing-item-add-icon landing-item-add-icon--new-store"></span>
					<span class="landing-item-text">
						<?= Loc::getMessage('LANDING_TPL_ACTION_ADD_PERSONAL_STORE') ?>
					</span>
				</span>
			</span>
		</div>
		<?php elseif ($arResult['ACCESS_SITE_NEW'] === 'Y' && !$arResult['IS_DELETED']): ?>
		<div class="landing-item landing-item-add-new">
			<?php $urlEdit = str_replace('#site_edit#', 0, $arParams['PAGE_URL_SITE_EDIT']);?>
			<span class="landing-item-inner" data-href="<?= \htmlspecialcharsbx($urlEdit) ?>">
				<span class="landing-item-add-new-inner">
					<span class="landing-item-add-icon"></span>
					<span class="landing-item-text">
						<?= $component->getMessageType('LANDING_TPL_ACTION_ADD') ?>
					</span>
				</span>
			</span>
		</div>
		<?php endif; ?>

		<?php foreach ($arResult['SITES'] as $item):

			// actions / urls
			$urlSettings = str_replace('#site_edit#', $item['ID'], $arParams['~PAGE_URL_SITE_SETTINGS']);
			$urlCreatePage = str_replace(array('#site_show#', '#landing_edit#'), array($item['ID'], 0), $arParams['~PAGE_URL_LANDING_EDIT']);
			$urlView = str_replace('#site_show#', $item['ID'], $arParams['~PAGE_URL_SITE']);
			$urlSwitchDomain = str_replace('#site_edit#', $item['ID'], $arParams['~PAGE_URL_SITE_DOMAIN_SWITCH']);
			if ($arParams['DRAFT_MODE'] === 'Y' && $item['DELETED'] !== 'Y')
			{
				$item['ACTIVE'] = 'Y';
			}
			if (in_array($item['ID'], $arResult['DELETE_LOCKED']))
			{
				$item['ACCESS_DELETE'] = 'N';
			}
			?>
			<div class="landing-item <?php
					?><?= $item['ACTIVE'] !== 'Y' || $item['DELETED'] !== 'N' ? ' landing-item-unactive' : '' ?><?php
					?><?= $item['DELETED'] === 'Y' ? ' landing-item-deleted' : '' ?>">
				<div class="landing-item-inner">
					<div class="landing-title">
						<div class="landing-title-btn"
							 onclick="showTileMenu(this,{
									ID: '<?= $item['ID']?>',
									domainId: '<?= $item['DOMAIN_ID']?>',
									domainProvider: '<?= $item['DOMAIN_PROVIDER']?>',
									domainName: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['DOMAIN_NAME'])) ?>',
									domainB24Name: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['DOMAIN_B24_NAME'])) ?>',
									publicUrl: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['PUBLIC_URL'])) ?>',
									viewSite: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlView)) ?>',
									createPage: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlCreatePage)) ?>',
									switchDomainPage: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlSwitchDomain)) ?>',
									deleteSite: '#',
									editSite: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlSettings)) ?>',
								 	exportSite: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['EXPORT_URI'])) ?>',
								 	isExportSiteDisabled: <?= ($item['ACCESS_EXPORT'] !== 'Y') ? 'true' : 'false' ?>,
									publicPage: '#',
								 	isActive: <?= ($item['ACTIVE'] === 'Y') ? 'true' : 'false' ?>,
								 	isDeleted: <?= ($item['DELETED'] === 'Y') ? 'true' : 'false' ?>,
								 	isEditDisabled: <?= ($item['ACCESS_EDIT'] !== 'Y') ? 'true' : 'false' ?>,
								 	isSettingsDisabled: <?= ($item['ACCESS_SETTINGS'] !== 'Y') ? 'true' : 'false' ?>,
								 	isPublicationDisabled: <?= ($item['ACCESS_PUBLICATION'] !== 'Y') ? 'true' : 'false' ?>,
								 	isDeleteDisabled: <?= ($item['ACCESS_DELETE'] !== 'Y') ? 'true' : 'false' ?>
								}
							)">
							<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS')?></span>
						</div>
						<div class="landing-title-wrap">
							<div class="landing-title-overflow"><?= htmlspecialcharsbx($item['TITLE'])?></div>
						</div>
					</div>
					<span class="landing-item-cover"
						<?php if ($item['PREVIEW']) {?> style="background-image: url(<?= htmlspecialcharsbx($item['PREVIEW'])?>);"<?}?>>
					</span>
				</div>
				<?php if ($item['DELETED'] === 'Y'):?>
					<span class="landing-item-link"></span>
				<?php elseif ($arParams['TILE_MODE'] === 'view' && $item['PUBLIC_URL']):?>
					<a href="<?= htmlspecialcharsbx($item['PUBLIC_URL']) ?>" class="landing-item-link"></a>
				<?php elseif ($urlView):?>
					<a href="<?= $urlView ?>" class="landing-item-link">
						<?php if ($arParams['OVER_TITLE']):?>
							<button class="landing-item-btn" type="button"><?= $arParams['OVER_TITLE'];?></button>
						<?php endif;?>
					</a>
				<?php else:?>
					<span class="landing-item-link"></span>
				<?php endif; ?>
				<?php if ($arParams['DRAFT_MODE'] != 'Y' || $item['DELETED'] == 'Y'):?>
				<div class="landing-item-status-block">
					<div class="landing-item-status-inner">
						<?php if ($item['DELETED'] == 'Y'):?>
							<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_DELETED');?></span>
						<?php elseif ($item['ACTIVE'] != 'Y'):?>
							<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_UNPUBLIC');?></span>
						<?php else:?>
							<span class="landing-item-status landing-item-status-published">
								<?= Loc::getMessage('LANDING_TPL_PUBLIC_URL', ['#LINK#' => '<a href="' . $item['PUBLIC_URL'] . '" target="_blank">' . $item['DOMAIN_NAME'] . '</a>']);?>
							</span>
						<?php endif; ?>
						<?php if ($item['DELETED'] == 'Y'):?>
						<span class="landing-item-status landing-item-status-changed">
							<?= Loc::getMessage('LANDING_TPL_TTL_DELETE');?>:
							<?= $item['DATE_DELETED_DAYS'];?>
							<?= Loc::getMessage('LANDING_TPL_TTL_DELETE_D');?>
						</span>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<?php
		// show developer sites (from main module)
		if ($lastPage && ($arParams['TYPE'] == 'PAGE' || $arParams['TYPE'] == 'STORE') && $arResult['SMN_SITES'])
		{
			foreach ($arResult['SMN_SITES'] as $item)
			{
				?>
				<div class="landing-item <?= $item['ACTIVE'] != 'Y' ? ' landing-item-unactive' : '';?>">
					<div class="landing-item-inner">
						<div class="landing-title">
							<div class="landing-title-btn"
								 onclick="showTileMenu(this,{
									 ID: '<?= $item['ID']?>',
									 domainId: 0,
									 domainName: '',
									 domainB24Name: '',
									 domainProvider: '',
									 publicUrl: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['PUBLIC_URL'])) ?>',
									 viewSite: '',
									 createPage: '',
									 switchDomainPage: '',
									 deleteSite: '',
									 editSite: '/bitrix/admin/site_edit.php?lang=<?= LANGUAGE_ID;?>&amp;LID=<?= $item['LID'] ?>',
									 exportSite: '',
									 publicPage: '',
									 isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false' ?>,
									 isDeleted: false,
									 isEditDisabled: true,
									 isSettingsDisabled: false,
									 isPublicationDisabled: true,
									 isDeleteDisabled: true
									 }
									 )">
								<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS')?></span>
							</div>
							<div class="landing-title-wrap">
								<div class="landing-title-overflow"><?= htmlspecialcharsbx($item['NAME'])?></div>
							</div>
						</div>
						<span class="landing-item-cover" style="background-image: url('/bitrix/images/landing/dev_site.png');">
					</span>
					</div>
					<?if ($item['PUBLIC_URL']):?>
					<a href="<?= htmlspecialcharsbx($item['PUBLIC_URL']);?>" target="_blank" class="landing-item-link"></a>
					<?else:?>
					<span class="landing-item-link"></span>
					<?endif;?>
					<div class="landing-item-status-block">
						<div class="landing-item-status-inner">
							<?if ($item['ACTIVE'] != 'Y'):?>
								<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_UNPUBLIC');?></span>
							<?else:?>
								<span class="landing-item-status landing-item-status-published">
									<?= Loc::getMessage('LANDING_TPL_PUBLIC_URL', ['#LINK#' => '<a href="' . $item['PUBLIC_URL'] . '" target="_blank">' . $item['DOMAIN_NAME'] . '</a>']);?>
								</span>
							<?endif;?>
						</div>
					</div>
				</div>
				<?
			}
		}
		if (
			$lastPage &&
			!$arResult['IS_DELETED'] &&
			($arParams['TYPE'] == 'PAGE' || $arParams['TYPE'] == 'STORE') &&
			!ModuleManager::isModuleInstalled('bitrix24') &&
			ModuleManager::isModuleInstalled('sale')
		)
		{
			$APPLICATION->includeComponent(
				'bitrix:sale.bsm.site.master.button',
				'.default'
			);
		}
		if ($formCode ?? null)
		{
			?>
			<div class="landing-item landing-item-dev" onclick="BX.fireEvent(BX('landing-feedback-<?= $formCode?>-button'), 'click');">
				<span class="landing-item-inner">
					<span class="landing-item-dev-title"><?= $component->getMessageType('LANDING_TPL_DEV_HELP');?></span>
					<span class="landing-item-dev-subtitle"><?= $component->getMessageType('LANDING_TPL_DEV_ORDER_MSGVER_1');?></span>
					<button class="ui-btn ui-btn-primary"><?= $component->getMessageType('LANDING_TPL_DEV_BTN');?></button>
				</span>
			</div>
			<?
		}
		?>

	</div>
</div>

<?if ($navigation->getPageCount() > 1):?>
	<div class="<?= (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '' : 'landing-navigation';?>">
			<?$APPLICATION->IncludeComponent(
				'bitrix:main.pagenavigation',
				'',//grid
				array(
					'NAV_OBJECT' => $navigation,
					'SEF_MODE' => 'N',
					'BASE_LINK' => $arResult['CUR_URI']
				),
				false
			);?>
	</div>
<?endif;?>

<script>
	if (
		typeof BX.SidePanel !== 'undefined' &&
		typeof BX.SidePanel.Instance !== 'undefined'
	)
	{
		var condition = [];
		<?php if ($arParams['PAGE_URL_SITE_SETTINGS']): ?>
		condition.push('<?= str_replace(['#site_edit#', '?'], ['(\\\d+)', '\\\?'], CUtil::jsEscape($arParams['PAGE_URL_SITE_SETTINGS']))?>');
		<?php endif; ?>
		<?php if ($arParams['PAGE_URL_LANDING_EDIT']): ?>
		condition.push('<?= str_replace(['#site_show#', '#landing_edit#', '?'], ['(\\\d+)', '(\\\d+)', '\\\?'], CUtil::jsEscape($arParams['PAGE_URL_LANDING_EDIT'])) ?>');
		<?php endif; ?>
		<?php if ($urlAddCondition <> ''): ?>
		condition.push('<?= $urlAddCondition ?>');
		<?php endif; ?>

		if (condition)
		{
			BX.SidePanel.Instance.bindAnchors(
				top.BX.clone({
					rules: [
						{
							condition: condition,
							stopParameters: [
								'action',
								'folderId',
								'folderUp',
								'fields%5Bdelete%5D',
								'nav'
							],
							options: {
								allowChangeHistory: false,
								events: {
									onOpen: function(event)
									{
										if (BX.hasClass(BX('landing-create-element'), 'ui-btn-disabled'))
										{
											event.denyAction();
										}
									}
								}
							}
						}]
				})
			);
		}
	}

	<?if ($arResult['ACCESS_SITE_NEW'] == 'Y' && $arParams['SHOW_MASTER_BUTTON'] == 'Y'):?>
	BX.bind(document.querySelector('.landing-item-add-new-super span.landing-item-inner'), 'click', function(event) {
		BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
			allowChangeHistory: false,
			width: 1200,
			data: {
				rightBoundary: 0
			}
		});
	});
	<?php elseif ($arResult['ACCESS_SITE_NEW'] == 'Y'):?>
	BX.bind(document.querySelector('.landing-item-add-new span.landing-item-inner'), 'click', function(event) {
		BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
			allowChangeHistory: false
		});
	});
	<?php endif; ?>

	var tileGrid;
	var isMenuShown = false;
	var menu;

	BX.ready(function ()
	{
		var wrapper = BX('grid-tile-wrap');
		var title_list = Array.prototype.slice.call(wrapper.getElementsByClassName('landing-item'));
		tileGrid = new BX.Landing.TileGrid({
			wrapper: wrapper,
			siteType: '<?= $arParams['TYPE'];?>',
			inner: BX('grid-tile-inner'),
			tiles: title_list,
			sizeSettings : {
				minWidth : 350,
				maxWidth: 450
			}
		});

		// disable some buttons for deleted
		var createFolderEl = BX('landing-create-folder');
		var createElement = BX('landing-create-element');

		<?if ($arResult['IS_DELETED']):?>
		if (createFolderEl)
		{
			BX.addClass(createFolderEl, 'ui-btn-disabled');
		}
		if (createElement)
		{
			BX.addClass(createElement, 'ui-btn-disabled');
		}
		<?php else:?>
		if (createFolderEl)
		{
			BX.removeClass(createFolderEl, 'ui-btn-disabled');
		}
		if (createElement)
		{
			BX.removeClass(createElement, 'ui-btn-disabled');
		}
		<?php endif; ?>
	});

	if (typeof showTileMenu === 'undefined')
	{
		function showTileMenu(node, params)
		{
			if (typeof showTileMenuCustom === 'function')
			{
				showTileMenuCustom(node, params);
				return;
			}
			var menuItems = [
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
					href: params.viewSite,
					disabled: !params.viewSite || params.isDeleted,
				},
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
					className: 'landing-popup-menu-item-icon',
					disabled: !params.publicUrl || params.isDeleted,
					onclick: function(e, item)
					{
						if (BX.clipboard.isCopySupported())
						{
							BX.clipboard.copy(params.publicUrl);
						}
						var menuItem = item.layout.item;
						menuItem.classList.add('landing-link-copied');

						BX.bind(menuItem.childNodes[0], 'transitionend', function ()
						{
							setTimeout(function()
							{
								this.popupWindow.close();
								menuItem.classList.remove('landing-link-copied');
								menu.destroy();
								isMenuShown = false;
							}.bind(this),250);
						}.bind(this))
					}
				},
				{
					text: '<?= CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_GOTO'));?>',
					className: 'landing-popup-menu-item-icon',
					href: params.publicUrl,
					target: '_blank',
					disabled: !params.publicUrl || params.isDeleted || !params.isActive,
				},
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_ADDPAGE'));?>',
					href: params.createPage,
					disabled: params.isDeleted || params.isEditDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				{
					text: '<?= CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_EDIT'));?>',
					href: params.editSite,
					target: '_blank',
					disabled: params.isDeleted || params.isSettingsDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				<?php if ($arParams['DRAFT_MODE'] != 'Y'):?>
				{
					text: params.isActive
						? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC'));?>'
						: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC'));?>',
					href: params.publicPage,
					disabled: params.isDeleted || params.isPublicationDisabled,
					onclick: function(event)
					{
						event.preventDefault();

						var successFunction = function()
						{
							tileGrid.action(
								params.isActive
									? 'Site::unpublic'
									: 'Site::publication',
								{
									id: params.ID
								},
								null,
								'<?= CUtil::jsEscape($this->getComponent()->getName());?>'
							);
						};

						if (!params.isActive && <?= $arResult['AGREEMENT'] ? 'true' : 'false';?>)
						{
							landingAgreementPopup({
								success: successFunction
							});
							return;
						}
						else
						{
							successFunction();
							this.popupWindow.close();
						}
						menu.destroy();
					}
				},
				<?php endif; ?>
				params.exportSite
					? {
						text: '<?= CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_EXPORT'));?>',
						disabled: params.isExportSiteDisabled,
						<?if ($arResult['EXPORT_DISABLED'] == 'Y'):?>
						onclick: function(event)
						{
							landingExportDisabled();
							BX.PreventDefault(event);
						}
						<?php else: ?>
						href: params.exportSite
						<?php endif; ?>
					}
					: null,
				params.exportSite
					? {
						delimiter: true
					}
					: null,
				{
					text: params.isDeleted
							? '<?= CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_UNDELETE'));?>'
							: '<?= CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_DELETE'));?>',
					disabled: params.isDeleteDisabled,
					href: params.deleteSite,
					onclick: function(event)
					{
						event.preventDefault();
						this.popupWindow.close();
						menu.destroy();

						if (params.isDeleted)
						{
							tileGrid.action(
								'Site::markUndelete',
								{
									id: params.ID
								}
							);
						}
						else if (params.domainProvider)
						{
							top.BX.SidePanel.Instance.open(
								params.switchDomainPage,
								{
									width: 750,
									allowChangeHistory: false,
									events: {
										onClose: function(event)
										{
											if (event.slider.url.indexOf('switch=Y') !== -1)
											{
												BX.Landing.UI.Tool.ActionDialog.getInstance()
													.show({
														content: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_CONFIRM'));?>'
													})
													.then(
														function() {
															tileGrid.action('Site::markDelete', {id: params.ID});
														}
													);
											}
										}
									}
								}
							);
							BX.PreventDefault();
						}
						else
						{
							BX.Landing.UI.Tool.ActionDialog.getInstance()
								.show({
									content: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_CONFIRM'));?>'
								})
								.then(
									function() {
										tileGrid.action('Site::markDelete', {id: params.ID});
									}
								);
						}
					}
				}
			];

			if (!isMenuShown) {
				menu = new BX.PopupMenuWindow(
					'landing-popup-menu' + params.ID,
					node,
					menuItems,
					{
						autoHide : true,
						offsetTop: -2,
						offsetLeft: -55,
						className: 'landing-popup-menu',
						events: {
							onPopupClose: function onPopupClose() {
								menu.destroy();
								isMenuShown = false;
							},
						},
					}
				);
				menu.show();

				isMenuShown = true;
			}
			else
			{
				menu.destroy();
				isMenuShown = false;
			}

		}
	}

</script>

<?php
if ($arParams['TYPE'] === 'STORE' && Loader::includeModule('crm'))
{
	NotificationsManager::showSignUpFormOnCrmShopCreated();
}

if ($arResult['AGREEMENT'])
{
	include Manager::getDocRoot() . '/bitrix/components/bitrix/landing.start/templates/.default/popups/agreement.php';
}
