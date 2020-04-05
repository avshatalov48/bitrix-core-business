<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Page\Asset;
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

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$folderId = $request->get($arParams['ACTION_FOLDER']);

// title
if (isset($arResult['SITES'][$arParams['SITE_ID']]))
{
	\Bitrix\Landing\Manager::setPageTitle(
		\htmlspecialcharsbx($arResult['SITES'][$arParams['SITE_ID']]['TITLE'])
	);
}

\CJSCore::init(array(
	'landing_master', 'action_dialog', 'clipboard', 'sidepanel'
));

// assets
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background landing-tile landing-tile-pages');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.sites/templates/.default/script.js');
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.sites/templates/.default/style.css');

// get site selector
$siteSelector = '<select id="landing-site-selector" style="display: none;" class="ui-select">';
foreach ($arResult['SITES'] as $site)
{
	$selected = $site['ID'] == $arParams['SITE_ID'] ? ' selected="selected"' : '';
	$siteSelector .= '<option value="' . $site['ID'] . '"' . $selected . '>' .
						\htmlspecialcharsbx($site['TITLE']) .
					'</option>';
}
$siteSelector .= '</select>';
echo $siteSelector;
?>

<div class="grid-tile-wrap landing-pages-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">

	<?if ($folderId):
		$curUrlWoFolder = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
		$curUrlWoFolder->deleteParams(array(
			$arParams['ACTION_FOLDER']
		));
		?>
		<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
			<a class="landing-item-inner" href="<?= \htmlspecialcharsbx($curUrlWoFolder->getUri());?>">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon landing-item-add-icon-up"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_FOLDER_UP');?></span>
			</span>
			</a>
		</div>
	<?endif;?>

	<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
		<?
		$arParams['PAGE_URL_LANDING_ADD'] = str_replace('#landing_edit#', 0, $arParams['PAGE_URL_LANDING_EDIT']);
		if ($folderId)
		{
			$arParams['PAGE_URL_LANDING_ADD'] = new \Bitrix\Main\Web\Uri(
				$arParams['PAGE_URL_LANDING_ADD']
			);
			$arParams['PAGE_URL_LANDING_ADD']->addParams(array(
				$arParams['ACTION_FOLDER'] => $folderId
			));
			$arParams['PAGE_URL_LANDING_ADD'] = $arParams['PAGE_URL_LANDING_ADD']->getUri();
		}
		?>
		<span class="landing-item-inner" data-href="<?= $arParams['PAGE_URL_LANDING_ADD']?>">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_ADD')?></span>
			</span>
		</span>
	</div>

<?foreach (array_values($arResult['LANDINGS']) as $i => $item):

	if ($item['DELETE_FINISH'])//@tmp
	{
		continue;
	}

	$uriFolder = null;
	$urlEdit = str_replace('#landing_edit#', $item['ID'], $arParams['PAGE_URL_LANDING_EDIT']);
	$urlView = str_replace('#landing_edit#', $item['ID'], $arParams['PAGE_URL_LANDING_VIEW']);

	$uriCopy = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriCopy->addParams(array(
		'action' => 'copy',
		'param' => $item['ID'],
		'sessid' => bitrix_sessid()
	));

	if ($item['FOLDER'] == 'Y' && $item['ID'] != $folderId)
	{
		$uriFolder = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
		$uriFolder->addParams(array(
			$arParams['ACTION_FOLDER'] => $item['ID']
		));
	}
	?>
	<?if ($uriFolder):?>
		<div class="landing-item landing-item-folder<?
			?><?= $item['ACTIVE'] != 'Y' || $item['DELETED'] != 'N' ? ' landing-item-unactive' : '';?><?
			?><?= $item['DELETED'] == 'Y' ? ' landing-item-deleted' : '';?>">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
				</div>
			</div>
			<div class="landing-item-cover">
				<div class="landing-item-preview">
					<?foreach ($item['FOLDER_PREVIEW'] as $picture):?>
					<div class="landing-item-preview-item" style="background-image: url(<?= $picture;?>);"></div>
					<?endforeach;?>
				</div>
				<div class="landing-item-folder-corner">
					<div class="landing-item-folder-dropdown"
						 onclick="showTileMenu(this,{
									viewSite: '<?= \CUtil::jsEscape($urlView);?>',
									ID: '<?= $item['ID']?>',
									publicUrl: '<?= \CUtil::jsEscape(\htmlspecialcharsbx($item['PUBLIC_URL']));?>',
									copyPage: '<?= \CUtil::jsEscape($uriCopy->getUri());?>',
									deletePage: '#',
									publicPage: '#',
									editPage: '<?= \CUtil::jsEscape($urlEdit);?>',
							 		isFolder: <?= ($item['FOLDER'] == 'Y') ? 'true' : 'false';?>,
							 		isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
							 		isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>
								})">
						<span class="landing-item-folder-dropdown-inner"></span>
					</div>
				</div>
			</div>
			<?if ($item['DELETED'] == 'Y'):?>
			<span class="landing-item-link"></span>
			<?else:?>
			<a href="<?= $uriFolder->getUri();?>" class="landing-item-link" target="_top"></a>
			<?endif;?>
		</div>
	<?else:?>
		<div class="landing-item<?
			?><?= $item['ACTIVE'] != 'Y' || $item['DELETED'] != 'N' ? ' landing-item-unactive' : '';?><?
			?><?= $item['DELETED'] == 'Y' ? ' landing-item-deleted' : '';?>">
			<div class="landing-item-inner">
				<div class="landing-title">
					<div class="landing-title-btn"
						 onclick="showTileMenu(this,{
									viewSite: '<?= \CUtil::jsEscape($urlView);?>',
									ID: '<?= $item['ID'];?>',
									isArea: <?= $item['IS_AREA'] ? 'true' : 'false';?>,
									publicUrl: '<?= \CUtil::jsEscape(\htmlspecialcharsbx($item['PUBLIC_URL']));?>',
									copyPage: '<?= \CUtil::jsEscape($uriCopy->getUri());?>',
									deletePage: '#',
							 		publicPage: '#',
									editPage: '<?= \CUtil::jsEscape($urlEdit);?>',
							 		isFolder: <?= ($item['FOLDER'] == 'Y') ? 'true' : 'false';?>,
							 		isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
							 		isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>
								})">
						<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS');?></span>
					</div>
					<div class="landing-title-wrap">
						<?if ($item['IS_HOMEPAGE']):?>
							<div class="landing-title-overflow landing-item-home-icon"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
						<?else:?>
							<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
						<?endif;?>
					</div>
				</div>
				<span class="landing-item-cover" <?if ($item['PREVIEW']) {?> style="background-image: url(<?=
					\htmlspecialcharsbx($item['PREVIEW'])?>);"<?}?>></span>
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
					<?elseif ($item['DATE_MODIFY_UNIX'] > $item['DATE_PUBLIC_UNIX']):?>
						<span class="landing-item-status landing-item-status-changed"><?= Loc::getMessage('LANDING_TPL_MODIF');?></span>
					<?endif;?>

				</div>
			</div>
		</div>
	<?endif;?>
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
	BX.SidePanel.Instance.bindAnchors(
		top.BX.clone({
			rules: [
				{
					condition: [
						'<?= str_replace(
							array(
								'#landing_edit#', '?'
							),
							array(
								'(\\\d+)', '\\\?'
							),
							\CUtil::jsEscape($arParams['PAGE_URL_LANDING_EDIT'])
						);?>',
						'<?= str_replace(
							array(
								'#landing_edit#', '?'
							),
							array(
								'(\\\d+)', '\\\?'
							),
							\CUtil::jsEscape($arParams['PAGE_URL_LANDING_ADD'])
						);?>'
					],
					stopParameters: [
						'action',
						'fields%5Bdelete%5D',
						'nav',
						'slider'
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
				minWidth : 280,
				maxWidth: 330
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
				disabled: params.isDeleted,
				onclick: function(e, item)
				{
					window.top.location.href = params.viewSite;
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
				className: 'landing-popup-menu-item-icon',
				disabled: params.isArea || params.isDeleted,
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

					}.bind(this));
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_GOTO'));?>',
				className: 'landing-popup-menu-item-icon',
				href: params.publicUrl,
				target: '_blank',
				disabled: params.isArea || params.isDeleted
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT'));?>',
				href: params.editPage,
				disabled: params.isDeleted,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPY'));?>',
				disabled: params.isDeleted || params.isFolder,
				onclick: function(event)
				{
					event.preventDefault();

					BX.Landing.UI.Tool.ActionDialog.getInstance()
						.show({
							title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPY_TITLE'));?>',
							content: BX('landing-site-selector')
						})
						.then(
							function() {
								params.copyPage += '&additional[siteId]=';
								params.copyPage += BX('landing-site-selector').value;
								top.window.location.href = params.copyPage;
							},
							function() {
								//
							}
						);
					this.popupWindow.close();
				}
			},
			{
				text: params.isActive
					? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC'));?>'
					: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC'));?>',
				href: params.publicPage,
				disabled: params.isDeleted,
				onclick: function(event)
				{
					event.preventDefault();

					tileGrid.action(
						params.isActive
						? 'Landing::unpublic'
						: 'Landing::publication',
						{
							lid: params.ID
						}
					);

					this.popupWindow.close();
				}
			},
			{
				text: params.isDeleted
					? (
						(params.isFolder && <?= !$folderId ? 'true' : 'false';?>)
						? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNDELETE_FOLDER'));?>'
						: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNDELETE'));?>'
					)
					: (
						(params.isFolder && <?= !$folderId ? 'true' : 'false';?>)
						? '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_FOLDER'));?>'
						: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE'));?>'
					),
				href: params.deletePage,
				<?if ($folderId):?>
				disabled: params.isFolder,
				<?endif;?>
				onclick: function(event)
				{
					event.preventDefault();

					this.popupWindow.close();

					if (params.isDeleted)
					{
						tileGrid.action(
							'Landing::markUndelete',
							{
								lid: params.ID
							}
						);
					}
					else
					{
						BX.Landing.UI.Tool.ActionDialog.getInstance()
							.show({
								content: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_REC_CONFIRM'));?>'
							})
							.then(
								function() {
									//BX.Landing.History.getInstance().removePageHistory(params.ID);
									tileGrid.action(
										'Landing::markDelete',
										{
											lid: params.ID
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
