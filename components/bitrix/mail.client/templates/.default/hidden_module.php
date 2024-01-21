<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if ($_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	CJSCore::Init();
	$APPLICATION->ShowHead();
}
?>

<script type="text/javascript">
	BX.ready(function()
	{
		const slider = top.BX.SidePanel && top.BX.SidePanel.Instance.getSliderByWindow(window);
		if (slider)
		{
			slider.close(false, () => {
				top.BX.UI.InfoHelper.show('<?= CUtil::JSEscape($arParams['MAIL_SLIDER_CODE'] ?? '') ?>');
			});
		}
		else
		{
			top.BX.addCustomEvent('SidePanel.Slider:onCloseComplete', () => {
				location.href = '/';
			});
			top && top.BX.loadExt('ui.info-helper').then(() => {
				top.BX.UI.InfoHelper.show('<?= CUtil::JSEscape($arParams['MAIL_SLIDER_CODE'] ?? '') ?>');
			});
		}
	});
</script>