<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

if (!empty($arResult['ERRORS']))
{
	showError(implode("\n", $arResult['ERRORS']));
	return;
}
if ($arResult['SUCCESS'])
{
	?><script type="text/javascript">top.window.location.reload();</script><?
}
?>

<script type="text/javascript">
	var isMenuShown = false;
	var menu = null;

	function showTileMenu(node, params)
	{
		var menuItems = [
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_BINDING_ACTION_BIND'));?>',
				href: '<?= \CUtil::jsEscape($arResult['ACTION_URL']);?>'.replace('__id__', params.ID)
			}//,
			<?/*if ($arParams['SITE_ID'] <= 0):?>
			{
				text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_BINDING_ACTION_LANDING'));?>',
				href: '<?= \CUtil::jsEscape($arResult['LANDING_URL']);?>'.replace('__id__', params.ID)
			}
			<?endif;*/?>
		];
		if (!isMenuShown)
		{
			menu = new BX.PopupMenuWindow(
				'landing-popup-menu' + params.ID,
				node,
				menuItems,
				{
					autoHide : true,
					offsetTop: -2,
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
		}
		else
		{
			menu.destroy();
		}
		isMenuShown = !isMenuShown;
	}
</script>
<style type="text/css">
	.landing-item-add-new {
		display: none!important;
	}
</style>

<?if ($arParams['SITE_ID'] <= 0):?>
	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.sites',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'DRAFT_MODE' => 'Y',
			'OVER_TITLE' => Loc::getMessage('LANDING_TPL_BINDING_ACTION_BIND'),
			'PAGE_URL_SITE' => str_replace('__id__', '#site_show#', $arResult['ACTION_URL'])
		),
		$component
	);?>
	<?Manager::setPageTitle($this->getComponent()->getMessageType('LANDING_TPL_BINDING_TITLE_SITE'));?>
<?else:?>
	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.landings',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'DRAFT_MODE' => 'Y',
			'SITE_ID' => $arParams['SITE_ID']
		),
		$component
	);?>
	<?Manager::setPageTitle($this->getComponent()->getMessageType('LANDING_TPL_BINDING_TITLE_LANDING'));?>
<?endif;?>
