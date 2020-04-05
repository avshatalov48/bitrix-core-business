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
$curUrl = $request->getRequestUri();
$folderId = $request->get($arParams['ACTION_FOLDER']);

// title
if (isset($arResult['SITES'][$arParams['SITE_ID']]))
{
	\Bitrix\Landing\Manager::getApplication()->setTitle(
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
		$curUrlWoFolder = new \Bitrix\Main\Web\Uri($curUrl);
		$curUrlWoFolder->deleteParams(array(
			$arParams['ACTION_FOLDER']
		));
		?>
		<div class="landing-item landing-item-add-new">
			<a class="landing-item-inner" href="<?= \htmlspecialcharsbx($curUrlWoFolder->getUri());?>">
			<span class="landing-item-add-new-inner">
				<span class="landing-item-add-icon landing-item-add-icon-up"></span>
				<span class="landing-item-text"><?= Loc::getMessage('LANDING_TPL_ACTION_FOLDER_UP');?></span>
			</span>
			</a>
		</div>
	<?endif;?>

	<div class="landing-item landing-item-add-new">
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

	$uriFolder = null;
	$urlEdit = str_replace('#landing_edit#', $item['ID'], $arParams['PAGE_URL_LANDING_EDIT']);
	$urlView = str_replace('#landing_edit#', $item['ID'], $arParams['PAGE_URL_LANDING_VIEW']);

	$uriDelete = new \Bitrix\Main\Web\Uri($urlEdit);
	$uriDelete->addParams(array(
		'fields' => array(
			'delete' => 'Y'
		),
		'sessid' => bitrix_sessid()
	));

	$uriCopy = new \Bitrix\Main\Web\Uri($curUrl);
	$uriCopy->addParams(array(
		'action' => 'copy',
		'param' => $item['ID'],
		'sessid' => bitrix_sessid()
	));
	$uriCopy->deleteParams(array(
		'IS_AJAX'
	));

	if ($item['FOLDER'] == 'Y' && $item['ID'] != $folderId)
	{
		$uriFolder = new \Bitrix\Main\Web\Uri($curUrl);
		$uriFolder->addParams(array(
			$arParams['ACTION_FOLDER'] => $item['ID']
		));
		$uriFolder->deleteParams(array(
			'IS_AJAX'
		));
	}
	?>
	<?if ($uriFolder):?>
		<div class="landing-item landing-item-folder<?= $item['ACTIVE'] != 'Y' ? ' landing-title-unactive' : ''?>">
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
								 deletePage: '<?= \CUtil::jsEscape($uriDelete->getUri());?>',
								 editPage: '<?= \CUtil::jsEscape($urlEdit);?>'
								 }
								 )">
						<span class="landing-item-folder-dropdown-inner"></span>
					</div>
				</div>
			</div>
			<a href="<?= $uriFolder->getUri();?>" class="landing-item-link" target="_top"></a>
		</div>
	<?else:?>
		<div class="landing-item<?= $item['ACTIVE'] != 'Y' ? ' landing-title-unactive' : ''?>">
			<div class="landing-item-inner">
				<div class="landing-title">
					<div class="landing-title-btn"
						 onclick="showTileMenu(this,{
								 viewSite: '<?= \CUtil::jsEscape($urlView);?>',
								 ID: '<?= $item['ID']?>',
								 publicUrl: '<?= \CUtil::jsEscape(\htmlspecialcharsbx($item['PUBLIC_URL']));?>',
								 copyPage: '<?= \CUtil::jsEscape($uriCopy->getUri());?>',
								 deletePage: '<?= \CUtil::jsEscape($uriDelete->getUri());?>',
								 editPage: '<?= \CUtil::jsEscape($urlEdit);?>'
								 }
								 )">
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
			<a href="<?= $urlView;?>" class="landing-item-link" target="_top"></a>
<!--			<div class="landing-item-status-block">
				<div class="landing-item-status-inner">
					<?/*if ($item['DATE_MODIFY_UNIX'] > $item['DATE_PUBLIC_UNIX']):*/?>
					<span class="landing-item-status landing-item-status-unpublished"><?/*= Loc::getMessage('LANDING_TPL_UNPUBLIC');*/?></span>
					<span class="landing-item-status landing-item-status-changed">Изменена</span>
					<?/*else:*/?>
					<span class="landing-item-status landing-item-status-published"><?/*= Loc::getMessage('LANDING_TPL_PUBLIC');*/?></span>
					<?/*endif;*/?>
				</div>
			</div>-->
		</div>
	<?endif;?>
<?endforeach;?>

	</div>
</div>


<script type="text/javascript">
	BX.SidePanel.Instance.bindAnchors({
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
					'fields%5Bdelete%5D'
				],
                options: {
					allowChangeHistory: false
				}
			}]
	});

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
	});


	function showTileMenu(node, params)
	{
		var menuItems = [
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
				onclick: function(e, item)
				{
					window.top.location.href = params.viewSite;
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPYLINK'));?>',
				className: 'landing-popup-menu-item-icon',
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
				target: '_blank'
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_EDIT'));?>',
				href: params.editPage,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_COPY'));?>',
				href: params.copyPage,
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
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE'));?>',
				href: params.deletePage,
				onclick: function(event)
				{
					event.preventDefault();

					BX.Landing.UI.Tool.ActionDialog.getInstance()
						.show({
							content: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_CONFIRM'));?>'
						})
						.then(
							function() {
								BX.Landing.History.getInstance().removePageHistory(params.ID);

								tileGrid.action(
									'Landing::delete',
									{
										lid: params.ID
									}
								);
							},
							function() {

							}
						);
					this.popupWindow.close();
				}
			}
		];

		BX.PopupMenu.show('landing-popup-menu' + params.ID, node, menuItems,{
			autoHide : true,
			offsetTop: -2,
			offsetLeft: -60,
			className: 'landing-popup-menu',
			events: {
				onPopupClose: function ()
				{
					BX.PopupMenu.destroy('landing-popup-menu' + params.ID);
				}
			}
		});
	}

</script>
