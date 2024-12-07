<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \CMain $APPLICATION */
/** @var \LandingLandingsComponent $component */

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Uri;

Loc::loadMessages(__FILE__);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
}

if ($arResult['FATAL'])
{
	return;
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$folder = $arResult['FOLDER'];
$folderId = $arResult['FOLDER_ID'];

// assets, title
\Bitrix\Landing\Manager::setPageTitle(\htmlspecialcharsbx($arResult['TITLE']));
\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'landing_master',
	'landing.explorer',
	'action_dialog',
	'clipboard',
	'sidepanel',
]);
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

// prepare urls
$arParams['PAGE_URL_LANDING_ADD_PLUS_BUTTON'] = $component->getUrlAdd(false, [
	'context_section' => 'pages_list',
	'context_element' => 'plus_button',
]);
$arParams['PAGE_URL_LANDING_ADD_FOLDER_MENU'] = $component->getUrlAdd(false, [
	'context_section' => 'pages_list',
	'context_element' => 'folder_menu_link',
]);
$arParams['PAGE_URL_LANDING_ADD_SIDEPANEL_CONDITION'] = $component->getUrlAddSidepanelCondition(false);

$sliderConditions = [
	str_replace(
		array(
			'#landing_edit#', '?'
		),
		array(
			'(\d+)', '\?'
		),
		CUtil::jsEscape($arParams['PAGE_URL_LANDING_SETTINGS'])
	),
];

$sliderFullConditions = [];
if ($arParams['TYPE'] === 'PAGE' && $component->isUseNewMarket())
{
	$sliderFullConditions[] = $arParams['PAGE_URL_LANDING_ADD_SIDEPANEL_CONDITION'];
}
else
{
	$sliderConditions[] = $arParams['PAGE_URL_LANDING_ADD_SIDEPANEL_CONDITION'];
}

$sliderShortConditions = [
	str_replace(
		array(
			'#folder_edit#', '?'
		),
		array(
			'(\d+)', '\?'
		),
		\CUtil::jsEscape($arParams['PAGE_URL_FOLDER_EDIT'])
	)
];

\trimArr($sliderFullConditions, true);
\trimArr($sliderConditions, true);
\trimArr($sliderShortConditions, true);

// Tool availability (by intranet settings)
if (!$component->isToolAvailable())
{
	echo $component->getToolUnavailableInfoScript();
}
?>

<div class="grid-tile-wrap landing-pages-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">

	<?if ($folderId):
		if ($folder['PARENT_ID'])
		{
			$urlUpFolder = $component->getUri(
				[$arParams['ACTION_FOLDER'] => $folder['PARENT_ID']]
			);
		}
		else
		{
			$urlUpFolder = $component->getUri(
				($component->request('IFRAME') === 'Y') ? ['folderUp' => 'Y'] : [],
				[$arParams['ACTION_FOLDER']]
			);
		}
		?>
		<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
			<a class="landing-item-inner" href="<?= \htmlspecialcharsbx($urlUpFolder)?>" data-slider-ignore-autobinding="true">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon landing-item-add-icon-up"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_FOLDER_UP')?></span>
			</span>
			</a>
		</div>
	<?endif;?>

	<?if ($arResult['ACCESS_SITE']['EDIT'] == 'Y'):?>
	<div class="landing-item landing-item-add-new" style="display: <?=$arResult['IS_DELETED'] ? 'none' : 'block';?>;">
		<span class="landing-item-inner" data-href="<?= \htmlspecialcharsbx($arParams['PAGE_URL_LANDING_ADD_PLUS_BUTTON']) ?>">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_ADD')?></span>
			</span>
		</span>
	</div>
	<?endif;?>

	<div class="landing-folder-placeholder"></div>

	<?if ($component->request('folderNew') === 'Y'):?>
	<form action="<?= \htmlspecialcharsbx($component->getPageParam(htmlspecialcharsback(POST_FORM_ACTION_URI), ['folderNew' => 'N']))?>" method="post" id="createFolderForm">
		<?= bitrix_sessid_post()?>
		<input type="hidden" name="action" value="addFolder" />
		<div class="landing-item landing-item-folder">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow --create-folder-input">
						<input type="text" name="param" value="" />
					</div>
				</div>
			</div>
			<div class="landing-item-cover">
				<div class="landing-item-preview">
				</div>
				<div class="landing-item-folder-corner">
					<div class="landing-item-folder-dropdown"></div>
				</div>
			</div>
		</div>
		<script>
			BX.ready(
				function() {
					let createFolderForm = document.body.querySelector('#createFolderForm');
					let createFolderText = document.body.querySelector('.--create-folder-input input');

					createFolderText.focus();

					createFolderText.addEventListener('keydown', function(event) {
						if (event.keyCode === 13)
						{
							event.preventDefault();
							if (createFolderText.value.length !== 0)
							{
								createFolderForm.submit();
							}
							else
							{
								event.stopPropagation()
							}
						}
					})

					createFolderText.addEventListener('blur', function () {
						if (createFolderText.value.length !== 0)
						{
							createFolderForm.submit();
						}
					});
				}
			);
		</script>
	</form>
	<?endif?>

<?php
foreach ($arResult['LANDINGS'] as $i => $item):

	$isFolder = array_key_exists('PARENT_ID', $item);

	if ($arParams['DRAFT_MODE'] == 'Y' && $item['DELETED'] != 'Y')
	{
		$item['ACTIVE'] = 'Y';
	}

	if ($isFolder)
	{
		$accessSite = $arResult['ACCESS_SITE'];
		$urlEditFolder = str_replace('#folder_edit#', $item['ID'], $arParams['~PAGE_URL_FOLDER_EDIT']);
	}
	else
	{
		$areaCode = '';
		$areaTitle = '';
		$accessSite = $arResult['ACCESS_SITE'];
		$urlSettings = new Uri(str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_SETTINGS']));
		$urlSettings->addParams([
			'PAGE' => 'LANDING_EDIT',
		]);
		$urlSettings = $urlSettings->getUri();

		$urlEdit = str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_EDIT']);
		$urlEditDesign = str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_DESIGN']);
		$urlView = str_replace('#landing_edit#', $item['ID'], $arParams['~PAGE_URL_LANDING_VIEW']);

		$uriCopy = new Uri($arResult['CUR_URI']);
		$uriCopy->addParams(array(
			'action' => 'copy',
			'param' => $item['ID'],
			'sessid' => bitrix_sessid()
		));

		$uriMove = new Uri($arResult['CUR_URI']);
		$uriMove->addParams(array(
			'action' => 'move',
			'param' => $item['ID'],
			'sessid' => bitrix_sessid()
		));

		if ($item['IS_AREA'])
		{
			$areaCode = $item['AREA_CODE'];
			$areaTitle = Loc::getMessage('LANDING_TPL_AREA_'.mb_strtoupper($item['AREA_CODE']));
		}
		else if ($item['IS_HOMEPAGE'])
		{
			$areaCode = 'main_page';
			$areaTitle = Loc::getMessage('LANDING_TPL_AREA_MAIN_PAGE');
			if ($arParams['TYPE'] === 'GROUP')
			{
				$accessSite['DELETE'] = 'N';
			}
		}

		if (in_array($item['ID'], $arResult['DELETE_LOCKED']))
		{
			$accessSite['DELETE'] = 'N';
		}
	}

	if (
		\Bitrix\Main\Loader::includeModule('catalog')
		&& \Bitrix\Catalog\Config\State::isExternalCatalog()
	)
	{
		if (
			$accessSite['PUBLICATION'] === 'Y'
			&& $arParams['TYPE'] === 'STORE'
			&& isset($arResult['SITES'][$arParams['SITE_ID']])
			&& !str_starts_with($arResult['SITES'][$arParams['SITE_ID']]['TPL_CODE'], 'store-chats')
		)
		{
			$accessSite['PUBLICATION'] = 'N';
		}
	}
	?>
	<?if ($isFolder):?>
		<div class="landing-item landing-item-folder<?
			?><?= $item['ACTIVE'] !== 'Y' || $item['DELETED'] !== 'N' ? ' landing-item-unactive' : '' ?><?
			?><?= $item['DELETED'] === 'Y' ? ' landing-item-deleted' : '';?>"
			title="<?= htmlspecialcharsbx($item['TITLE']);?>"
		>
			<div class="landing-title">
				<div class="landing-title-wrap" title="<?= htmlspecialcharsbx($item['TITLE']);?>">
					<div class="landing-title-overflow"><?= htmlspecialcharsbx($item['TITLE']);?></div>
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
									viewSite: '',
									ID: '<?= $item['ID']?>',
									title: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['TITLE']));?>',
									createPageUrl: '<?= htmlspecialcharsbx(CUtil::jsEscape($arParams['PAGE_URL_LANDING_ADD_FOLDER_MENU'])) ?>',
									publicUrl: '',
									copyPage: '',
									deletePage: '',
									editPage: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlEditFolder)) ?>',
							 		isFolder: true,
									isActive: <?= ($item['ACTIVE'] === 'Y') ? 'true' : 'false' ?>,
							 		isDeleted: <?= ($item['DELETED'] == 'Y') ? 'true' : 'false';?>,
							 		wasModified: false,
									isEditDisabled: true,
									isSettingsDisabled: <?= ($accessSite['SETTINGS'] !== 'Y') ? 'true' : 'false' ?>,
									isPublicationDisabled: <?= ($accessSite['PUBLICATION'] !== 'Y') ? 'true' : 'false' ?>,
									isDeleteDisabled: <?= ($accessSite['DELETE'] !== 'Y') ? 'true' : 'false' ?>
								})">
						<span class="landing-item-folder-dropdown-inner"></span>
					</div>
				</div>
			</div>
			<?if ($item['DELETED'] == 'Y'):?>
			<span class="landing-item-link"></span>
			<?else:?>
			<a href="<?= $component->getUri([$arParams['ACTION_FOLDER'] => $item['ID']], ['folderUp']);?>" data-slider-ignore-autobinding="true" class="landing-item-link"></a>
			<?endif;?>
		</div>
	<?else:?>
		<div class="landing-item<?php
			?><?= $item['ACTIVE'] !== 'Y' || $item['DELETED'] !== 'N' ? ' landing-item-unactive' : '' ?><?php
			?><?= $item['DELETED'] === 'Y' ? ' landing-item-deleted' : '' ?>">
			<div class="landing-item-inner">
				<div class="landing-title">
					<div class="landing-title-btn"
						 onclick="showTileMenu(this,{
							viewSite: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlView)) ?>',
							ID: '<?= $item['ID'] ?>',
							title: '<?= \htmlspecialcharsbx(\CUtil::jsEscape($item['TITLE']));?>',
							isArea: <?= $item['IS_AREA'] ? 'true' : 'false' ?>,
					        isMainPage: <?= $item['IS_HOMEPAGE'] ? 'true' : 'false' ?>,
							publicUrl: '<?= htmlspecialcharsbx(CUtil::jsEscape($item['PUBLIC_URL'])) ?>',
							copyPage: '<?= htmlspecialcharsbx(CUtil::jsEscape($uriCopy->getUri())) ?>',
							movePage: '<?= htmlspecialcharsbx(CUtil::jsEscape($uriMove->getUri())) ?>',
							deletePage: '#',
							settings: '<?= htmlspecialcharsbx(CUtil::jsEscape($urlSettings)) ?>',
					        isFolder: false,
					        isActive: <?= ($item['ACTIVE'] === 'Y') ? 'true' : 'false' ?>,
					        isDeleted: <?= ($item['DELETED'] === 'Y') ? 'true' : 'false' ?>,
							wasModified: <?= ($item['WAS_MODIFIED'] === 'Y') ? 'true' : 'false' ?>,
							isEditDisabled: <?= ($accessSite['EDIT'] !== 'Y') ? 'true' : 'false' ?>,
							isSettingsDisabled: <?= ($accessSite['SETTINGS'] !== 'Y') ? 'true' : 'false' ?>,
							isPublicationDisabled: <?= ($accessSite['PUBLICATION'] !== 'Y') ? 'true' : 'false' ?>,
							isDeleteDisabled: <?= ($accessSite['DELETE'] !== 'Y') ? 'true' : 'false' ?>
						})">
						<span class="landing-title-btn-inner"><?= Loc::getMessage('LANDING_TPL_ACTIONS') ?></span>
					</div>
					<div class="landing-title-wrap" title="<?= htmlspecialcharsbx($item['TITLE']);?>">
						<div class="landing-title-overflow"><?= htmlspecialcharsbx($item['TITLE']) ?></div>
					</div>
				</div>
				<?if ($item['IS_HOMEPAGE']):?>
					<div class="landing-item-desc">
						<span class="landing-item-desc-text"><?= htmlspecialcharsbx($areaTitle) ?></span>
					</div>
				<?php endif;?>
				<div class="landing-item-cover<?= $item['IS_AREA'] ? ' landing-item-cover-area' : '' ?>"
					<?php if ($item['PREVIEW'] && !$item['IS_AREA']) :?>
						style="background-image: url(<?= htmlspecialcharsbx($item['PREVIEW']) ?>);"
						<?php if (
							$item['PUBLISHED']
							&& ($item['CLOUD_PREVIEW'] ?? null)
							&& ($item['CLOUD_PREVIEW'] !== $item['PREVIEW'])
						) :?>
							data-cloud-preview="<?= $item['CLOUD_PREVIEW'] ?>"
						<?php endif; ?>
					<?php endif; ?>
				>
					<?if ($item['IS_HOMEPAGE'] || $item['IS_AREA']):?>
					<div class="landing-item-area">
						<div class="landing-item-area-icon<?=' landing-item-area-icon-' . htmlspecialcharsbx($areaCode) ?>"></div>
						<?if ($item['IS_AREA']):?>
							<span class="landing-item-area-text"><?= htmlspecialcharsbx($areaTitle);?></span>
						<?php endif;?>
					</div>
					<?php endif;?>
				</div>
			</div>
			<?if ($item['DELETED'] == 'Y'):?>
				<span class="landing-item-link"></span>
			<?elseif ($arParams['TILE_MODE'] == 'view' && $item['PUBLIC_URL']):?>
				<a href="<?= htmlspecialcharsbx($item['PUBLIC_URL']);?>" class="landing-item-link" target="_top"></a>
			<?elseif ($urlView):?>
				<a href="<?= htmlspecialcharsbx($urlView);?>" class="landing-item-link" target="_top"></a>
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

<script>
	(() => {
		const sliderConditions = <?= CUtil::phpToJSObject($sliderConditions);?>;
		if (sliderConditions.length > 0)
		{
			BX.SidePanel.Instance.bindAnchors(
				top.BX.clone({
					rules: [
						{
							condition: sliderConditions,
							stopParameters: [
								'action',
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
						},
					]
				})
			);
		}

		const sliderFullConditions = <?= CUtil::phpToJSObject($sliderFullConditions);?>;
		if (sliderFullConditions.length > 0)
		{
			BX.SidePanel.Instance.bindAnchors(
				top.BX.clone({
					rules: [
						{
							condition: sliderFullConditions,
							options: {
								allowChangeHistory: false,
								cacheable: false,
								customLeftBoundary: 0,
							}
						},
					]
				})
			);
		}

		const sliderShortConditions = <?= CUtil::phpToJSObject($sliderShortConditions);?>;
		if (sliderShortConditions.length > 0)
		{
			BX.SidePanel.Instance.bindAnchors(
				top.BX.clone({
					rules: [
						{
							condition: sliderShortConditions,
							options: {
								allowChangeHistory: false,
								cacheable: false,
								width: 800
							}
						}
					]
				})
			);
		}
	})();
</script>
<script>
	// + button open page add slider
	BX.bind(document.querySelector('.landing-item-add-new span.landing-item-inner'), 'click', event => {
		BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
			allowChangeHistory: false,
			<?php
				echo $component->isUseNewMarket() ? 'customLeftBoundary: 0,' : '';
				echo $component->isUseNewMarket() ? 'cacheable: false,' : '';
			?>
		});
	});

	var tileGrid;
	var isMenuShown = false;
	var menu;

	BX.ready(function ()
	{
		const wrapper = BX('grid-tile-wrap');
		const title_list = Array.prototype.slice.call(wrapper.getElementsByClassName('landing-item'));

		tileGrid = new BX.Landing.TileGrid({
			wrapper: wrapper,
			siteId: <?= $arParams['SITE_ID'];?>,
			siteType: '<?= $arParams['TYPE'];?>',
			inner: BX('grid-tile-inner'),
			tiles: title_list,
			sizeSettings : {
				minWidth : 280,
				maxWidth: 330
			}
		});

		// init previews
		const previews = document.querySelectorAll('.landing-item-cover[data-cloud-preview]');
		previews.forEach(item => lazyLoadCloudPreview(item));
		function lazyLoadCloudPreview(item)
		{
			const cloudPreview = item.dataset.cloudPreview;
			const previewUrl =
				cloudPreview
				+ ((cloudPreview.indexOf('?') > 0) ? '&' : '?')
				+ 'refreshed' + (Date.now()/86400000|0)
			;
			const xhr = new XMLHttpRequest();
			xhr.open("HEAD", previewUrl);
			xhr.onload = () => {
				const expires = xhr.getResponseHeader("expires");
				if (
					expires
					&& (new Date(expires)) <= (new Date())
				)
				{
					setTimeout(lazyLoadCloudPreview, 3000, item);
				}
				else
				{
					item.style.backgroundImage = 'url(' + previewUrl + ')';
				}
			};
			xhr.send();
		}

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

	var landingExplorer = null;

	if (typeof showTileMenu === 'undefined')
	{
		function showTileMenu(node, params)
		{
			if (typeof showTileMenuCustom === 'function')
			{
				showTileMenuCustom(node, params);
				return;
			}

			if (landingExplorer === null)
			{
				landingExplorer = new BX.Landing.Explorer({
					type: '<?= $arParams['TYPE'];?>',
					siteId: <?= $arParams['SITE_ID'];?>,
					folderId: <?= (int)$arResult['FOLDER_ID'];?>,
					startBreadCrumbs: <?= \CUtil::phpToJSObject($arResult['FOLDER_PATH']);?>
				});
			}

			var menuItems = [
				params.isFolder ? null :
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
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
				<?if ($arParams['DRAFT_MODE'] != 'Y'):?>
				{
					text: params.wasModified && params.isActive
						? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC_CHANGED')) ?>'
						: (
							params.isFolder
							? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC_FOLDER_MSGVER_1')) ?>'
							: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_PUBLIC')) ?>'
						),
					disabled: params.isDeleted || params.isPublicationDisabled || (!params.wasModified && params.isActive),
					onclick: function(event)
					{
						event.preventDefault();

						var successFunction = function()
						{
							if (params.isFolder)
							{
								tileGrid.action(
									'Site::publicationFolder',
									{
										folderId: params.ID
									}
								);
							}
							else
							{
								tileGrid.action('Landing::publication',
									{
										lid: params.ID
									},
									null,
									'<?= CUtil::jsEscape($this->getComponent()->getName());?>'
								);
							}
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
				{
					text: params.isFolder
						? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC_FOLDER_MSGVER_1')) ?>'
						: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNPUBLIC')) ?>'
					,
					disabled: params.isDeleted || params.isPublicationDisabled || !params.isActive,
					onclick: function(event)
					{
						event.preventDefault();

						var successFunction = function()
						{
							if (params.isFolder)
							{
								tileGrid.action(
									'Site::unPublicFolder',
									{
										folderId: params.ID
									}
								);
							}
							else
							{
								tileGrid.action(
									'Landing::unpublic',
									{
										lid: params.ID
									},
									null,
									'<?= CUtil::jsEscape($this->getComponent()->getName());?>'
								);
							}
						};

						successFunction();
						this.popupWindow.close();
						menu.destroy();
					}
				},
				<?endif;?>
				!params.isFolder ? null :
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_ADD_PAGE'));?>',
					href: BX.util.add_url_param(
						params.createPageUrl,
						{
							folderId: params.ID
						}
					)
				},
				params.isFolder ? null :
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_GOTO'));?>',
					className: 'landing-popup-menu-item-icon',
					href: params.publicUrl,
					target: '_blank',
					disabled: params.isArea || params.isDeleted || !params.isActive || !params.publicUrl,
					onclick: function(event)
					{
						if (top.window !== window)
						{
							event.preventDefault();
							top.window.location.href = params.publicUrl;
						}
					}
				},
				params.isFolder ? null :
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
					className: 'landing-popup-menu-item-icon',
					disabled: params.isArea || params.isDeleted || !params.publicUrl,
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
				params.isFolder && <?= $arParams['DRAFT_MODE'] === 'Y' ? 'true' : 'false'?> ? null : {
					delimiter: true
				},
				{
					text: params.isFolder
							? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT_FOLDER_MSGVER_1')) ?>'
							: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT_2')) ?>',
					href: params.isFolder ? params.editPage : params.settings,
					disabled: params.isDeleted || params.isSettingsDisabled,
					onclick: function()
					{
						this.popupWindow.close();
					}
				},
				params.isFolder ? null :
				{
					delimiter: true
				},
				params.isFolder ? null :
				{
					text: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPY'));?>',
					disabled: params.isDeleted || (params.isFolder && <?= !$folderId ? 'true' : 'false';?>) || params.isEditDisabled,
					onclick: function(event)
					{
						event.preventDefault();
						landingExplorer.copy({ ID: parseInt(params.ID), TITLE: params.title});
						this.popupWindow.close();
					}
				},
				{
					text: params.isFolder
							? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_MOVE_FOLDER'));?>'
							: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_MOVE'));?>',
					disabled: params.isDeleted || (params.isEditDisabled && !params.isFolder) || params.isDeleteDisabled || params.isMainPage,
					onclick: function(event)
					{
						event.preventDefault();
						if (params.isFolder)
						{
							landingExplorer.moveFolder({ ID: parseInt(params.ID), TITLE: params.title});
						}
						else
						{
							landingExplorer.move({ ID: parseInt(params.ID), TITLE: params.title});
						}
						this.popupWindow.close();
					}
				},
				{
					delimiter: true
				},
				{
					text: params.isDeleted
						? (
							params.isFolder
							? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNDELETE_FOLDER'));?>'
							: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_UNDELETE'));?>'
						)
						: (
							params.isFolder
							? '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_FOLDER'));?>'
							: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE'));?>'
						),
					href: params.deletePage,
					disabled: params.isDeleteDisabled,
					onclick: function(event)
					{
						event.preventDefault();

						this.popupWindow.close();
						menu.destroy();

						if (params.isDeleted)
						{
							tileGrid.action(
								params.isFolder ? 'Site::markFolderUnDelete' : 'Landing::markUnDelete',
								params.isFolder ? { id: params.ID } : { lid: params.ID }
							);
						}
						else
						{
							BX.Landing.UI.Tool.ActionDialog.getInstance()
								.show({
									content: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_REC_CONFIRM'));?>'
								})
								.then(
									function() {
										tileGrid.action(
											params.isFolder ? 'Site::markFolderDelete' : 'Landing::markDelete',
											params.isFolder ? { id: params.ID } : { lid: params.ID }
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

						if (gotoSiteButton.dataset.isLanding === 'Y')
						{
							var sitePath = '<?= CUtil::jsEscape($arParams['PAGE_URL_LANDING_VIEW']);?>';
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

				BX.onCustomEvent('BX.Landing.Filter:apply');
			}
		);
	</script>
<?endif?>

<?php
if ($arResult['AGREEMENT'])
{
	include \Bitrix\Landing\Manager::getDocRoot() .
			'/bitrix/components/bitrix/landing.start/templates/.default/popups/agreement.php';
}
