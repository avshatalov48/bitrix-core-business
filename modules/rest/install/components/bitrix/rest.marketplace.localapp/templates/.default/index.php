<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

$arCRMFormParams = array(
	"ru" => array(
		"js_params" => "b24form({\"id\":\"65\",\"lang\":\"ru\",\"sec\":\"joq1dv\",\"type\":\"link\",\"click\":\"\"});",
		"link_class_string" => "b24-web-form-popup-btn-65",
	),
	"ua" => array(
		"js_params" => "b24form({\"id\":\"99\",\"lang\":\"ua\",\"sec\":\"s72e0x\",\"type\":\"link\",\"click\":\"\"});",
		"link_class_string" => "b24-web-form-popup-btn-99",
	),
);

$portalZoneId = (Loader::includeModule('bitrix24')) ? (CBitrix24::getPortalZone()) : 'ru';
$arReplaceParams = array(
	"js_params" => "",
	"link_class_string" => "",
);
switch ($portalZoneId)
{
	case 'ua':
		$arReplaceParams = $arCRMFormParams[$portalZoneId];
		break;

	case 'ru':
	case 'by':
	case 'kz':
		$arReplaceParams = $arCRMFormParams['ru'];
		break;

	default:
		if (in_array(LANGUAGE_ID, array('ua', 'ru')))
			$arReplaceParams = $arCRMFormParams['ru'];
		break;
}

$APPLICATION->IncludeComponent(
	"bitrix:rest.marketplace.localapp.toolbar",
	".default",
	array(
		"COMPONENT_PAGE" => $arParams["COMPONENT_PAGE"],
		"ADD_URL" => $arParams["ADD_URL"],
		"LIST_URL" => $arParams["LIST_URL"],
	),
	$component
);
?>

<div class="mp-app-add-block">
	<div class="mp-app-add-block-header">
		<div class="mp-app-add-block-pagetitle"><?=GetMessage("MARKETPLACE_PAGE_TITLE")?></div>
		<div class="mp-app-add-block-pagetitle"><?=GetMessage("MARKETPLACE_PAGE_TITLE2", array("#MARKETPLACE_PAGE_TITLE2_LINK_CLASS_STRING#" => $arReplaceParams['link_class_string']))?></div>
	</div>

	<div class="mp-app-add-block-content">
		<div class="mp-app-add-block-box">
			<div class="mp-app-add-box-header">
				<?=GetMessage("MARKETPLACE_BLOCK1_TITLE")?>
			</div>
			<?=GetMessage("MARKETPLACE_BLOCK1_INFO")?>
			<a href="<?=$arParams['ADD_URL']?>" class="mp-app-add-green-btn"><?=GetMessage("MARKETPLACE_BUTTON_ADD")?></a>
		</div>

		<div class="mp-app-add-block-box mp-app-add-block-box-right">
			<div class="mp-app-add-box-header">
				<?=GetMessage("MARKETPLACE_BLOCK2_TITLE")?>
			</div>
			<?=GetMessage("MARKETPLACE_BLOCK2_INFO")?>
			<a href="<?=GetMessage("MARKETPLACE_BLOCK2_LINK")?>" target="_blank" class="mp-app-add-green-btn"><?=GetMessage("MARKETPLACE_BUTTON")?></a>
		</div>

		<div class="mp-app-add-block-or"><?=GetMessage("MARKETPLACE_OR")?></div>
	</div>
</div>

<?
if (in_array(LANGUAGE_ID, array('ua', 'ru')))
{
	?>
<script>
	BX.ready(function() {
		(function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
				(w[b].forms=w[b].forms||[]).push(arguments[0])};
			if(w[b]['forms']) return;
			s=d.createElement('script');r=1*new Date();s.async=1;s.src=u+'?'+r;
			h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
		})(window,document,'https://bitrix24.team/bitrix/js/crm/form_loader.js','b24form');

			<?=$arReplaceParams['js_params'];?>
	});
	</script>
	<?
}
?>