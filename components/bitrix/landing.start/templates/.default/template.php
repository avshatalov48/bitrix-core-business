<?php

use Bitrix\Landing\Update\Stepper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */

$sef = [];
foreach ($arParams['SEF_URL_TEMPLATES'] as $code => $url)
{
	$sef[$code] = $arParams['SEF_FOLDER'] . $url;
}

CJSCore::init('landing.metrika');
Stepper::show();
?>

<?php $result = $APPLICATION->IncludeComponent(
	'bitrix:landing.sites',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'PAGE_URL_SITE' => $arParams['PAGE_URL_SITE_SHOW'],
		'PAGE_URL_SITE_EDIT' => $arParams['PAGE_URL_SITE_EDIT'],
		'PAGE_URL_SITE_DESIGN' => $arParams['PAGE_URL_SITE_DESIGN'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
		'PAGE_URL_SITE_CONTACTS' => $arParams['PAGE_URL_SITE_CONTACTS'],
		'PAGE_URL_SITE_DOMAIN_SWITCH' => $arParams['PAGE_URL_SITE_DOMAIN_SWITCH'],
		'PAGE_URL_SITE_DOMAIN' => $arParams['PAGE_URL_SITE_DOMAIN'],
		'TILE_MODE' => $arParams['TILE_SITE_MODE'],
		'DRAFT_MODE' => $arParams['DRAFT_MODE'],
		'SEF' => $sef,
		'AGREEMENT' => $arResult['AGREEMENT']
	),
	$component
);?>

<?php if ($arParams['REOPEN_LOCATION_IN_SLIDER'] === 'Y'):
	CJSCore::init('sidepanel');
	?>
	<script type="text/javascript">
		BX.ready(function()
		{
			BX.SidePanel.Instance.open(
				window.location.href
			);
		});
	</script>
<?php endif; ?>
