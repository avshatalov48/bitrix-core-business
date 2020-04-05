<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/bitrix24/styles.css');

$logoFile = "/bitrix/gadgets/bitrix/bitrix24/images/logo-".LANGUAGE_ID.".png";
if (!file_exists($_SERVER["DOCUMENT_ROOT"].$logoFile))
	$logoFile = "/bitrix/gadgets/bitrix/bitrix24/images/logo-en.png";
?>
<div class="bx-gadget-top-label-wrap"><div class="bx-gadget-top-label"><?echo GetMessage("GD_BITRIX24")?></div></div>
<div class="bx-gadget-title-wrap">
	<span class="bx-gadget-title-text"><?echo GetMessage("GD_BITRIX24_TITLE")?></span><img src="<?echo $logoFile?>" alt="Bitrix24"/>
</div>
<a class="bx-gadget-bitrix24-btn" href="<?echo htmlspecialcharsBx(GetMessage("GD_BITRIX24_LINK"));?>"><?echo GetMessage("GD_BITRIX24_BUTTON")?></a>
<div class="bx-gadget-bitrix24-text-wrap">
	<span class="bx-gadget-bitrix24-text-left"></span><span class="bx-gadget-bitrix24-text">
	<?echo GetMessage("GD_BITRIX24_LIST")?>
	<span class="bx-gadget-bitrix24-text-other"><?echo GetMessage("GD_BITRIX24_MORE")?></span> <br>
	</span>
</div>
<div class="bx-gadget-shield"></div>
