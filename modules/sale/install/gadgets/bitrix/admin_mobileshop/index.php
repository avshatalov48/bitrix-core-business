<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @global CMain $APPLICATION */
global $APPLICATION;

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/admin_mobileshop/styles.css');
?>

<div class="bx-gadgets-content-layout-perform">
	<div class="bx-gadgets-title">
		<?=GetMessage("GD_MOBILESHOP_TITLE")?>
		<div class="bx-gadgets-title-2"><?=GetMessage("GD_MOBILESHOP_TITLE2")?></div>
	</div>
	<div class="bx-gadget-bottom-cont">
		<a class="bx-gadget-button" target="_blank" href="https://www.1c-bitrix.ru/products/mobile/adm.php">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?=GetMessage("GD_MOBILESHOP_VIEW")?></div>
		</a>
		<div class="bx-gadget-mark">
			<?=GetMessage("GD_MOBILESHOP_DOWNLOAD")?>
		</div>
		<div class="bx-gadget-mobile-icon">
			<a href="https://play.google.com/store/apps/details?id=com.bitrix.admin" target="_blank" class="bx-gadget-mobile-icon-andr"></a>
			<a href="https://itunes.apple.com/app/id621524973" target="_blank" class="bx-gadget-mobile-icon-ios"></a>
		</div>
	</div>
</div>
<div class="bx-gadget-shield"></div>

