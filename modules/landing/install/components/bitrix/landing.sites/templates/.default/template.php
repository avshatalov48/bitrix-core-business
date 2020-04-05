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

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$curUrl = $request->getRequestUri();

// title
$APPLICATION->setTitle(
	$this->__component->getMessageType('LANDING_TPL_TITLE')
);

// assets
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings landing-tile no-background');

\CJSCore::Init(array('sidepanel', 'landing_master', 'action_dialog'));
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">
		<div class="landing-item landing-item-add-new">
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
			$urlEdit = str_replace('#site_edit#', $item['ID'], $arParams['PAGE_URL_SITE_EDIT']);
			$urlCreatePage = str_replace(array('#site_show#', '#landing_edit#'), array($item['ID'], 0), $arParams['PAGE_URL_LANDING_EDIT']);
			$urlView = str_replace('#site_show#', $item['ID'], $arParams['PAGE_URL_SITE']);

			$uriDelete = new \Bitrix\Main\Web\Uri($urlEdit);
			$uriDelete->addParams(array(
				'fields' => array(
					'delete' => 'Y'
				),
				'sessid' => bitrix_sessid()
			));
			?>
			<div class="landing-item <?= $item['ACTIVE'] != 'Y' ? 'landing-item-unactive' : ''?>">
				<div class="landing-item-inner">
					<div class="landing-title">
						<div class="landing-title-btn"
							 onclick="showTileMenu(this,{
								ID: '<?= $item['ID']?>',
								publicUrl: '<?= \CUtil::jsEscape(\htmlspecialcharsbx($item['PUBLIC_URL']));?>',
								viewSite: '<?= \CUtil::jsEscape($urlView);?>',
								createPage: '<?= \CUtil::jsEscape($urlCreatePage);?>',
								deleteSite: '<?= \CUtil::jsEscape($uriDelete->getUri());?>',
								editSite:'<?= \CUtil::jsEscape($urlEdit);?>'
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
				<a href="<?= $urlView;?>" class="landing-item-link" target="_top"></a>
			</div>
		<?endforeach;?>
	</div>
</div>




<script type="text/javascript">
	if (
		typeof BX.SidePanel !== 'undefined' &&
		typeof BX.SidePanel.Instance !== 'undefined'
	)
	{
		BX.SidePanel.Instance.bindAnchors({
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
						allowChangeHistory: false
                    }
				}]
		});
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
	});

	function showTileMenu(node, params)
	{
		var menuItems = [
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_VIEW'));?>',
				href: params.viewSite
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
					}.bind(this))
				}
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_GOTO'));?>',
				className: 'landing-popup-menu-item-icon',
				href: params.publicUrl,
				target: '_blank'
			},
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_ADDPAGE'));?>',
				href: params.createPage,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: '<?= \CUtil::jsEscape($this->__component->getMessageType('LANDING_TPL_ACTION_EDIT'));?>',
				href: params.editSite,
				onclick: function()
				{
					this.popupWindow.close();
				}
			},
			{
				text: '<?= \CUtil::jsEscape($this->__component->getMessageType('LANDING_TPL_ACTION_DELETE'));?>',
				href: params.deleteSite,
				onclick: function(event)
				{
					event.preventDefault();

					BX.Landing.UI.Tool.ActionDialog.getInstance()
						.show({
							content: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACTION_DELETE_CONFIRM'));?>'
						})
						.then(
							function() {
								tileGrid.action(
									'Site::delete',
									{
										id: params.ID
									}
								);
							},
							function(error) {
								//
							}
						);
					this.popupWindow.close();
				}
			}
		];

		BX.PopupMenu.show('landing-popup-menu' + params.ID, node, menuItems,{
			autoHide : true,
			offsetTop: -2,
			offsetLeft: -55,
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