<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Main\UI\PageNavigation $navigation */

use \Bitrix\Landing\Manager;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$context = \Bitrix\Main\Application::getInstance()->getContext();
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

// errors title
Manager::setPageTitle(
	$this->__component->getMessageType('LANDING_TPL_TITLE')
);
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
\CJSCore::init([
	'sidepanel', 'landing_master', 'action_dialog'
]);
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\Page\Asset::getInstance()->addCSS(
	'/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css'
);
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">
		<?if ($arResult['ACCESS_SITE_NEW'] == 'Y'):?>
		<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
			<?$urlEdit = str_replace('#site_edit#', 0, $arParams['PAGE_URL_SITE_EDIT']);?>
			<span class="landing-item-inner" data-href="<?= $urlEdit;?>">
				<span class="landing-item-add-new-inner">
					<span class="landing-item-add-icon"></span>
					<span class="landing-item-text">
						<?= $this->__component->getMessageType('LANDING_TPL_ACTION_ADD');?>
					</span>
				</span>
			</span>
		</div>
		<?endif;?>

		<?foreach ($arResult['SITES'] as $item):

			if ($item['DELETE_FINISH'])//@tmp
			{
				continue;
			}

			// actions / urls
			$urlEdit = str_replace('#site_edit#', $item['ID'], $arParams['~PAGE_URL_SITE_EDIT']);
			$urlCreatePage = str_replace(array('#site_show#', '#landing_edit#'), array($item['ID'], 0), $arParams['~PAGE_URL_LANDING_EDIT']);
			$urlView = str_replace('#site_show#', $item['ID'], $arParams['~PAGE_URL_SITE']);
			if ($arParams['DRAFT_MODE'] == 'Y' && $item['DELETED'] != 'Y')
			{
				$item['ACTIVE'] = 'Y';
			}
			?>
			<div class="landing-item <?
					?><?= $item['ACTIVE'] != 'Y' || $item['DELETED'] != 'N' ? ' landing-item-unactive' : '';?><?
					?><?= $item['DELETED'] == 'Y' ? ' landing-item-deleted' : '';?>">
				<div class="landing-item-inner">
					<div class="landing-title">
						<div class="landing-title-btn"
							 onclick="showTileMenu(this,{
									ID: '<?= $item['ID']?>',
									domainId: '<?= $item['DOMAIN_ID']?>',
									domainName: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['DOMAIN_NAME']));?>',
									domainB24Name: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['DOMAIN_B24_NAME']));?>',
									publicUrl: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['PUBLIC_URL']));?>',
									viewSite: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlView));?>',
									createPage: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlCreatePage));?>',
									deleteSite: '#',
									editSite: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlEdit));?>',
								 	exportSite: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['EXPORT_URI']));?>',
									publicPage: '#',
								 	isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
								 	isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>,
								 	isEditDisabled: <?= ($item['ACCESS_EDIT'] != 'Y') ? 'true' : 'false';?>,
								 	isSettingsDisabled: <?= ($item['ACCESS_SETTINGS'] != 'Y') ? 'true' : 'false';?>,
								 	isPublicationDisabled: <?= ($item['ACCESS_PUBLICATION'] != 'Y') ? 'true' : 'false';?>,
								 	isDeleteDisabled: <?= ($item['ACCESS_DELETE'] != 'Y') ? 'true' : 'false';?>
								}
							)">
							<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS')?></span>
						</div>
						<div class="landing-title-wrap">
							<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
						</div>
					</div>
					<span class="landing-item-cover"
						<?if ($item['PREVIEW']) {?> style="background-image: url(<?= \htmlspecialcharsbx($item['PREVIEW'])?>);"<?}?>>
					</span>
				</div>
				<?if ($item['DELETED'] == 'Y'):?>
					<span class="landing-item-link"></span>
				<?elseif ($arParams['TILE_MODE'] == 'view' && $item['PUBLIC_URL']):?>
					<a href="<?= \htmlspecialcharsbx($item['PUBLIC_URL']);?>" class="landing-item-link"></a>
				<?elseif ($urlView):?>
					<a href="<?= $urlView;?>" class="landing-item-link"></a>
				<?else:?>
					<span class="landing-item-link"></span>
				<?endif;?>
				<?if ($arParams['OVER_TITLE']):?>
					<div class="landing-item-btn-wrap">
						<button class="landing-item-btn" type="button"><?= $arParams['OVER_TITLE'];?></button>
					</div>
				<?endif;?>
				<?if ($arParams['DRAFT_MODE'] != 'Y' || $item['DELETED'] == 'Y'):?>
				<div class="landing-item-status-block">
					<div class="landing-item-status-inner">
						<?if ($item['DELETED'] == 'Y'):?>
							<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_DELETED');?></span>
						<?elseif ($item['ACTIVE'] != 'Y'):?>
							<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_UNPUBLIC');?></span>
						<?else:?>
							<span class="landing-item-status landing-item-status-published"><?= Loc::getMessage('LANDING_TPL_PUBLIC');?></span>
						<?endif;?>
						<?if ($item['DELETED'] == 'Y'):?>
						<span class="landing-item-status landing-item-status-changed">
							<?= Loc::getMessage('LANDING_TPL_TTL_DELETE');?>:
							<?= $item['DATE_DELETED_DAYS'];?>
							<?= Loc::getMessage('LANDING_TPL_TTL_DELETE_D');?>
						</span>
						<?endif;?>
					</div>
				</div>
				<?endif;?>
			</div>
		<?endforeach;?>

		<?
		// show developer sites (from main module)
		if ($lastPage && $arParams['TYPE'] == 'PAGE' && $arResult['SMN_SITES'])
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
									 publicUrl: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['PUBLIC_URL']));?>',
									 viewSite: '',
									 createPage: '',
									 deleteSite: '',
									 editSite: '/bitrix/admin/site_edit.php?lang=<?= LANGUAGE_ID;?>&amp;LID=<?= $item['LID'];?>',
									 exportSite: '',
									 publicPage: '',
									 isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
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
								<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['NAME'])?></div>
							</div>
						</div>
						<span class="landing-item-cover" style="background-image: url('/bitrix/images/landing/dev_site.png');">
					</span>
					</div>
					<?if ($item['PUBLIC_URL']):?>
					<a href="<?= \htmlspecialcharsbx($item['PUBLIC_URL']);?>" target="_blank" class="landing-item-link"></a>
					<?else:?>
					<span class="landing-item-link"></span>
					<?endif;?>
					<div class="landing-item-status-block">
						<div class="landing-item-status-inner">
							<?if ($item['ACTIVE'] != 'Y'):?>
								<span class="landing-item-status landing-item-status-unpublished"><?= Loc::getMessage('LANDING_TPL_UNPUBLIC');?></span>
							<?else:?>
								<span class="landing-item-status landing-item-status-published"><?= Loc::getMessage('LANDING_TPL_PUBLIC');?></span>
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
		?>

	</div>
</div>

<?if (false && $request->get('IS_AJAX') != 'Y' && Manager::isB24()):?>
<div id="landing_domain_popup" style="display: none; width: 400px;">
	<p><?= Loc::getMessage('LANDING_TPL_TRANSFER_NOTE');?></p>
	<p id="landing_domain_address_allow" style="display: none;">
		<?= Loc::getMessage('LANDING_TPL_TRANSFER_NEW_ADDRESS');?>:
	</p>
	<p id="landing_domain_address_disallow" style="display: none; color: #d2000d;">
		<?= Loc::getMessage('LANDING_TPL_TRANSFER_NEW_ADDRESS_DISABLED');?>:
	</p>
	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.domain_rename',
		'.default',
		array(
			'TYPE' => 'STORE',
			'FIELD_ID' => 'new_domain_name'
		),
		false
	);?>
	<?if ($helpUrl = \Bitrix\Landing\Help::getHelpUrl('SITE_TRANSFER')):?>
	<p><a href="<?= $helpUrl;?>" target="_blank"><?= Loc::getMessage('LANDING_TPL_TRANSFER_HELP_LINK');?></a></p>
	<?endif;?>
</div>
<?endif;?>

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


<script type="text/javascript">
	if (
		typeof BX.SidePanel !== 'undefined' &&
		typeof BX.SidePanel.Instance !== 'undefined'
	)
	{
		BX.SidePanel.Instance.bindAnchors(
			top.BX.clone({
				rules: [
					{
						condition: [
							'<?= str_replace('#site_edit#', '(\\\d+)', \CUtil::jsEscape($arParams['PAGE_URL_SITE_EDIT']));?>',
							'<?= str_replace(array('#site_show#', '#landing_edit#'), '(\\\d+)', \CUtil::jsEscape($arParams['PAGE_URL_LANDING_EDIT']));?>'
						],
						stopParameters: [
							'action',
							'fields%5Bdelete%5D'
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

    BX.bind(document.querySelector('.landing-item-add-new span.landing-item-inner'), 'click', function(event) {
        BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
            allowChangeHistory: false
        });
    });

	var tileGrid;
	var isMenuShown = false;
	var menu;

	BX.ready(function ()
	{
		var wrapper = BX('grid-tile-wrap');
		var title_list = Array.prototype.slice.call(wrapper.getElementsByClassName('landing-item'));
		tileGrid = new BX.Landing.TileGrid({
			wrapper: wrapper,
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
		<?else:?>
		if (createFolderEl)
		{
			BX.removeClass(createFolderEl, 'ui-btn-disabled');
		}
		if (createElement)
		{
			BX.removeClass(createElement, 'ui-btn-disabled');
		}
		<?endif;?>
	});

	if (typeof showTileMenu === 'undefined')
	{
		function showTileMenu(node, params)
		{
			var menuItems = [
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
					href: params.viewSite,
					disabled: !params.viewSite || params.isDeleted,
				},
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
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
					text: '<?= \CUtil::jsEscape($component->getMessageType('LANDING_TPL_ACTION_GOTO'));?>',
					className: 'landing-popup-menu-item-icon',
					href: params.publicUrl,
					target: '_blank',
					disabled: !params.publicUrl || params.isDeleted || !params.isActive,
				},
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_ADDPAGE'));?>',
					href: params.createPage,
					disabled: params.isDeleted || params.isEditDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				{
					text: '<?= \CUtil::jsEscape($this->__component->getMessageType('LANDING_TPL_ACTION_EDIT'));?>',
					href: params.editSite,
					target: '_blank',
					disabled: params.isDeleted || params.isSettingsDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				<?if ($arParams['DRAFT_MODE'] != 'Y'):?>
				{
					text: params.isActive
						? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC'));?>'
						: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC'));?>',
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
								'<?= \CUtil::jsEscape($this->getComponent()->getName());?>'
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
				<?endif;?>
				{
					text: params.isDeleted
							? '<?= \CUtil::jsEscape($this->__component->getMessageType('LANDING_TPL_ACTION_UNDELETE'));?>'
							: '<?= \CUtil::jsEscape($this->__component->getMessageType('LANDING_TPL_ACTION_DELETE'));?>',
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
						else
						{
							BX.Landing.UI.Tool.ActionDialog.getInstance()
								.show({
									content: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_CONFIRM'));?>'
								})
								.then(
									function() {
										//BX.Landing.History.getInstance().removePageHistory(params.ID);
										tileGrid.action(
											'Site::markDelete',
											{
												id: params.ID
											}
										);
									},
									function() {

									}
								);
						}
					}
				},
				<?if (defined('ASD_TTT')):?>
				params.exportSite
				? {
					delimiter: true
				}
				: null,
				params.exportSite
				? {
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EXPORT'));?>',
					href: params.exportSite
					<?if ($arResult['EXPORT_ENABLED'] == 'N'):?>
					,onclick: function(event)
					{
						var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
						msg.show({
							content: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_EXPORT_DISABLED'));?>',
							confirm: 'OK',
							type: 'alert'
						});
						BX.PreventDefault(event);
					}
					<?endif;?>
				}
				: null,
				<?endif;?>
				<?if (false && $arParams['TYPE'] != 'STORE' && \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')):?>
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_TRANSFER'));?>',
					onclick: function(event)
					{
						tileGrid.transfer(params.ID, {
							type: 'store',
							domainId: params.domainId,
							domainName: params.domainName
											.replace('.bitrix24site.by', '.bitrix24shop.by')
											.replace('.bitrix24.site', '.bitrix24.shop'),
							domainB24Name: params.domainB24Name
						});
						BX.PreventDefault(event);
					}
				}
				<?endif;?>
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
if ($arResult['AGREEMENT'])
{
	include Manager::getDocRoot() . '/bitrix/components/bitrix/landing.start/templates/.default/popups/agreement.php';
}
