<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @global CMain $APPLICATION */
global $APPLICATION;

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/admin_marketpalce/styles.css');
?>

<div class="bx-gadgets-content-layout-perform">
	<div class="bx-gadgets-title"><?=GetMessage("GD_MARKETPLACE_TITLE")?></div>
	<div class="bx-gadget-bottom-cont">
		<a class="bx-gadget-button" target="_blank" href="https://marketplace.1c-bitrix.ru/">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?=GetMessage("GD_MARKETPLACE_VIEW")?></div>
		</a>
		<div class="bx-gadget-mark">
			<?=GetMessage("GD_MARKETPLACE_ADDITIONAL")?>
			<div class="bx-gadget-mark-desc"><?=GetMessage("GD_MARKETPLACE_ADDITIONAL2")?></div>
		</div>
	</div>
</div>
<div class="bx-gadget-shield"></div>

