<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @var $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('SENDER_MASTER_YANDEX_PAGE_TITLE'));
$isComponentInSlider = $arResult['IN_SIDE_SLIDER'];
$sitePublicUrls = (array)$arParams['SITE_PUBLIC_URLS'];
?>
<div id="master-yandex-container" class="<?= $isComponentInSlider ? 'master-yandex-container-side' : '' ?>">

</div>
<script src="https://yastatic.net/s3/direct-frontend/uac-widget/partner-script.js"></script>
<script>
	BX.ready(() => {
		window.YaMasterWidget.init({
			container: document.querySelector('#master-yandex-container'),
			<?=
				count($sitePublicUrls) === 1
					? "site: '" . CUtil::JSescape($sitePublicUrls[0]) . "'"
					: "site: " . CUtil::PhpToJSObject($sitePublicUrls)
			?>,
			partnerId: <?= (int)$arParams['PARTNER_ID'] ?>,
		});
	});
	<?php if ($isComponentInSlider): ?>
	const slider = BX.SidePanel.Instance.getTopSlider();
	if (slider !== null)
	{
		const iframe = slider.iframe;
		BX.Dom.style(iframe, 'overflow', 'hidden');
		BX.Dom.attr(iframe, 'scrolling', 'no');
	}
	<?php endif; ?>
</script>