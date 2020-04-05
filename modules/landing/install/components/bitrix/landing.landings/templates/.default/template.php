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
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass.' ' : '') .
	'no-all-paddings no-background landing-tile landing-tile-pages'
);
Asset::getInstance()->addJS(
	'/bitrix/components/bitrix/landing.sites/templates/.default/script.js'
);
Asset::getInstance()->addCSS(
	'/bitrix/components/bitrix/landing.sites/templates/.default/style.css'
);

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

// prepare urls
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

$sliderConditions = [
	str_replace(
		array(
			'#landing_edit#', '?'
		),
		array(
			'(\d+)', '\?'
		),
		\CUtil::jsEscape($arParams['PAGE_URL_LANDING_EDIT'])
	),
	str_replace(
		array(
			'#landing_edit#', '?'
		),
		array(
			'(\d+)', '\?'
		),
		\CUtil::jsEscape($arParams['PAGE_URL_LANDING_ADD'])
	)
];

if ($arParams['TILE_MODE'] == 'view')
{
	$sliderConditions[] = str_replace(
		array(
			'#landing_edit#', '?'
		),
		array(
			'(\d+)', '\?'
		),
		\CUtil::jsEscape($arParams['PAGE_URL_LANDING_VIEW'])
	);
}
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

	<?if ($arResult['ACCESS_SITE']['EDIT'] == 'Y'):?>
	<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
		<span class="landing-item-inner" data-href="<?= $arParams['PAGE_URL_LANDING_ADD']?>">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_ADD')?></span>
			</span>
		</span>
	</div>
	<?endif;?>

<?foreach (array_values($arResult['LANDINGS']) as $i => $item):

	if ($item['DELETE_FINISH'])//@tmp
	{
		continue;
	}

	$uriFolder = null;
	$areaCode = '';
	$areaTitle = '';
	$urlEdit = str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_EDIT']);
	$urlView = str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_VIEW']);

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
	if ($arParams['DRAFT_MODE'] == 'Y' && $item['DELETED'] != 'Y')
	{
		$item['ACTIVE'] = 'Y';
	}

	if ($item['IS_AREA'])
	{
		$areaCode = $item['AREA_CODE'];
		$areaTitle = Loc::getMessage('LANDING_TPL_AREA_' . strtoupper($item['AREA_CODE']));
	}
	else if ($item['IS_HOMEPAGE'])
	{
		$areaCode = 'main_page';
		$areaTitle = Loc::getMessage('LANDING_TPL_AREA_MAIN_PAGE');
	}
	?>
	<?if ($uriFolder):?>
		<div class="landing-item landing-item-folder<?
			?><?= $item['ACTIVE'] != 'Y' || $item['DELETED'] != 'N' ? ' landing-item-unactive' : '';?><?
			?><?= $item['DELETED'] == 'Y' ? ' landing-item-deleted' : '';?>">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE']);?></div>
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
									viewSite: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlView));?>',
									ID: '<?= $item['ID']?>',
									publicUrl: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['PUBLIC_URL']));?>',
									copyPage: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($uriCopy->getUri()));?>',
									deletePage: '#',
									publicPage: '#',
									editPage: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlEdit));?>',
							 		folderIndex: false,
							 		isFolder: <?= ($item['FOLDER'] == 'Y') ? 'true' : 'false';?>,
							 		isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
							 		isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>,
									isEditDisabled: <?= ($arResult['ACCESS_SITE']['EDIT'] != 'Y') ? 'true' : 'false';?>,
									isSettingsDisabled: <?= ($arResult['ACCESS_SITE']['SETTINGS'] != 'Y') ? 'true' : 'false';?>,
									isPublicationDisabled: <?= ($arResult['ACCESS_SITE']['PUBLICATION'] != 'Y') ? 'true' : 'false';?>,
									isDeleteDisabled: <?= ($arResult['ACCESS_SITE']['DELETE'] != 'Y') ? 'true' : 'false';?>
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
									viewSite: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlView));?>',
									ID: '<?= $item['ID'];?>',
									isArea: <?= $item['IS_AREA'] ? 'true' : 'false';?>,
									publicUrl: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['PUBLIC_URL']));?>',
									copyPage: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($uriCopy->getUri()));?>',
									deletePage: '#',
							 		publicPage: '#',
									editPage: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($urlEdit));?>',
						 			folderIndex: <?= ($item['FOLDER'] == 'Y') ? 'true' : 'false';?>,
							 		isFolder: <?= ($item['FOLDER'] == 'Y') ? 'true' : 'false';?>,
							 		isActive: <?= ($item['ACTIVE'] == 'Y') ? 'true' : 'false';?>,
							 		isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>,
									isEditDisabled: <?= ($arResult['ACCESS_SITE']['EDIT'] != 'Y') ? 'true' : 'false';?>,
									isSettingsDisabled: <?= ($arResult['ACCESS_SITE']['SETTINGS'] != 'Y') ? 'true' : 'false';?>,
									isPublicationDisabled: <?= ($arResult['ACCESS_SITE']['PUBLICATION'] != 'Y') ? 'true' : 'false';?>,
									isDeleteDisabled: <?= ($arResult['ACCESS_SITE']['DELETE'] != 'Y') ? 'true' : 'false';?>
								})">
						<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS');?></span>
					</div>
					<div class="landing-title-wrap">
						<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE']);?></div>
					</div>
				</div>
				<?if ($item['IS_HOMEPAGE']):?>
					<div class="landing-item-desc">
						<span class="landing-item-desc-text"><?= \htmlspecialcharsbx($areaTitle);?></span>
					</div>
				<?endif;?>
				<div class="landing-item-cover<?= $item['IS_AREA'] ? ' landing-item-cover-area' : '';?>"
					<?if ($item['PREVIEW'] && !$item['IS_AREA']) {?> style="background-image: url(<?=
					\htmlspecialcharsbx($item['PREVIEW'])?>);"<?}?>>
					<?if ($item['IS_HOMEPAGE'] || $item['IS_AREA']):?>
					<div class="landing-item-area">
						<div class="landing-item-area-icon<?=' landing-item-area-icon-' . htmlspecialcharsbx($areaCode);?>"></div>
						<?if ($item['IS_AREA']):?>
							<span class="landing-item-area-text"><?= \htmlspecialcharsbx($areaTitle);?></span>
						<?endif;?>
					</div>
					<?endif;?>
				</div>
			</div>
			<?if ($item['DELETED'] == 'Y'):?>
				<span class="landing-item-link"></span>
			<?elseif ($arParams['TILE_MODE'] == 'view' && $item['PUBLIC_URL']):?>
				<a href="<?= \htmlspecialcharsbx($item['PUBLIC_URL']);?>" class="landing-item-link" target="_top"></a>
			<?elseif ($urlView):?>
				<a href="<?= \htmlspecialcharsbx($urlView);?>" class="landing-item-link" target="_top"></a>
			<?else:?>
				<span class="landing-item-link"></span>
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
					<?elseif ($item['DATE_MODIFY_UNIX'] > $item['DATE_PUBLIC_UNIX']):?>
						<span class="landing-item-status landing-item-status-changed">
							<?= Loc::getMessage('LANDING_TPL_MODIF');?>
						</span>
					<?endif;?>

				</div>
			</div>
			<?endif;?>
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
					condition: <?= \CUtil::phpToJSObject($sliderConditions);?>,
					stopParameters: [
						'action',
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

	if (typeof showTileMenu === 'undefined')
	{
		function showTileMenu(node, params)
		{
			var menuItems = [
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
					disabled: params.isDeleted || params.isEditDisabled,
					<?if ($arParams['TILE_MODE'] == 'view'):?>
					href: params.viewSite,
					<?else:?>
					onclick: function(e, item)
					{
						window.top.location.href = params.viewSite;
					}
					<?endif;?>
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
								menu.destroy();
								isMenuShown = false;
							}.bind(this),250);

						}.bind(this));
					}
				},
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_GOTO'));?>',
					className: 'landing-popup-menu-item-icon',
					href: params.publicUrl,
					target: '_blank',
					disabled: params.isArea || params.isDeleted || !params.isActive,
					onclick: function(event)
					{
						if (top.window !== window)
						{
							event.preventDefault();
							top.window.location.href = params.publicUrl;
						}
					}
				},
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT'));?>',
					href: params.editPage,
					disabled: params.isDeleted || params.isSettingsDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				{
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPY'));?>',
					disabled: params.isDeleted || (params.isFolder && <?= !$folderId ? 'true' : 'false';?>) || params.isEditDisabled,
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
									<?if ($folderId):?>
									params.copyPage += '&additional[folderId]=';
									params.copyPage += <?= (int)$folderId;?>;
									<?endif;?>
									var loaderContainer = BX.create('div',{
										attrs:{className:'landing-filter-loading-container'}
									});
									document.body.appendChild(loaderContainer);
									var loader = new BX.Loader({size: 130, color: '#bfc3c8'});
									loader.show(loaderContainer);
									if (top.window !== window)
									{
										// we are in slider
										window.location.href = params.copyPage;
									}
									else
									{
										top.window.location.href = params.copyPage;
									}
								},
								function() {
									//
								}
							);
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
									? 'Landing::unpublic'
									: 'Landing::publication',
								{
									lid: params.ID
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
					disabled: params.folderIndex || params.isDeleteDisabled,
					onclick: function(event)
					{
						event.preventDefault();

						this.popupWindow.close();
						menu.destroy();

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

	if (window.location.hash === '#createPage')
	{
		window.location.hash = '';
		var addButton = document.querySelector('.landing-item-add-new .landing-item-inner');

		if (BX.type.isDomNode(addButton))
		{
			addButton.click();
		}
	}

</script>



<?php
if ($arResult['AGREEMENT'])
{
	include \Bitrix\Landing\Manager::getDocRoot() .
			'/bitrix/components/bitrix/landing.start/templates/.default/popups/agreement.php';
}
