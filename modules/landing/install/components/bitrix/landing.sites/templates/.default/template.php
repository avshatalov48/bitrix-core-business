<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
}

if ($arResult['FATAL'])
{
	return;
}

// title
\Bitrix\Landing\Manager::setPageTitle(
	$this->__component->getMessageType('LANDING_TPL_TITLE')
);

// assets
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings landing-tile no-background');

\CJSCore::Init(array('sidepanel', 'landing_master', 'action_dialog'));
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">
		<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
			<?$urlEdit = str_replace('#site_edit#', 0, $arParams['PAGE_URL_SITE_EDIT']);?>
			<span class="landing-item-inner" data-href="<?=$urlEdit?>">
				<span class="landing-item-add-new-inner">
					<span class="landing-item-add-icon"></span>
					<span class="landing-item-text">
						<?= $this->__component->getMessageType('LANDING_TPL_ACTION_ADD');?>
					</span>
				</span>
			</span>
		</div>

		<?foreach ($arResult['SITES'] as $item):

			if ($item['DELETE_FINISH'])//@tmp
			{
				continue;
			}

			$urlEdit = str_replace('#site_edit#', $item['ID'], $arParams['PAGE_URL_SITE_EDIT']);
			$urlCreatePage = str_replace(array('#site_show#', '#landing_edit#'), array($item['ID'], 0), $arParams['PAGE_URL_LANDING_EDIT']);
			$urlView = str_replace('#site_show#', $item['ID'], $arParams['PAGE_URL_SITE']);
			?>
			<div class="landing-item <?
					?><?= $item['ACTIVE'] != 'Y' || $item['DELETED'] != 'N' ? ' landing-item-unactive' : '';?><?
					?><?= $item['DELETED'] == 'Y' ? ' landing-item-deleted' : '';?>">
				<div class="landing-item-inner">
					<div class="landing-title">
						<div class="landing-title-btn"
							 onclick="showTileMenu(this,{
									ID: '<?= $item['ID']?>',
									publicUrl: '<?= \CUtil::jsEscape(\htmlspecialcharsbx($item['PUBLIC_URL']));?>',
									viewSite: '<?= \CUtil::jsEscape($urlView);?>',
									createPage: '<?= \CUtil::jsEscape($urlCreatePage);?>',
									deleteSite: '#',
									editSite:'<?= \CUtil::jsEscape($urlEdit);?>',
									publicPage: '#',
								 	isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
								 	isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>
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
				<?else:?>
					<a href="<?= $urlView;?>" class="landing-item-link" target="_top"></a>
				<?endif;?>
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
			</div>
		<?endforeach;?>
	</div>
</div>

<?if ($arResult['NAVIGATION']->getPageCount() > 1):?>
	<div class="<?= (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '' : 'landing-navigation';?>">
			<?$APPLICATION->IncludeComponent(
				'bitrix:main.pagenavigation',
				'',//grid
				array(
					'NAV_OBJECT' => $arResult['NAVIGATION'],
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

	function showTileMenu(node, params)
	{
		var menuItems = [
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
				href: params.viewSite,
				disabled: params.isDeleted,
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
				className: 'landing-popup-menu-item-icon',
				disabled: params.isDeleted,
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
						}.bind(this),250);
					}.bind(this))
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_GOTO'));?>',
				className: 'landing-popup-menu-item-icon',
				href: params.publicUrl,
				target: '_blank',
				disabled: params.isDeleted,
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_ADDPAGE'));?>',
				href: params.createPage,
				disabled: params.isDeleted,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT'));?>',
				href: params.editSite,
				disabled: params.isDeleted,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: params.isActive
					? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC'));?>'
					: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC'));?>',
				href: params.publicPage,
				<?if ($folderId):?>
				disabled: params.isFolder || params.isDeleted,
				<?else:?>
				disabled: params.isDeleted,
				<?endif;?>
				onclick: function(event)
				{
					event.preventDefault();

					tileGrid.action(
						params.isActive
						? 'Site::unpublic'
						: 'Site::publication',
						{
							id: params.ID
						}
					);

					this.popupWindow.close();
				}
			},
			{
				text: params.isDeleted
						? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNDELETE'));?>'
						: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE'));?>',
				href: params.deleteSite,
				onclick: function(event)
				{
					event.preventDefault();
					this.popupWindow.close();

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
			}
		];

		var menu = new BX.PopupMenuWindow('landing-popup-menu' + params.ID, node, menuItems,{
			autoHide : true,
			offsetTop: -2,
			offsetLeft: -55,
			className: 'landing-popup-menu'
		});

		menu.show();
	}

</script>